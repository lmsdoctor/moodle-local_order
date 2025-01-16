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
 * @copyright  2025 LMS Doctor <support@lmsdoctor.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

require("$CFG->libdir/tablelib.php");

use local_order\order_table;
use \core\output\notification;

define('PLUGIN', 'local_order');

$site = get_site();
$vieworders = get_string('viewoders', PLUGIN);
$title = "$site->shortname: $vieworders";

$pageurl = new moodle_url('/local/order/index.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($pageurl);

if (!has_capability('enrol/payment:manage', $context)) {
    redirect(new moodle_url('/my'), get_string('requiredpermissions', 'enrol_payment'), 0, notification::NOTIFY_WARNING);
}

$PAGE->requires->js_call_amd('local_order/confirm', 'init');

// Filter form,
$mform = new \local_order\form\filter_form(null);

// Order table.
$download = optional_param('download', '', PARAM_ALPHA);
$table = new order_table('uniqueid');
$table->is_downloading($download, 'Orders_' . time(), 'orders');

// Default SELECT and FROM statements
$select = 't.id,
            u.id as userid, u.email, t.sessionid, t.userid, t.userids,
            t.courseid, c.shortname,
            CAST(t.value AS DECIMAL(10, 2)) AS value,
            t.status, t.updatedat, s.coupon';
$from = '{enrol_payment_transaction} t
         JOIN {course} c ON c.id = t.courseid
         JOIN {enrol_payment_session} s ON s.id = t.sessionid
         JOIN {user} u ON u.id = t.userid';
$where = '1 = 1';
$params = array();  // Array to hold query parameters

if ($mform->is_cancelled()) {
    redirect($pageurl);
} else if ($search = $mform->get_data()) {

    // Process validated data
    if (!empty($search->purchaserid)) {
        $where .= ' AND u.id = :purchaserid';
        $params['purchaserid'] = $search->purchaserid;
    }

    if (!empty($search->course)) {
        $where .= ' AND c.id = :courseid';
        $params['courseid'] = $search->course;
    }

    if (!empty($search->purchaseby)) {
        $where .= ' AND u.email LIKE :purchaseby';
        $params['purchaseby'] = '%' . $search->purchaseby . '%';
    }

    if (!empty($search->startdate) && !empty($search->enddate)) {
        $where .= ' AND t.updatedat BETWEEN :startdate AND :enddate';
        $params['startdate'] = $search->startdate;
        $params['enddate'] = $search->enddate;
    } else if (!empty($search->startdate)) {
        $where .= ' AND t.updatedat > :startdate';
        $params['startdate'] = $search->startdate;
    } else if (!empty($search->enddate)) {
        $where .= ' AND t.updatedat < :enddate';
        $params['enddate'] = $search->enddate;
    }

    if (!empty($search->discountcode)) {
        $codestr = $DB->get_field('enrol_payment_discountcode', 'code', ['id' => $search->discountcode]);
        $where .= ' AND s.coupon = :discountcode';
        $params['discountcode'] = $codestr;
    }

    if (!empty($search->status)) {
        $where .= ' AND t.status LIKE :status';
        $params['status'] = '%' . $search->status . '%';
    }
}

if (!$table->is_downloading()) {

    // Only print headers if not asked to download data.
    // Title and headings.
    $PAGE->set_title($title);
    $PAGE->set_heading($site->fullname);

    // Navigation bar.
    $PAGE->navbar->ignore_active();
    $PAGE->navbar->add(get_string('pluginname', 'enrol_payment'));
    $PAGE->navbar->add(get_string('orders', PLUGIN), $pageurl);

    // Output header.
    echo $OUTPUT->header();
}

// Set the SQL for the table
$table->set_sql($select, $from, $where, $params);
$table->define_baseurl($pageurl);

echo html_writer::tag('h2', get_string('orders', PLUGIN), ['class' => 'custom-payment-title mb-5']);
$mform->display();
$table->out(50, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
