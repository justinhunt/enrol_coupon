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
 * Coupon enrol plugin implementation.
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("lib.php");

class enrol_coupon_helper{

	/**
     * Get existing coupon codes for this enrolment instance
     * @param integer instance id
     * @return array() array of couponcode=>id pairs
     */
	public function get_existing_couponcodes($instanceid){
		global $DB;
		$existingcodes=$DB->get_records_menu(ENROL_COUPON_TABLE_COUPON,
			array('instanceid'=>$instanceid),'couponcode','couponcode,id');
		return $existingcodes;

	}
	
	/**
     * use coupon
     * @param integer instance id
     * @param text couponcode
     * @return boolean true=successful | false =unsuccessful
     */
	public function use_couponcode($instanceid, $couponcode){
		global $DB,$USER;
		//do we have such a coupon
		/*
		$code=$DB->get_record(ENROL_COUPON_TABLE_COUPON,
			array('instanceid'=>$instanceid, 'couponcode'=>$couponcode));
		*/
		$sql="SELECT * FROM {".ENROL_COUPON_TABLE_COUPON."} WHERE instanceid = " . 
		 	$instanceid . " AND " .$DB->sql_compare_text('couponcode') . "= :couponcode";
		$code = $DB->get_record_sql($sql, array('couponcode'=>  $couponcode));
		if(!$code){
			return get_string('invalidcouponcode', 'enrol_coupon');
		}
		
		//is the coupon used already
		$codeuses=$DB->get_records(ENROL_COUPON_TABLE_USER,
			array('instanceid'=>$instanceid, 'couponid'=>$code->id));
		if($codeuses && count($codeuses)>=$code->maxuses){
			return get_string('alreadyusedcouponcode', 'enrol_coupon');
		}
		
		//can the coupon still be used
		$now = time();
		if(!($code->fromdate ==0 || $now > $code->fromdate)){
			return get_string('notyetcouponcode', 'enrol_coupon');
		}   
		if(!($code->todate ==0 || $now < $code->todate)){
			return get_string('toolatecouponcode', 'enrol_coupon');
		} 
		
		//add a user entry and return
		//maybe we shouldn't add an entry here ... to chce
		$usercode = new stdClass();
		$usercode->couponid = $code->id;
		$usercode->instanceid=$code->instanceid;
		$usercode->courseid = $code->courseid;
		$usercode->userid=$USER->id;
		$usercode->usedate=time();
		$DB->insert_record(ENROL_COUPON_TABLE_USER,$usercode);
		
		//return true
		return true;

	}
	
	/**
     * Get type key that links bulk or randombulk entries together
     * @param integer instance id
     * @return string key that binds individual coupons together as a unit for bulk admin
     */
	public function get_new_typekey($instanceid){
		global $DB;
		$ret = $DB->get_record_sql('SELECT MAX(typekey) AS maxtypekey, 1		
                                     FROM {'. ENROL_COUPON_TABLE_COUPON .'} WHERE instanceid=' . $instanceid);
        if(empty($ret->maxtypekey)){
        	$maxtypekey=1;
        }else{
        	$maxtypekey=$ret->maxtypekey + 1;
        }
        return $maxtypekey;
	}
	
	/**
     * Get a random coupon code
     * @param string couponprefix
     * @return string a random coupon code
     */
	public function generate_couponcode($coupon_prefix,$length=5){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen($characters);
		$random_string = '';
		for ($i = 0; $i < $length; $i++) {
			$random_string .= $characters[rand(0, $characters_length - 1)];
		}
		return $coupon_prefix . $random_string;
		
	}

}

class enrol_coupon_enrol_form extends moodleform {
    protected $instance;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        $mform = $this->_form;
       	$instance = $this->_customdata;
		$buttontext = $instance->{ENROL_COUPON_ENROL_BUTTON_TEXT};
       	if(empty($buttontext)){$buttontext = get_string('enrolme', 'enrol_coupon');}
        $this->instance = $instance;
        $plugin = enrol_get_plugin(ENROL_COUPON_ENROLTYPE);

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'selfheader', $heading);
		
		//to show or hide couponcode
        if ($instance->{ENROL_COUPON_SHOW_COUPON_FIELD}) {
            // Change the id of self enrolment key input as there can be multiple self enrolment methods.
            $mform->addElement('text', 'couponcode', get_string('couponcode', 'enrol_coupon'),
                    array('id' => 'couponcode_'.$instance->id));
        } else {
            $mform->addElement('static', 'nocouponcode', '', get_string('nocouponcode', 'enrol_coupon'));
            $mform->addElement('hidden', 'couponcode');
        }
		
		$url =$instance->{ENROL_COUPON_COUPONTERMS_URL};
		if(!empty(trim($url))){
            $mform->addElement('static', 'policylink', '', '<a href="'.$url.'" onclick="this.target=\'_blank\'">'.get_string('policyagreementclick','enrol_coupon').'</a>');
            $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
            $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
		}

        $this->add_action_buttons(false, $buttontext);

		//settings for important fields
 		$mform->setType('couponcode', PARAM_ALPHANUMEXT);
        $mform->setDefault('couponcode', '');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

        if ($instance->{ENROL_COUPON_SHOW_COUPON_FIELD}) {
        	$helper = new enrol_coupon_helper();
        	$wasvalid = $helper->use_couponcode($instance->id, $data['couponcode']);
            if ($wasvalid !==true) {             
                $errors['couponcode'] = $wasvalid;
            }
        }

        return $errors;
    }
}
