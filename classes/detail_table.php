<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Session table.
 *
 * @package    report_bigbluebuttonsessions
 * @copyright  2021 LMS Doctor, Solin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_order;

use moodle_url;
use pix_icon;
use html_writer;

global $CFG;

require_once($CFG->dirroot . '/lib/enrollib.php');
require_once(dirname(__FILE__, 2) . '/global.php');

/**
 * Overall table class.
 */
class detail_table extends \table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        // Define the list of columns to show.
        $columns = array(
            'courseid',
            'taxpercent',
            'amount',
            'sessionid',
            'enrollmentdate',
        );

        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('course'),
            get_string('tax', PLUGINNAME),
            get_string('amount', PLUGINNAME),
            get_string('subtotal', PLUGINNAME),
            get_string('enrollmentdate', PLUGINNAME),
        );
        $this->define_headers($headers);
    }

    /**
     * Returns the employee number if exist, empty otherwise.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_courseid($row) {
        global $DB;

        if (empty($row->courseid)) {
            return '';
        }

        return $DB->get_field('course', 'fullname', array('id' => $row->courseid));
    }

    /**
     * Returns the user enrollment date if any.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_enrollmentdate($row) {
        global $DB;

        $params = array(
            'instanceid' => $row->sessionid,
            'enrol' => 'payment',
            'courseid' => $row->courseid,
            'status' => 0
        );

        $sql = "SELECT ue.*
                FROM {" . TABLE_TRAN . "} t
                JOIN {enrol} e ON e.status = :status
                 AND e.courseid = :courseid
                 AND e.enrol = :enrol
                JOIN {user_enrolments} ue
                  ON ue.enrolid = e.id
                 AND ue.userid = t.userid
               WHERE t.instanceid = :instanceid";
        $userenrollment = $DB->get_record_sql($sql, $params, IGNORE_MISSING);;

        if (empty($userenrollment)) {
            if (!$this->is_downloading()) {
                return html_writer::tag(
                    'span',
                    get_string('noenrollment', PLUGINNAME),
                    array('class' => 'badge badge-warning')
                );
            }
            return get_string('noenrollment', PLUGINNAME);
        }
        return userdate($userenrollment->timecreated, get_string('strftimedatetimeshort', 'langconfig'));
    }

    /**
     * Returns the amount.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_amount($row) {
        global $DB;

        if (empty($row->amount)) {
            return '0.00';
        }
        return "$$row->amount $row->currency";
    }

    /**
     * Returns the amount.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_taxpercent($row) {
        global $DB;

        if (empty($row->taxpercent)) {
            return '0.00';
        }
        $tax = $row->taxpercent * $row->amount;
        return "$$tax $row->currency";
    }

    /**
     * Returns the amount.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_sessionid($row) {
        global $DB;

        if (empty($row->taxpercent)) {
            return '0.00';
        }
        $tax = $row->taxpercent * $row->amount;
        $subtotal = $tax + $row->amount;
        return "$$subtotal $row->currency";
    }
}
