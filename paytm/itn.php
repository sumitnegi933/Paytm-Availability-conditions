<?php

// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Listens for Instant Payment Notification from Paytm
 *
 * This script waits for Payment notification from Paytm,
 * then double checks that data by sending it back to Paytm.
 * If Paytm verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    availability_paytm
 * @copyright 2020 Sumit Negi
 * @author     Sumit Negi - based on code by others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
//define('NO_DEBUG_DISPLAY', true);


require_once('../../../config.php');
global $CFG, $DB;
require_once($CFG->libdir . '/filelib.php');

// Ignore include of paytm_common.inc and encdec_paytm.php file if paytm enrollment plugin is enrolled and exist
//if (!file_exists($CFG->dirroot.'/enrol/paytm/paytm_common.inc')) {
    require_once("paytm_common.inc");
//}
//if (!file_exists($CFG->dirroot.'/enrol/paytm/encdec_paytm.php')) {
    require_once("encdec_paytm.php");
//}

//set_exception_handler('availability_paytm_itn_exception_handler');
/// Keep out casual intruders
if (empty($_POST) or ! empty($_GET)) {
    print_error($_REQUEST['errorMessage']);
}

$pfError = false;
$pfErrMsg = '';
$pfDone = false;
$pfData = array();
$pfParamString = '';

$data = new stdClass();

foreach ($_POST as $key => $value) {
    $data->$key = $value;
}
$custom = explode('-', $data->MERC_UNQ_REF);
$data->userid = (int) $custom[0];
$data->courseid = (int) $custom[1];
$data->contextid = (int) $custom[2];
$data->payment_currency = 'INR';
$data->timeupdated = time();


/// get the user and course records

if (!$user = $DB->get_record("user", array("id" => $data->userid))) {
    $pfError = true;
    $pfErrMsg .= "Not a valid user id \n";
}

if (!$course = $DB->get_record("course", array("id" => $data->courseid))) {
    $pfError = true;
    $pfErrMsg .= "Not a valid course id \n";
}

if (!$context = context::instance_by_id($data->contextid, IGNORE_MISSING)) {
    $pfError = true;
    $pfErrMsg .= "Not a valid context id \n";
}


$instanceid = $context->instanceid;
if ($context instanceof context_module) {
    $availability = $DB->get_field('course_modules', 'availability', array('id' => $instanceid), MUST_EXIST);
    $availability = json_decode($availability);
    foreach ($availability->c as $condition) {
        if ($condition->type == 'paytm') {
            // TODO: handle more than one paytm for this context.
            $paytm = $condition;
            break;
        } else {
            $pfError = true;
            $pfErrMsg .= "Not a valid context id \n";
        }
    }
} else {
    // TODO: handle sections.
    print_error('support to sections not yet implemented.');
}

//// Notify Paytm that information has been received
if (!$pfError && !$pfDone) {
    header('HTTP/1.0 200 OK');
    flush();
}


//// Get data sent by Paytm
if (!$pfError && !$pfDone) {
    // Posted variables from ITN
    $pfData = paytm_pfGetData();
    if ($pfData === false) {
        $pfError = true;
        $pfErrMsg = PF_ERR_BAD_ACCESS;
    }
}


//// Check data against internal order
if (!$pfError && !$pfDone) {
    if ((float) $paytm->cost < 0) {
        $cost = (float) 0;
    } else {
        $cost = (float) $paytm->cost;
    }

    // Use the same rounding of floats as on the plugin form.
    $cost = format_float($cost, 2, false);

    if (number_format($data->TXNAMOUNT, 2) < $cost) {
        message_paytm_error_to_admin("Amount paid is not enough ({$data->txnamount} < {$cost}))", $data);
        die;
    }
}




if (!$pfError && !$pfDone) {
    if ($existing = $DB->get_record("availability_paytm_tnx", array("txnid" => $data->TXNID))) {   // Make sure this transaction doesn't exist already
        $pfErrMsg .= "Transaction $data->TXNID is being repeated! \n";
        $pfError = true;
    }
    if ($data->payment_currency != $paytm->currency) {
        $pfErrMsg .= "Currency does not match activity settings, received: " . $data->mc_currency . "\n";
        $pfError = true;
    }

    if (!$user = $DB->get_record('user', array('id' => $data->userid))) {   // Check that user exists
        $pfErrMsg .= "User $data->userid doesn't exist \n";
        $pfError = true;
    }

    if (!$course = $DB->get_record('course', array('id' => $data->courseid))) { // Check that course exists
        $pfErrMsg .= "Course $data->courseid doesn't exist \n";
        $pfError = true;
    }
}


