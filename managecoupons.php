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
 * Action for adding/editing a coupon 
 *
 * @package enrol_coupon
 * @copyright  2015 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../config.php");
require_once($CFG->dirroot.'/enrol/coupon/forms.php');
require_once($CFG->dirroot.'/enrol/coupon/locallib.php');

global $USER,$DB;

// first get the nfo passed in to set up the page
$couponid = optional_param('couponid',0 ,PARAM_INT);
$id     = required_param('id', PARAM_INT);         // Coupon Enrolment Instance ID
$type     = optional_param('type',ENROL_COUPON_TYPE_STANDARD, PARAM_INT);
$action = optional_param('action','edit',PARAM_TEXT);

$instance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);
$courseid=$instance->courseid;
$course = get_course($courseid);
$mode='manage';
$chelper = new enrol_coupon_helper();
$context = context_course::instance($courseid, MUST_EXIST);

if ($courseid == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);

$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/coupon/managecoupons.php', array('id'=>$id, 'couponid'=>$couponid, 'action'=>$action));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managecoupons', 'enrol_coupon'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('managecoupons', 'enrol_coupon'));

/*
$renderer = $PAGE->get_renderer('enrol_coupon');
echo $renderer->header($instance);
*/


//are we in new or edit mode?
if ($couponid) {
    $coupon = $DB->get_record('enrol_coupon_coupons', array('id'=>$couponid), '*', MUST_EXIST);
	if(!$coupon){
		print_error('could not find coupon of id:' . $couponid);
	}
    $type = $coupon->type;
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the main coupons page
$redirecturl = new moodle_url('/enrol/coupon/viewcoupons.php', array('id'=>$id));

	//handle delete actions
    if($action == 'confirmdelete'){
		$renderer = $PAGE->get_renderer('enrol_coupon');
		echo $renderer->header($instance,$mode, null, get_string('confirmcoupondeletetitle', 'enrol_coupon'));
		echo $renderer->confirm(get_string("confirmcoupondelete","enrol_coupon",$coupon->name), 
			new moodle_url('managecoupons.php', array('action'=>'delete','id'=>$instance->id,'couponid'=>$couponid)), 
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete Question NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		//$success = enrol_coupon_delete_coupon($instance,$couponid);
		$DB->delete_records("enrol_coupon_coupons",array('id'=>$couponid));
        redirect($redirecturl);
	
    }


//get the mform for our coupon
switch($type){
	case ENROL_COUPON_TYPE_STANDARD:
		$mform = new enrol_coupon_standard_form(null,array('editmode'=>$edit));
		break;
	case ENROL_COUPON_TYPE_BULK:
		$mform = new enrol_coupon_bulk_form(null,array('editmode'=>$edit));
		break;
	case ENROL_COUPON_TYPE_RANDOMBULK:
		$mform = new enrol_coupon_randombulk_form(null,array('editmode'=>$edit));
		break;
	default:
		print_error('No coupon type specifified');

}

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the coupons page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$thecoupon = new stdClass;
        $thecoupon->instanceid = $instance->id;
        $thecoupon->id = $data->couponid;
		$thecoupon->courseid = $courseid;
		$thecoupon->name = $data->name;
		$thecoupon->description = $data->description;
		$thecoupon->couponcode = trim($data->couponcode);
		$thecoupon->type = $data->type;
	
		//currently unused, but later to be combined with payments
		$thecoupon->couponaction = $data->couponaction;
		$thecoupon->couponvalue = $data->couponvalue;
		
		
		$thecoupon->maxuses = $data->maxuses;
		$thecoupon->fromdate = $data->fromdate;
		$thecoupon->todate = $data->todate;
		$thecoupon->duration = $data->duration;
		$thecoupon->timemodified=time();
		
		//Insert a new coupon if we need to
		if(!$edit){	
			$thecoupon->createdby=$USER->id;
			$thecoupon->timecreated=$thecoupon->timemodified;
			switch($thecoupon->type){
				
				case ENROL_COUPON_TYPE_BULK:
					$existingcodes= $chelper->get_existing_couponcodes($instance->id);
					$thecoupon->typekey=$chelper->get_new_typekey($instance->id);
					$codes = explode("\n",$data->couponcode);
					//add as many coupons as couponcode count
					foreach($codes as $code){
						if(array_key_exists($code,$existingcodes)){
							//error out or continue?
							//continue;
							print_error("Could not insert the coupon!" . $code);
							redirect($redirecturl);
						}
						$existingcodes[$code]=1;
						$thecoupon->couponcode = trim($code);
						
						//save the coupon
						if (!$thecoupon->id = $DB->insert_record(ENROL_COUPON_TABLE_COUPON,$thecoupon)){
							print_error("Could not insert the coupon!");
							redirect($redirecturl);
						}
					}
					break;
				case ENROL_COUPON_TYPE_RANDOMBULK:
					$existingcodes= $chelper->get_existing_couponcodes($instance->id);
					$thecoupon->typekey=$chelper->get_new_typekey($instance->id);
					//add as many coupons as couponcode count
					for($x=0;$x<$data->couponcount;$x++){
						//generate a unique couponcode from stub
						$newcode = $chelper->generate_couponcode($data->couponcode);
						$emergencyexit=0;
						while(array_key_exists($newcode,$existingcodes)){
							if($emergencyexit>1000){
								print_error("Could not generate a non existing coupon!");
								redirect($redirecturl);
							}
							$emergency++;
							$newcode = $chelper->generate_couponcode($data->couponcode);
						}
						$existingcodes[$newcode]=1;
						$thecoupon->couponcode = trim($newcode);
						
						//save the coupon
						if (!$thecoupon->id = $DB->insert_record(ENROL_COUPON_TABLE_COUPON,$thecoupon)){
							print_error("Could not insert the coupon!");
							redirect($redirecturl);
						}
		
					}
					break;
				case ENROL_COUPON_TYPE_STANDARD:
				default:
					$thecoupon->typekey=0;
					if (!$thecoupon->id = $DB->insert_record(ENROL_COUPON_TABLE_COUPON,$thecoupon)){
						print_error("Could not insert the coupon!" . $thecoupon->couponcode);
						redirect($redirecturl);
					}
					break;

			}
			
		//otherwise update it
		}else{	
			//now update the db once we have saved files and stuff
			if (!$DB->update_record(ENROL_COUPON_TABLE_COUPON,$thecoupon)){
					print_error("Could not update coupon!");
					redirect($redirecturl);
			}
		}
		
		//go back to edit quiz page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the coupon into a data object
if ($edit) {
	$data = $coupon;
	$data->couponid = $coupon->id;
	$data->id=$instance->id;		
	 
}else{
	$data=new stdClass;
	$data->id = $instance->id;
	$data->couponid = null;
	$data->type=$type;
}
		

    $mform->set_data($data);
    $PAGE->navbar->add(get_string('edit'), new moodle_url('/enrol/coupon/managecoupons.php', array('id'=>$id, 'couponid'=>$couponid)));
    $PAGE->navbar->add(get_string('editingcoupon', 'enrol_coupon'));
	$renderer = $PAGE->get_renderer('enrol_coupon');
	echo $renderer->header($instance,$mode, null, get_string('edit'));
	$mform->display();
	echo $renderer->footer();