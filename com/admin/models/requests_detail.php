<?php
/*
 ****************************************************************
 Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 * @license	GNU/GPL, see http://www.gnu.org/licenses/gpl-2.0.html
 *
 * ABPro is distributed WITHOUT ANY WARRANTY, or implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 *
 ************************************************************
 The latest version of ABPro is available to subscribers at:
 http://www.appointmentbookingpro.com/
 ************************************************************
*/


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//DEVNOTE: import MODEL object class
jimport('joomla.application.component.model');


class requests_detailModelrequests_detail extends JModelLegacy
{
		var $_id_requests = null;
		var $_data = null;
//		var $_data_MyBookings = null;
		var $_table_prefix = null;

	function __construct()
	{
		parent::__construct();
		$jinput = JFactory::getApplication()->input;
		
		//initialize class property
	  	$this->_table_prefix = '#__sv_apptpro3_';			

		$array = $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$this->setId((int)$array[0]);

	}

	/**
	 * Method to set the requests identifier
	 *
	 * @access	public
	 * @param	int requests identifier
	 */
	function setId($id_requests)
	{
		// Set requests id and wipe data
		$this->_id_requests		= $id_requests;
		$this->_data	= null;
	}

	/**
	 * Method to get a requests
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the requests data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	/**
	 * Method to checkout/lock the requests
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_requests)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$requests = $this->getTable();
			
			
			if(!$requests->checkout($uid, $this->_id_requests)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the requests
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_requests)
		{
			$requests = $this->getTable();
			if(! $requests->checkin($this->_id_requests)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if requests is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}	
		
	/**
	 * Method to load content requests data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM '.$this->_table_prefix.'requests WHERE id_requests = '. $this->_id_requests;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the requests data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$detail = new stdClass();
			$detail->resource_id = null;
			$detail->user_id = null;
			$detail->operator_id = null;
			$detail->name = null;
			$detail->phone = null;
			$detail->email = null;
			$detail->resource = null;
			$detail->category = null;
			$detail->starttime = null;
			$detail->startdate = null;
			$detail->enddate = null;
			$detail->endtime = null;
			$detail->comment = null;
			$detail->admin_comment = null;
			$detail->request_status = null;
			$detail->payment_status = null;
			$detail->show_on_calendar = null;
			$detail->calendar_category = null;
			$detail->calendar_calendar = null;
			$detail->calendar_comment = null;
			$detail->created = null;
			$detail->cancellation_id = null;
			$detail->service = null;
			$detail->txnid = null;
			$detail->sms_reminders = null;
			$detail->sms_phone = null;
			$detail->sms_dial_code = null;
			$detail->google_event_id = '';
			$detail->google_calendar_id = '';
			$detail->booking_total = 0.00;
			$detail->booking_deposit = 0.00;
  			$detail->booking_due = 0.00;
			$detail->coupon_code = null;
			$detail->booked_seats = 0;
			$detail->booking_language = 'en-GB';
			$detail->credit_used = "0.00";
			$detail->invoice_number = "";
			$detail->gift_cert = "";
			$detail->cancel_reason = "";
			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->ordering = 1;
			$detail->published = 0;
			$this->_data	= $detail;
			return (boolean) $this->_data;
		}
		return true;
	}
  	

	/**
	 * Method to store the requests text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$jinput = JFactory::getApplication()->input;
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/requests_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the requests table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if (!$row->id_requests) {
			$where = 'id_requests = ' . $row->id_requests ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$this->_db->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $this->_db->loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "model_requests_detail(1)", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	
		// get resource info for the selected resource
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$row->resource;
		try{
			$this->_db->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $this->_db->loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "model_requests_detail(2)", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	
		// if request_status = 'accepted', check max seats not exceeded
		// first just see if this booking's seats > the resource's
	
		// max_seats = 0 = no limit
		if($res_detail->max_seats > 0 && $row->request_status == "accepted"){	
			if($row->booked_seats > $res_detail->max_seats){
				echo "<script> alert('".JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."'); window.history.go(-1); </script>\n";
				exit();	
			}	
			// now check to see if there are other bookings and if so how many total seats are booked.
			$currentcount = getCurrentSeatCount($row->startdate, $row->starttime, $row->enddate, $row->resource, $row->id_requests);
		
			if ($currentcount + $row->booked_seats > $res_detail->max_seats){
				echo "<script> alert('".JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."'); window.history.go(-1); </script>\n";
				exit();	
			}
		}	

		// Store the requests table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		// save seat counts if required
		$seat_type_count = $jinput->getInt( 'seat_type_count', '0' );
		if($seat_type_count > 0 ){
			// For each seat type there are two possibilities; 
			// 1. there was an entry and it needs to be updated
			// 2. there was no entry and we need a new one IF the qty is now >0
			// If the was en entry and it's qty is down to 0, do not delete it, just update 
			
			for($st =0; $st<$seat_type_count; $st++){
				$seat_type_id = $jinput->getString( 'seat_type_id_'.$st );
				$seat_type_qty = $jinput->getString( 'seat_'.$st );
				$request_id = $row->id_requests;
				$seat_type_org_qty = $jinput->getString( 'seat_type_org_qty_'.$st );
				if($seat_type_org_qty != $seat_type_qty){				
					$sql = "UPDATE #__sv_apptpro3_seat_counts SET seat_type_qty=".$seat_type_qty." WHERE request_id=".$request_id." AND seat_type_id=".$seat_type_id;				
					try{
						$this->_db->setQuery($sql);
						$result = NULL;
						$result = $this->_db->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "model_requests_detail(3)", "", "");
						echo JText::_('RS1_SQL_ERROR').$e->getMessage();
						exit;
					}		
					if ($this->_db->getAffectedRows()==0 && $seat_type_qty>0) {
						$sql = "INSERT INTO #__sv_apptpro3_seat_counts (request_id, seat_type_id, seat_type_qty) values(".$request_id.",".$seat_type_id.",".$seat_type_qty.")";				
						try{
							$this->_db->setQuery($sql);
							$result = $this->_db->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "model_requests_detail(4)", "", "");
							echo JText::_('RS1_SQL_ERROR').$e->getMessage();
							exit;
						}		
					}			
				}
			}
		}		
	
		// save udf changes
		$udf_rows_count = $jinput->getInt( 'udf_rows_count', '0' );
		if($udf_rows_count > 0 ){
			for($udfr=0; $udfr < $udf_rows_count; $udfr++){
				$udf_value_id = $jinput->getInt( 'udf_id_'.$udfr);
				$udf_value = $jinput->get('udf_value_'.$udfr, 'default', 'RAW');				
				$sql = "UPDATE #__sv_apptpro3_udfvalues SET udf_value='".$this->_db->escape($udf_value)."' WHERE id=".$udf_value_id;	
				try{			
					$this->_db->setQuery($sql);
					$result = NULL;
					$result = $this->_db->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "model_requests_detail(5)", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
			}
		}

	
		// calendar stuff
		if($apptpro_config->which_calendar != 'None'){
			
			// remove calendar entry
			// First delete calendar record for this request if one exists
			if($apptpro_config->which_calendar == "Google" and $row->google_event_id != ""){
			
				include_once( JPATH_SITE."/components/com_rsappt_pro3/svgcal.php" );
				$gcal = new SVGCal;
				// login
				$result = $gcal->login($res_detail);
				if( $result == "ok"){
					$client = $gcal->getClient();	
						if($row->google_calendar_id == ""){
						$gcal->deleteEventById($gcal->getClient(), $row->google_event_id);
					} else {
						$result = $gcal->deleteEvent($gcal->getClient(), $row->google_event_id, $row->google_calendar_id);
						if($result != "ok"){
							echo $result;
							logIt($result, "on delete of Google Calendar event"); 
						}
					}	
					// set event ID back in request
					$sql = "UPDATE #__sv_apptpro3_requests SET google_event_id = '' WHERE id_requests = ".$row->id_requests;
					try{
						$this->_db->setQuery($sql);
						$this->_db->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "model_requests_detail(6)", "", "");
						echo JText::_('RS1_SQL_ERROR').$e->getMessage();
						exit;
					}		
						
				} else {
					echo $result;
					logIt($result, "on login for delete of Google Calendar event"); 
				}						
			}	
			
			if ($jinput->getString('show_on_calendar')=='Yes' and $jinput->getString('request_status')=='accepted'){
				try{
					$sql = "SELECT description FROM #__sv_apptpro3_resources WHERE id_resources = '".$jinput->getString('resource')."'";
					$this->_db->setQuery( $sql );
					$rows = $this->_db->loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "model_requests_detail(7)", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
				$Title = $rows[0]->description; 
				
		
				// get resource name
				$res_data = NULL;
				$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources= ".$jinput->getString('resource');
				try{
					$this->_db->setQuery($sql);
					$res_data = $this->_db->loadObject();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "model_requests_detail(8)", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
		
				switch ($apptpro_config->calendar_title) {
				  case 'resource.name': {
					$title_text = stripslashes($res_data->name);	
					break;
				  }
				  case 'request.name': {
					$title_text = stripslashes($row->name);	
					break;
				  }
				  default: {
					// must be a udf, get udf_value
					$sql = "SELECT udf_value FROM #__sv_apptpro3_udfvalues WHERE request_id = ".$row->id_requests." and udf_id=".$apptpro_config->calendar_title;
					$this->_db->setQuery( $sql);
					$title_text = $this->_db->loadResult(); 		
				  }
				}
	
				$calendar_comment = "";
				if($apptpro_config->calendar_body2 != "") {
					$calendar_comment = buildMessage($row->id_requests, "calendar_body", "No");
				}		
				stripslashes($calendar_comment);
				$calendar_comment = str_replace("'", "`", $calendar_comment);
				$user = JFactory::getUser();
	
				if($apptpro_config->which_calendar == "Google"){			
					include_once( JPATH_SITE."/components/com_rsappt_pro3/svgcal.php" );
					require_once( JPATH_CONFIGURATION.DIRECTORY_SEPARATOR.'configuration.php' );
					$CONFIG = new JConfig();
					$offset = $CONFIG->offset;
					// Joomla team changed the time zone offset, which is the number of hours offset from GMT, 
					// from a number to a city name. So now we need to figure out the real offset from the city name.. aarrgggh
					$TimeZonebyCity = new DateTimeZone($CONFIG->offset);
					$localTimebyCity = new DateTime($row->startdate, $TimeZonebyCity);
					$timeOffset = $TimeZonebyCity->getOffset($localTimebyCity);
					$offset = $timeOffset/3600;
					
					$offset = tz_offset_to_string($offset);
					$gcal = new SVGCal;
					// login
					$result = $gcal->login($res_detail);
					if( $result != "ok"){
						echo $result;
						logIt($result, "on login to add Google Calendar event"); 
					}		
					$gcal->setTZOffset($offset);
					// set calendar
					if($res_data->google_default_calendar_name != ""){
						try{
							$gcal->setCalID($res_data->google_default_calendar_name);
						}catch (RuntimeException $e) { 
							logIt($e->getMessage(), "model_requests_detail(9)");
						} 				
					}	
					//create event
					try{						
						$event_id_full = $gcal->createEvent( 
							$title_text,
							$calendar_comment, 
							'',
							trim($row->startdate),
							trim($row->starttime),
							trim($row->enddate),
							trim($row->endtime));
					}catch (RuntimeException $e) { 
						logIt($e->getMessage(), "model_requests_detail(10)", "", "");
					} 				
							
					// set event ID back in request
					$sql = "UPDATE #__sv_apptpro3_requests SET google_event_id = '".$event_id_full."', ".
							"google_calendar_id = '".$res_detail->google_default_calendar_name."' where id_requests = ".$row->id_requests;
					try{
						$this->_db->setQuery($sql);
						$this->_db->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "model_requests_detail(11)", "", "");
						echo JText::_('RS1_SQL_ERROR').$e->getMessage();
						exit;
					}		
				}
			}
		}

		$config = JFactory::getConfig();
		$tzoffset = $config->get('offset');      
//		if($apptpro_config->daylight_savings_time == "Yes"){
//			$tzoffset = $tzoffset+1;
//		}
		$tz = new DateTimeZone($tzoffset);
		$offsetdate = new JDate("now", $tz);
		$reminder_log_time_format = "Y-m-d H:i:s";
		$user = JFactory::getUser();
		
		// Messages
		$sql = 'SELECT * FROM #__sv_apptpro3_mail WHERE id_mail = '.($res_detail->mail_id ==1 || $res_detail->mail_id == null?"1":$res_detail->mail_id);
		try{
			$this->_db->setQuery($sql);
			$messages_to_use = $this->_db->loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_model_requests_detail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
		
		// If status was not accepted and is now, send a confirmation
		if($jinput->getString('request_status')=='accepted'){
			if($jinput->getWord('old_status') != 'accepted'){
				// send confirmation	
				$language = JFactory::getLanguage();
				$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
				$subject = JText::_('RS1_CONFIRMATION_EMAIL_SUBJECT');
				sendMail($row->email, $subject, "confirmation", $row->id_requests);	
				if($res_detail->resource_email != ""){
					sendMail($res_detail->resource_email, $subject, "confirmation_admin", $row->id_requests, "", $messages_to_use->attach_ics_resource);	
				} else if($apptpro_config->mailTO != "") {
					sendMail($apptpro_config->mailTO, $subject, "confirmation_admin", $row->id_requests, "", $messages_to_use->attach_ics_admin);	
				}
				$returnCode = "";
				if($res_detail->sms_phone != ""){
					sv_sendSMS($row->id_requests, "confirmation", $returnCode, $toResource="Yes");			
					logReminder("Booking set to accepted status:".$returnCode, $row->id_requests, $user->id, $row->name, $offsetdate->format($reminder_log_time_format, true, true));
				}
				if($row->sms_reminders == "Yes"){
					sv_sendSMS($row->id_requests, "confirmation", $returnCode, $toResource="No");			
					logReminder("Booking confirmation to customer:".$returnCode, $row->id_requests, $user->id, $row->name, $offsetdate->format($reminder_log_time_format, true, true));
				}
			}
		}
		// post booking message
		if($jinput->getString('request_status')==strtolower($messages_to_use->send_on_status)){
			if($jinput->getWord('old_status') != strtolower($messages_to_use->send_on_status)){
				// send post booking message	
				$language = JFactory::getLanguage();
				$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
				$subject = JText::_('RS1_ADMIN_CONFIG_MSG_THANKYOU_SUBJECT');
				sendMail($row->email, $subject, "thankyou", $row->id_requests);	
			}
		}
		if($jinput->getString('request_status')=='canceled'){
			if($jinput->getWord('old_status') != 'canceled'){
				
				// send cancellation	
				$language = JFactory::getLanguage();
				$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
				$subject = JText::_('RS1_CANCELLATION_EMAIL_SUBJECT');
				sendMail($row->email, $subject, "cancellation", $row->id_requests);			
				if($res_detail->resource_email != ""){
					sendMail($res_detail->resource_email, $subject, "cancellation", $row->id_requests);	
				} else if($apptpro_config->mailTO != "") {
					sendMail($apptpro_config->mailTO, $subject, "cancellation", $row->id_requests);	
				}
				$returnCode = "";
				sv_sendSMS($row->id_requests, "cancellation", $returnCode, $toResource="Yes");			
				logReminder("Booking set to cancelled status:".$returnCode, $row->id_requests, $user->id, $row->name, $offsetdate->format($reminder_log_time_format));
				
				if($jinput->getWord('old_status') == 'accepted' || $jinput->getWord('old_status') == 'pending'){
					// return credit is used and refunds allowed.			
					if($apptpro_config->allow_user_credit_refunds == "Yes" && $row->credit_used > 0){
						$refund_amount = $row->credit_used;
						if($row->booking_total > 0 && $row->payment_status == 'paid'){
							// part of booking was paid by paypal, need to add that back to user's credit total
							$refund_amount += $row->booking_total;
						}				
						if($row->gift_cert !=""){
							$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance + ".$refund_amount." WHERE gift_cert = ".$row->gift_cert;
						} else {
							$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance + ".$refund_amount." WHERE user_id = ".$row->user_id;
						}
						try{
							$this->_db->setQuery($sql);
							$this->_db->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "model_requests_detail(12)", "", "");
							echo JText::_('RS1_SQL_ERROR').$e->getMessage();
							exit;
						}		
						// set request.credit_used to -1 to indicate refunded and prevent multiple refunds if operator sets to canceled again.
						$sql = "UPDATE #__sv_apptpro3_requests SET credit_used = -1 WHERE id_requests = ".$row->id_requests;
						try{
							$this->_db->setQuery($sql);
							$this->_db->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "model_requests_detail(13)", "", "");
							echo JText::_('RS1_SQL_ERROR').$e->getMessage();
							exit;
						}		
						
						// add credit audit
						$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, request_id, gift_cert, increase, comment, operator_id, balance) '.
						"VALUES (".$row->user_id.",".
						$row->id_requests.",".
						"'".$row->gift_cert."',".
						$refund_amount.",".
						"'".JText::_('RS1_ADMIN_CREDIT_ACTIVITY_REFUND_ON_CANCEL')."',".
						$user->id.",";
						if($row->gift_cert !=""){
							$sql .= "(SELECT balance from #__sv_apptpro3_user_credit WHERE gift_cert = ".$row->gift_cert.")";
						} else {
							$sql .= "(SELECT balance from #__sv_apptpro3_user_credit WHERE user_id = ".$row->user_id.")";
						}
						$sql .= ")";
						try{
							$this->_db->setQuery($sql);
							$this->_db->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "model_requests_detail(14)", "", "");
							echo JText::_('RS1_SQL_ERROR').$e->getMessage();
							exit;
						}		
					}

					// if notification list enabled, check to see if any notifications need to be sent.
					if($apptpro_config->enable_notification_list == "Yes"){
						$sql = "SELECT email FROM #__sv_apptpro3_notification_list WHERE ".
							"resource = ".$row->resource.
							" AND booking_start = '".$row->startdate." ".$row->starttime."'";
						try{
							$this->_db->setQuery($sql);
							$notification_emails = NULL;
							$notification_emails = $this->_db->loadObjectList();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "model_requests_detail(15)", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}	
						foreach ($notification_emails as $notification_email){
							// send notification
							//print_r($notification_email->email);						
							sendMail($notification_email->email, JText::_('RS1_NOTIFICATION_EMAIL_SUBJECT'), "notification", $row->id_requests);	
						}
					}
					
				}
			}
		}
		return true;
	}


	function delete($cid = array())	{
		
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$this->_db->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $this->_db -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "model_requests_detail(16)", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	
		foreach ($cid as $x=>$one_id) {
			// First delete calendar record for this request if one exists
			if($apptpro_config->which_calendar == "Google" ){
				// need to get google_event_id from request
				$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE id_requests = ".$one_id;
				try{
					$this->_db->setQuery($sql);
					$row = NULL;
					$row = $this->_db->loadObject();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "model_requests_detail(17)", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
				if($row->google_event_id != ""){
					
					include_once( JPATH_SITE."/components/com_rsappt_pro3/svgcal.php" );
					$gcal = new SVGCal;
					// login
					$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources = ".$row->resource;
					$this->_db->setQuery($sql);
					$res_detail = NULL;
					$res_detail = $this->_db->loadObject();
					$result = $gcal->login($res_detail);
					if( $result == "ok"){
						$client = $gcal->getClient();	
							if($row->google_calendar_id == ""){
							$gcal->deleteEventById($gcal->getClient(), $row->google_event_id);
						} else {
							$result = $gcal->deleteEvent($gcal->getClient(), $row->google_event_id, $row->google_calendar_id);
							if($result != "ok"){
								echo $result;
								logIt($result); 
							}
						}		
					} else {
						echo $result;
						logIt($result); 
					}						
				}
			}	
			
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(18)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		}
		unset($one_id); // break the reference with the last element
	
	
	
		if (sv_count_($cid))
		{
			$ids = implode(',', $cid);
	
			// Delete udf_values
			$sql = "DELETE FROM `#__sv_apptpro3_udfvalues` WHERE request_id IN (".$ids.")";
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(19)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
	
			// Delete extras_values
			$sql = "DELETE FROM `#__sv_apptpro3_extras_data` WHERE request_id IN (".$ids.")";
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(20)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		

			// Delete seat_counts
			$sql = "DELETE FROM `#__sv_apptpro3_seat_counts` WHERE request_id IN (".$ids.")";
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(21)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
	
			// paypal stuff
			try{
				$this->_db->setQuery("DELETE FROM #__sv_apptpro3_paypal_transactions \nWHERE custom IN (".$ids.")");
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(22)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		

			// authnet stuff
			try{
				$this->_db->setQuery("DELETE FROM #__sv_apptpro3_authnet_transactions WHERE x_invoice_num IN (".$ids.")");
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(23)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		

			// 2co stuff
			try{
				$this->_db->setQuery("DELETE FROM #__sv_apptpro3__2co_transactions WHERE merchant_order_id IN (".$ids.")");
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(24)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		

			// stripe stuff
			try{
				$this->_db->setQuery("DELETE FROM #__sv_apptpro3_stripe_transactions WHERE request_id IN (".$ids.")");
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "model_requests_detail(25)", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		

			$this->_db->setQuery("DELETE FROM #__sv_apptpro3_requests WHERE id_requests IN (".$ids.")");
		}
		if (!$this->_db->execute()) {
			echo $this->_db->getErrorMsg();
			logIt($this->_db->getErrorMsg()); 
			return false;
		}

		
		return true;
		
	}



}

?>
