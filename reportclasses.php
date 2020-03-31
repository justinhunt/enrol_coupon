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
 * Coupon Report Classes.
 *
 * @package    enrol_coupon
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for tquiz reports.
 *
 *	The important functions are:
*  process_raw_data : turns log data for one thig (question attempt) into one row
 * fetch_formatted_fields: uses data prepared in process_raw_data to make each field in fields full of formatted data
 * The allusers report is the simplest example 
 *
 * @package    enrol_coupon
 * @copyright  2015 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class enrol_coupon_base_report {

    protected $report="";
    protected $head=array();
	protected $rawdata=null;
    protected $fields = array();
	protected $dbcache=array();
	protected $instance=null;
	
	abstract function process_raw_data($formdata);
	abstract function fetch_formatted_heading();
	
	function __construct($instance){
		$this->instance = $instance;
	}
	
	public function fetch_fields(){
		return $this->fields;
	}
	public function fetch_head(){
		$head=array();
		foreach($this->fields as $field){
			$head[]=get_string($field,ENROL_COUPON_FRANKY);
		}
		return $head;
	}
	public function fetch_name(){
		return $this->report;
	}


	public function fetch_cache($table,$rowid){
		global $DB;
		if(!array_key_exists($table,$this->dbcache)){
			$this->dbcache[$table]=array();
		}
		if(!array_key_exists($rowid,$this->dbcache[$table])){
			$this->dbcache[$table][$rowid]=$DB->get_record($table,array('id'=>$rowid));
		}
		return $this->dbcache[$table][$rowid];
	}

	public function fetch_time_difference($starttimestamp,$endtimestamp){
			
			//return empty string if the timestamps are not both present.
			if(!$starttimestamp || !$endtimestamp){return '';}
			
			$s = $date = new DateTime();
			$s->setTimestamp($starttimestamp);
						
			$e =$date = new DateTime();
			$e->setTimestamp($endtimestamp);
						
			$diff = $e->diff($s);
			$ret = $diff->format("%H:%I:%S");
			return $ret;
	}
	
	public function fetch_time_difference_js($starttimestamp,$endtimestamp){
			
			//return empty string if the timestamps are not both present.
			if(!$starttimestamp || !$endtimestamp){return '';}
			
			$s = $date = new DateTime(); 
			$s->setTimestamp($starttimestamp / 1000);
						
			$e =$date = new DateTime();
			$e->setTimestamp($endtimestamp / 1000);
						
			$diff = $e->diff($s);
			$ret = $diff->format("%H:%I:%S");
			return $ret;
	}
	
	public function fetch_formatted_rows($withlinks=true){
		$records = $this->rawdata;
		$fields = $this->fields;
		$returndata = array();
		foreach($records as $record){
			$data = new stdClass();
			foreach($fields as $field){
				$data->{$field}=$this->fetch_formatted_field($field,$record,$withlinks);
			}//end of for each field
			$returndata[]=$data;
		}//end of for each record
		return $returndata;
	}
	

	
}



/*
* enrol_coupon_setoverview_report 
*
*
*/
class enrol_coupon_setcoupon_report extends  enrol_coupon_allcoupon_report {
	
	protected $report="setcoupon";
	
	public function fetch_formatted_heading(){
		return get_string('setcouponreport',ENROL_COUPON_FRANKY);
	}
	
	protected function fetch_data_sql($formdata){
		$sql = 'SELECT c.id as couponid, c.couponcode , c.name as couponname,  c.maxuses, c.typekey, COUNT(u.id) as usecount,type as coupontype, timecreated as datecreated ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_COUPON.'} c ';
		$sql .= 'LEFT OUTER JOIN {'.ENROL_COUPON_TABLE_USER . '} u ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		$sql .= 'AND c.typekey = '. $formdata->typekey . ' ';
		$sql .= 'GROUP BY c.id ';
		return $sql;
	
	}
	
	
}

/*
* enrol_coupon_allcoupon_report 
*
*
*/
class enrol_coupon_allcoupon_report extends  enrol_coupon_base_report {
	
