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
 * Payment Express enrolments plugin settings and presets.
 *
 * @package    enrol_paymentexpress
 * @author     Eugene Venter <eugene@cataylst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // --- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_paymentexpress_settings', '', get_string('pluginname_desc', 'enrol_paymentexpress')));

    $settings->add(new admin_setting_configtext('enrol_paymentexpress/pxpayuserid', get_string('pxpayuserid', 'enrol_paymentexpress'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('enrol_paymentexpress/pxpaykey', get_string('pxpaykey', 'enrol_paymentexpress'), '', '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('enrol_paymentexpress/mailstudents', get_string('mailstudents', 'enrol_paymentexpress'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_paymentexpress/mailteachers', get_string('mailteachers', 'enrol_paymentexpress'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_paymentexpress/mailadmins', get_string('mailadmins', 'enrol_paymentexpress'), '', 0));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    // it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_paymentexpress/expiredaction',
        get_string('expiredaction', 'enrol_paymentexpress'), get_string('expiredaction_help', 'enrol_paymentexpress'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    // --- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_paymentexpress_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_paymentexpress/status',
        get_string('status', 'enrol_paymentexpress'), get_string('status_desc', 'enrol_paymentexpress'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_paymentexpress/cost', get_string('cost', 'enrol_paymentexpress'), '', 0, PARAM_FLOAT, 4));

    $paymentexpresscurrencies = enrol_get_plugin('paymentexpress')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_paymentexpress/currency', get_string('currency', 'enrol_paymentexpress'), '', 'USD', $paymentexpresscurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_paymentexpress/roleid',
            get_string('defaultrole', 'enrol_paymentexpress'), get_string('defaultrole_desc', 'enrol_paymentexpress'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_paymentexpress/enrolperiod',
        get_string('enrolperiod', 'enrol_paymentexpress'), get_string('enrolperiod_desc', 'enrol_paymentexpress'), 0));
}
