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
 * Payment enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    local_order
 * @copyright  LMS Doctor <support@lmsdoctor.com>
 * @author     Seth Yoder <seth.a.yoder@gmail.com> - based on code by Eugene Venter, Martin Dougiamas and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This function extends the navigation with the tool items.
 *
 * @param navigation_node $navigation The navigation node to extend
 */
function local_order_extend_navigation($nav) {
    global $PAGE;

    $nodecredential = $nav->add(get_string('pluginname', PLUGINNAME));

    if (is_siteadmin()) {
        $discounts = navigation_node::create(
            'Discounts',
            new moodle_url('/enrol/payment/coupon/index.php'),
            navigation_node::TYPE_CUSTOM,
            'discounts',
            'discounts',
            new pix_icon('i/permissions', '')
        );
        $discounts->showinflatnavigation = true;
        $nodecredential->add_node($discounts);
    }

}