	protected $report="allcoupon";
	protected $fields = array('couponname','coupontype','couponcode','maxuses','usecount','datecreated');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'datecreated':
					$ret=date("Y-m-d",$record->datecreated);
					break;
				case 'coupontype':
					switch($record->coupontype){
						case ENROL_COUPON_TYPE_STANDARD:
							//actuallystandard should not enter results here, just in case
							$ret = get_string('standard',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_BULK:
							$ret = get_string('bulk',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_RANDOMBULK:
							$ret = get_string('randombulk',ENROL_COUPON_FRANKY);
							break;
						default:
							$ret = get_string('unknown',ENROL_COUPON_FRANKY);
							break;
					}
					break;
				case 'usecount':
					if(property_exists($record,'usecount')){
						if($withlinks && $record->usecount > 0){
							$link = new moodle_url('/enrol/coupon/reports.php',array('id'=>$this->instance->id, 'report'=>'coupondetails','itemid'=>$record->couponid ));
							$ret =  html_writer::link($link, $record->usecount);
						}else{
							$ret = $record->usecount;
						}
					}else{
						$ret = '';
					}
					break;
				case 'couponname':
				case 'maxuses':
				case 'couponcode':
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allcouponreport',ENROL_COUPON_FRANKY);
	}
	
	protected function fetch_data_sql($formdata){
		$sql = 'SELECT c.id as couponid, c.couponcode , c.typekey, c.name as couponname,  c.maxuses, COUNT(u.id) as usecount,type as coupontype, timecreated as datecreated ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_COUPON.'} c ';
		$sql .= 'LEFT OUTER JOIN {'.ENROL_COUPON_TABLE_USER . '} u ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		$sql .= 'GROUP BY c.id ';
		
		return $sql;
	
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		$sql = $this->fetch_data_sql($formdata);
		
		
		$coupons = $DB->get_records_sql($sql);
		
		if($coupons){
			$this->rawdata= $coupons;
		}else{
			$this->rawdata= array();
		}
		return true;
	}

}


/*
* enrol_coupon_attempt_report 
*
*
*/
class enrol_coupon_bulkcoupon_report extends  enrol_coupon_base_report {
	
	protected $report="bulkcoupon";
	protected $fields = array('couponname','coupontype','totalcoupons','totalseats','usedseats','datecreated');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'datecreated':
					$ret=date("Y-m-d",$record->datecreated);
					break;
				case 'coupontype':
					switch($record->coupontype){
						case ENROL_COUPON_TYPE_STANDARD:
							//actuallystandard should not enter results here, just in case
							$ret = get_string('standard',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_BULK:
							$ret = get_string('bulk',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_RANDOMBULK:
							$ret = get_string('randombulk',ENROL_COUPON_FRANKY);
							break;
						default:
							$ret = get_string('unknown',ENROL_COUPON_FRANKY);
							break;
					}
					break;
				case 'couponname':
					if(property_exists($record,'couponname')){
						if($withlinks){
							$link = new moodle_url('/enrol/coupon/reports.php',array('id'=>$this->instance->id, 'report'=>'setcoupon','itemid'=>$record->typekey ));
							$ret =  html_writer::link($link, $record->couponname);
						}else{
							$ret = $record->couponname;
						}
					}else{
						$ret = '';
					}
					break;
				
				case 'usedseats':
					if($withlinks && $record->usedseats !=0 ){
						$link = new moodle_url('/enrol/coupon/reports.php',array('id'=>$this->instance->id, 'report'=>'setusers','itemid'=>$record->typekey ));
						$ret =  html_writer::link($link, $ret=$record->{$field});	
					}else{
						$ret=$record->usedseats;
					}
					break;
					
				case 'totalcoupons':
				case 'totalseats':
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allbulkcouponreport',ENROL_COUPON_FRANKY);
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		$sql = 'SELECT c.name as couponname, count(c.id) as totalcoupons, c.typekey as typekey, sum(maxuses) as totalseats, COUNT(u.id) as usedseats,type as coupontype, timecreated as datecreated ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_COUPON.'} c ';
		$sql .= 'LEFT OUTER JOIN {'.ENROL_COUPON_TABLE_USER . '} u ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		$sql .= 'AND c.type IN (' . ENROL_COUPON_TYPE_BULK . ',' . ENROL_COUPON_TYPE_RANDOMBULK .') ';
		$sql .= 'GROUP BY c.typekey ';
		
		$bulkcoupons = $DB->get_records_sql($sql);
		
		if($bulkcoupons){
			$this->rawdata= $bulkcoupons;
		}else{
			$this->rawdata= array();
		}
		return true;
	}

}

