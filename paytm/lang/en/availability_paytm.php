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
 * @package availability_paytm
 * @copyright  2020 Sumit Negi <sumitnegi.933@gmail.com>
 * @author     Sumit Negi - based on code by others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['ajaxerror'] = 'Error contacting server';
$string['businessemail'] = 'Business email';
$string['continue'] = 'Click here and go back to Moodle';
$string['cost'] = 'Cost';
$string['currency'] = 'Currency';
$string['description'] = 'Require users to make a payment via Paytm to access the activity or resource.';
$string['error_businessemail'] = 'You must provide a business email.';
$string['error_cost'] = 'You must provide a cost and it must be greater than 0.';
$string['error_itemname'] = 'You must provide an item name.';
$string['error_itemnumber'] = 'You must provide an item number.';
$string['itemname'] = 'Item name';
$string['itemname_help'] = 'Name of the item to be shown on Paytm form';
$string['itemnumber'] = 'Item number';
$string['itemname_help'] = 'Number of the item to be shown on Paytm form';
$string['notdescription'] = 'you have not sent a <a href="{$a}">payment with Paytm</a>';
$string['paymentcompleted'] = 'Your payment was accepted and now you can access the activity or resource. Thank you.';
$string['paymentinstant'] = 'Use the button below to pay and access the activity or resource.';
$string['paymentpending'] = 'There is a pending payment registered for you.';
$string['paymentrequired'] = 'You must make a payment access the activity or resource.';
$string['paymentwaitreminder'] = 'Please note that if you already made a payment recently, it should be processing. Please wait a few minutes and refresh this page.';
$string['paytmaccepted'] = 'Paytm payments accepted';
$string['pluginname'] = 'Paytm Availability';
$string['pluginname_desc'] = 'Paytm Availability plugin will enable to set payment for the activity access in the course';
$string['sendpaymentbutton'] = 'Pay Now';
$string['title'] = 'Paytm payment';
$string['activityaccessnew'] = 'New acivity access in {$a}';
$string['activityacess'] = '{$a->user} has been granted acess of "{$a->activityname}" in course "{$a->course}"';
$string['merchant_id'] = 'Paytm Merchant ID';
$string['merchant_id_desc'] = 'The Merchant ID provided by Paytm';
$string['merchant_key'] = 'Paytm Merchant Key';
$string['merchant_key_desc'] = 'The Merchant Key provided by Paytm';
$string['merchant_website'] = 'Paytm Merchant Website';
$string['merchant_website_desc'] = 'The Merchant Website provided by Paytm';
$string['merchant_industrytype'] = 'Industry Type';
$string['merchant_industrytype_desc'] = 'The Industry type provided by Paytm';
$string['merchant_channelid'] = 'Channel Type';
$string['merchant_channelid_desc'] = 'The Channel type provided by Paytm';
$string['merchant_passphrase'] = 'Paytm Secure Passphrase';
$string['merchant_passphrase_desc'] = 'DO NOT SET THIS UNLESS YOU HAVE SET IT ON THE Paytm WEBSITE';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['paytm:config'] = 'Configure Paytm enrol instances';
$string['paytm:manage'] = 'Manage enrolled users';
$string['paytm:unenrol'] = 'Unenrol users from course';
$string['paytm:unenrolself'] = 'Unenrol self from the course';
$string['paytm_live'] = 'Live Mode';
$string['paytm_test'] = 'Sandbox Mode';
$string['paytm_mode'] = 'Paytm Mode';
$string['paytm_mode_desc'] = 'Testing or Live Mode';
$string['transaction_url'] = 'Paytm Transaction URL';
$string['transaction_url_desc'] = 'The Transaction URL provided by Paytm';
$string['transaction_status_url'] = 'Paytm Transaction Status URL';
$string['transaction_status_url_desc'] = 'The Transaction Status URL provided by Paytm';
$string['paytm_callback'] = 'Enable Callback Mode';
$string['paytm_callback_desc'] = 'Uncheck to disable the callback url';
$string['eitherdescription'] = 'you must pay <b>{$a->cost}</b> to access this activity  <a class="btn btn-primary" href="{$a->url}">Pay Now</a>';
$string['paymentthanks'] = 'Thank you for your payment! You are now got access of your activity: {$a}';
$string['paymentsorry'] = 'Thank you for your payment!  Unfortunately your payment has not yet been fully processed, and you are not yet get access to enter the activity "{$a->fullname}".  Please try continuing to the course in a few seconds, but if you continue to have trouble then please alert the {$a->teacher} or the site administrator';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:availability_paytm'] = 'Paytm availability messages';