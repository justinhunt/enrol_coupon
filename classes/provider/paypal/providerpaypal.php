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
 * Coupon Enrol Method Paypal enrolment Plugin
 *
 * @package    enrol_coupon
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_coupon\provider\paypal;

class providerpaypal extends \enrol_coupon\provider\providerbase {

	const PROVIDERTYPE='paypal';
	protected $active = false;

	const MFIELD_MAXENROLLED = 'customint3';
	const MFIELD_SENDWAITLISTMESSAGE = 'customint4';
	const MFIELD_WAITLISTMESSAGE = 'customtext1';
	const MFIELD_SENDCONFIRMMESSAGE = 'customint5';
	const MFIELD_CONFIRMEDMESSAGE = 'customtext2';
	//const DFIELD_SEATS = 'customint1';
	const QFIELD_ASSIGNEDSEATS = 'allocseats';
	const QFIELD_SEATS = 'seats';
	
	
	public $course = 0;
    public $waitlist = 0;
	public $maxseats = 0;
    public $activeseats = 0;
	public $notificationtypes = 0;
	

    
}