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


defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/enrol/coupon/forms.php');
require_once($CFG->dirroot.'/enrol/coupon/locallib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package enrol_coupon
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_coupon_renderer extends plugin_renderer_base {

	  /**
     * Returns the header for the enrol coupon module
     *
     * @param enrol instance $instance an enrolment instance
     * @param string $currenttab current tab that is shown.
     * @param int    $coupon id of the question that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($instance, $currenttab = '', $couponid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = $instance->name;
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
       $context = context_course::instance($instance->courseid, MUST_EXIST);

    /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();

        if (has_capability('enrol/coupon:manage', $context)) {
            $output .= $this->output->heading_with_help($activityname, 'overview', 'enrol_coupon');

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/enrol/coupon/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }

        return $output;
    }

    
    /**
     * Return HTML to display limited header
     */
      public function notabsheader(){
      	return $this->output->header();
      }
	
	 /**
     * Return HTML to display add first page links
     * @param lesson $lesson
     * @return string
     */
 public function add_edit_page_links($instance) {
		global $CFG;
        $couponid = 0;

        $output = $this->output->heading(get_string("whatdonow", "enrol_coupon"), 3);
        $links = array();

        $addstandardcouponurl = new moodle_url('/enrol/coupon/managecoupons.php',
			array('id'=>$instance->id, 'couponid'=>$couponid, 'type'=>ENROL_COUPON_TYPE_STANDARD));
        $links[] = html_writer::link($addstandardcouponurl, get_string('addstandardcoupon', 'enrol_coupon'));
        
        $addbulkcouponurl = new moodle_url('/enrol/coupon/managecoupons.php',
			array('id'=>$instance->id, 'couponid'=>$couponid, 'type'=>ENROL_COUPON_TYPE_BULK));
        $links[] = html_writer::link($addbulkcouponurl, get_string('addbulkcoupon', 'enrol_coupon'));
        
        $addrandombulkcouponurl = new moodle_url('/enrol/coupon/managecoupons.php',
			array('id'=>$instance->id, 'couponid'=>$couponid, 'type'=>ENROL_COUPON_TYPE_RANDOMBULK));
        $links[] = html_writer::link($addrandombulkcouponurl, get_string('addrandombulkcoupon', 'enrol_coupon'));
 
        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }
	
	/**
	 * Return the html table of coupons for a coupon enrol instance
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_coupons_list($coupons,$instance){
	
		if(!$coupons){
			return $this->output->heading(get_string('nocoupons','enrol_coupon'), 3, 'main');
		}
	
		$table = new html_table();
		$table->id = 'enrol_coupon_cpanel';
		$table->head = array(
			get_string('couponname', 'enrol_coupon'),
			get_string('coupontype', 'enrol_coupon'),
			get_string('couponcode', 'enrol_coupon'),
			get_string('maxuses', 'enrol_coupon'),
			get_string('actions', 'enrol_coupon')
		);
		$table->headspan = array(1,1,1,1,3);
		$table->colclasses = array(
			'couponname','coupontype', 'couponcode','actions'
		);

		//sort by start date
		core_collator::asort_objects_by_property($coupons,'timecreated',core_collator::SORT_NUMERIC);

		//loop through the homoworks and add to table
		foreach ($coupons as $coupon) {
			$row = new html_table_row();
		
		
			$couponnamecell = new html_table_cell($coupon->name);	
			switch($coupon->type){
				case ENROL_COUPON_TYPE_STANDARD:
				default:
					$coupontype = get_string('standard','enrol_coupon');
					break;
				
			} 
			$coupontypecell = new html_table_cell($coupontype);
			
			$couponcodecell = new html_table_cell($coupon->couponcode);
			
			$maxusescell = new html_table_cell($coupon->maxuses);
		
			$actionurl = '/enrol/coupon/managecoupons.php';
			$editurl = new moodle_url($actionurl, array('id'=>$instance->id,'couponid'=>$coupon->id));
			$editlink = html_writer::link($editurl, get_string('editcoupon', 'enrol_coupon'));
			$editcell = new html_table_cell($editlink);
			
			$viewlink = $this->fetch_view_link($coupon->id,$instance->id);
			$viewcell = new html_table_cell($viewlink);
		
			$deleteurl = new moodle_url($actionurl, array('id'=>$instance->id,'couponid'=>$coupon->id,'action'=>'confirmdelete'));
			$deletelink = html_writer::link($deleteurl, get_string('deletecoupon', 'enrol_coupon'));
			$deletecell = new html_table_cell($deletelink);

			$row->cells = array(
				$couponnamecell, $coupontypecell, $couponcodecell, $maxusescell,$viewcell, $editcell, $deletecell
			);
			$table->data[] = $row;
		}

		return html_writer::table($table);

	}

	
	
	function fetch_view_link($couponid, $instanceid){
		// print's a popup link to your custom page
		$link = new moodle_url('/enrol/coupon/reports.php',array('couponid'=>$couponid, 'id'=>$instanceid));
		return  $this->output->action_link($link, get_string('reports','enrol_coupon'), 
			new popup_action('click', $link));
	
	}
	
	
	/*
	
	public function fetch_question_div($question, $tquiz,$modulecontext){
			$q = $this->fetch_question_display($question, $tquiz,$modulecontext);
			$q .= $this->fetch_answers_display($question, $tquiz,$modulecontext);
			return html_writer::tag('div', $q, array('class'=>'enrol_coupon_qdiv','id'=>'tquiz_qdiv_' . $question->id));
	}
	*/

}


/**
 * Renderer for coupon reports.
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_coupon_report_renderer extends plugin_renderer_base {


	public function render_reportmenu($tquiz,$cm, $coupons) {
		
		$allattempts = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allattempts')), 
			get_string('attemptsmanager','tquiz'), 'get');
		/*
		$allsummary = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'summary')), 
			get_string('allsummary','tquiz'), 'get');
		*/
		$allusers = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allusers')), 
			get_string('allusers','tquiz'), 'get');
			
		$ret = html_writer::div( $this->render($allattempts) . $this->render($allusers) ,'enrol_coupon_listbuttons');
		
		foreach($coupons as $coupon){	
			$qdetails = new single_button(
				new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questiondetails', 'questionid'=>$coupon->id)), 
				get_string('questiondetails','tquiz', $coupon->name), 'get');
			/*
			$qsummary= new single_button(
				new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questionsummary', 'questionid'=>$coupon->id)), 
				get_string('questionsummary','tquiz', $coupon->name), 'get');
				
			$ret .= html_writer::div( $this->render($qsummary) . $this->render($qdetails),'enrol_coupon_listbuttons');
			*/
			$ret .= html_writer::div( $this->render($qdetails),'enrol_coupon_listbuttons');
		}

		return $ret;
	}


	public function render_reporttitle_html($course,$username) {
		$ret = $this->output->heading(format_string($course->fullname),2);
		$ret .= $this->output->heading(get_string('reporttitle','tquiz',$username),3);
		return $ret;
	}

	public function render_empty_section_html($sectiontitle) {
		global $CFG;
		return $this->output->heading(get_string('nodataavailable','tquiz'),3);
	}
	
	public function render_exportbuttons_html($cm,$formdata,$showreport){
		//convert formdata to array
		$formdata = (array) $formdata;
		$formdata['id']=$cm->id;
		$formdata['report']=$showreport;
		
		$formdata['format']='pdf';
		$pdf = new single_button(
			new moodle_url('/enrol/coupon/reports.php',$formdata),
			get_string('exportpdf','tquiz'), 'get');
		
		$formdata['format']='csv';
		$excel = new single_button(
			new moodle_url('/enrol/coupon/reports.php',$formdata), 
			get_string('exportexcel','tquiz'), 'get');

		//return html_writer::div( $this->render($pdf) . $this->render($excel),'enrol_coupon_actionbuttons');
		return html_writer::div( $this->render($excel),'enrol_coupon_actionbuttons');
	}
	
	public function render_continuebuttons_html($course){
		$backtocourse = new single_button(
			new moodle_url('/course/view.php',array('id'=>$course->id)), 
			get_string('backtocourse','tquiz'), 'get');
		
		$selectanother = new single_button(
			new moodle_url('/enrol/coupon/index.php',array('id'=>$course->id)), 
			get_string('selectanother','tquiz'), 'get');
			
		return html_writer::div($this->render($backtocourse) . $this->render($selectanother),'tquiz_listbuttons');
	}
	
	public function render_section_csv($sectiontitle, $report, $head, $rows, $fields) {

        // Use the sectiontitle as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($sectiontitle, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
		$quote = '"';
		$delim= ",";//"\t";
		$newline = "\r\n";

		header("Content-Disposition: attachment; filename=$name.csv");
		header("Content-Type: text/comma-separated-values");

		//echo header
		$heading="";	
		foreach($head as $headfield){
			$heading .= $quote . $headfield . $quote . $delim ;
		}
		echo $heading. $newline;
		
		//echo data rows
        foreach ($rows as $row) {
			$datarow = "";
			foreach($fields as $field){
				$datarow .= $quote . $row->{$field} . $quote . $delim ;
			}
			 echo $datarow . $newline;
		}
        exit();
        break;
	}

	public function render_delete_allattempts($cm){
		$deleteallbutton = new single_button(
				new moodle_url('/enrol/coupon/manageattempts.php',array('id'=>$cm->id,'action'=>'confirmdeleteall')), 
				get_string('deleteallattempts','tquiz'), 'get');
		$ret =  html_writer::div( $this->render($deleteallbutton) ,'enrol_coupon_actionbuttons');
		return $ret;
	}
	
	public function render_section_html($sectiontitle, $report, $head, $rows, $fields) {
		global $CFG;
		if(empty($rows)){
			return $this->render_empty_section_html($sectiontitle);
		}
		
		//set up our table and head attributes
		$tableattributes = array('class'=>'generaltable tquiz_table');
		$headrow_attributes = array('class'=>'tquiz_headrow');
		
		$htmltable = new html_table();
		$htmltable->attributes = $tableattributes;
		
		
		$htr = new html_table_row();
		$htr->attributes = $headrow_attributes;
		foreach($head as $headcell){
			$htr->cells[]=new html_table_cell($headcell);
		}
		$htmltable->data[]=$htr;
		
		foreach($rows as $row){
			$htr = new html_table_row();
			//set up descrption cell
			$cells = array();
			foreach($fields as $field){
				$cell = new html_table_cell($row->{$field});
				$cell->attributes= array('class'=>'tquiz_cell_' . $report . '_' . $field);
				$htr->cells[] = $cell;
			}

			$htmltable->data[]=$htr;
		}
		$html = $this->output->heading($sectiontitle, 4);
		$html .= html_writer::table($htmltable);
		return $html;
		
	}
	
	function show_reports_footer($tquiz,$cm, $formdata,$showreport){
		// print's a popup link to your custom page
		$link = new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id));
		$ret =  html_writer::link($link, get_string('returntoreports','enrol_coupon'));
		$ret .= $this->render_exportbuttons_html($cm,$formdata,$showreport);
		return $ret;
	}

}


