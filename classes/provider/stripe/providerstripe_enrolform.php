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
 * Coupon Enrol Method Stripe enrolment Plugin
 *
 * @package    enrol_coupon
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_coupon\provider\stripe;
 
require_once($CFG->libdir.'/formslib.php');


class providerstripe_enrolform extends \moodleform {
    protected $method;


    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
		list( $waitinglist,$method,$listtotal) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        $mform = $this->_form;
       list( $waitinglist,$method,$listtotal) = $this->_customdata;
        $this->method = $method;
        $plugin = enrol_get_plugin('waitinglist');

        $heading = $plugin->get_instance_name($waitinglist);
       $mform->addElement('header', 'selfheader', $heading. ' : ' . get_string('self_menutitle','enrol_waitinglist'));
       
       //queuewarning
       if($listtotal>0){
       	$mform->addElement('static','queuewarning',get_string('self_queuewarning_label','enrol_waitinglist'),get_string('self_queuewarning','enrol_waitinglist',$listtotal));
       }
       
        if ($method->password) {
            // Change the id of self enrolment key input as there can be multiple self enrolment methods.
			//NB actually this probably doesnt apply to waitinglist self enrolment, but just to be safe
            $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_self'),
                    array('id' => 'enrolpassword_'.$method->id));
        } else {
            $mform->addElement('static', 'nokey', '', get_string('nopassword', 'enrol_self'));
        }

        $this->add_action_buttons(false, get_string('enrolme', 'enrol_self'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $waitinglist->courseid);
        
        $mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
		$mform->setDefault('methodtype', $this->method->get_methodtype());

        $mform->addElement('hidden', 'waitinglist');
        $mform->setType('waitinglist', PARAM_INT);
        $mform->setDefault('waitinglist', $waitinglist->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $method = $this->method;


        if ($method->password) {
            if ($data['enrolpassword'] !== $method->password) {
                if ($method->{enrolmethodself::MFIELD_GROUPKEY}) {
                    $groups = $DB->get_records('groups', array('courseid'=>$method->courseid), 'id ASC', 'id, enrolmentkey');
                    $found = false;
                    foreach ($groups as $group) {
                        if (empty($group->enrolmentkey)) {
                            continue;
                        }
                        if ($group->enrolmentkey === $data['enrolpassword']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }

                } else {
                    $plugin = enrol_get_plugin('self');
                    if ($plugin->get_config('showhint')) {
                        $hint = core_text::substr($method->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_self', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }
                }
            }
        }

        return $errors;
    }
}
