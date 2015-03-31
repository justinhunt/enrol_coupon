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
 * TQuiz Report Renderer.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for tquiz reports.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tquiz_reportrenderer extends plugin_renderer_base {

	public function render_reportmenu($tquiz,$cm, $questions) {
		
		$allattempts = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allattempts')), 
			get_string('allattempts','tquiz'), 'get');
		$allsummary = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'summary')), 
			get_string('allsummary','tquiz'), 'get');
		$allusers = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'allusers')), 
			get_string('allusers','tquiz'), 'get');
			
		$ret = html_writer::div( $this->render($allattempts) . $this->render($allusers) . $this->render($allsummary) ,'tquiz_listbuttons');
		
		foreach($questions as $question){	
			$qdetails = new single_button(
				new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questiondetails', 'questionid'=>$question->id)), 
				get_string('questiondetails','tquiz', $question->name), 'get');
			$qsummary= new single_button(
				new moodle_url('/mod/tquiz/reports.php',array('id'=>$cm->id, 'n'=>$tquiz->id, 'report'=>'questionsummary', 'questionid'=>$question->id)), 
				get_string('questionsummary','tquiz', $question->name), 'get');
				
			$ret .= html_writer::div( $this->render($qsummary) . $this->render($qdetails),'tquiz_listbuttons');
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
		return "";
	}
	
	public function render_exportbuttons_html($course,$selecteduser){
		$pdf = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$course->id, 'userid'=>$selecteduser->id, 'format'=>RLCR_FORMAT_PDF, 'action'=>'doexport')),
			get_string('exportpdf','tquiz'), 'get');
		
		$excel = new single_button(
			new moodle_url('/mod/tquiz/reports.php',array('id'=>$course->id, 'userid'=>$selecteduser->id, 'format'=>RLCR_FORMAT_EXCEL, 'action'=>'doexport')), 
			get_string('exportexcel','tquiz'), 'get');

		return html_writer::div( $this->render($pdf) . $this->render($excel),'tquiz_listbuttons');
	}
	
	public function render_continuebuttons_html($course){
		$backtocourse = new single_button(
			new moodle_url('/course/view.php',array('id'=>$course->id)), 
			get_string('backtocourse','tquiz'), 'get');
		
		$selectanother = new single_button(
			new moodle_url('/mod/tquiz/index.php',array('id'=>$course->id)), 
			get_string('selectanother','tquiz'), 'get');
			
		return html_writer::div($this->render($backtocourse) . $this->render($selectanother),'tquiz_listbuttons');
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
}
