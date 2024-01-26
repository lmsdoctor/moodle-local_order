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

if (!is_siteadmin($USER->id)) {
    redirect(
        new moodle_url('/my'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        notification::NOTIFY_WARNING
    );
}

global $PAGE, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$urlparams = array('id' => $id, 'action' => $action);

$orderurl = new moodle_url(HOME);
$updateurl = new moodle_url(UPDATEURL);

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
$PAGE->requires->js_call_amd('local_order/confirm', 'init');

$transaction = $DB->get_record(TABLE_TRAN, array('id' => $id));
$transaction->action = $action;

$user = $DB->get_record('user', array('id' => $transaction->userid));
$user->fullname = fullname($user);
$mform = new order_form(
    $updateurl,
    null,
    'post',
    '',
    array(
        'name' => 'updatestatus',
        'action-cancel' => new moodle_url('/local/order/cancel.php')
    )
);

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    redirect(new moodle_url(CANCEL));
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
    html_writer::tag('h3', get_string('courseslinkedtransaction', PLUGINNAME), array('class' => 'alert-heading')) .
        html_writer::tag('hr', '', array()) .
        html_writer::tag('p', get_string('courseslinkedtransaction_info', PLUGINNAME, $user), array('class' => 'container')),
    array('class' => 'alert alert-warning border-radius py-4 mt-5')
);


$table->define_baseurl($CFG->wwwroot . DETAILURL);
$table->out(10, true);

echo $OUTPUT->footer();
