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
 * Detail page.
 *
 * @package    local_order
 * @copyright  2021 Andres, David Q.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(dirname(__FILE__) . '/global.php');
require("$CFG->libdir/tablelib.php");

require_login();

if (!is_siteadmin($USER->id)) {
    redirect(
        new moodle_url('/my'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        \core\output\notification::NOTIFY_WARNING
    );
}

global $DB;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url(DETAILURL));

$id = required_param('id', PARAM_INT);

$table = new local_order\detail_table('uniqueid');
$table->is_downloading(null, 'Details_' . time(), 'details');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data
    // Print the page header
    $PAGE->set_title('Order #' . $id);
    $PAGE->set_heading('Order #' . $id);
    $PAGE->navbar->add(get_string('orders', PLUGINNAME), new moodle_url(HOME));
    $PAGE->navbar->add('Detail');
    echo $OUTPUT->header();
}

// Work out the sql for the table.
$table->set_sql(
    'd.id, d.sessionid, d.courseid, d.amount, d.currency, c.taxpercent',
    "{" . TABLE_DETAIL . "} d
    JOIN {" . TABLE_SESSION . "} c ON c.id = d.sessionid",
    'd.sessionid = ?',
    array($id)
);

$table->define_baseurl($CFG->wwwroot . DETAILURL);

// Get the user records to display it.
$session = $DB->get_record(TABLE_SESSION, array('id' => $id));
$transaction = $DB->get_record(TABLE_TRAN, array('instanceid' => $id));
$user = $DB->get_record('user', array('id' => $transaction->userid));

// Open card.
echo html_writer::start_div('card');
echo html_writer::start_div('card-header');
echo 'Buyer information';
echo html_writer::end_div();
// List.
echo html_writer::start_div('card-body');
echo html_writer::tag(
    'ul',
    '<li class="list-group-item"><b>Transaction date:</b> ' . userdate($transaction->timeupdated, get_string('strftimedatetimeshort', 'langconfig')) . '</li>' .
        '<li class="list-group-item"><b>Status:</b> ' . ucfirst($transaction->paymentstatus) . '</li>' .
        '<li class="list-group-item"><b>Names:</b> ' . fullname($user) . '</li>' .
        '<li class="list-group-item"><b>Email:</b> ' . $user->email . '</li>' .
        '<li class="list-group-item"><b>City:</b> ' . $user->city . '</li>' .
        '<li class="list-group-item"><b>Total:</b> $' . $transaction->memo . '</li>',
    array('class' => 'list-group list-group-flush'),
);

echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::tag('br', '');

$table->out(20, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
