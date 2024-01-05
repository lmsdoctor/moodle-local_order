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
require_login();

global $DB;

require("$CFG->libdir/tablelib.php");

use local_order\detail_table;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(DETAILURL);

$id = required_param('id', PARAM_INT);

$table = new detail_table('uniqueid');
$table->is_downloading($download, 'Details_' . time(), 'details');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data
    // Print the page header
    $PAGE->set_title('Order #' . $id);
    $PAGE->set_heading('Order #' . $id);
    $PAGE->navbar->add(get_string('orders', PLUGINNAME), new moodle_url('/local/order/index.php'));
    $PAGE->navbar->add('Detail');
    echo $OUTPUT->header();
}

// Work out the sql for the table.
$table->set_sql('id,sessionid,courseid,amount', "{enrol_payment_detail}", 'sessionid = ?', array($id));

$table->define_baseurl($CFG->wwwroot . DETAILURL);

// Get the user records to display it.
$transaction = $DB->get_record('enrol_payment_transaction', array('instanceid' => $id));
$user = $DB->get_record('user', array('id' => $transaction->userid));
profile_load_data($user);
$organization = ($user->profile_field_organization == 'My organization is not here') ? 'None' : $user->profile_field_organization;

// Open card.
echo html_writer::start_div('card');
echo html_writer::start_div('card-header');
echo 'Buyer information';
echo html_writer::end_div();
// List.
echo html_writer::start_div('card-body');
echo html_writer::tag(
    'p',
    '<br><b>Transaction date:</b> ' . userdate($transaction->timeupdated, get_string('strftimedatetimeshort', 'langconfig')) .
        '<br><b>Status:</b> ' . ucfirst($transaction->paymentstatus) .
        '<br><b>Name:</b> ' . fullname($user) . '<br><b>Email:</b> ' . $user->email . '<br>
            <b>City:</b> ' . $user->city . '<br><b>Is member?</b> ' . $user->profile_field_ismember .
        '<br><b>Organization:</b> ' . $organization,
    array('class' => 'card-text'),
);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::tag('br', '');

$table->out(20, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
