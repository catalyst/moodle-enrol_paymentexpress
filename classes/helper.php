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
 * Helper class.
 *
 * @package    enrol_paymentexpress
 * @author     Eugene Venter <eugene@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_paymentexpress;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/enrol/paymentexpress/extlib/PxPay_Curl.inc.php');

class helper {

    /**
     *
     * @return boolean true on success; false on failures
     */
    public static function process_pxpay_result($result, $instance=null) {
        global $DB;

        $plugin = enrol_get_plugin('paymentexpress');

        $pxpay = new \PxPay_Curl(
            self::get_pxpay_url(),
            $plugin->get_config('pxpayuserid'),
            $plugin->get_config('pxpaykey'));

        // getResponse method in PxPay object returns PxPayResponse object
        // which encapsulates all the response data.
        $rsp = $pxpay->getResponse($result);

        $success = $rsp->getSuccess();   // =1 when request succeeds

        if ($success !== "1") {
            return false;
        }

        if (empty($instance)) {
            $instanceid = $rsp->getTxnData3();
            $instance = $DB->get_record("enrol", array("id" => $instanceid, "enrol" => "paymentexpress", "status" => 0), "*", MUST_EXIST);
        }

        $merchantref = self::parse_merchant_reference($rsp->getMerchantReference());
        $userid = $merchantref->userid;

        if (is_enrolled(\context_course::instance($instance->courseid), $userid)) {
            // User already enrolled - happy day!
            return true;
        }

        self::record_pxpay_response($userid, $instance, $rsp);

        $responsetext = $rsp->getResponseText();

        // Enrol the user!
        $enrolstarttime = $enrolendtime = 0;
        if (!empty($instance->enrolperiod)) {
            $enrolstarttime = time();
            $enrolendtime = $enrolstarttime + $instance->enrolperiod;
        }
        $plugin->enrol_user($instance, $userid, $instance->roleid, $enrolstarttime, $enrolendtime);

        self::send_enrol_notifications($instance, $userid, $instance->courseid);

        return true;
    }

    public static function parse_merchant_reference($refstring) {
        $refarray = explode('-', $refstring);

        $refobject = new \stdClass();
        $refobject->userid = $refarray[0];
        $refobject->instanceid = $refarray[1];
        $refobject->courseid = $refarray[2];

        return $refobject;
    }

    public static function initiate_pxpay($instance, $data) {
        global $CFG, $PAGE, $USER, $COURSE;

        $plugin = enrol_get_plugin('paymentexpress');

        $pxpay = new \PxPay_Curl(
            self::get_pxpay_url(),
            $plugin->get_config('pxpayuserid'),
            $plugin->get_config('pxpaykey'));

        $request = new \PxPayRequest();

        $returnurl = new \moodle_url('/enrol/paymentexpress/fprn.php');
        $returnurl->param('c', $instance->courseid);

        $reference = "{$USER->id}-{$instance->id}-{$instance->courseid}";

        // Calculate localised and "." cost, make sure we send Payment Express the same value,
        // please note Payment Express expects amount with 2 decimal places and "." separator.
        // TODO: currencies without decimal division
        $localisedcost = format_float($instance->cost, 2, true);
        $cost = format_float($instance->cost, 2, false);

        // Generate a unique identifier for the transaction.
        $txnid = uniqid("ID");

        // Set PxPay properties
        $request->setMerchantReference($reference);
        $request->setAmountInput($cost);
        $request->setTxnData1(fullname($USER));
        $request->setTxnData2($COURSE->shortname);
        $request->setTxnData3($instance->id);
        $request->setTxnType("Purchase");
        $request->setCurrencyInput($instance->currency);
        $request->setEmailAddress($USER->email);
        $request->setUrlFail($returnurl->out());  // can be a dedicated failure page
        $request->setUrlSuccess($returnurl->out());  // can be a dedicated success page
        $request->setTxnId($txnid);

        // Call makeRequest function to obtain input XML
        $requeststring = $pxpay->makeRequest($request);

        // Obtain output XML
        $response = new \MifMessage($requeststring);

        // Parse output XML
        $url = $response->get_element_text('URI');
        $valid = $response->get_attribute("valid");
        if (empty($url) || $valid != 1) {
            $debugging = "PaymentExpress error: ".$response->get_element_text('Reco').' - '.$response->get_element_text('ResponseText');
            print_error('errcannotgeturi', 'enrol_paymentexpress', '', null, $debugging);
        }

        // Redirect to Px payment page
        header("Location: ".$url);
    }

