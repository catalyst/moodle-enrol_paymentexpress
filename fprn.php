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
 * Listens for Fail-proof Result Notifications (FPRN) from PaymenExpress.
 *
 * @package    enrol_paymentexpress
 * @author     Eugene Venter <eugene@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
// define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine This script does not require login.
require("../../config.php");
require_once($CFG->libdir.'/enrollib.php');

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('paymentexpress')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_paymentexpress');
}

$result = optional_param('result', '', PARAM_ALPHANUM);
$courseid = required_param('c', PARAM_INT);  // Don't use this for anything important.

// Keep out casual intruders.
if (empty($result) || empty($_GET) || !empty($_POST) ) {
    http_response_code(400);
    throw new moodle_exception('invalidrequest', 'core_error');
}

$PAGE->set_context(context_system::instance());

// Determine if this request is straight from PX's servers (FPRN).
$directfrompx = false;
if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'PX') === 0) {
    $directfrompx = true;
}

if (\enrol_paymentexpress\helper::process_pxpay_result($result)) {
    // Success!
    if (!$directfrompx) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        redirect($courseurl, get_string('paymentsuccess', 'enrol_paymentexpress'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }
} else {
    // Failure...
    if ($directfrompx) {
        http_response_code(404);
    } else {
        $enrolurl = new moodle_url('/enrol/index.php', array('id' => $courseid));
        redirect($enrolurl, get_string('transactionfailed', 'enrol_paymentexpress'), null,
            \core\output\notification::NOTIFY_ERROR);
    }
}
