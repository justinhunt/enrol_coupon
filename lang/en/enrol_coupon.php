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
 * Strings for component 'enrol_coupon', language 'en'.
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['canntenrol'] = 'Enrolment is disabled or inactive';
$string['cohortnonmemberinfo'] = 'Only members of cohort \'{$a}\' can enrol here.';
$string['cohortonly'] = 'Only cohort members';
$string['cohortonly_help'] = 'Coupon enrolment may be restricted to members of a specified cohort only. Note that changing this setting has no effect on existing enrolments.';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to user\'s profile page {$a->profileurl}';
$string['custombuttontext'] = 'Custom button text';
$string['custombuttontext_help'] = 'The text to show on the button to submit the coupon. If not specified will be "Enrol Me".';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during coupon enrolment';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can enrol themselves until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolme'] = 'Enrol me';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user enrols themselves. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can enrol themselves from this date onward only.';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'Coupon enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'Coupon enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Coupon enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['longtimenosee'] = 'Unenrol inactive after';
$string['longtimenosee_help'] = 'If users haven\'t accessed a course for a long time, then they are automatically unenrolled. This parameter specifies that time limit.';
$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can coupon enrol. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to coupon-enrol was already reached.';
$string['messageprovider:expiry_notification'] = 'Coupon enrolment expiry notifications';
$string['newenrols'] = 'Allow new enrolments';
$string['newenrols_desc'] = 'Allow users to coupon enrol into new courses by default.';
$string['newenrols_help'] = 'This setting determines whether a user can enrol into this course.';
$string['nopassword'] = 'No enrolment key required.';
$string['password'] = 'Enrolment key';
$string['password_help'] = 'An enrolment key enables access to the course to be restricted to only those who know the key.

If the field is left blank, any user may enrol in the course.