//// Check status and update order
if (!$pfError && !$pfDone) {
    $merchant_key = get_config('availability_paytm', 'merchant_key');
    $merchant_id = get_config('availability_paytm', 'merchant_id');
    // $paytm_mode = $plugin->get_config( 'paytm_mode' ); 	
    $transaction_status_url = get_config('availability_paytm', 'transaction_status_url');
    $paramList = $pfData;
    $paytmChecksum = isset($paramList["CHECKSUMHASH"]) ? $paramList["CHECKSUMHASH"] : "";
    $isValidChecksum = paytm_verifychecksum_e($paramList, $merchant_key, $paytmChecksum);

    $transaction_id = $pfData['TXNID'];
    $coursemod = get_activity_from_cmid($instanceid);
    if ($isValidChecksum == "1" || $isValidChecksum == "TRUE") {
        switch ($pfData['STATUS']) {
            case 'TXN_SUCCESS':

                // Create an array having all required parameters for status query.
                $requestParamList = array("MID" => $merchant_id, "ORDERID" => $paramList['ORDERID']);

                $StatusCheckSum = paytm_getChecksumFromArray($requestParamList, $merchant_key);

                $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;

                $check_status_url = $transaction_status_url;
                $responseParamList = paytm_callNewAPI($check_status_url, $requestParamList);
                if ($responseParamList['STATUS'] == 'TXN_SUCCESS' && $responseParamList['TXNAMOUNT'] == $paramList["TXNAMOUNT"]) {
                    $coursecontext = context_course::instance($course->id, IGNORE_MISSING);
                    if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC', '', '', '', '', false, true)) {
                        $users = sort_by_roleassignment_authority($users, $context);
                        $teacher = array_shift($users);
                    } else {
                        $teacher = false;
                    }

                    $mailstudents = get_config('availability_paytm', 'mailstudents');
                    $mailteachers = get_config('availability_paytm', 'mailteachers');
                    $mailadmins = get_config('availability_paytm', 'mailadmins');

                    $shortname = format_string($course->shortname, true, array('context' => $context));

                    if (!empty($mailstudents)) {
                        $a = new stdClass();
                        $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
                        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";
                        $a->activityname = format_string($coursemod->name);
                        $eventdata = new \core\message\message();
                        $eventdata->courseid = $course->id;
                        $eventdata->modulename = 'moodle';
                        $eventdata->component = 'availability_paytm';
                        $eventdata->name = 'availability_paytm';
                        $eventdata->userfrom = empty($teacher) ? get_admin() : $teacher;
                        $eventdata->userto = $user;
                        $eventdata->subject = get_string("activityaccessnew", 'availability_paytm', $coursemod->name);
                        $eventdata->fullmessage = get_string('activityacess', 'availability_paytm', $a);
                        $eventdata->fullmessageformat = FORMAT_PLAIN;
                        $eventdata->fullmessagehtml = '';
                        $eventdata->smallmessage = '';
                        message_send($eventdata);
                    }

                    if (!empty($mailteachers) && !empty($teacher)) {
                        $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
                        $a->user = fullname($user);
                        $a->activityname = format_string($coursemod->name);
                        $eventdata = new \core\message\message();
                        $eventdata->modulename = 'moodle';
                        $eventdata->component = 'availability_paytm';
                        $eventdata->name = 'availability_paytm';
                        $eventdata->userfrom = $user;
                        $eventdata->userto = $teacher;
                        $eventdata->subject = get_string("activityaccessnew", 'availability_paytm', $coursemod->name);
                        $eventdata->fullmessage = get_string('activityacess', 'availability_paytm', $a);
                        $eventdata->fullmessageformat = FORMAT_PLAIN;
                        $eventdata->fullmessagehtml = '';
                        $eventdata->smallmessage = '';
                        message_send($eventdata);
                    }

                    if (!empty($mailadmins)) {
                        $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
                        $a->user = fullname($user);
                        $a->activityname = format_string($coursemod->name);
                        $admins = get_admins();
                        foreach ($admins as $admin) {
                            $eventdata = new \core\message\message();
                            $eventdata->modulename = 'moodle';
                            $eventdata->component = 'availability_paytm';
                            $eventdata->name = 'availability_paytm';
                            $eventdata->userfrom = $user;
                            $eventdata->userto = $admin;
                            $eventdata->subject = get_string("activityaccessnew", 'availability_paytm', $coursemod->name);
                            $eventdata->fullmessage = get_string('activityacess', 'availability_paytm', $a);
                            $eventdata->fullmessageformat = FORMAT_PLAIN;
                            $eventdata->fullmessagehtml = '';
                            $eventdata->smallmessage = '';
                            message_send($eventdata);
                        }
                    }
                    $fullname = format_string($coursemod->name);
                    $data = (object) array_change_key_case((array) $data, CASE_LOWER);
                    $data->txndate = strtotime($data->txndate);
                    $DB->insert_record("availability_paytm_tnx", $data);
                    $destination = "$CFG->wwwroot/mod/$coursemod->modname/view.php?id=$coursemod->id";
                    redirect($destination, get_string('paymentthanks', 'availability_paytm', $fullname));
                } else {
                    echo "<b>It seems some issue in server to server communication. Kindly connect with administrator.</b>";
                    exit;
                }
                break;

            case 'TXN_FAILURE':
                $a = new stdClass();
                $a->teacher = get_string('defaultcourseteacher');
                $a->fullname = $coursemod->name;
                $destination = "$CFG->wwwroot/mod/$coursemod->modname/view.php?id=$coursemod->id";
                notice(get_string('paymentsorry', 'availability_paytm', $a), $destination);
                $data = (object) array_change_key_case((array) $data, CASE_LOWER);
                $data->txndate = strtotime($data->txndate);
                $DB->insert_record("availability_paytm_tnx", $data, false);
                break;

            case 'OPEN':
                $eventdata = new \core\message\message();
                $eventdata->modulename = 'moodle';
                $eventdata->component = 'availability_paytm';
                $eventdata->name = 'availability_paytm';
                $eventdata->userfrom = get_admin();
                $eventdata->userto = $user;
                $eventdata->subject = "Moodle: Paytm payment";
                $eventdata->fullmessage = "Your Paytm payment is pending.";
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml = '';
                $eventdata->smallmessage = '';
                message_send($eventdata);

                message_paytm_error_to_admin("Payment pending", $data);
                $data = (object) array_change_key_case((array) $data, CASE_LOWER);
                $data->txndate = strtotime($data->txndate);
                $DB->insert_record("availability_paytm_tnx", $data, false);
                break;

            default:
                // If unknown status, do nothing (safest course of action)
                $data = (object) array_change_key_case((array) $data, CASE_LOWER);
                $data->txndate = strtotime($data->txndate);
                $DB->insert_record("availability_paytm_tnx", $data, false);
                break;
        }
    } else {
        echo "<b>Checksum mismatched.</b>";
        exit;
    }
} else {
    message_paytm_error_to_admin("Received an invalid payment notification!! (Fake payment?)\n" . $pfErrMsg, $data);
    die('ERROR encountered, view the logs to debug.');
}

