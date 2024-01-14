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

define('PLUGINURL', 'local/order');
define('PLUGINNAME', 'local_order');

define('HOME', '/local/order/index.php');
define('CANCEL', '/local/order/cancel.php');
define('DETAILURL', '/local/order/detail.php');
define('EDITURL', '/local/order/edit.php');
define('UPDATEURL', '/local/order/action/update.php');
define('DELETEURL', '/local/order/action/delete.php');

define('PLUGINAMD', PLUGINNAME . '/confirm');

define('TABLE_TRAN', 'enrol_payment_transaction');
define('TABLE_DETAIL', 'enrol_payment_detail');
define('TABLE_SESSION', 'enrol_payment_session');