If an enrolment key is specified, any user attempting to enrol in the course will be required to supply the key. Note that a user only needs to supply the enrolment key ONCE, when they enrol in the course.';
$string['passwordinvalid'] = 'Incorrect enrolment key, please try again';
$string['passwordinvalidhint'] = 'That enrolment key was incorrect, please try again<br />
(Here\'s a hint - it starts with \'{$a}\')';
$string['pluginname'] = 'Coupon enrolment';
$string['pluginname_desc'] = 'The coupon enrolment plugin allows users to choose which courses they want to participate in. The courses may be protected by an enrolment key. Internally the enrolment is done via the manual enrolment plugin which has to be enabled in the same course.';
$string['requirepassword'] = 'Require enrolment key';
$string['requirepassword_desc'] = 'Require enrolment key in new courses and prevent removing of enrolment key from existing courses.';
$string['role'] = 'Default assigned role';
$string['coupon:config'] = 'Configure coupon enrol instances';
$string['coupon:manage'] = 'Manage enrolled users';
$string['coupon:unenrol'] = 'Unenrol users from course';
$string['coupon:unenrolself'] = 'Unenrol self from the course';
$string['sendcoursewelcomemessage'] = 'Send course welcome message';
$string['sendcoursewelcomemessage_help'] = 'If enabled, users receive a welcome message via email when they coupon-enrol in a course.';
$string['showhint'] = 'Show hint';
$string['showhint_desc'] = 'Show first letter of the guest access key.';
$string['status'] = 'Enable existing enrolments';
$string['status_desc'] = 'Enable coupon enrolment method in new courses.';
$string['status_help'] = 'If disabled all existing coupon enrolments are suspended and new users can not enrol.';
$string['unenrol'] = 'Unenrol user';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['usepasswordpolicy'] = 'Use password policy';
$string['usepasswordpolicy_desc'] = 'Use standard password policy for enrolment keys.';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';
$string['whatdonow'] = 'What do you want to do?';
$string['standard'] = 'standard';
$string['bulk'] = 'bulk';
$string['randombulk'] = 'random bulk';
$string['unknown'] = 'unknown';
$string['addstandardcoupon'] = 'Add standard coupon';
$string['addbulkcoupon'] = 'Add bulk coupons';
$string['addrandombulkcoupon'] = 'Add random bulk coupons';
$string['nocoupons'] = 'There are no coupons active.';
$string['couponname'] = 'Name';
$string['coupontype'] = 'Type';
$string['maxuses'] = 'Max. Uses';
$string['fromdate'] = 'From Date';
$string['todate'] = 'To Date';
$string['duration'] = 'Duration (days)';
$string['actions'] = 'Actions';
$string['standard'] = 'Standard';
$string['editcoupon'] = 'Edit Coupon';
$string['deletecoupon'] = 'Delete Coupon';
$string['overview'] = 'Overview';
$string['overview_help'] = 'Overview Help';
$string['viewcoupons'] = 'View Coupons';
$string['reports'] = 'Reports';
$string['viewreports'] = 'View Reports';
$string['manage'] = 'Manage Coupons';
$string['managecoupons'] = 'Manage Coupons';
$string['editingcoupon'] = 'Editing Coupon';
$string['confirmcoupondelete'] = 'Are you sure you want to delete coupon: {$a}';
$string['confirmcoupondeletetitle'] = 'Really Delete the Coupon?';
$string['editingacoupon'] = 'Editing a {$a} Coupon';
$string['savecoupon'] = 'Save Coupon';
$string['standard'] = 'Standard';
$string['randombulk'] = 'Random Bulk';
$string['bulk'] = 'Bulk';
$string['base'] = 'Base';
$string['couponcount'] = 'Coupon Count';
$string['couponcode'] = 'Coupon Code';
$string['nocouponcode'] = 'No coupon code required';
$string['nocouponsregistered'] = 'No coupons registered for this enrolment method';
$string['showcouponfield'] = 'Coupon Field on Enrol Form';
$string['showcouponfield_help'] = 'If yes(default) a coupon field will be shown on the sign up form.';
$string['showcouponfield_desc'] = 'If yes(default) a coupon field will be shown on the sign up form.';
$string['invalidcouponcode'] = 'The submitted coupon code was invalid';
$string['alreadyusedcouponcode'] = 'The submitted coupon code has already been used the maximum number of times.';
$string['notyetcouponcode'] = 'The submitted coupon code can not be used yet.';
$string['toolatecouponcode'] = 'The submitted coupon code has expired.';
$string['managetab'] = 'Manage';
$string['reportstab'] = 'Reports';
$string['coupontype'] = 'Coupon Type';
$string['datecreated'] = 'Date Created';
$string['dateredeemed'] = 'Use Date';
$string['user'] = 'User';
$string['details'] = 'details';
$string['totalcoupons'] = 'Total Coupons'; 
$string['totalseats'] = 'Total Seats';
$string['usedseats'] = 'Used Seats';  
$string['exportpdf'] = 'Export as PDF';
$string['exportexcel'] = 'Export as Excel';
$string['viewreport'] = 'view report';
$string['details'] = 'Use Report';
$string['usecount'] = 'Use Count';
$string['couponprefix'] = 'Coupon Prefix';
$string['id'] = 'ID';
$string['coupondetailsreport'] = 'Report for {$a->couponname} : {$a->couponcode}      Use Count: {$a->usecount} / {$a->totalseats} ';
/* $string['allcouponsreport'] = 'All Coupons Report'; */
$string['allusersreport'] = 'All Users Report';
$string['setusersreport'] = 'Coupon Set Users Report';
$string['allcouponreport'] = 'All Coupons Report';
$string['setcouponreport'] = 'Coupon Set Report';
$string['allbulkcouponreport'] = 'Bulk/Random Coupons';
$string['bulkcouponreport'] = 'Bulk/Random Coupons Report';
$string['nodataavailable'] = 'No data available for display';
$string['returntoreports'] = 'Return to Reports Home';
$string['policyagreementclick'] = 'View coupon terms and conditions (new window)';
$string['coupontermsurl'] = 'Terms of Use URL';
$string['coupontermsurl_help'] = 'Enter the URL to the coupon terms and conditions if they exist. If this box is not empty the user will be shown a checkbox and a link to view the terms. They must agree and check the checkbox to submit the coupon code.';