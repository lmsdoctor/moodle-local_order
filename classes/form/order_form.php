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
 * Update order form.
 *
 * @package   local_order
 * @copyright 2021 Andres, David Q <andresmao2@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_order\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class order_form extends moodleform {

    /**
     * Default form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Hidden elements.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);

        $status = array(
            'pending' => 'Pending payment',
            'on-hold' => 'On hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
        );
        $mform->addElement('select', 'paymentstatus', get_string('status', PLUGIN), $status);

        $this->add_action_buttons(true, get_string('save'));

    }

    /**
     * Additional validations.
     *
     * @param  array $data
     * @param  stdclass $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = array();
        return $errors;
    }
}