exit;

//--- HELPER FUNCTIONS --------------------------------------------------------------------------------------


function message_paytm_error_to_admin($subject, $data) {
    $admin = get_admin();
    $site = get_site();

    $message = "$site->fullname:  Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= "$key => $value\n";
    }

    $eventdata = new \core\message\message();
    $eventdata->modulename = 'moodle';
    $eventdata->component = 'availability_paytm';
    $eventdata->name = 'availability_paytm';
    $eventdata->userfrom = $admin;
    $eventdata->userto = $admin;
    $eventdata->subject = "PAYTM ERROR: " . $subject;
    $eventdata->fullmessage = $message;
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml = '';
    $eventdata->smallmessage = '';
    // pflog( 'Error To Admin: ' . print_r( $eventdata, true ) );
    message_send($eventdata);
}

/**
 * Silent exception handler.
 *
 * @param Exception $ex
 * @return void - does not return. Terminates execution!
 */
function availability_paytm_itn_exception_handler($ex) {
    global $OUTPUT;
    $info = get_exception_info($ex);
    if (isset($_REQUEST['errorMessage'])) {
        echo $OUTPUT->header();
        echo "<div class='alert alert-danger'>" . $_REQUEST['errorMessage'] . "</div>";

        echo $OUTPUT->footer();
    }
    $logerrmsg = "availability_paytm ITN exception handler: " . $info->message;
    $logerrmsg .= ' Debug: ' . $info->debuginfo . "\n" . format_backtrace($info->backtrace, true);

    error_log($logerrmsg);

    exit(0);
}

function get_activity_from_cmid($cmid) {
    global $CFG, $DB;
    if (!$cmrec = $DB->get_record_sql("SELECT cm.*, md.name as modname
                               FROM {course_modules} cm,
                                    {modules} md
                               WHERE cm.id = ? AND
                                     md.id = cm.module", array($cmid))) {
        availability_paytm_itn_exception_handler('Invalid Course Module');
    } elseif (!$modrec = $DB->get_record($cmrec->modname, array('id' => $cmrec->instance))) {
        availability_paytm_itn_exception_handler('Invalid Course Module');
    }
    $modrec->instance = $modrec->id;
    $modrec->cmid = $cmrec->id;
    $cmrec->name = $modrec->name;

    return $cmrec;
}