/*
* enrol_coupon_coupondetails_report 
*
*
*/
class enrol_coupon_coupondetails_report extends  enrol_coupon_base_report {
	
	protected $report="coupondetails";
	protected $fields = array('coupontype','user','dateredeemed');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'dateredeemed':
					$ret=date("Y-m-d",$record->dateredeemed);
					break;
				case 'coupontype':
					switch($record->coupontype){
						case ENROL_COUPON_TYPE_STANDARD:
							//actuallystandard should not enter results here, just in case
							$ret = get_string('standard',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_BULK:
							$ret = get_string('bulk',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_RANDOMBULK:
							$ret = get_string('randombulk',ENROL_COUPON_FRANKY);
							break;
						default:
							$ret = get_string('unknown',ENROL_COUPON_FRANKY);
							break;
					}
					break;
				case 'user':
					$ret = '';
					if(property_exists($record,'user') && $record->user ){
						$user = $DB->get_record('user',array('id'=>$record->user ));
						if($user){
							$ret=fullname($user);
						}
					}
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('coupondetailsreport',ENROL_COUPON_FRANKY, $this->headingdata);
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		$sql = 'SELECT c.couponcode as couponcode, c.name as couponname, maxuses as totalseats,type as coupontype, timecreated as datecreated, u.userid as user, u.usedate as dateredeemed ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_COUPON.'} c ';
		$sql .= 'LEFT OUTER JOIN {'.ENROL_COUPON_TABLE_USER . '} u ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		$sql .= 'AND c.id = '. $formdata->couponid . ' ';	
		$bulkcoupons = $DB->get_records_sql($sql);
		
		//set headingdata
		$this->headingdata = new stdClass();
		$this->headingdata->usecount=0;
		$this->headingdata->couponcode=0;
		$commondata=false;
		foreach($bulkcoupons as $coupon){
			if(!$commondata){
				$this->headingdata->couponname = $coupon->couponname;
				$this->headingdata->couponcode = $coupon->couponcode;
				$this->headingdata->totalseats = $coupon->totalseats;
				$this->headingdata->coupontype = $coupon->coupontype;
				$commondata=true;
			}
			if($coupon->user){
				$this->headingdata->usecount =  $this->headingdata->usecount + 1;
			}
		}
		
		//if we have no user data, just set empty array, so we show a nice message to user
		if($this->headingdata->usecount <1){
			$this->rawdata= array();
		}else{
			$this->rawdata= $bulkcoupons;
		}
		return true;
	}
	
}

/*
* enrol_coupon_setusers_report 
*
*
*/
class enrol_coupon_setusers_report extends  enrol_coupon_allusers_report {
	protected $report="setusers";
	public function fetch_formatted_heading(){
		return get_string('setusersreport',ENROL_COUPON_FRANKY, $this->headingdata);
	}
	
	protected function fetch_data_sql($formdata){
		$sql = 'SELECT c.couponcode as couponcode, c.name as couponname,type as coupontype, u.userid as user, u.usedate as dateredeemed ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_USER.'} u ';
		$sql .= 'INNER JOIN {'.ENROL_COUPON_TABLE_COUPON . '} c ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		$sql .= 'AND c.typekey = '. $formdata->typekey . ' ';
		return $sql;
	
	}


}

/*
* enrol_coupon_allusers_report 
*
*
*/
class enrol_coupon_allusers_report extends  enrol_coupon_base_report {
	
