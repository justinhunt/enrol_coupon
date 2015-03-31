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


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // tquiz instance ID - it should be named as the first character of the module
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$showreport = optional_param('report', 'menu', PARAM_TEXT); // report type
$questionid = optional_param('questionid', 0, PARAM_INT); // report type
$userid = optional_param('userid', 0, PARAM_INT); // report type
$attemptid = optional_param('attemptid', 0, PARAM_INT); // report type


if ($id) {
    $cm         = get_coursemodule_from_id('tquiz', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tquiz  = $DB->get_record('tquiz', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $tquiz  = $DB->get_record('tquiz', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $tquiz->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('tquiz', $tquiz->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

//Diverge logging logic at Moodle 2.7
if($CFG->version<2014051200){
	add_to_log($course->id, 'tquiz', 'reports', "reports.php?id={$cm->id}", $tquiz->name, $cm->id);
}else{
	// Trigger module viewed event.
	$event = \mod_tquiz\event\course_module_viewed::create(array(
	   'objectid' => $tquiz->id,
	   'context' => $modulecontext
	));
	$event->add_record_snapshot('course_modules', $cm);
	$event->add_record_snapshot('course', $course);
	$event->add_record_snapshot('tquiz', $tquiz);
	$event->trigger();
} 


/// Set up the page header
$PAGE->set_url('/mod/tquiz/reports.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tquiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('course');

	//Get an admin settings 
	$config = get_config('mod_tquiz');


//get our javascript all ready to go
//We can omit $jsmodule, but its nice to have it here, 
//if for example we need to include some funky YUI stuff
$jsmodule = array(
	'name'     => 'mod_tquiz',
	'fullpath' => '/mod/tquiz/module.js',
	'requires' => array()
);
//here we set up any info we need to pass into javascript
$opts =Array();

//this inits the M.mod_tquiz thingy, after the page has loaded.
$PAGE->requires->js_init_call('M.mod_tquiz.helper.init', array($opts),false,$jsmodule);


//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer('mod_tquiz');
$reportrenderer = $PAGE->get_renderer('mod_tquiz','report');

//From here we actually display the page.
//this is core renderer stuff
$mode = "reports";
$extraheader="";
switch ($showreport){

	//not a true report, separate implementation in renderer
	case 'menu':
		$questions = $DB->get_records('tquiz_questions',array('tquiz'=>$tquiz->id));
		echo $renderer->header($tquiz, $cm, $mode, null, get_string('reports', 'tquiz'));
		echo $reportrenderer->render_reportmenu($tquiz,$cm, $questions);
		// Finish the page
		echo $renderer->footer();
		return;
	
	case 'attemptlog':
		$report = new mod_tquiz_attemptlog_report();
		$formdata = new stdClass();
		$formdata->attemptid=$attemptid;
		break;
		
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
		
	default:
		echo $renderer->header($tquiz, $cm, $mode, null, get_string('reports', 'tquiz'));
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
		echo $renderer->header($tquiz, $cm, $mode, null, get_string('reports', 'tquiz'));
		echo $extraheader;
		echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		echo $reportrenderer->show_reports_footer($tquiz,$cm,$formdata,$showreport);
		echo $renderer->footer();
}