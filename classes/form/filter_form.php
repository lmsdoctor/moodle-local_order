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
 * Filter form.
 *
 * @package   local_order
 * @copyright 2024 LMS Doctor <support@lmsdoctor.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_order\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Filter form class.
 */
class filter_form extends moodleform {

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

        $mform->addElement('header', 'filter-header', get_string('search'));
        $mform->setExpanded('filter-header', true);

        $status = array(
            'select' => get_string('selectstatus', PLUGIN),
            'pending' => get_string('pending', PLUGIN),
            'on-hold' => get_string('on-hold', PLUGIN),
            'completed' => get_string('completed', PLUGIN),
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
        );

        if (!empty($this->_customdata->gateway && $this->_customdata->gateway == 'stripe')) {
            $status = array(
                'select' => get_string('selectstatus', PLUGIN),
                'open' => 'Open',
                'pending' => get_string('pending', PLUGIN),
                'on-hold' => get_string('on-hold', PLUGIN),
                'complete' => get_string('complete', PLUGIN),
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed',
            );
        }

        $mform->addElement('text', 'purchaserid', get_string('purchaserid', 'local_order'));
        $mform->setType('purchaserid', PARAM_TEXT);

        $mform->addElement('text', 'purchaseby', get_string('purchaseremail', 'local_order'));
        $mform->setType('purchaseby', PARAM_TEXT);

        $startarr = array(get_string('all'));
        $coursesobj = get_courses($categoryid = "all", $sort = "c.shortname ASC", $fields="c.id, c.shortname");
        $courses = array_map(function($obj) { return $obj->shortname; }, $coursesobj);
        $merge = array_merge($startarr, $courses);

        $mform->addElement('select', 'course', get_string('course'), $merge);
        $mform->setType('course', PARAM_TEXT);

        $options = array(
            'startyear' => 1990,
            'stopyear'  => date("Y"),
            'timezone'  => 99,
            'step'      => 5,
            'optional' => true,
        );

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_order'), $options);
        $mform->setType('startdate', PARAM_INT);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_order'), $options);
        $mform->setType('enddate', PARAM_INT);

        $codes = array(get_string('all'));
        $mform->addElement('select', 'discountcode', get_string('discountcode', 'local_order'), $codes);
        $mform->setType('discountcode', PARAM_TEXT);

        $mform->addElement('select', 'status', get_string('orderstatus', PLUGIN), $status);

        $this->add_action_buttons(true, get_string('search'));

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