	protected $report="allusers";
	protected $fields = array('couponcode','user','coupontype','dateredeemed');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
			global $DB;
			switch($field){
				case 'dateredeemed':
					$ret=date("Y-m-d",$record->dateredeemed);
					break;
				case 'coupontype':
					switch($record->coupontype){
						case ENROL_COUPON_TYPE_STANDARD:
							//actuallystandard should not enter results here, just in case
							$ret = get_string('standard',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_BULK:
							$ret = get_string('bulk',ENROL_COUPON_FRANKY);
							break;
						case ENROL_COUPON_TYPE_RANDOMBULK:
							$ret = get_string('randombulk',ENROL_COUPON_FRANKY);
							break;
						default:
							$ret = get_string('unknown',ENROL_COUPON_FRANKY);
							break;
					}
					break;
				case 'user':
					$ret = '';
					if(property_exists($record,'user') && $record->user ){
						$user = $DB->get_record('user',array('id'=>$record->user ));
						if($user){
							$ret=fullname($user);
						}
					}
					break;
				case 'couponcode':
					$ret = $record->couponcode;
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allusersreport',ENROL_COUPON_FRANKY, $this->headingdata);
	}
	
	protected function fetch_data_sql($formdata){
		$sql = 'SELECT c.couponcode as couponcode, c.name as couponname,type as coupontype, u.userid as user, u.usedate as dateredeemed ';
		$sql .= 'FROM {'.ENROL_COUPON_TABLE_USER.'} u ';
		$sql .= 'INNER JOIN {'.ENROL_COUPON_TABLE_COUPON . '} c ';
		$sql .= 'ON c.id = u.couponid ';
		$sql .= 'WHERE c.instanceid = '. $this->instance->id . ' ';
		return $sql;
	
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		$sql = $this->fetch_data_sql($formdata);
		$coupons = $DB->get_records_sql($sql);
		
		//set headingdata
		$this->headingdata = new stdClass();

		//if we have no user data, just set empty array, so we show a nice message to user
		if(!$coupons){
			$this->rawdata= array();
		}else{
			$this->rawdata= $coupons;
		}
		return true;
	}
	
}


/*
* enrol_coupon_attempt_report 
*
*
*/
/*
class enrol_coupon_questiondetails_report extends  enrol_coupon_base_report {
	
	protected $report="questiondetails";
	protected $fields = array('username','timetaken','qplaycount','correct');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'timetaken':
						//$ret = $this->fetch_time_difference($record->revealanswerstime,$record->selectanswertime);
						$ret = $this->fetch_time_difference_js($record->revealanswerstime_js,$record->selectanswertime_js);
						break;

				case 'username':
						$theuser = $this->fetch_cache('user',$record->userid);
						$ret = fullname($theuser);
					break;
				
				case 'qplaycount':
						$ret = $record->qplaycount;
					break;
					
				case 'correct':
						$thequestion = $this->fetch_cache('tquiz_questions',$record->questionid);
						$correctanswer = $thequestion->correctanswer;

						if($record->selectanswer==$correctanswer){
							$ret =get_string('yes');
						}else{
							$ret=get_string('no');
						}
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		$record = $this->headingdata;
		$ret='';
		if(!$record){return $ret;}
		$q = $this->fetch_cache('tquiz_questions',$record->questionid);
		return get_string('questiondetails','tquiz',$q->name);
		
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		//heading data is just qname really
		$this->headingdata = new stdClass();
		$this->headingdata->questionid=$formdata->questionid;
		
		//get all data for this question by user
		$sql =	"SELECT tal.*
		FROM {tquiz_attempt_log} tal
		INNER JOIN {tquiz_attempt} ta ON ta.id = tal.attemptid
		WHERE ta.status = 'current' AND tal.questionid=:talquestionid
		ORDER BY tal.userid";
		$params=array();
		$params['talquestionid'] = $formdata->questionid;
	
		
		$alldata = $DB->get_records_sql($sql,$params); 
		$currentuserid=-1;
		$theattempt=null;
		foreach($alldata as $adata){
			//if we have changed question
			//stash the last one and start building the next one
			if($adata->userid!=$currentuserid){
					//stash the previous q if we had one
					if($theattempt){$attemptdata[]=$theattempt;}
					
					//init new row/question data object
					$theattempt = new stdClass();
					$theattempt->questionid=$adata->questionid;
					$theattempt->attemptid=$adata->attemptid;
					$theattempt->userid=$adata->userid;
					$theattempt->qplaycount=0;
					$theattempt->revealanswerstime=false;
					$theattempt->revealanswerstime_js=false;
					$theattempt->startplayquestiontime=false;
					$theattempt->startplayquestiontime_js=false;
					$theattempt->endplayquestiontime=false;
					$theattempt->endplayquestiontime_js=false;
					$theattempt->selectanswer=false;
					$theattempt->selectanswertime=false;
					$theattempt->selectanswertime_js=false;
					
					$currentuserid = $adata->userid;

			}
			//get event log data into the attempt object
			switch ($adata->eventkey){
					case 'startplayquestion':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						$theattempt->qplaycount++;
						break;
					case 'endplayquestion':
					case 'revealanswers':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						break;
					case 'selectanswer':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						$theattempt->{$adata->eventkey}=$adata->eventvalue;
						break;
					default:
						$theattempt->{$adata->eventkey}=$adata->eventvalue;
						break;
			}//end of switch
		}//end of for each
		
		//stash the final parsed question
		if($theattempt){
			$attemptdata[]=$theattempt;
		}
		
		
		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		//need to make final and start "questions" have different ids (0 and 9999)
		
		//probably should loop here to get question duration data
		
		$this->rawdata= $attemptdata;
		return true;
	}

}
*/

/*
* enrol_coupon_allusers_report 
*
*
*/
/*
class enrol_coupon_allusers_report extends  enrol_coupon_base_report {
	
	protected $report="allusers";
	protected $fields = array('date','username','timetaken','score');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'date':
					$ret =  date("Y-m-d",$record->timecreated);
					break;
				case 'timetaken':
					$ret = $this->fetch_time_difference($record->timecreated,$record->timefinished);
					break;

				case 'username':
						$theuser = $this->fetch_cache('user',$record->userid);
						$ret = fullname($theuser);
						if($withlinks){
							$detailsurl = new moodle_url('/mod/tquiz/reports.php', 
								array('n'=>$record->tquizid,
								'report'=>'attempt',
								'userid'=>$record->userid,
								'attemptid'=>$record->id));
							$ret = html_writer::link($detailsurl,$ret);
						}
						
					break;
				
				case 'score':
						$ret = $record->score;
					break;
				
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allusers','tquiz');
	}
	
	public function process_raw_data($formdata){
		global $DB;

		//no data in the heading, so an empty class even is overkill ..
		$this->headingdata = new stdClass();
		
		//the current attempts
		$alldata = $DB->get_records('tquiz_attempt',array('tquizid'=>$formdata->tquizid,'status'=>'current'));

		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		$this->rawdata= $alldata;
		return true;
	}

}
*/
/*
* enrol_coupon_allusers_report 
*
*
*/
/*
class enrol_coupon_allattempts_report extends  enrol_coupon_base_report {
	
	protected $report="allattempts";
	protected $fields = array('starttime', 'username','status','details','logs','delete');
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'starttime':
					$ret =  date("Y-m-d H:i:s",$record->timecreated);
					break;

				case 'username':
					$theuser = $this->fetch_cache('user',$record->userid);
					$ret = fullname($theuser);
					break;
				
				case 'status':
						$ret = $record->status;
					break;
					
				case 'details':
						if($withlinks){
							$detailsurl = new moodle_url('/mod/tquiz/reports.php', 
								array('n'=>$record->tquizid,
								'report'=>'attempt',
								'userid'=>$record->userid,
								'attemptid'=>$record->id));
							$ret = html_writer::link($detailsurl,get_string('viewreport', 'tquiz'));
						}else{
							$ret="";
						}
					break;
				case 'logs':
					if($withlinks){
						//$actionurl = '/mod/tquiz/manageattempts.php';
						//$logsurl = new moodle_url($actionurl, array('id'=>$record->cmid,'attemptid'=>$record->id));
						$logurl =  new moodle_url('/mod/tquiz/reports.php', 
								array('n'=>$record->tquizid,
								'report'=>'attemptlog',
								'userid'=>$record->userid,
								'attemptid'=>$record->id));
						$ret = html_writer::link($logurl, get_string('logs', 'tquiz'));
					}else{
						$ret="";
					}
					
					break;
				case 'delete':
					if($withlinks){
						$actionurl = '/mod/tquiz/manageattempts.php';
						$deleteurl = new moodle_url($actionurl, array('id'=>$record->cmid,'attemptid'=>$record->id,'action'=>'confirmdelete'));
						$ret = html_writer::link($deleteurl, get_string('deleteattempt', 'tquiz'));
					}else{
						$ret="";
					}
					break;	
				
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allattempts','tquiz');
	}
	
	public function process_raw_data($formdata){
		global $DB;

		//no data in the heading, so an empty class even is overkill ..
		$this->headingdata = new stdClass();
		
		//the current attempts
		$alldata = $DB->get_records('tquiz_attempt',array('tquizid'=>$formdata->tquizid));
		foreach($alldata as $adata){
			$adata->cmid = $formdata->cmid;
		}

		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		$this->rawdata= $alldata;
		return true;
	}

}
*/
/*
* enrol_coupon_allusers_report 
*
*
*/
/*
class enrol_coupon_attemptlog_report extends  enrol_coupon_base_report {
	
	protected $report="attemptlog";
	protected $fields = array('qname','eventkey','eventvalue','eventtime');
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'qname':
					if($record->questionid==0){
						$ret="";
					}else{
						$thequestion = $this->fetch_cache('tquiz_questions',$record->questionid);
						$ret = $thequestion->name;
					}
					break;

				case 'eventkey':
					$ret = $record->eventkey;
					break;
				
				case 'eventvalue':
					$ret = $record->eventvalue;
					break;
					
				case 'eventtime':
					$ret =  date("Y-m-d H:i:s",$record->eventtime / 1000) . '('. $record->eventtime % 1000 .')';
					break;	
				
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		$attempt = $this->fetch_cache('tquiz_attempt',$this->headingdata->attemptid);
		$user = $this->fetch_cache('user',$attempt->userid);
		$tquiz = $this->fetch_cache('tquiz',$attempt->tquizid);
		$a = new stdClass();
		$a->tquizname = $tquiz->name;
		$a->username = fullname($user);
		$a->status = $attempt->status;
		$a->attemptdate = date("Y-m-d H:i:s",$attempt->timecreated);
		return get_string('attemptlogheader','tquiz',$a);
	}
	
	public function process_raw_data($formdata){
		global $DB;

		//The data to help display a meaningful heading
		$hdata = new stdClass();
		$hdata->attemptid = $formdata->attemptid;
		$this->headingdata = $hdata;
		
		
		//the current attempts
		//the current attempts
		$logs = $DB->get_records('tquiz_attempt_log',array('attemptid'=>$formdata->attemptid));

		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		$this->rawdata= $logs;
		return true;
	}

}
*/

/*
* enrol_coupon_attempt_report 
*
*
*/
/*
class enrol_coupon_responsedetails_report extends  enrol_coupon_base_report {
	
	protected $report="responsedetails";
	protected $fields = array('responsenumber','rplaycount');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'responsenumber':
						$ret = $record->responsenumber;
						break;

				case 'rplaycount':
						$ret = $record->rplaycount;
					break;
				
			
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		$record = $this->headingdata;
		$ret='';
		if(!$record){return $ret;}
		$attempt = $this->fetch_cache('tquiz_attempt',$record->attemptid);
		$user = $this->fetch_cache('user',$attempt->userid);
		$tquiz = $this->fetch_cache('tquiz',$attempt->tquizid);
		$question = $this->fetch_cache('tquiz_questions',$record->questionid);
		$a = new stdClass();
		$a->date = date("Y-m-d H:i:s",$attempt->timecreated);
		$a->tquizname = $tquiz->name;
		$a->username = fullname($user);
		$a->qname = $question->name;
		return get_string('responsedetailsheader','tquiz',$a);
		
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		//heading data is just qname really
		$this->headingdata = new stdClass();
		$this->headingdata->questionid=$formdata->questionid;
		$this->headingdata->attemptid=$formdata->attemptid;
		
		//get all data for this question by user
		$sql =	"SELECT tal.*
		FROM {tquiz_attempt_log} tal
		INNER JOIN {tquiz_attempt} ta ON ta.id = tal.attemptid
		WHERE tal.attemptid = :talattemptid AND tal.questionid=:talquestionid
		ORDER BY tal.eventtime";
		$params=array();
		$params['talquestionid'] = $formdata->questionid;
		$params['talattemptid'] = $formdata->attemptid;
	
		
		$alldata = $DB->get_records_sql($sql,$params); 
		$ret=array();
		foreach($alldata as $adata){
			//get event log data into the attempt object
			switch ($adata->eventkey){
					case 'startplayanswer':
						if(array_key_exists($adata->eventvalue,$ret)){
							$ret[$adata->eventvalue] = $ret[$adata->eventvalue]+1; 
						}else{
							$ret[$adata->eventvalue] =1;
						}
						break;
					default:
						break;
			}//end of switch
		}//end of for each
		
		$rdata=array();
		foreach ($ret as $rkey=>$rvalue){
			$adata = new stdClass();
			$adata->responsenumber=$rkey;
			$adata->rplaycount=$rvalue;
			$rdata[]=$adata;
		}
		
		$this->rawdata= $rdata;
		return true;
	}

}
*/