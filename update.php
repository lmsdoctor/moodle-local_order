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
 * Update order form page.
 *
 * @package    local_order
 * @copyright  2021 Andres, David Q.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

global $PAGE, $DB, $USER;

define('PLUGIN', 'local_order');
define('TABLE', 'enrol_payment_transactionv2');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$urlparams = array('id' => $id, 'action' => $action);
$orderurl = new moodle_url('/local/order/index.php');
$pageurl = new moodle_url('/local/order/update.php', $urlparams);

// Javascript/jQuery code is found in amd/src/confirm.js.
$PAGE->requires->js_call_amd('local_order/confirm', 'init');

$strupdateorder = get_string('updateorder', PLUGIN);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_title($strupdateorder);
$PAGE->set_heading($strupdateorder);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', PLUGIN));
$PAGE->navbar->add(get_string('order', PLUGIN), $orderurl);
$PAGE->navbar->add($strupdateorder);

// Action delete.
if ($action === 'delete') {
    $DB->delete_records(TABLE, array('id' => $id));
    redirect($orderurl, get_string('deleted', PLUGIN), 0, \core\output\notification::NOTIFY_SUCCESS);
}

$transaction = $DB->get_record(TABLE, array('id' => $id));
$transaction->action = $action;

$mform = new \local_order\form\order_form(null, $transaction);

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    redirect($orderurl);
} else if ($formdata = $mform->get_data()) {

    // Those are for single transactions.
    if ($formdata->action === 'edit') {

        // Unenroll the student for each course if the status is not completed.
        // if ($formdata->paymentstatus != 'completed') {
        //     $sql = 'SELECT d.id, d.courseid, t.userid
        //           FROM {enrol_payment_detail} d
        //           JOIN {enrol_payment_transaction} t ON t.instanceid = d.sessionid
        //          WHERE t.id = :id';
        //     $details = $DB->get_records_sql($sql, array('id' => $formdata->id));
        //     // Get the enrollment plugin.
        //     $plugin = enrol_get_plugin('payment');

        //     foreach ($details as $detail) {
        //         // Enrol user.
        //         $params = array('enrol' => 'payment', 'courseid' => $detail->courseid, 'status' => 0);
        //         $plugininstance = $DB->get_record('enrol', $params);
        //         $plugin->unenrol_user($plugininstance, $detail->userid);
        //     }
        // }
        // print_object($formdata);
        // die();
        $DB->update_record(TABLE, $formdata);
        $status = get_string('updated', PLUGIN);
        $id = $formdata->id;
    }

    if ($formdata->action === 'add') {
        $DB->insert_record(TABLE, $formdata, true, true);
        $status = get_string('saved', PLUGIN);
    }

    redirect($orderurl, $status, 0, \core\output\notification::NOTIFY_SUCCESS);

} else {

    // Set default data (if any).
    $toform = array(
        'id' => $id,
        'status' => $transaction->status,
        'action' => $action,
        'userid' => $USER->id
    );

    $mform->set_data($toform);

    // Display the info.
    echo $OUTPUT->header();
    echo html_writer::div($strupdateorder, 'mb-5 alert alert-primary');
    $mform->display();
    echo $OUTPUT->footer();

}
