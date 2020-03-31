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
    public function header($instance, $currenttab = '', $itemid = null, $extrapagetitle = null) {
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
       * Returns HTML to display a single paging bar to provide access to other pages  (usually in a search)
       * @param int $totalcount The total number of entries available to be paged through
       * @param int $page The page you are currently viewing
       * @param int $perpage The number of entries that should be shown per page
       * @param string|moodle_url $baseurl url of the current page, the $pagevar parameter is added
       * @param string $pagevar name of page parameter that holds the page number
       * @return string the HTML to output.
       */
    function show_paging_bar($totalcount,$page,$perpage,$baseurl,$pagevar="pageno"){
    	return $this->output->paging_bar($totalcount,$page,$perpage,$baseurl,$pagevar);
    }
	
	/**
	 * Return the html table of coupons for a coupon enrol instance
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_coupons_list($coupons,$instance,$unsortedurl,$currentsort){
	
		if(!$coupons){
			return $this->output->heading(get_string('nocoupons','enrol_coupon'), 3, 'main');
		}
	
		$table = new html_table();
		$table->id = ENROL_COUPON_FRANKY . '_cpanel';
		$table->head = array(
			html_writer::link(new moodle_url($unsortedurl,array('sort'=>$currentsort =='iddsc' ? 'idasc' : 'iddsc')),get_string('id', 'enrol_coupon')),
			html_writer::link(new moodle_url($unsortedurl,array('sort'=>$currentsort =='namedsc' ? 'nameasc' : 'namedsc')),get_string('couponname', 'enrol_coupon')),
			html_writer::link(new moodle_url($unsortedurl,array('sort'=>$currentsort =='typedsc' ? 'typeasc' : 'typedsc')),get_string('coupontype', 'enrol_coupon')),
			html_writer::link(new moodle_url($unsortedurl,array('sort'=>$currentsort =='couponcodedsc' ? 'couponcodeasc' : 'couponcodedsc')),get_string('couponcode', 'enrol_coupon')),
			html_writer::link(new moodle_url($unsortedurl,array('sort'=>$currentsort =='maxusesdsc' ? 'maxusesasc' : 'maxusesdsc')),get_string('maxuses', 'enrol_coupon')),
			get_string('actions', 'enrol_coupon')
		);
		$table->headspan = array(1,1,1,1,1,3);
		$table->colclasses = array(
			'couponid','couponname','coupontype', 'couponcode','actions'
		);

		//sort by start date
		//core_collator::asort_objects_by_property($coupons,'timecreated',core_collator::SORT_NUMERIC);

		//loop through the homoworks and add to table
		foreach ($coupons as $coupon) {
			$row = new html_table_row();
		
			$couponidcell = new html_table_cell($coupon->id);	
			$couponnamecell = new html_table_cell($coupon->name);	
			switch($coupon->type){
				case ENROL_COUPON_TYPE_STANDARD:
					//actuallystandard should not enter results here, just in case
					$coupontype = get_string('standard',ENROL_COUPON_FRANKY);
					break;
				case ENROL_COUPON_TYPE_BULK:
					$coupontype = get_string('bulk',ENROL_COUPON_FRANKY);
					break;
				case ENROL_COUPON_TYPE_RANDOMBULK:
					$coupontype = get_string('randombulk',ENROL_COUPON_FRANKY);
					break;
				default:
					$coupontype = get_string('unknown',ENROL_COUPON_FRANKY);
					break;
			}
			$coupontypecell = new html_table_cell($coupontype);
			
			$couponcodecell = new html_table_cell($coupon->couponcode);
			
			$maxusescell = new html_table_cell($coupon->maxuses);
		
			$actionurl = '/enrol/coupon/managecoupons.php';
			$editurl = new moodle_url($actionurl, array('id'=>$instance->id,'couponid'=>$coupon->id));
			$editlink = html_writer::link($editurl, get_string('editcoupon', 'enrol_coupon'));
			$editcell = new html_table_cell($editlink);

			$reporturl = new moodle_url('/enrol/coupon/reports.php',array('itemid'=>$coupon->id, 'id'=>$instance->id, 'report'=>'coupondetails'));
			$reportlink = html_writer::link($reporturl, get_string('details', 'enrol_coupon'));
			$reportcell = new html_table_cell($reportlink);
		
			$deleteurl = new moodle_url($actionurl, array('id'=>$instance->id,'couponid'=>$coupon->id,'action'=>'confirmdelete'));
			$deletelink = html_writer::link($deleteurl, get_string('deletecoupon', 'enrol_coupon'));
			$deletecell = new html_table_cell($deletelink);

			$row->cells = array(
				$couponidcell, $couponnamecell, $coupontypecell, $couponcodecell, $maxusescell,$reportcell, $editcell, $deletecell
			);
			$table->data[] = $row;
		}

		return html_writer::table($table);

	}



}


/**
 * Renderer for coupon reports.
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_coupon_report_renderer extends plugin_renderer_base {


	public function render_reportmenu($instance) {
		
		$bulkcoupon = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$instance->id, 'report'=>'bulkcoupon')), 
			get_string('bulkcouponreport',ENROL_COUPON_FRANKY), 'get');
			
		$allcoupon = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$instance->id, 'report'=>'allcoupon')), 
			get_string('allcouponreport',ENROL_COUPON_FRANKY), 'get');
			
		$allusers = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$instance->id, 'report'=>'allusers')), 
			get_string('allusersreport',ENROL_COUPON_FRANKY), 'get');
/*
		$allusers = new single_button(
			new moodle_url('/enrol/coupon/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allusers')), 
			get_string('allusers','tquiz'), 'get');
			
		$ret = html_writer::div( $this->render($allattempts) . $this->render($allusers) ,ENROL_COUPON_FRANKY . '_listbuttons');
*/		
		$ret = html_writer::div($this->render($allcoupon) .  $this->render($allusers) . $this->render($bulkcoupon)  ,ENROL_COUPON_FRANKY . '_listbuttons');

		return $ret;
	}


	public function render_reporttitle_html($course,$username) {
		$ret = $this->output->heading(format_string($course->fullname),2);
		$ret .= $this->output->heading(get_string('reporttitle',ENROL_COUPON_FRANKY,$username),3);
		return $ret;
	}

	public function render_empty_section_html($sectiontitle) {
		global $CFG;
		return $this->output->heading(get_string('nodataavailable',ENROL_COUPON_FRANKY),3);
	}
	
	public function render_exportbuttons_html($instance,$formdata,$showreport,$itemid){
		//convert formdata to array
		$formdata = (array) $formdata;
		$formdata['id']=$instance->id;
		$formdata['itemid']=$itemid;
		$formdata['report']=$showreport;
		
		$formdata['format']='pdf';
		$pdf = new single_button(
			new moodle_url('/enrol/coupon/reports.php',$formdata),
			get_string('exportpdf',ENROL_COUPON_FRANKY), 'get');
		
		$formdata['format']='csv';
		$excel = new single_button(
			new moodle_url('/enrol/coupon/reports.php',$formdata), 
			get_string('exportexcel',ENROL_COUPON_FRANKY), 'get');

		//return html_writer::div( $this->render($pdf) . $this->render($excel),ENROL_COUPON_FRANKY . '_actionbuttons');
		return html_writer::div( $this->render($excel),ENROL_COUPON_FRANKY . '_actionbuttons');
	}
	
	public function render_continuebuttons_html($course){
		$backtocourse = new single_button(
			new moodle_url('/course/view.php',array('id'=>$course->id)), 
			get_string('backtocourse',ENROL_COUPON_FRANKY), 'get');
		
		$selectanother = new single_button(
			new moodle_url('/enrol/coupon/index.php',array('id'=>$course->id)), 
			get_string('selectanother',ENROL_COUPON_FRANKY), 'get');
			
		return html_writer::div($this->render($backtocourse) . $this->render($selectanother),ENROL_COUPON_FRANKY . '_listbuttons');
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
	}
