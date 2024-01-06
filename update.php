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

require_once(__DIR__ . '/../../config.php');
require_once(dirname(__FILE__) . '/global.php');
require("$CFG->libdir/tablelib.php");

require_login();

use \core\output\notification;
use \local_order\form\order_form;

global $PAGE, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$urlparams = array('id' => $id, 'action' => $action);

$orderurl = new moodle_url(HOME);
$pageurl = new moodle_url(UPDATEURL, (array)$urlparams);

$strupdateorder = get_string('updateorder', PLUGINNAME);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($orderurl);
$PAGE->set_title($strupdateorder);
$PAGE->set_heading($strupdateorder);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', PLUGINNAME));
$PAGE->navbar->add('Order', $orderurl);
$PAGE->navbar->add($strupdateorder);

$hasaction = in_array($action, array('delete', 'add', 'edit'), true);

if (!$id) {
    redirect($orderurl);
}

// Action delete.
if ($action === 'delete') {
    $DB->delete_records(TABLE_TRAN, array('id' => $id));
    redirect($orderurl, get_string('deleted', PLUGINNAME), 0, notification::NOTIFY_SUCCESS);
}

$table = new local_order\detail_table('uniqueid');
// Javascript/jQuery code is found in amd/src/confirm.js.
$PAGE->requires->js_call_amd('local_order/confirm', 'init', [$pageurl]);

$transaction = $DB->get_record(TABLE_TRAN, array('id' => $id));
$transaction->action = $action;

$mform = new order_form($pageurl, (array)$transaction);

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    redirect($orderurl);
} else if ($formdata = $mform->get_data()) {

    // Those are for single transactions.
    if ($formdata->action === 'edit') {

        // Unenroll the student for each course if the status is not completed.
        if ($formdata->paymentstatus != 'completed') {
            $sql = 'SELECT d.id, d.courseid, t.userid
                      FROM {' . TABLE_DETAIL . '} d
                      JOIN {' . TABLE_TRAN . '} t ON t.instanceid = d.sessionid
                     WHERE t.id = :id';
            $details = $DB->get_records_sql($sql, array('id' => $formdata->id));
            // Get the enrollment plugin.
            $plugin = enrol_get_plugin('payment');

            foreach ($details as $detail) {
                // Enrol user.
                $params = array('enrol' => 'payment', 'courseid' => $detail->courseid, 'status' => 0);
                $plugininstance = $DB->get_record('enrol', $params);
                $plugin->unenrol_user($plugininstance, $detail->userid);
            }
        }

        $DB->update_record(TABLE_TRAN, $formdata);
        $status = get_string('updated', PLUGINNAME);
        $id = $formdata->id;
    }

    if ($formdata->action === 'add') {
        $id = $DB->insert_record(TABLE_TRAN, $formdata, true, true);
        $status = get_string('saved', PLUGINNAME);
    }

    if ($hasaction) {
        redirect($orderurl, $status, 0, notification::NOTIFY_SUCCESS);
    }
} else {

    // Set default data (if any).
    $toform = array(
        'id' => $id,
        'paymentstatus' => $transaction->paymentstatus,
        'action' => $action,
        'userid' => $USER->id
    );

    $mform->set_data($toform);

    // Display the info.
    echo $OUTPUT->header();
    echo html_writer::div($strupdateorder, 'mb-5 alert alert-primary');
    $mform->display();
}

// Work out the sql for the table.
$table->set_sql(
    'd.id, d.sessionid, d.courseid, d.amount, d.currency, c.taxpercent',
    "{" . TABLE_DETAIL . "} d
    JOIN {" . TABLE_SESSION . "} c ON c.id = d.sessionid",
    'd.sessionid = ?',
    array($transaction->instanceid)
);


echo html_writer::tag(
    'div',
    html_writer::tag(
        'div',
        '<h3 class="display-5">' . get_string('courseslinkedtransaction', PLUGINNAME) . '</h3>' .
            '<p class="lead">' . get_string('courseslinkedtransaction_info', PLUGINNAME) . '</p>',
        array('class' => 'container')
    ),
    array('class' => 'jumbotron jumbotron-fluid border-radius py-4 mt-5')
);

$table->define_baseurl($CFG->wwwroot . DETAILURL);
$table->out(10, true);

echo $OUTPUT->footer();
