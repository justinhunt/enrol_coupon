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

/**
 * Forms for Coupon Enrolment
 *
 * @package    enrol_coupon
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/enrol/coupon/lib.php');

/**
 * Abstract class that question type's inherit from.
 *
 * This is the abstract class that add question type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class enrol_coupon_base_form extends moodleform {

    /**
     * This is the classic define that is used to identify this questiontype.
     * @var string
     */
    public $type;
    public $typestring;



    /**
     * Each question type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}


    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $editmode = $customdata['editmode'];
	
        $mform->addElement('header', 'typeheading', get_string('editingacoupon', 'enrol_coupon', get_string($this->typestring, 'enrol_coupon')));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'couponid');
        $mform->setType('couponid', PARAM_INT);

		$mform->addElement('hidden', 'type');
		$mform->setType('type', PARAM_INT);
		
		$mform->addElement('hidden', 'couponaction');
		$mform->setType('couponaction', PARAM_INT);
		
		$mform->addElement('hidden', 'couponvalue');
		$mform->setType('couponvalue', PARAM_INT);

		$mform->addElement('text', 'name', get_string('couponname', 'enrol_coupon'), array('size'=>70));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string('required'), 'required', null, 'client');
		
		$mform->addElement('textarea', 'description', get_string('description'), 'wrap="virtual" rows="4" cols="50"');
		$mform->setType('description', PARAM_TEXT);
		
			
        $this->custom_definition();
        

		
		$mform->addElement('text', 'maxuses', get_string('maxuses', 'enrol_coupon'));
		$mform->setType('maxuses', PARAM_INT);
		$mform->setDefault('maxuses', 1);
		$mform->addRule('maxuses', get_string('required'), 'required', null, 'client');
		
        $options = array('optional'=>true);
        $mform->addElement('date_selector', 'fromdate',get_string('fromdate','enrol_coupon'), $options);
        $mform->addElement('date_selector', 'todate',get_string('todate','enrol_coupon'), $options);

		$mform->addElement('text', 'duration', get_string('duration', 'enrol_coupon'));
		$mform->setType('duration', PARAM_INT);
		$mform->setDefault('duration', 0);
		$mform->addRule('duration', get_string('required'), 'required', null, 'client');


		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('savecoupon', 'enrol_coupon'));

    }

}

//this is the standard form for creating a multi choice question
class enrol_coupon_standard_form extends enrol_coupon_base_form {

    public $type = ENROL_COUPON_TYPE_STANDARD;
    public $typestring = 'standard';

    public function custom_definition() {
		$mform = $this->_form;

		$mform->addElement('text', 'couponcode', get_string('couponcode', 'enrol_coupon'), array('size'=>70));
		$mform->setType('couponcode', PARAM_ALPHANUMEXT);
		$mform->addRule('couponcode', get_string('required'), 'required', null, 'client');

	}
}
//this is the standard form for creating a multi choice question
class enrol_coupon_bulk_form extends enrol_coupon_base_form {

    public $type = ENROL_COUPON_TYPE_BULK;
    public $typestring = 'bulk';

    public function custom_definition() {
		$mform = $this->_form;
		$customdata = $this->_customdata;
        $editmode = $customdata['editmode'];
		
		if(!$editmode){
			$mform->addElement('textarea', 'couponcode', get_string('couponcode', 'enrol_coupon'), array('cols'=>'20', 'rows'=>'15'));
			$mform->setType('couponcode', PARAM_TEXT);
			$mform->addRule('couponcode', get_string('required'), 'required', null, 'client');
		}else{
			$mform->addElement('text', 'couponcode', get_string('couponcode', 'enrol_coupon'), array('size'=>70));
			$mform->setType('couponcode', PARAM_ALPHANUMEXT);
			$mform->addRule('couponcode', get_string('required'), 'required', null, 'client');		
		}
	}
}

//this is the standard form for creating a multi choice question
class enrol_coupon_randombulk_form extends enrol_coupon_base_form {

    public $type = ENROL_COUPON_TYPE_RANDOMBULK;
    public $typestring = 'randombulk';

    public function custom_definition() {
		$mform = $this->_form;
		$customdata = $this->_customdata;
        $editmode = $customdata['editmode'];
		
		//number of coupons to create
		$mform->addElement('text', 'couponcount', get_string('couponcount', 'enrol_coupon'));
		$mform->setType('couponcount', PARAM_INT);
		$mform->setDefault('couponcount', 5);
		$mform->addRule('couponcount', get_string('required'), 'required', null, 'client');

		//coupon code in this case is the stub or prefix if its new, or just code if its editig
		if(!$editmode){
			$mform->addElement('text', 'couponcode', get_string('couponprefix', 'enrol_coupon'), array('size'=>70));		
		}else{
			$mform->addElement('text', 'couponcode', get_string('couponcode', 'enrol_coupon'), array('size'=>70));		
		}
		$mform->setType('couponcode', PARAM_ALPHANUMEXT);
		$mform->addRule('couponcode', get_string('required'), 'required', null, 'client');
		

	}
}

