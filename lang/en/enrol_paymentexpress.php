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
 * Strings for component 'enrol_paymentexpress', language 'en'.
 *
 * @package    enrol_paymentexpress
 * @author     Eugene Venter <eugene@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['alreadyenrolled'] = 'Already enrolled';
$string['assignrole'] = 'Assign role';
$string['cannotenrol'] = 'You cannot enrol using this enrolment method.';
$string['cannotenrolearly'] = 'You cannot enrol yet - enrolment starts on {$a}.';
$string['cannotenrollate'] = 'You cannot enrol any more - enrolment ended on {$a}.';
$string['cost'] = 'Enrol cost';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during Payment Express enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['errcannotgeturi'] = 'Unable to determine Payment Express payment URI. Please contact your site administrator.';
$string['errdisabled'] = 'Payment Express plugin is disabled and does not handle payment notifications.';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:paymentexpress_enrolment'] = 'Payment Express enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['paymentexpresssync'] = 'Payment Express sync';
$string['paymentexpress:config'] = 'Configure Payment Express enrol instances';
$string['paymentexpress:manage'] = 'Manage enrolled users';
$string['paymentexpress:unenrol'] = 'Unenrol users from course';
$string['paymentexpress:unenrolself'] = 'Unenrol self from the course';
$string['paymentexpressaccepted'] = 'Payment Express payments accepted';
$string['paymentsuccess'] = 'Payment successful!';
$string['paynow'] = 'Pay now';
$string['pluginname'] = 'Payment Express';
$string['pluginname_desc'] = 'The Payment Express module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['pxpayuserid'] = 'PxPay username';
$string['pxpaykey'] = 'PxPay key';
$string['status'] = 'Allow Payment Express enrolments';
$string['status_desc'] = 'Allow users to use Payment Express to enrol into a course by default.';
$string['transactionfailed'] = 'Transaction failed.';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Thanks, we\'ve received your payment of {$a->cost} {$a->currency} for {$a->coursename}.
View the course here: {$a->courseurl}';
$string['welcometocoursehtml'] = '<p>Thanks, we\'ve received your payment of {$a->cost} {$a->currency} for {$a->coursename}.</p>
<p><a href="{$a->courseurl}">View the course here</a></p>';
