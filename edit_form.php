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
 * Adds new instance of enrol_coupon to specified course
 * or edits current instance.
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once("lib.php");

class enrol_coupon_edit_form extends moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_coupon'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_coupon'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_coupon');

        $options = array(1 => get_string('yes'), 0 => get_string('no'));
        $mform->addElement('select', ENROL_COUPON_ALLOW_NEW_ENROLS, get_string('newenrols', 'enrol_coupon'), $options);
        $mform->addHelpButton(ENROL_COUPON_ALLOW_NEW_ENROLS, 'newenrols', 'enrol_coupon');
        $mform->disabledIf(ENROL_COUPON_ALLOW_NEW_ENROLS, 'status', 'eq', ENROL_INSTANCE_DISABLED);

        $options = array(1 => get_string('yes'),
                         0 => get_string('no'));
        $mform->addElement('select', ENROL_COUPON_SHOW_COUPON_FIELD, get_string('showcouponfield', 'enrol_coupon'), $options);
        $mform->addHelpButton(ENROL_COUPON_SHOW_COUPON_FIELD, 'showcouponfield', 'enrol_coupon');
        $mform->setDefault(ENROL_COUPON_SHOW_COUPON_FIELD, 1);

        $roles = $this->extend_assignable_roles($context, $instance->roleid);
        $mform->addElement('select', 'roleid', get_string('role', 'enrol_coupon'), $roles);

        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_coupon'), array('optional' => true, 'defaultunit' => 86400));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_coupon');

        $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        $mform->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), array('optional' => false, 'defaultunit' => 86400));
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_coupon'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_coupon');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_coupon'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_coupon');

        $options = array(0 => get_string('never'),
                 1800 * 3600 * 24 => get_string('numdays', '', 1800),
                 1000 * 3600 * 24 => get_string('numdays', '', 1000),
                 365 * 3600 * 24 => get_string('numdays', '', 365),
                 180 * 3600 * 24 => get_string('numdays', '', 180),
                 150 * 3600 * 24 => get_string('numdays', '', 150),
                 120 * 3600 * 24 => get_string('numdays', '', 120),
                 90 * 3600 * 24 => get_string('numdays', '', 90),
                 60 * 3600 * 24 => get_string('numdays', '', 60),
                 30 * 3600 * 24 => get_string('numdays', '', 30),
                 21 * 3600 * 24 => get_string('numdays', '', 21),
                 14 * 3600 * 24 => get_string('numdays', '', 14),
                 7 * 3600 * 24 => get_string('numdays', '', 7));
        $mform->addElement('select', ENROL_COUPON_UNENROL_DAYS, get_string('longtimenosee', 'enrol_coupon'), $options);
        $mform->addHelpButton(ENROL_COUPON_UNENROL_DAYS, 'longtimenosee', 'enrol_coupon');

        $mform->addElement('text', ENROL_COUPON_MAX_ENROLS, get_string('maxenrolled', 'enrol_coupon'));
        $mform->addHelpButton(ENROL_COUPON_MAX_ENROLS, 'maxenrolled', 'enrol_coupon');
        $mform->setType(ENROL_COUPON_MAX_ENROLS, PARAM_INT);

        $cohorts = array(0 => get_string('no'));
        list($sqlparents, $params) = $DB->get_in_or_equal($context->get_parent_context_ids(), SQL_PARAMS_NAMED);
        $params['current'] = $instance->{ENROL_COUPON_COHORT_ONLY};
        $sql = "SELECT id, name, idnumber, contextid
                  FROM {cohort}
                 WHERE contextid $sqlparents OR id = :current
              ORDER BY name ASC, idnumber ASC";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $c) {
            $ccontext = context::instance_by_id($c->contextid);
            if ($c->id != $instance->{ENROL_COUPON_COHORT_ONLY} and !has_capability('moodle/cohort:view', $ccontext)) {
                continue;
            }
            $cohorts[$c->id] = format_string($c->name, true, array('context'=>$context));
            if ($c->idnumber) {
                $cohorts[$c->id] .= ' ['.s($c->idnumber).']';
            }
        }
        if (!isset($cohorts[$instance->{ENROL_COUPON_COHORT_ONLY}])) {
            // Somebody deleted a cohort, better keep the wrong value so that random ppl can not enrol.
            $cohorts[$instance->{ENROL_COUPON_COHORT_ONLY}] = get_string('unknowncohort', 'cohort', $instance->{ENROL_COUPON_COHORT_ONLY});
        }
        $rs->close();
        if (count($cohorts) > 1) {
            $mform->addElement('select', ENROL_COUPON_COHORT_ONLY, get_string('cohortonly', 'enrol_coupon'), $cohorts);
            $mform->addHelpButton(ENROL_COUPON_COHORT_ONLY, 'cohortonly', 'enrol_coupon');
        } else {
            $mform->addElement('hidden', ENROL_COUPON_COHORT_ONLY);
            $mform->setType(ENROL_COUPON_COHORT_ONLY, PARAM_INT);
            $mform->setConstant(ENROL_COUPON_COHORT_ONLY, 0);
        }

        $mform->addElement('advcheckbox', ENROL_COUPON_SEND_COURSE_WELCOME, get_string('sendcoursewelcomemessage', 'enrol_coupon'));
        $mform->addHelpButton(ENROL_COUPON_SEND_COURSE_WELCOME, 'sendcoursewelcomemessage', 'enrol_coupon');

        $mform->addElement('textarea', ENROL_COUPON_CUSTOM_WELCOME_TEXT, get_string('customwelcomemessage', 'enrol_coupon'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton(ENROL_COUPON_CUSTOM_WELCOME_TEXT, 'customwelcomemessage', 'enrol_coupon');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;
 

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_coupon');
            }
        }

        if ($data['expirynotify'] > 0 and $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        return $errors;
    }

    /**
    * Gets a list of roles that this user can assign for the course as the default for self-enrolment.
    *
    * @param context $context the context.
    * @param integer $defaultrole the id of the role that is set as the default for self-enrolment
    * @return array index is the role id, value is the role name
    */
    function extend_assignable_roles($context, $defaultrole) {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id'=>$defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }
}
