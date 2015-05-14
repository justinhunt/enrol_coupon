<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

namespace enrol_coupon\provider;

require_once($CFG->dirroot . '/enrol/coupon/lib.php');

/**
 * Enrol Coupon Provider Base Plugin
 *
 * @package    enrol_coupon
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */

abstract class providerbase  {

	const PROVIDERTYPE='base';
	const TABLE='enrol_coupon_provider';
	const DATATABLE='enrol_coupon_providerdata';
	
	public $course = 0;
	protected $active = false;
	public $status = 0;

	

	 /**
     *  Constructor
     */
    public function __construct()
    {
      $this->_cache = array();
    }
	
	 /**
     *  Construct instance from DB record
     */
	 public static function get_by_record($record){
		$wlm = new static();
		foreach(get_object_vars($record) as $propname=>$propvalue){
			$wlm->{$propname}=$propvalue;
		}
		return $wlm;
	}
	
	public static function get_display_name(){
		return get_string(static::PROVIDERTYPE . '_displayname', 'enrol_coupon');
	}

	public function get_providertype(){
		return static::METHODTYPE;
	}
	
	  /**
     * Add new instance of method with default settings.
     * @param stdClass $course
     * @return int id of new instance, null if can not be created
     */
    public static function add_default_instance($courseid,$instanceid) {
    	global $DB;
        	$rec = new \stdClass();
			$rec->courseid = $courseid;
			$rec->instanceid = $instanceid;
			$rec->providertype = static::PROVIDERTYPE;
			$rec->status = false;
			$rec->emailalert=true;
			$id = $DB->insert_record(self::TABLE,$rec);
			if($id){
				$rec->id = $id;
				return $rec;
			}else{
				return $id;
			}
    }

	
	 //activation functions
	 public function is_active(){return $this->status;}
	 
	 //activate
	 public function activate(){
		global $DB;
		$this->status=true;
		$updateobject = new \stdClass;
		$updateobject->id=$this->id;
		$updateobject->status=true;
		$DB->update_record(self::TABLE,$updateobject);
	 }
	public function deactivate(){
		global $DB;
		$this->status=false;
		$updateobject = new \stdClass;
		$updateobject->id=$this->id;
		$updateobject->status=false;
		$DB->update_record(self::TABLE,$updateobject);
	 }
	 
	 public function get_type(){return static::PROVIDERTYPE;}


	
	/**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
		return array();
	}
	
	/**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass instance
     * @return null
     */
    public function enrol_page_hook(\stdClass $instance) {
        return null;
    }
	
	
    
    /**
     * Get the email template to send
     *
     * @param stdClass $waitinglist instance data
     * @param string $message key
     * @return void
     */
    protected function get_email_template($waitinglist,$messagekey='') {
    	/*
    	if (trim($this->{static::MFIELD_WAITLISTMESSAGE}) !== '') {
    		$message = $this->{static::MFIELD_WAITLISTMESSAGE};
    	}else{
    		$message = get_string('waitlistmessagetext_' . static::METHODTYPE, 'enrol_waitinglist');
    	}
	     return $message;
	     */
    }
    
    
    /**
     * Send  email to specified user telling them they are waitlisted
     *
     * @param stdClass $instance
     * @param stdClass $user user record
     * @return void
     */
    protected function email_waitlist_message($waitinglist, $entry, $user, $messagekey='') {
        global $CFG, $DB;
/*
        $course = $DB->get_record('course', array('id'=>$waitinglist->courseid), '*', MUST_EXIST);
        $context =  \context_course::instance($course->id);

        $a = new  \stdClass();
        $a->coursename = format_string($course->fullname, true, array('context'=>$context));
        $a->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid;
        $a->editenrolurl = $CFG->wwwroot . '/enrol/waitinglist/edit_enrolform.php?id=' . 
        		$waitinglist->courseid . '&methodtype=' . static::METHODTYPE;

		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$seatsonqueue = $entry->seats - $entry->allocseats;
		if($seatsonqueue > 0){
			$qposition= $queueman->get_listtotal($entry->id);
		}else{
			$qposition= 0;
			$seatsonqueue= 0;
		}
        $a->queueno = $qposition;
        $a->totalseats = $entry->seats;
        $a->allocatedseats = $entry->allocseats;
        $a->waitingseats = $seatsonqueue;


        $message = $this->get_email_template($waitinglist,$messagekey);
		$message = str_replace('{$a->coursename}', $a->coursename, $message);
		$message = str_replace('{$a->courseurl}', $a->courseurl, $message);
		$message = str_replace('{$a->editenrolurl}', $a->editenrolurl, $message);
		$message = str_replace('{$a->queueno}', $a->queueno, $message);
		$message = str_replace('{$a->totalseats}', $a->totalseats, $message);
		$message = str_replace('{$a->queueseats}', $a->totalseats, $message);//legacy
		$message = str_replace('{$a->waitingseats}', $a->waitingseats, $message);
		$message = str_replace('{$a->allocatedseats}', $a->allocatedseats, $message);
		
		if (strpos($message, '<') === false) {
			// Plain text only.
			$messagetext = $message;
			$messagehtml = text_to_html($messagetext, null, false, true);
		} else {
			$messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
			$messagetext = html_to_text($messagehtml);
		}
      

        $subject = get_string('waitlistmessagetitle' . $messagekey . '_' . static::METHODTYPE, 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)));

        $rusers = array();
        if (!empty($CFG->coursecontact)) {
            $croles = explode(',', $CFG->coursecontact);
            list($sort, $sortparams) = users_order_by_sql('u');
            $rusers = get_role_users($croles, $context, true, '', 'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
        }
        if ($rusers) {
            $contact = reset($rusers);
        } else {
            $contact =  \core_user::get_support_user();
        }

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
        */
    }


	 //some methods such as "unnamed bulk" don't enrol onto course automatically
	 //others like "self" do. We check for that here
	
	 public  function can_enrol(\stdClass $instance, $checkuserenrolment = true){return false;}
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return false;}
	 public  function get_dummy_form_plugin(){return false;}
	 

}
