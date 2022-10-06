<?php
/**
 * Simple file test.php to drop into root of Moodle installation.
 * This is the skeleton code to print a downloadable, paged, sorted table of
 * data from a sql query.
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
$table->is_downloading($download, 'test', 'testing123');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data
    // Print the page header
    $PAGE->set_title('Orders');
    $PAGE->set_heading('Orders');
    $PAGE->navbar->add('Orders', new moodle_url('/test.php'));
    echo $OUTPUT->header();
}

// Work out the sql for the table.
$table->set_sql('id,userid,memo,paymentstatus,timeupdated', "{enrol_payment_transaction}", '1=1');

$table->define_baseurl("$CFG->wwwroot/local/order/index.php");

$table->out(40, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}