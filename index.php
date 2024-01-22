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
use local_order\form\order_filter_form;
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
$params = new stdClass();
$params->paymentstatus = optional_param('paymentstatus', '', PARAM_TEXT);
$params->itemname = optional_param('itemname', '', PARAM_TEXT);
$params->startdate = '';
$params->finaldate = '';

$startdate = optional_param('startdate', '', PARAM_TEXT);
if (is_array($startdate)) {
    $params->startdate = implode('-', array(
        $startdate['year'],
        str_pad($startdate['month'], 2, '0', STR_PAD_LEFT),
        str_pad($startdate['day'], 2, '0', STR_PAD_LEFT),
    ));
} else if ($startdate) {
    $params->startdate = $startdate;
}

$finaldate = optional_param('finaldate', '', PARAM_TEXT);
if (is_array($finaldate)) {
    $params->finaldate = implode('-', array(
        $finaldate['year'],
        str_pad($finaldate['month'], 2, '0', STR_PAD_LEFT),
        str_pad($finaldate['day'], 2, '0', STR_PAD_LEFT),
    ));
} else if ($finaldate) {
    $params->finaldate = $finaldate;
}


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
    $mform = new order_filter_form(null, null, 'post', '', array('name' => 'filter'));

    // Define heading and title.
    $PAGE->set_title(get_string('orders', PLUGINNAME));
    $PAGE->set_heading(get_string('orders', PLUGINNAME));
    $PAGE->navbar->add(get_string('orders', PLUGINNAME), $orderurl);

    if ($mform->is_cancelled()) {
        redirect(new moodle_url(CANCEL));
    } else if ($getdata = $mform->get_data()) {
        $mform->set_data($getdata);
    }

    echo $OUTPUT->header();

    // Form processing and displaying is done here.
    $mform->display();
}

// Work out the sql for the table.
$sql = new stdClass();
$sql->fields = "id, instanceid, itemname, userid, memo, paymenttype, paymentstatus, timeupdated";
$sql->from = "{" . TABLE_TRAN . "}";
$sql->where = "1=1";
$sql->params = (array)$params;

if (!empty($params->paymentstatus)) {
    $sql->where = $sql->where . ' AND paymentstatus = :paymentstatus';
}

if (!empty($params->itemname)) {
    $sql->where = $sql->where . " AND itemname LIKE :itemname";
    $sql->params['itemname'] = '%' . $DB->sql_like_escape($params->itemname) . '%';
}

if (!empty($params->startdate)) {
    if (!empty($params->finaldate)) {
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
$table->define_baseurl(new moodle_url(HOME, (array)$params));
$table->out(40, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
