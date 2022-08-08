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
require_login();

$context = context_system::instance();
// require_capability('local/credential:addetr', context_system::instance());

global $PAGE, $DB, $USER;

define('PLUGIN', 'local_order');
define('TABLE', 'enrol_payment_transaction');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$urlparams = array('id' => $id, 'action' => $action);
$orderurl = new moodle_url('/local/order/index.php');
$pageurl = new moodle_url('/local/order/update.php', $urlparams);

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_title('Update order');
$PAGE->set_heading('Update order');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', PLUGIN));
$PAGE->navbar->add('Order', $orderurl);
$PAGE->navbar->add('Add/Update order');

// Action delete.
if ($action === 'delete') {
    $DB->delete_records(TABLE, array('id' => $id));
    redirect($orderurl, 'Deleted', 0, \core\output\notification::NOTIFY_SUCCESS);
}

$customdata = array(
    'id' => $id,
    'action' => $action
);

$mform = new \local_order\form\order_form(null, $customdata);

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    redirect($orderurl);
} else if ($formdata = $mform->get_data()) {

    if (!is_array($formdata->userid)) {
        // Those are for single transactions.
        if ($formdata->action === 'edit') {
            $DB->update_record(TABLE, $formdata);
            $status = 'Updated';
            $id = $formdata->id;
        }

        if ($formdata->action === 'add') {
            $id = $DB->insert_record(TABLE, $formdata, true, true);
            $status = 'Saved';
        }

        redirect($orderurl, $status, 0, \core\output\notification::NOTIFY_SUCCESS);
    }

} else {

    // Set default data (if any).
    $toform = array(
        'id' => null,
        'action' => $action,
        'userid' => $USER->id
    );

    $mform->set_data($toform);

    // Display the info.
    echo $OUTPUT->header();
    echo html_writer::div('Update order', 'mb-5 alert alert-primary');
    $mform->display();
    echo $OUTPUT->footer();

}
