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
 * Prints a particular instance of tquiz
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_tquiz
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/reportclasses.php');


$id = optional_param('id', 0, PARAM_INT); // instance ID, or
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$showreport = optional_param('report', 'menu', PARAM_TEXT); // report type
$itemid = optional_param('itemid', 0, PARAM_INT); // itemid
$userid = optional_param('userid', 0, PARAM_INT); //userid



if ($id) {
    $instance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);
    $courseid=$instance->courseid;
	$course = get_course($courseid);
	$context = context_course::instance($courseid, MUST_EXIST);
} else {
    error('You must specify an instance ID');
}

require_login($course);


/// Set up the page header
$PAGE->set_url('/enrol/coupon/reports.php', array('id' => $instance->id));
//$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');


//get our javascript all ready to go
//We can omit $jsmodule, but its nice to have it here, 
//if for example we need to include some funky YUI stuff
/*
$jsmodule = array(
	'name'     => 'enrol_coupon',
	'fullpath' => '/enrol/coupon/module.js',
	'requires' => array()
);
//here we set up any info we need to pass into javascript
$opts =Array();

//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.enrol_coupon.helper.init', array($opts),false,$jsmodule);
*/


//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer('enrol_coupon');
$reportrenderer = $PAGE->get_renderer('enrol_coupon','report');

//From here we actually display the page.
//this is core renderer stuff
$mode = "reports";
$extraheader="";
switch ($showreport){

	//not a true report, separate implementation in renderer
	case 'menu':
		echo $renderer->header($instance, 'reports', null, get_string('reportstab', ENROL_COUPON_FRANKY));
		echo $reportrenderer->render_reportmenu($instance);
		// Finish the page
		echo $renderer->footer();
		return;
	
	case 'bulkcoupon':
		$report = new enrol_coupon_bulkcoupon_report($instance);
		$formdata = new stdClass();
		break;
		
	case 'allcoupon':
		$report = new enrol_coupon_allcoupon_report($instance);
		$formdata = new stdClass();
		break;
		
	case 'setcoupon':
		$report = new enrol_coupon_setcoupon_report($instance);
		$formdata = new stdClass();
		$formdata->typekey=$itemid;
		break;
		
	case 'coupondetails':
		$report = new enrol_coupon_coupondetails_report($instance);
		$formdata = new stdClass();
		$formdata->couponid = $itemid;
		break;
		
		
	case 'allusers':
		$report = new enrol_coupon_allusers_report($instance);
		$formdata = new stdClass();
		break;
		
	case 'setusers':
		$report = new enrol_coupon_setusers_report($instance);
		$formdata = new stdClass();
		$formdata->typekey=$itemid;
		break;
/*		
	case 'allattempts':
		$report = new mod_tquiz_allattempts_report();
		$formdata = new stdClass();
		$formdata->tquizid=$tquiz->id;
		$formdata->cmid=$cm->id;
		$extraheader = $reportrenderer->render_delete_allattempts($cm);
		break;
	
	
	case 'allusers':
		$report = new mod_tquiz_allusers_report();
		$formdata = new stdClass();
		$formdata->tquizid=$tquiz->id;
		break;	
		
	case 'questiondetails':
		$report = new mod_tquiz_questiondetails_report();
		$formdata = new stdClass();
		$formdata->questionid=$questionid;
		$formdata->tquizid=$tquiz->id;
		break;
		
	case 'attempt':
		$report = new mod_tquiz_attempt_report();
		$formdata = new stdClass();
		$formdata->userid=$userid;
		$formdata->attemptid=$attemptid;
		break;
		
	case 'responsedetails':
		$report = new mod_tquiz_responsedetails_report();
		$formdata = new stdClass();
		$formdata->questionid=$questionid;
		$formdata->attemptid=$attemptid;
		break;
*/		
	default:
		echo $renderer->header($instance, 'reports', null, get_string('reportstab', ENROL_COUPON_FRANKY));
		echo "unknown report type.";
		echo $renderer->footer();
		return;
}

/*
1) load the class
2) call report->process_raw_data
3) call $rows=report->fetch_formatted_records($withlinks=true(html) false(print/excel))
5) call $reportrenderer->render_section_html($sectiontitle, $report->name, $report->get_head, $rows, $report->fields);
*/

$report->process_raw_data($formdata);
$reportheading = $report->fetch_formatted_heading();

switch($format){
	case 'csv':
		$reportrows = $report->fetch_formatted_rows(false);
		$reportrenderer->render_section_csv($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		exit;
	default:
		
		$reportrows = $report->fetch_formatted_rows(true);
		echo $renderer->header($instance, 'reports', null, get_string('reportstab', ENROL_COUPON_FRANKY));
		echo $extraheader;
		echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		echo $reportrenderer->show_reports_footer($instance,$formdata,$showreport,$itemid);
		echo $renderer->footer();
}