/*
	public function render_delete_allattempts($cm){
		$deleteallbutton = new single_button(
				new moodle_url('/enrol/coupon/manageattempts.php',array('id'=>$cm->id,'action'=>'confirmdeleteall')), 
				get_string('deleteallattempts','tquiz'), 'get');
		$ret =  html_writer::div( $this->render($deleteallbutton) ,ENROL_COUPON_FRANKY . '_actionbuttons');
		return $ret;
	}
*/	
	public function render_section_html($sectiontitle, $report, $head, $rows, $fields) {
		global $CFG;
		if(empty($rows)){
			return $this->render_empty_section_html($sectiontitle);
		}
		
		//set up our table and head attributes
		$tableattributes = array('class'=>'generaltable ' . ENROL_COUPON_FRANKY . '_table');
		$headrow_attributes = array('class'=> ENROL_COUPON_FRANKY . '_headrow');
		
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
	
	function show_reports_footer($instance, $formdata,$showreport,$itemid){
		// print's a popup link to your custom page
		$link = new moodle_url('/enrol/coupon/reports.php',array('id'=>$instance->id));
		$ret =  html_writer::link($link, get_string('returntoreports',ENROL_COUPON_FRANKY));
		$ret .= $this->render_exportbuttons_html($instance,$formdata,$showreport,$itemid);
		return $ret;
	}

}