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
 * Order page.
 *
 * @package    local_order
 * @copyright  2021 Andres, David Q.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

require("$CFG->libdir/tablelib.php");

use local_order\order_table;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/order/index.php');
$PAGE->requires->js_call_amd('local_order/confirm', 'init');

$download = optional_param('download', '', PARAM_ALPHA);
define('PLUGIN', 'local_order');

$table = new order_table('uniqueid');
$table->is_downloading($download, 'Orders_' . time(), 'orders');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data.
    // Print the page header.
    $PAGE->set_title(get_string('orders', PLUGIN));
    $PAGE->set_heading(get_string('orders', PLUGIN));
    $PAGE->navbar->add(get_string('orders', PLUGIN), new moodle_url('/index.php'));
    echo $OUTPUT->header();
}

// Work out the sql for the table.
$table->set_sql('id,instanceid,userid,memo,paymentstatus,timeupdated', "{enrol_payment_transaction}", '1=1');
$table->define_baseurl("$CFG->wwwroot/local/order/index.php");
$table->out(40, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}