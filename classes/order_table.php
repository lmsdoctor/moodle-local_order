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
 * @package    local_order
 * @copyright  2023 LMS Doctor
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
            'userids',
            'courseid',
            'updatedat',
            'coupon',
            'status',
            'value',
        );

        // Display column if not downloading.
        if (!$this->is_downloading()) {
            $columns[] = 'actions';
        }

        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('order', PLUGIN),
            get_string('purchasedby', PLUGIN),
            get_string('enrolleduser', PLUGIN),
            get_string('course'),
            get_string('date'),
            get_string('discountcode', PLUGIN),
            get_string('status', PLUGIN),
            get_string('total', PLUGIN),
        );

        if (!$this->is_downloading()) {
            $headers[] = get_string('actions', PLUGIN);
        }
        $this->define_headers($headers);

    }

    /**
     * Returns purchased by.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_userid($row) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $row->userid));
        return $user->email;

    }

    /**
     * Returns enrolled users list.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_userids($row) {
        global $DB;

        $userids = explode(',', $row->userids);
        $userlist = array();
        foreach ($userids as $userid) {
            $user = $DB->get_record('user', array('id' => $userid));
            $userlist[] = fullname($user);
        }

        return implode(', ', $userlist);

    }

    /**
     * Returns the course shortname.
     *
     * @param stdClass $row Contains object with all the values of record.
     * @return string
     */
    public function col_courseid($row) {
        global $DB;

        if (empty($row->courseid)) {
            return '';
        }

        return $DB->get_field('course', 'shortname', array('id' => $row->courseid));

    }

    /**
     * Returns the order id.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_id($row) {

        return $row->sessionid;
    }

    /**
     * Returns the time completed.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_updatedat($row) {

        if (empty($row->updatedat)) {
            return '-';
        }
        return userdate($row->updatedat, get_string('strftimedatetimeshort', 'langconfig'));
    }

    /**
     * Returns order status.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_status($row) {
        if (empty($row->status)) {
            return '-';
        }
        return get_string(strtolower($row->status), PLUGIN);
    }

    /**
     * Returns discount codes.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_coupon($row) {

        if (empty($row->coupon)) {
            $row->coupon = '-';
        }
        return $row->coupon;

    }

    /**
     * Returns value.
     *
     * @param  stdClass $row
     * @return string
     */
    public function col_value($row) {
        return '$' . (float) $row->value;
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
            $updateurl = new moodle_url('/local/order/update.php',
                array('id' => $row->id, 'action' => 'edit'));
            $actions = $OUTPUT->action_icon($updateurl, new pix_icon('i/edit', ''));

            $deleteurl = new moodle_url('/local/order/update.php',
                array('id' => $row->id, 'action' => 'delete', 'class' => 'action-delete'));
            $actions .= $OUTPUT->action_icon($deleteurl, new pix_icon('i/trash', ''), null, array('class' => 'action-delete'));


        }
        return $actions;
    }

}