    private static function send_enrol_notifications($instance, $userid, $courseid) {
        global $CFG, $DB;

        $plugin = enrol_get_plugin('paymentexpress');

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $context = \context_course::instance($course->id, MUST_EXIST);

        // Pass $view=true to filter hidden caps if the user cannot see them
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                             '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins   = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));

        $strmgr = get_string_manager();
        if (!empty($mailstudents)) {
            $a = new \stdClass();
            $a->coursename = format_string($course->fullname, true, array('context' => $context));
            $a->courseurl = "{$CFG->wwwroot}/course/view.php?id={$course->id}";
            $a->cost = format_float($instance->cost, 2, true);
            $a->currency = $instance->currency;
            $a->userfullname = fullname($user);

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paymentexpress';
            $eventdata->name              = 'paymentexpress_enrolment';
            $eventdata->userfrom          = empty($teacher) ? \core_user::get_noreply_user() : $teacher;
            $eventdata->userto            = $user;
            $eventdata->subject           = $strmgr->get_string("welcometocourse", 'enrol_paymentexpress', $shortname, $user->lang);
            $eventdata->fullmessage       = $strmgr->get_string('welcometocoursetext', 'enrol_paymentexpress', $a, $user->lang);
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml   = $strmgr->get_string('welcometocoursehtml', 'enrol_paymentexpress', $a, $user->lang);;
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailteachers) && !empty($teacher)) {
            $a->course = format_string($course->fullname, true, array('context' => $context));
            $a->user = fullname($user);
            $a->cost = format_float($instance->cost, 2, true);
            $a->currency = $instance->currency;

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paymentexpress';
            $eventdata->name              = 'paymentexpress_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $teacher;
            $eventdata->subject           = $strmgr->get_string("enrolmentnew", 'enrol', $shortname, $teacher->lang);
            $eventdata->fullmessage       = $strmgr->get_string('enrolmentnewuser', 'enrol', $a, $teacher->lang);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $context));
            $a->user = fullname($user);
            $a->cost = format_float($instance->cost, 2, true);
            $a->currency = $instance->currency;

            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new \core\message\message();
                $eventdata->courseid          = $course->id;
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_paymentexpress';
                $eventdata->name              = 'paymentexpress_enrolment';
                $eventdata->userfrom          = $user;
                $eventdata->userto            = $admin;
                $eventdata->subject           = $strmgr->get_string("enrolmentnew", 'enrol', $shortname, $admin->lang);
                $eventdata->fullmessage       = $strmgr->get_string('enrolmentnewuser', 'enrol', $a, $admin->lang);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
        }
    }

    private static function record_pxpay_response($userid, $instance, $response) {
        global $DB;

        $todb = new \stdClass;
        $todb->userid = $userid;
        $todb->enrolid = $instance->id;
        $todb->success = (int)$response->getSuccess();
        $todb->emailaddress = $response->getEmailAddress();
        $todb->responsetext = $response->getResponseText();
        $todb->authcode = $response->getAuthCode();
        $todb->txnid = $response->getTxnId();
        $todb->amount = $response->getAmountSettlement();
        $todb->txndata1 = $response->getTxnData1();
        $todb->txndata2 = $response->getTxnData2();
        $todb->txndata3 = $response->getTxnData3();
        $todb->merchantreference = $response->getMerchantReference();
        $todb->timemodified = time();

        $DB->insert_record('enrol_paymentexpress', $todb);
    }


    public static function get_pxpay_url() {
        global $CFG;

        if (empty($CFG->debugpxpay)) {
            return "https://sec.paymentexpress.com/pxaccess/pxpay.aspx";
        } else {
            return 'https://uat.paymentexpress.com/pxaccess/pxpay.aspx';
        }
    }
}
