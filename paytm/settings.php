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
 * paytm availability plugin settings and presets.
 *
 * @package    availability_paytm
 * @copyright  2020 Sumit Negi <sumitnegi.933@gmail.com>
 * @author     Sumit Negi - based on code by others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('availability_paytm_settings', '', get_string('pluginname_desc', 'availability_paytm')));

    $settings->add(new admin_setting_configtext('availability_paytm/merchant_id', get_string('merchant_id', 'availability_paytm'), get_string('merchant_id_desc', 'availability_paytm'), '', PARAM_ALPHANUM));

    // $settings->add(new admin_setting_configtext('availability_paytm/merchant_key', get_string( 'merchant_key', 'availability_paytm'), get_string('merchant_key_desc', 'availability_paytm'), '', '/^[a-zA-Z0-9-\(\)@.,_:#\/ ]*$/'));
    $settings->add(new admin_setting_configtext('availability_paytm/merchant_key', get_string('merchant_key', 'availability_paytm'), get_string('merchant_key_desc', 'availability_paytm'), '', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configtext('availability_paytm/merchant_website', get_string('merchant_website', 'availability_paytm'), get_string('merchant_website_desc', 'availability_paytm'), '', PARAM_ALPHANUM));

    $settings->add(new admin_setting_configtext('availability_paytm/merchant_industrytype', get_string('merchant_industrytype', 'availability_paytm'), get_string('merchant_industrytype_desc', 'availability_paytm'), '', PARAM_ALPHANUM));

    $settings->add(new admin_setting_configtext('availability_paytm/merchant_channelid', get_string('merchant_channelid', 'availability_paytm'), get_string('merchant_channelid_desc', 'availability_paytm'), '', PARAM_ALPHANUM));

    //$settings->add(new admin_setting_configtext('availability_paytm/merchant_passphrase', get_string('merchant_passphrase', 'enrol_payfast'), get_string('merchant_passphrase_desc', 'enrol_payfast'), '', '/^[a-zA-Z0-9-\(\)@.,_:#\/ ]*$/'));

    /* $options = array(
      'test'  => get_string('paytm_test', 'availability_paytm'),
      'live'  => get_string('paytm_live', 'availability_paytm')
      );
      $settings->add(new admin_setting_configselect('availability_paytm/paytm_mode', get_string('paytm_mode', 'availability_paytm'), get_string('paytm_mode_desc', 'availability_paytm'), 'test', $options)); */

    $settings->add(new admin_setting_configtext('availability_paytm/transaction_url', get_string('transaction_url', 'availability_paytm'), get_string('transaction_url_desc', 'availability_paytm'), '', 0));
    $settings->add(new admin_setting_configtext('availability_paytm/transaction_status_url', get_string('transaction_status_url', 'availability_paytm'), get_string('transaction_status_url_desc', 'availability_paytm'), '', 0));

    $settings->add(new admin_setting_configcheckbox('availability_paytm/paytm_callback', get_string('paytm_callback', 'availability_paytm'), get_string('paytm_callback_desc', 'availability_paytm'), 1));

    $settings->add(new admin_setting_configcheckbox('availability_paytm/mailstudents', get_string('mailstudents', 'availability_paytm'), '', 0));

    $settings->add(new admin_setting_configcheckbox('availability_paytm/mailteachers', get_string('mailteachers', 'availability_paytm'), '', 0));

    $settings->add(new admin_setting_configcheckbox('availability_paytm/mailadmins', get_string('mailadmins', 'availability_paytm'), '', 0));

//    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
//    //       it describes what should happen when users are not supposed to be enrolled any more.
//    $options = array(
//        ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
//        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
//        ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol'),
//    );
//    $settings->add(new admin_setting_configselect('availability_paytm/expiredaction', get_string('expiredaction', 'availability_paytm'), get_string('expiredaction_help', 'availability_paytm'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));
//
//    //--- enrol instance defaults ----------------------------------------------------------------------------
//    $settings->add(new admin_setting_heading('availability_paytm_defaults', get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));
//
//    $options = array(ENROL_INSTANCE_ENABLED => get_string('yes'),
//        ENROL_INSTANCE_DISABLED => get_string('no'));
//    $settings->add(new admin_setting_configselect('availability_paytm/status', get_string('status', 'availability_paytm'), get_string('status_desc', 'availability_paytm'), ENROL_INSTANCE_DISABLED, $options));
//
//    $settings->add(new admin_setting_configtext('availability_paytm/cost', get_string('cost', 'availability_paytm'), '', 0, PARAM_FLOAT, 4));
//     $codes = array(
//            'INR');
//        $currencies = array();
//        foreach ($codes as $c) {
//            $currencies[$c] = new \lang_string($c, 'core_currencies');
//        }
//    $paytmcurrencies = $currencies;
//    $settings->add(new admin_setting_configselect('availability_paytm/currency', get_string('currency', 'availability_paytm'), '', 'INR', $paytmcurrencies));
//
//    if (!during_initial_install()) {
//        $options = get_default_enrol_roles(context_system::instance());
//        $student = get_archetype_roles('student');
//        $student = reset($student);
//        $settings->add(new admin_setting_configselect('availability_paytm/roleid', get_string('defaultrole', 'availability_paytm'), get_string('defaultrole_desc', 'availability_paytm'), $student->id, $options));
//    }
//
//    $settings->add(new admin_setting_configduration('availability_paytm/enrolperiod', get_string('enrolperiod', 'availability_paytm'), get_string('enrolperiod_desc', 'availability_paytm'), 0));
}
