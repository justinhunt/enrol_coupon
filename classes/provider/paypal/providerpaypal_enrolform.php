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
 * Coupon Enrol Method Paypal enrolment Plugin
 *
 * @package    enrol_coupon
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_coupon\provider\paypal;
 
require_once($CFG->libdir.'/formslib.php');


class providerpaypal_enrolform extends \moodleform {

    protected $method;
    protected $method;
    protected $waitinglist;
    protected $queuestatus;
    protected $toomany = true;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
		list( $waitinglist,$method,$queuestatus) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
    	global $CFG;
    
        $mform = $this->_form;
       list( $waitinglist,$method,$queuestatus) = $this->_customdata;
        $this->method = $method;
        $this->waitinglist = $waitinglist;
        $this->queuestatus=$queuestatus;
        
        $plugin = enrol_get_plugin('waitinglist');

        $heading = $plugin->get_instance_name($waitinglist);
        $mform->addElement('header', 'selfheader', $heading. ' : ' . get_string('unnamedbulk_menutitle','enrol_waitinglist'));
        
        $mform->addElement('static','formintro',
			'',
			get_string('unnamedbulk_enrolformintro','enrol_waitinglist'));
        
        //add caution for number of seats available, and waiting list size etc
        if($queuestatus->hasentry){
			$mform->addElement('static','aboutqueuestatus',
			get_string('unnamedbulk_enrolformqueuestatus_label','enrol_waitinglist'),
			get_string('unnamedbulk_enrolformqueuestatus','enrol_waitinglist',$queuestatus));
        }
        
        //add form input elements
        $mform->addElement('text','seats',  get_string('reserveseatcount', 'enrol_waitinglist'), array('size' => '8'));
		$mform->addRule('seats', null, 'numeric', null, 'client');
		$mform->setType('seats', PARAM_INT);

        

		$mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $waitinglist->courseid);
		$mform->addElement('hidden', 'waitinglist');
        $mform->setType('waitinglist', PARAM_INT);
		$mform->setDefault('waitinglist', $waitinglist->id);
		$mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
		$mform->setDefault('methodtype', $this->method->get_methodtype());
		$mform->addElement('hidden', 'datarecordid');
        $mform->setType('datarecordid', PARAM_INT);
		
		//add submit + enter course
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('reserveseats', 'enrol_waitinglist'));
		if($queuestatus->assignedseats>0){
			$url = $CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid;
			$buttonarray[] = &$mform->createElement('button', 'entercoursebutton', get_string('entercoursenow', 'enrol_waitinglist'),array('class'=>'entercoursenowbutton','onclick'=>'location.href="' . $url .'"'));
		}
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		
		//use this in place of button group, if you don't need the go to course button
		//$this->add_action_buttons(false, get_string('reserveseats', 'enrol_waitinglist'));

    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $method = $this->method;
        $queuestatus = $this->queuestatus;
        $waitinglist = $this->waitinglist;

	   $availabletouser = ($queuestatus->waitlistsize - $queuestatus->queueposition) + 
	   		($queuestatus->vacancies + $queuestatus->assignedseats);
       //if($queuestatus->waitlistsize && $queuestatus->waitlistsize  < ($queuestatus->queueposition + $data['seats'])){
       if($availabletouser  < $data['seats']){
       		$available = $queuestatus->waitlistsize - $queuestatus->queueposition - $queuestatus->waitingseats;
       		$a = new \stdClass;
       		$a->available = $available;
       		$a->vacancies =  $queuestatus->vacancies;
        	$errors['seats'] = get_string('nomoreseats', 'enrol_waitinglist', $a);
        	return $errors;
        }

        return $errors;
    }
}
