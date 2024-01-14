<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Order form page.
 *
 * @package    local_order
 * @copyright  2021 Andres, David Q.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__, 4) . '/config.php');
require_once(dirname(__FILE__, 2) . '/global.php');
require("$CFG->libdir/tablelib.php");

require_login();

use \core\output\notification;

global $PAGE, $DB, $USER;

$formdata = new stdClass();
$formdata->id = optional_param('id', 0, PARAM_INT);
$formdata->paymentstatus = optional_param('paymentstatus', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

$status = array('pending', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed');

if (!is_siteadmin($USER->id) || $action != 'edit' || !$formdata->id || !in_array($formdata->paymentstatus, $status, true)) {
    redirect(
        new moodle_url('/'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        notification::NOTIFY_WARNING
    );
}

if (!$transaction = $DB->get_record(TABLE_TRAN, array('id' => $formdata->id))) {
    redirect(
        new moodle_url('/'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        notification::NOTIFY_WARNING
    );
}

$sql = 'SELECT d.id, d.courseid, t.userid
          FROM {' . TABLE_DETAIL . '} d
          JOIN {' . TABLE_TRAN . '} t ON t.instanceid = d.sessionid
         WHERE t.id = :id';
$details = $DB->get_records_sql($sql, array('id' => $formdata->id));

$isenrolment = $formdata->paymentstatus != 'completed';

foreach ($details as $detail) {
    // Enrol user.
    $params = array('enrol' => 'payment', 'courseid' => $detail->courseid, 'status' => 0);
    $enrol = $DB->get_record('enrol', $params);
    // Get the enrollment plugin.
    $plugin = enrol_get_plugin($enrol->enrol);

    // Set the enrollment period.
    $timestart = 0;
    $timeend   = 0;
    if ($enrol->enrolperiod) {
        $timestart = time();
        $timeend   = $timestart + $enrol->enrolperiod;
    }

    $plugin->enrol_user($enrol, $transaction->userid, $enrol->roleid, $timestart, $timeend, $isenrolment);
}

$orderurl = new moodle_url(HOME);

$DB->update_record(TABLE_TRAN, $formdata);

redirect($orderurl, get_string('updated', PLUGINNAME), 0, notification::NOTIFY_SUCCESS);
