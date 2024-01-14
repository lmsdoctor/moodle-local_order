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
require_once(dirname(__FILE__) . '/global.php');
require("$CFG->libdir/tablelib.php");

require_login();

global $SESSION, $USER, $DB;

use local_order\order_table;
use \local_order\form\order_filter_form;
use \core\output\notification;

$context = context_system::instance();

if (!is_siteadmin($USER->id)) {
    redirect(
        new moodle_url('/my'),
        get_string('requiredpermissions', PLUGINNAME),
        0,
        notification::NOTIFY_WARNING
    );
}

$download = optional_param('download', '', PARAM_ALPHA);
$forminitdata = new stdClass();
$forminitdata->paymentstatus = optional_param('paymentstatus', '', PARAM_TEXT);
$forminitdata->itemname = optional_param('itemname', '', PARAM_TEXT);
$forminitdata->startdate = optional_param('startdate', 0, PARAM_INT);
$forminitdata->finaldate = optional_param('finaldate', 0, PARAM_INT);

$orderurl = new moodle_url(HOME);
$PAGE->set_context($context);
$PAGE->set_url($orderurl);

$table = new order_table('uniqueid');
$table->is_downloading($download, 'Orders_' . time(), 'orders');

$PAGE->requires->css('/' . PLUGINURL . '/styles/main.css');
$PAGE->requires->js_call_amd('local_order/confirm', 'init');

// If the table is not downloading.
if (!$table->is_downloading()) {

    // Search Filter Form Instance.
    $mform = new order_filter_form(null, (array)$forminitdata, 'post', '', array('name' => 'filter'));

    // Define heading and title.
    $PAGE->set_title(get_string('orders', PLUGINNAME));
    $PAGE->set_heading(get_string('orders', PLUGINNAME));
    $PAGE->navbar->add(get_string('orders', PLUGINNAME), $orderurl);

    echo $OUTPUT->header();

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        redirect(new moodle_url(CANCEL));
    } else if ($getdata = $mform->get_data()) {
        $mform->set_data($getdata);
    }
    $mform->display();
}

// Work out the sql for the table.
$sql = new stdClass();
$sql->fields = "id, instanceid, itemname, userid, memo, paymenttype, paymentstatus, timeupdated";
$sql->from = "{" . TABLE_TRAN . "}";
$sql->where = "1=1";
$sql->params = (array)$forminitdata;

if (!empty($forminitdata->paymentstatus)) {
    $sql->where = $sql->where . ' AND paymentstatus = :paymentstatus';
}

if (!empty($forminitdata->itemname)) {
    $sql->where = $sql->where . " AND itemname LIKE :itemname";
    $sql->params['itemname'] = '%' . $DB->sql_like_escape($forminitdata->itemname) . '%';
}

$symbol = '-';
if (!empty($forminitdata->startdate)) {
    $startdate = array(
        $forminitdata->startdate['year'],
        str_pad($forminitdata->startdate['month'], 2, '0', STR_PAD_LEFT),
        str_pad($forminitdata->startdate['day'], 2, '0', STR_PAD_LEFT),
    );
    $sql->params['startdate'] = implode($symbol, $startdate);

    if (!empty($forminitdata->finaldate)) {
        $finaldate = array(
            $forminitdata->finaldate['year'],
            str_pad($forminitdata->finaldate['month'], 2, '0', STR_PAD_LEFT),
            str_pad($forminitdata->finaldate['day'], 2, '0', STR_PAD_LEFT),
        );

        $sql->params['finaldate'] = implode($symbol, $finaldate);
        $sql->where = $sql->where . ' AND DATE(FROM_UNIXTIME(timeupdated)) BETWEEN :startdate AND :finaldate ';
    } else {
        $sql->where = $sql->where . ' AND DATE(FROM_UNIXTIME(timeupdated)) = :startdate';
    }
}

// Work out the sql for the table.
$table->set_sql(
    $sql->fields,
    $sql->from,
    $sql->where,
    $sql->params
);
$table->define_baseurl($orderurl);
$table->out(40, true);
$table->finish_output();

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
