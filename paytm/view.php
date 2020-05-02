<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * Prints a particular instance of paytm
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    availability_paytm
 * @copyright  22020 Sumit Negi <sumitnegi.933@gmail.com>
 * @author     Sumit Negi - based on code by others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('encdec_paytm.php');
$contextid = required_param('contextid', PARAM_INT);

$context = context::instance_by_id($contextid);
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
            print_error('no paytm condition for this context.');
        }
    }
} else {
    // TODO: handle sections.
    print_error('support to sections not yet implemented.');
}
$coursecontext = $context->get_course_context();
$course = $DB->get_record('course', array('id' => $coursecontext->instanceid));

require_login($course);

if ($paymenttnx = $DB->get_record('availability_paytm_tnx', array('userid' => $USER->id, 'contextid' => $contextid))) {

    if ($paymenttnx->payment_status == 'TXN_SUCCESS') {
        redirect($context->get_url(), get_string('paymentcompleted', 'availability_paytm'));
    }
}

$PAGE->set_url('/availability/condition/paytm/view.php', array('contextid' => $contextid));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

$PAGE->navbar->add($paytm->itemname);

echo $OUTPUT->header();

echo $OUTPUT->heading($paytm->itemname);

if ($paymenttnx && ($paymenttnx->payment_status == 'Pending')) {
    echo get_string('paymentpending', 'availability_paytm');
} else {

    // Calculate localised and "." cost, make sure we send paytm the same value,
    // please note paytm expects amount with 2 decimal places and "." separator.
    $localisedcost = format_float($paytm->cost, 2, true);
    $cost = format_float($paytm->cost, 2, false);

    if (isguestuser()) { // Force login only for guest user, not real users with guest role.
        if (empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
        } else {
            // This actually is not so secure ;-), 'cause we're in unencrypted connection...
            $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        }
        echo '<div class="mdl-align"><p>' . get_string('paymentrequired', 'availability_paytm') . '</p>';
        echo '<div class="mdl-align"><p>' . get_string('paymentwaitremider', 'availability_paytm') . '</p>';
        echo '<p><b>' . get_string('cost') . ": $instance->currency $localisedcost" . '</b></p>';
        echo '<p><a href="' . $wwwroot . '/login/">' . get_string('loginsite') . '</a></p>';
        echo '</div>';
    } else {
        // Sanitise some fields before building the paytm form.
        $userfullname = fullname($USER);
        $userfirstname = $USER->firstname;
        $userlastname = $USER->lastname;
        $useraddress = $USER->address;
        $usercity = $USER->city;
        ?>
        <p><?php print_string("paymentrequired", 'availability_paytm') ?></p>
        <p><b><?php echo get_string("cost") . ": {$paytm->currency} {$localisedcost}"; ?></b></p>
        <p><img alt="<?php print_string('paytmaccepted', 'availability_paytm') ?>"
                title="<?php print_string('paytmaccepted', 'availability_paytm') ?>"
                src="<?php echo $CFG->wwwroot . '/availability/condition/paytm/paytm.png' ?>" /></p>
        <p><?php print_string("paymentinstant", 'availability_paytm') ?></p>
        <?php
        $paytmurl = get_config('availability_paytm', 'transaction_url');
        $merchant_id = get_config('availability_paytm','merchant_id');
        $merchant_key = get_config('availability_paytm','merchant_key');
        ?>


        <?php
        // Sumit

        $formArray = array(
            /* 'return_url'=> $CFG->wwwroot.'/enrol/payfast/return.php?id='.$course->id,
              'cancel_url' => $CFG->wwwroot,
              'notify_url' => $CFG->wwwroot.'/enrol/payfast/itn.php',
              'name_first' => $userfirstname,
              'name_last' => $userlastname,
              'email_address'=> $USER->email,
              'm_payment_id' => "{$USER->id}-{$course->id}-{$instance->id}",
              'amount' => $cost,
              'item_name' => html_entity_decode( $courseshortname ),
              'item_description' => html_entity_decode( $coursefullname ), */
            "MID" => get_config('availability_paytm', 'merchant_id'),
            "MERC_UNQ_REF" => "{$USER->id}-{$course->id}-{$context->id}",
            "ORDER_ID" => uniqid("ORDR_"),
            "CUST_ID" => $USER->email,
            "WEBSITE" => get_config('availability_paytm', 'merchant_website'),
            "INDUSTRY_TYPE_ID" => get_config('availability_paytm', 'merchant_industrytype'),
            "EMAIL" => $USER->email,
            "CHANNEL_ID" => get_config('availability_paytm', 'merchant_channelid'),
            "TXN_AMOUNT" => $cost,
                //"CALLBACK_URL" => $CFG->wwwroot.'/enrol/paytm/itn.php',
        );

        if (get_config('availability_paytm', 'paytm_callback') == '1') {
            $formArray["CALLBACK_URL"] = $CFG->wwwroot . '/availability/condition/paytm/itn.php';
        }
        $checksum = paytm_getChecksumFromArray($formArray, $merchant_key);
        $formArray['CHECKSUMHASH'] = $checksum;

//        foreach ($formArray as $k => $v) {
//            echo '<input type="hidden" name=' . p($k) . ' value=' . p($v) . ' />';
//        }
        //echo '<input type="submit" value='.print_string("sendpaymentbutton", "availability_paytm").' />';

        include('paytm.html');
    }
    ?>
    <?php
}
// Finish the page.
echo $OUTPUT->footer();
?>
