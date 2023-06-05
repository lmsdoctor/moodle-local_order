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
            'instanceid',
            // 'userid',
            'email',
            'ismember',
            'organization',
            'timeupdated',
            'paymentstatus',
            // 'course',
            'memo',
        );

        // Display column if not downloading.
        if (!$this->is_downloading()) {
            $columns[] = 'actions';
        }

        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('order', PLUGIN),
            // get_string('user'),
            get_string('email'),
            get_string('ismember', PLUGIN),
            get_string('organization', PLUGIN),
            get_string('date'),
            get_string('status', PLUGIN),
            // get_string('course'),
            get_string('total', PLUGIN),
        );

        if (!$this->is_downloading()) {
            $headers[] = get_string('actions', PLUGIN);
        }
        $this->define_headers($headers);

    }

    /**
     * Returns the user fullname.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_userid($row) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $row->userid));
        return fullname($user);

    }

    /**
     * Returns the user fullname.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_email($row) {
        global $DB;
        return $DB->get_field('user', 'email', array('id' => $row->userid));
    }

    /**
     * Returns the user fullname.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_ismember($row) {
        global $DB;
        $user = $DB->get_record('user', array('id' => $row->userid));
        \profile_load_data($user);
        return $user->profile_field_ismember;
    }

    /**
     * Returns the user fullname.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_organization($row) {
        global $DB;
        $user = $DB->get_record('user', array('id' => $row->userid));
        \profile_load_data($user);
        return $user->profile_field_organization;
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
     * Returns the id as a link to the detail page.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_instanceid($row) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $row->userid));

        if (!$this->is_downloading()) {
            return html_writer::link(
                new moodle_url('/local/order/detail.php', array('id' => $row->instanceid)),
                '#' . $row->instanceid . ' - ' . fullname($user));
        }
        return $row->instanceid . ' - ' . fullname($user);
    }

    /**
     * Returns the time completed.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_timeupdated($row) {

        if (empty($row->updatedat)) {
            return '-';
        }
        // return userdate($row->updatedat, get_string('strftimedatetimeshort', 'langconfig'));
        return $row->updatedat;
    }

    /**
     * Returns the license type.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_paymentstatus($row) {
        if (empty($row->status)) {
            return '-';
        }
        return get_string(strtolower($row->status), PLUGIN);
    }

    /**
     * Returns subcategory.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_memo($row) {
        return '$' . $row->value;
    }

    /**
     * Returns the course name.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_course($row) {
        global $DB;
        $sql = 'SELECT c.fullname
                  FROM {course} c
                  JOIN {enrol} e ON e.courseid = c.id
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                  WHERE ue.id = :id AND e.enrol = :enrol AND ue.userid = :userid';
        $params = array('enrol' => 'payment', 'userid' => $row->userid, 'id' => $row->instanceid);
        return $DB->get_field_sql($sql, $params);
    }

    /**
     * Processing of the actions value.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_actions($row) {
        global $OUTPUT;

        if (!$this->is_downloading()) {

            // Remove the path of the url.
            $viewdetail = new moodle_url('/local/order/detail.php', array('id' => $row->instanceid));
            $actions = $OUTPUT->action_icon($viewdetail, new pix_icon('i/search', ''));

            // Remove the path of the url.
            $updateurl = new moodle_url('/local/order/update.php',
                array('id' => $row->id, 'action' => 'edit'));
            $actions .= $OUTPUT->action_icon($updateurl, new pix_icon('i/edit', ''));

            $deleteurl = new moodle_url('/local/order/update.php',
                array('id' => $row->id, 'action' => 'delete', 'class' => 'action-delete'));
            $actions .= $OUTPUT->action_icon($deleteurl, new pix_icon('i/trash', ''), null, array('class' => 'action-delete'));


        }
        return $actions;
    }

}
