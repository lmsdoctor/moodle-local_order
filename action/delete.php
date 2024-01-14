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

require_login();

use \core\output\notification;

global $PAGE, $DB, $USER;

$formdata = new stdClass();
$formdata->id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);


if (!is_siteadmin($USER->id) || $action != 'delete') {
    redirect(
        new moodle_url('/'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        notification::NOTIFY_WARNING
    );
}

if (!$transaction = $DB->get_record(TABLE_TRAN, (array) $formdata)) {
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
$details = $DB->get_records_sql($sql, (array) $formdata);
$plugin = enrol_get_plugin('payment');

foreach ($details as $detail) {
    // Enrol user.
    $enrol = $DB->get_record(
        'enrol',
        array('enrol' => 'payment', 'courseid' => $detail->courseid, 'status' => 0)
    );
    // Get the enrollment plugin.
    $plugin->unenrol_user($enrol, $detail->userid);
    $DB->delete_records(TABLE_DETAIL, array('id' => $detail->id));
}

$orderurl = new moodle_url(HOME);

$DB->delete_records(TABLE_SESSION, array('id' => $transaction->instanceid));
$DB->delete_records(TABLE_TRAN, (array) $formdata);


redirect($orderurl, get_string('orderdelete', PLUGINNAME), 0, notification::NOTIFY_SUCCESS);
