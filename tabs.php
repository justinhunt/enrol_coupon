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
* Sets up the tabs at the top of the enrol coupon　for teachers.
*
* This file was adapted from the mod/lesson/tabs.php
*
 * @package enrol_coupon
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
*/

defined('MOODLE_INTERNAL') || die();

/// This file to be included so we can assume config.php has already been included.
global $DB;
if (empty($instance)) {
    print_error('cannotcallscript');
}
if (!isset($currenttab)) {
    $currenttab = '';
}

if (!isset($course)) {
    $course = $DB->get_record('course', array('id' => $instance->courseid));
	$context = context_course::instance($course->id, MUST_EXIST);
}

$tabs = $row = $inactive = $activated = array();


$row[] = new tabobject('manage', "$CFG->wwwroot/enrol/coupon/viewcoupons.php?id=$instance->id", get_string('viewcoupons', 'enrol_coupon'), get_string('viewcoupons', 'enrol_coupon'));
$row[] = new tabobject('reports', "$CFG->wwwroot/enrol/coupon/reports.php?id=$instance->id", get_string('reports', 'enrol_coupon'), get_string('viewreports', 'tquiz'));


$tabs[] = $row;


print_tabs($tabs, $currenttab, $inactive, $activated);
