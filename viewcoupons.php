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
 * Provides the interface for coupon enrolment
 *
 * @package enrol_coupon
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
//require_once($CFG->dirroot.'/mod/lesson/locallib.php');

$id = required_param('id', PARAM_INT);
$perpage = optional_param('perpage',25, PARAM_INT);
$pageno = optional_param('pageno',0, PARAM_INT);
$sort = optional_param('sort','iddsc', PARAM_TEXT);

$instance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);

//prepare paging data
$fields = '*';
$fromrecord = $pageno * $perpage;
$couponcount = $DB->count_records('enrol_coupon_coupons',array('instanceid'=>$id));
if(substr($sort,-3)=='asc'){
	$use_sort = substr($sort,0,-3) . ' ASC';
}else{
	$use_sort = substr($sort,0,-3) . ' DESC';
} 

//get coupons
$coupons = $DB->get_records('enrol_coupon_coupons',array('instanceid'=>$id),$use_sort,$fields,$fromrecord,$perpage);
//$couponusers = $DB->get_records('enrol_coupon_user',array('instanceid'=>$id));
$courseid=$instance->courseid;
$course = get_course($courseid);
$context = context_course::instance($courseid, MUST_EXIST);

if ($courseid == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);

$canconfig = has_capability('moodle/course:enrolconfig', $context);

$unsortedurl = new moodle_url('/enrol/coupon/viewcoupons.php', array('id'=>$id,'perpage'=>$perpage));
$PAGE->set_url('/enrol/coupon/viewcoupons.php', array('id'=>$id,'perpage'=>$perpage,'sort'=>$sort));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('viewcoupons', 'enrol_coupon'));
$PAGE->set_heading($course->fullname);


$renderer = $PAGE->get_renderer('enrol_coupon');
$PAGE->navbar->add(get_string('view'));
echo $renderer->header($instance, 'manage', null, get_string('managetab', ENROL_COUPON_FRANKY));

/*
    // There are no questions; give teacher some options
    require_capability('mod/tquiz:edit', $context);
*/
    echo $renderer->add_edit_page_links($instance);


if($couponcount){
	$pagingbar ="";
	if($couponcount > $perpage){
		$pagingbar =$renderer->show_paging_bar($couponcount, $pageno, $perpage,$PAGE->url,'pageno');
	}
	echo $pagingbar;
	echo $renderer->show_coupons_list($coupons,$instance,$unsortedurl,$sort);
	echo $pagingbar;
}
echo $renderer->footer();
