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


defined( '_JEXEC' ) or die( 'Restricted access' );

	if(!defined("DS")){
		define( 'DS', DIRECTORY_SEPARATOR );
	}
	
	$path = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR."google-api-php-client-master".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."Google";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	//echo $path;
	if(!file_exists ( $path.DIRECTORY_SEPARATOR.'Client.php' )){
		echo "ABPro set to use Google Calendar but the Google Library is not installed. See <a href='http://appointmentbookingpro.com/index.php?option=com_content&view=article&id=89&Itemid=190' target='_blank'>Tutorial</a>";
		exit;
	}	
	require_once $path.DIRECTORY_SEPARATOR."Client.php";
    require_once $path.DIRECTORY_SEPARATOR."Service.php";
	

class SVGCal
{
	var $client = null;
	var $tzOffset = "0";
	var $cal_id = "";
	var $username = "";
	
	function login($res_detail){
	
		try {
	 	    $this->client = new Google_Client();
			$this->client->setApplicationName($res_detail->google_app_name);
			$this->client->setClientId($res_detail->google_client_id);
			$this->client->setAssertionCredentials( 
				new Google_Auth_AssertionCredentials(
					$res_detail->google_app_email_address,
					array("https://www.googleapis.com/auth/calendar"),
					file_get_contents(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.$res_detail->google_p12_key_filename),
					'notasecret','http://oauth.net/grant_type/jwt/1.0/bearer',false,false
				)
			);
		}
		catch (RuntimeException $e) {
		    return 'Problem authenticating Google Calendar:'.$e->getMessage();
		}
		return "ok";	
	}


	function setTZOffset($value){
		$this->tzOffset = $value;		
	}

	function setCalID($value){
		$this->cal_id = $value;		
	}

	function getClient(){
		return $this->client;
	}

	function createEvent ($title='', $desc='', $where='',  $startDate='', $startTime='', $endDate='', $endTime=''){
		if($this->client == null){
			echo "Not Logged in";
			return -1;
		}		
		$service = new Google_Service_Calendar($this->client);		
		$newEvent = new Google_Service_Calendar_Event();
		$newEvent->setSummary($title);
		$newEvent->setLocation($where);
		$newEvent->setDescription($desc);
		$event_start = new Google_Service_Calendar_EventDateTime();
		$event_start->setDateTime("{$startDate}T{$startTime}{$this->tzOffset}");
		$newEvent->setStart($event_start);
		$event_end = new Google_Service_Calendar_EventDateTime();
		$event_end->setDateTime("{$endDate}T{$endTime}{$this->tzOffset}");
		$newEvent->setEnd($event_end);
	
		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
			
		$createdEvent = null;
		if($this->cal_id != ""){
			try {
				$createdEvent = $service->events->insert($this->cal_id, $newEvent);
				$createdEvent_id= $createdEvent->getId();
			} catch (Google_ServiceException $e) {
				logIt("svgcal_v3,".$e->getMessage()); 
//				echo $e->getMessage();
//				exit;
			}			
			
//			$createdEvent = $gdataCal->insertEvent($newEvent, "http://www.google.com/calendar/feeds/".$this->cal_id."/private/full");
			
			
		} else {
			logIt("svgcal_v3, No calendar ID specified in the resource setup screen."); 
			return null;
		}
		return $createdEvent_id;
	}

	
	function deleteEventById ($client, $eventId) 
	{
		$service = new Google_Service_Calendar($this->client);
		$event = $service->events->get($client, $eventId);
		if($event != null){
			try {
				$service->events->delete('primary', $eventId);
				$event->delete();
			} catch (Exception $e) {
				logIt("svgcal_v3 (del 1),".$e->getMessage()); 
			}
		}
	}
	

	function deleteEvent ($client, $eventId, $cal_id='primary') 
	{
		$service = new Google_Service_Calendar($this->client);		
		try {
			$service->events->delete($cal_id, $eventId);
		} catch (Exception $e) {
			logIt("svgcal_v3 (del 2),".$e->getMessage()); 
		}
		return "ok";
	}
	
	
	function getEvent($client, $eventId, $cal_id='default'){ 
		$service = new Google_Service_Calendar($this->client);		
//		$gdataCal = new Zend_Gdata_Calendar($client); 
		$query = $gdataCal->newEventQuery(); 
		$query->setUser($cal_id); 
		$query->setVisibility('private'); 
		$query->setProjection('full'); 
		$query->setEvent($eventId); 
		
		try { 
			$eventEntry = $gdataCal->getCalendarEventEntry($query); 
			return $eventEntry; 
		} catch (Zend_Gdata_App_Exception $e) { 
			echo $e->getMessage();
			return null; 
		} 
	} 



}
?>