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

/**
 * Overall table class.
 */
class order_table extends \table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        // Define the list of columns to show.
        $columns = array(
            'id',
            'userid',
            // 'courseid',
            'timeupdated',
            'paymentstatus',
            'memo',
            'actions',
        );

        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('order', PLUGIN),
            get_string('user'),
            // get_string('course'),
            get_string('date'),
            get_string('status', PLUGIN),
            get_string('total', PLUGIN),
            get_string('actions', PLUGIN),
        );
        $this->define_headers($headers);

    }

    /**
     * This public function is called for each data row to allow processing of the
     * username value.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_userid($row) {
        global $DB, $OUTPUT;

        $user = $DB->get_record('user', array('id' => $row->userid));
        return fullname($user);

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

        // return $row->courseid;
        return $DB->get_field('course', 'fullname', array('id' => $row->courseid));

    }

    /**
     * Returns the time completed.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_order($row) {
        global $DB;

        if (empty($row->order)) {
            return '-';
        }
        return $row->order;
    }

    /**
     * Returns the time completed.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_timeupdated($row) {
        global $DB;

        if (empty($row->timeupdated)) {
            return '-';
        }
        return userdate($row->timeupdated, get_string('strftimedatetimeshort', 'langconfig'));
    }

    /**
     * Returns the license type.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_paymentstatus($row) {
        if (empty($row->paymentstatus)) {
            return '-';
        }
        return get_string(strtolower($row->paymentstatus), PLUGIN);
    }

    /**
     * Returns subcategory.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_memo($row) {
        global $DB;
        return '$' . $row->memo;
        // return $DB->get_field('local_order_type_subcat', 'name', array('id' => $row->typesubcatid));
    }

    /**
     * Processing of the actions value.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_actions($row) {
        global $OUTPUT, $CFG;

        // Remove the path of the url.
        $updateurl = new moodle_url('/local/order/update.php',
            array('id' => $row->id, 'action' => 'edit'));
        $actions = $OUTPUT->action_icon($updateurl, new pix_icon('i/edit', ''));

        $deleteurl = new moodle_url('/local/order/update.php',
            array('id' => $row->id, 'action' => 'delete', 'class' => 'action-delete'));
        $actions .= $OUTPUT->action_icon($deleteurl, new pix_icon('i/trash', ''), null, array('class' => 'action-delete'));

        return $actions;
    }

}
