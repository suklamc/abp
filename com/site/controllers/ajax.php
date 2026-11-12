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

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' );


/**
 * rsappt_pro3  Controller
 */
 
class ajaxController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		// Register tasks
		$this->registerTask( 'list_bookings', 'list_bookings' );
		$this->registerTask( 'cancel_booking', 'cancel_booking' );
		$this->registerTask( 'delete_booking', 'delete_booking' );
		$this->registerTask( 'ajax_calview', 'ajax_calview' );

		$this->registerTask( 'ajax', 'generic_ajax' );
		$this->registerTask( 'ajax_validate', 'ajax_validate' );
		$this->registerTask( 'ajax_validate_edit', 'ajax_validate_edit' );

		$this->registerTask( 'ajax_gad', 'ajax_gad' );
		$this->registerTask( 'ajax_gad2', 'ajax_gad2' );

		$this->registerTask( 'ajax_check_overlap', 'ajax_check_overlap' );

		$this->registerTask( 'ajax_fetch', 'ajax_fetch' );

		$this->registerTask( 'ajax_who_booked', 'ajax_who_booked' );

		$this->registerTask( 'ajax_user_search', 'ajax_user_search' );

		$this->registerTask( 'ajax_get_rate_overrides', 'ajax_get_rate_overrides' );
		$this->registerTask( 'ajax_get_rate_adjustments', 'ajax_get_rate_adjustments' );

		$this->registerTask( 'get_gw_token', 'gw_token' );
		$this->registerTask( 'gw_fail', 'gw_fail' );
		$this->registerTask( 'gw_wrapit', 'gw_wrapit' );

		$this->registerTask( 'ajax_check_overrun', 'ajax_check_overrun' );

		$this->registerTask( 'ajax_quick_status_change', 'ajax_quick_status_change' );
		
		$this->registerTask( 'ajax_add_to_notification_list', 'ajax_add_to_notification_list' );
		$this->registerTask( 'ajax_resources_for_service', 'ajax_resources_for_service' );
		$this->registerTask( 'ajax_services_for_category', 'ajax_services_for_category' );

		$this->registerTask( 'ajax_set_book_dates_enable', 'ajax_set_book_dates_enable' );
		$this->registerTask( 'ajax_purge_old_dates', 'ajax_purge_old_dates' );

		$this->registerTask( 'ajax_export_table_update', 'ajax_export_table_update' );
	
	}

	function list_bookings()
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'backup_restore' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

	}
      
	function cancel_booking()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/fe_cancel.php');
	}


	function delete_booking()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/fe_delete.php');
	}

	function ajax_calview()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/calview_ajax.php');
	}

	function ajax_calviewview()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/calviewview_ajax.php');
	}
	function generic_ajax()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/getSlots.php');
	}

	function ajax_validate()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/fe_val.php');
	}

	function ajax_validate_edit()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/fe_val_edit.php');
	}

	function ajax_gad()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/gad_ajax.php');
	}
	
	function ajax_gad2()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/gad_ajax2.php');
	}

	function ajax_check_overlap()
	{	
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/fe_overlap.php');
	}

	function ajax_fetch()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/fe_fetch.php');
	}

	function ajax_who_booked()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/who_booked.php');
	}

	function ajax_user_search()
	{
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/ajax/user_search.php');
	}

	
	/** function cancel
	*
	* Check in the selected detail 
	* and set Redirection to the list of items	
	* 		
	* @return set Redirection
	*/
	function cancel($key=null)
	{
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=ajax',$msg );
	}	


	function ajax_get_rate_overrides()
	{
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		$jinput = JFactory::getApplication()->input;
		$user_id = $jinput->getString( 'id', '0' );
		
		// get resource rates
		$database =JFactory::getDBO(); 
		$sql = 'SELECT id_resources,rate,rate_unit,deposit_amount,deposit_unit,res_user_drag_duration_enable,res_user_drag_duration_snap FROM #__sv_apptpro3_resources';
		try{
			$database->setQuery($sql);
			$res_rates = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/ajax", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
		}		
		$rateArrayString = "";
		$base_rate = "0.00";
		for($i=0; $i<sv_count_($res_rates); $i++){
			$base_rate = getOverrideRate("resource", $res_rates[$i]->id_resources, $res_rates[$i]->rate, $user_id, "rate");
			$rateArrayString = $rateArrayString.$res_rates[$i]->id_resources.":".$base_rate."";
			if($i<sv_count_($res_rates)-1){
				$rateArrayString = $rateArrayString.",";
			}
		}
		
		echo json_encode($rateArrayString);
		jExit();
	}

	function ajax_get_rate_adjustments()
	{
		$jinput = JFactory::getApplication()->input;

		$res_adjustments  = null;
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		$ent = $jinput->getWord( 'ent', '0' );
		$ent_id = $jinput->getInt( 'ent_id', '0' );
		$bkg_date = $jinput->getString( 'bkg_date', '0' );
		$bkg_start = $jinput->getString( 'bkg_start', '0' );
		$bkg_end = $jinput->getString( 'bkg_end', '0' );
		
		$day_adjustment = "";
		$day_adjustment_unit = "";
		$time_adjustment = "";
		$time_adjustment_unit = "";
		
		// get resource adjustments
		$database =JFactory::getDBO(); 
		$sql = "SELECT * FROM #__sv_apptpro3_rate_adjustments WHERE ".
		" entity_type = '".$database->escape($ent)."'".
		" AND entity_id = ".$database->escape($ent_id).
		" AND published = 1".
		" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$database->escape($bkg_date)."' >= start_publishing ) ".
		" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$database->escape($bkg_date)."' <= end_publishing ) ";
		try{
			$database->setQuery($sql);
			$res_adjustments = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/ajax", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
		}	
		
		if(sv_count_($res_adjustments) == 0){
			// no adjustmemnt required
			echo json_encode(0);
			jExit();
			
		} else {
			foreach($res_adjustments as $res_adjustment){
				// are the found adjustments by-day
				if($res_adjustment->by_day_time == "DayOnly"){
					// Does the booking day match and enabled adjustment day
					$weekday = date( "w", strtotime($bkg_date));
					switch($weekday){
						case 0:
							if($res_adjustment->adjustSunday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 1:
							if($res_adjustment->adjustMonday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 2:
							if($res_adjustment->adjustTuesday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 3:
							if($res_adjustment->adjustWednesday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 4:
							if($res_adjustment->adjustThursday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 5:
							if($res_adjustment->adjustFriday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 6:
							if($res_adjustment->adjustSaturday == "Yes"){
								$day_adjustment = $res_adjustment->rate_adjustment;
								$day_adjustment_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
															
					}
				}

				if($res_adjustment->by_day_time == "TimeOnly"){
					// does booking start or end fall in range
					$temp = strtotime($bkg_start)+1;
					if(($temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd)) || 
					( $temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd))){
						$time_adjustment = $res_adjustment->rate_adjustment;
						$time_adjustment_unit = $res_adjustment->rate_adjustment_unit;
					}
					$temp = strtotime($bkg_end)-1;
					if(($temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd)) || 
					( $temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd))){
						$time_adjustment = $res_adjustment->rate_adjustment;
						$time_adjustment_unit = $res_adjustment->rate_adjustment_unit;
					}
				}
				
				if($res_adjustment->by_day_time == "DayAndTime"){
					// only adjust is both date and time match
					$weekday = date( "w", strtotime($bkg_date));
					$day_match = false;
					$time_match = false;
					$temp_day_adjust = "";
					$temp_time_adjust = "";
					$temp_day_adjust_unit = "";
					$temp_time_adjust_unit = "";
					switch($weekday){
						case 0:
							if($res_adjustment->adjustSunday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 1:
							if($res_adjustment->adjustMonday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 2:
							if($res_adjustment->adjustTuesday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 3:
							if($res_adjustment->adjustWednesday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 4:
							if($res_adjustment->adjustThursday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 5:
							if($res_adjustment->adjustFriday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
						case 6:
							if($res_adjustment->adjustSaturday == "Yes"){
								$day_match = true;
								$temp_day_adjust = $res_adjustment->rate_adjustment;
								$temp_day_adjust_unit = $res_adjustment->rate_adjustment_unit;
							}
							break;
															
					}
					if($day_match){
						// there is a da_adjustment but we only want to pass back a required adjustment if the time matches also
						$temp = strtotime($bkg_start)+1;
						if(($temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd)) || 
						( $temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd))){
							$time_match = true;
							$temp_time_adjust = $res_adjustment->rate_adjustment;
							$temp_time_adjust_unit = $res_adjustment->rate_adjustment_unit;
						}
						$temp = strtotime($bkg_end)-1;
						if(($temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd)) || 
						( $temp > strtotime($res_adjustment->timeRangeStart) && $temp < strtotime($res_adjustment->timeRangeEnd))){
							$time_match = true;
							$temp_time_adjust = $res_adjustment->rate_adjustment;
							$temp_time_adjust_unit = $res_adjustment->rate_adjustment_unit;
						}
					}
					if($day_match && $time_match){
						$day_adjustment = ""; // if date/time adjustment, clear day
						$day_adjustment_unit = "";
						$time_adjustment = $temp_time_adjust;
						$time_adjustment_unit = $temp_time_adjust_unit;
					}
				}
			}
		}
//		echo json_encode("day~".$day_adjustment.","."time~".$time_adjustment);
		$ret_val = array(
   			'day' => $day_adjustment,
   			'day_unit' => $day_adjustment_unit,
			'time' => $time_adjustment,
			'time_unit' => $time_adjustment_unit
	    );
	    echo json_encode( $ret_val );				
		jExit();
	}


	function gw_token()
	{
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		include_once( JPATH_SITE."/components/com_rsappt_pro3/payment_processors/google_wallet/JWT.php" );
		// get google_wallet settings
		$jinput = JFactory::getApplication()->input;
		$database =JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_google_wallet_settings;';
		try{
			$database->setQuery($sql);
			$google_wallet_settings = NULL;
			$google_wallet_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gw_token", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}
		$name = $jinput->getString( 'gw_name', 'Google Purchase' );
		$description = $jinput->getString( 'gw_description', 'Google Purchase' );
		$price = $jinput->getString( 'gw_price', '0.00' );
		$req_id = $jinput->getString( 'gw_req_id', '-1' );
		if($req_id != "cart"){
			$description = processTokens($req_id, $description);
		}

		$payload = array(
		  "iss" => $google_wallet_settings->google_wallet_seller_id,
		  "aud" => "Google",
		  "typ" => "google/payments/inapp/item/v1",
		  "exp" => time() + 3600,
		  "iat" => time(),
		  "request" => array (
			"name" => $name,
			"description" => $description,
			"price" => $price,
			"currencyCode" => "USD",
			"sellerData" => $req_id
		  )
		);
		$gwToken = JWT::encode($payload, $google_wallet_settings->google_wallet_seller_secret);		
		echo json_encode($gwToken);
		jExit();
	}
	
	function gw_fail()
	{
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		// get google_wallet settings
		$jinput = JFactory::getApplication()->input;
		$database =JFactory::getDBO(); 
		$req_id = $jinput->getString( 'req_id', '-1' );
		$sql = "UPDATE #__sv_apptpro3_requests set payment_processor_used='GoogleWallet', request_status='deleted', ".
		" admin_comment='Google Wallet transaction failed or was cancelled by user'".
		" WHERE id_requests=".$req_id;
		try{				
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gw_fail", "", "");
		}
		
				
		echo json_encode("Canceled");
		jExit();
	}

	function gw_wrapit()	
	{
		$jinput = JFactory::getApplication()->input;
		if($jinput->getString( 'req_id', '-1' ) == "cart"){
			$cart = "Yes";
		} else {
			$cart = "No";
		}
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/payment_processors/google_wallet/google_wallet_process_payment.php');		
	}

	function ajax_check_overrun()
	{
		// check to see if new booking, adjusted for service, extras, etc, overruns an exiting booking.	
		$jinput = JFactory::getApplication()->input;
		$resource = $jinput->getInt( 'res', '0' );
		$startdate = $jinput->getString( 'bk_date', '' );
		$starttime = $jinput->getString( 'bk_start', '' );
		$endtime = $jinput->getString( 'bk_end', '' );
	
		$database = JFactory::getDBO(); 
		$err = "";
		
		// if max_seats > 1 no need to check as all booking for a slot must be the same duration (no sercie based duration or extras durtion allowed.
		$sql = 'SELECT max_seats FROM #__sv_apptpro3_resources WHERE id_resources = '.(int)$resource;
		try{
			$database->setQuery($sql);
			$max_seats = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "chk_overrun", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
		}		
		if($max_seats > 1 || $max_seats == 0){
			jExit();
		}
		
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "chk_overrun", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
		}		
		
		$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
		$myenddatetime = "STR_TO_DATE('".$startdate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		$gap = 0;
		// if a gap is define, use it
		if($apptpro_config->gap > 0){
			$gap = $apptpro_config->gap;
		}
		
		$sql = "SELECT id_requests FROM #__sv_apptpro3_requests "
			." WHERE (resource = '". $resource ."')"
			." and (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"")." )"
			." and ((". $mystartdatetime ." >= CONCAT(startdate, ' ', starttime) and ". $mystartdatetime ." <= DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE))"
			." or (". $myenddatetime ." >= CONCAT(startdate, ' ', starttime) and ". $myenddatetime ." <= CONCAT(enddate, ' ', endtime))"
			." or ( CONCAT(startdate, ' ', starttime) >= ". $mystartdatetime ." and CONCAT(startdate, ' ', starttime) <= ". $myenddatetime ." )"
			." or ( DATE_ADD( STR_TO_DATE(CONCAT(enddate, ' ', endtime), '%Y-%m-%d %T'), INTERVAL ".$gap." MINUTE) >= ". $mystartdatetime ." and DATE_ADD( STR_TO_DATE(CONCAT(enddate, ' ', endtime), '%Y-%m-%d %T'), INTERVAL ".$gap." MINUTE) <= ". $myenddatetime ."))";	
		//logIt($sql, "chk_overrun", "", "");
		try{
			$database->setQuery($sql);
			$overruns = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "chk_overrun", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
		}		
		if(sv_count_($overruns) > 0){
			$err = $err.JText::_('RS1_INPUT_SCRN_CONFLICT_ERR')."|".JText::_('RS1_WARNING')."|".JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');
		}
		
		// make sure no overlap with book-offs
		$sql = "select count(*) from #__sv_apptpro3_bookoffs "
		." where (resource_id = '". $resource ."')"
		." and published = 1 and full_day = 'No' "
		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
		//print $sql; exit();
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "chk_overrun", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}		
		if ($overlapcount >0){
			$err = $err.JText::_('RS1_INPUT_SCRN_BO_CONFLICT_ERR')."|".JText::_('RS1_WARNING')."|".JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');
		}

		// make sure no overlap with rolling book-offs, rolling book-off is time only, ignores date
		// For rolling book-offs we need to see if the weekday matches the rolling_bookoff weekday.
		// Example rolling_bookoff = "0,1,1,1,0,1,0" = rb on mon,tue,wed,fri only
		$weekday = date("w",(strtotime($startdate)));
		$rb_filter = build_mask_filter($weekday);
		$mystartdatetime_rolling = "STR_TO_DATE(CONCAT(CURDATE(), '". $starttime ."'), '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
		$myenddatetime_rolling = "STR_TO_DATE(CONCAT(CURDATE(), '". $endtime ."'), '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		$myenddatetime_rolling = "DATE_ADD( $myenddatetime_rolling, INTERVAL ".$gap." MINUTE)";

		$sql = "select count(*) from #__sv_apptpro3_bookoffs "
		." where (resource_id = '". $resource ."')"
		." and published = 1 and full_day = 'No' "
		." and rolling_bookoff like '".$rb_filter."' "
		." and ((". $mystartdatetime_rolling ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime_rolling ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime_rolling ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime_rolling ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime_rolling ." and STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime_rolling .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime_rolling ." and STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime_rolling ."))";
		//print $sql; exit();
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "chk_overrun", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}	
		if ($overlapcount >0){
			$err = $err.JText::_('RS1_INPUT_SCRN_BO_CONFLICT_ERR')."|".JText::_('RS1_WARNING')."|".JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');
		}
		echo $err;		
		jExit();
	}

	function ajax_quick_status_change()
	{
		$jinput = JFactory::getApplication()->input;
		$id_requests = $jinput->getInt( 'bk', '0' );
		$new_status = $jinput->getString( 'new_stat', '' );
	
		$database = JFactory::getDBO(); 

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model = new admin_detailModelrequests_detail;
		$model->setId($id_requests);
		$detail	= $model->getData();
		//echo $detail->request_status;
		$jinput->set("old_status", $detail->request_status);
		$detail->request_status = $new_status;
	
		// run the staff validation to ensure this change will not create a conflict
		include_once( JPATH_SITE."/components/com_rsappt_pro3/fe_val_edit_pt2.php" );
		$err = do_staff_edit_validation($detail->id_requests,$detail->request_status,$detail->name,$detail->phone,$detail->email,$detail->resource,
			$detail->startdate,$detail->starttime,$detail->enddate,$detail->endtime,$detail->booked_seats,$detail->user_id);
			
		if( $err!=JText::_('RS1_INPUT_SCRN_VALIDATION_OK')){
			echo $err;
		} else {			
			if($result = $model->store($detail)){
				echo "OK";
			}
		}
		jExit();
	}

	function ajax_add_to_notification_list()
	{
		// check to see if new booking, adjusted for service, extras, etc, overruns an exiting booking.	
		$jinput = JFactory::getApplication()->input;
		$resource = $jinput->getInt( 'res', '0' );
		$startdate = $jinput->getString( 'startdate', '' );
		$starttime = $jinput->getString( 'starttime', '' );
		$email = $jinput->getString( 'email', '' );
		$remove = $jinput->getString( 'remove', 'false' );	
		
		if(!validEmail($email)){
			echo JText::_('RS1_INPUT_SCRN_EMAIL_ERR');
			jExit;
			return false;
		}	
			
		$database = JFactory::getDBO(); 
		if($remove == 'true'){
			$sql = "DELETE FROM #__sv_apptpro3_notification_list WHERE ".
				"resource = ".$resource.
				" AND booking_start = "."'".$database->escape($startdate)." ".$database->escape($starttime)."'".		
				" AND email = "."'".$database->escape($email)."'";
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ajax_add_to_notification_list", "", "");
				echo JText::_('RS1_SQL_ERROR');
				jExit();
			}	
			echo JText::_('RS1_REMOVED');
			jExit;
			
		} else {		
			// check to see if already added
			$sql = "SELECT count(*) FROM #__sv_apptpro3_notification_list WHERE ".
				"resource = ".$resource.
				" AND booking_start = "."'".$database->escape($startdate)." ".$database->escape($starttime)."'".		
				" AND email = "."'".$database->escape($email)."'";
			try{
				$database->setQuery($sql);
				$thecount = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ajax_add_to_notification_list", "", "");
				echo JText::_('RS1_SQL_ERROR');
				jExit();
			}	
			if($thecount > 0){
				echo JText::_('RS1_ALREADY_ADDED');
				jExit();
			}
			
			$sql = "INSERT INTO #__sv_apptpro3_notification_list (resource, booking_start, email) ".
				"VALUES (".
				$resource.",".
				"'".$database->escape($startdate)." ".$database->escape($starttime)."',".			
				"'".$database->escape($email)."'".
				")";
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ajax_add_to_notification_list", "", "");
				echo JText::_('RS1_SQL_ERROR');
				jExit();
			}	
			echo JText::_('RS1_ADDED_TO_LIST');
			jExit();
		}
	}

	function ajax_resources_for_service(){
		$ret_val = "";
		$jinput = JFactory::getApplication()->input;
		$database = JFactory::getDBO(); 

		$service = $jinput->getInt( 'sid', '0' );
		$sql = 'SELECT resource_scope FROM #__sv_apptpro3_services WHERE id_services = '.$service;
		try{
			$database->setQuery($sql);
			$service_resources = null;
			$service_resources = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_resources_for_service", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}
		if($service_resources != null){
			// make a sting for the IN clause
			$temp = str_replace("||",",",$service_resources);
			$ret_val = str_replace("|","",$temp);			
		}
		//logIt($ret_val, "ajax_resources_for_service", "", "");

		echo $ret_val;
		jExit();
	}
	
	function ajax_services_for_category(){
		$ret_val = "";
		$jinput = JFactory::getApplication()->input;
		$database = JFactory::getDBO(); 

		$cat = $jinput->getInt( 'cat', '0' );
		$sql = 'SELECT * FROM #__sv_apptpro3_services WHERE published = 1 ';
			$safe_search_string = '%|' . $database->escape( $cat, true ) . '|%' ;							
			$sql .= ' AND category_scope LIKE '.$database->quote( $safe_search_string, false );
			$sql .= ' ORDER BY ordering';
		try{
			$database->setQuery($sql);
			$cat_services = null;
			$cat_services = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_services_for_category", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}
		echo '<select name="service_selector" id="service_selector" class="sv_apptpro_request_dropdown" onchange="changeServiceSelector()" '.				
	  	    	'title="'.(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_SERVICE_SELECTOR_TOOLTIP')).'">';
					$k = 0;
					echo '<option value="">'.JText::_('RS1_SERVICE_SELECTOR_FIRSTROW').'</option>';
					for($i=0; $i < sv_count_($cat_services ); $i++) {
						$cat_service = $cat_services[$i];
			          	echo '<option value='.$cat_service->id_services.'>'.JText::_(stripslashes($cat_service->name)).'</option>\n';
    	      			$k = 1 - $k; 
					} 
        echo '</select>';
		jExit();
	}
	
	function ajax_set_book_dates_enable()
	{
		$jinput = JFactory::getApplication()->input;
		$new_value = $jinput->getWord( 'nv', 'Np' );
		$resource = $jinput->getInt( 'res', 0 );

		$database =JFactory::getDBO(); 
		$sql = 'UPDATE #__sv_apptpro3_resources SET date_specific_booking = "'.$new_value.'" WHERE id_resources = '.$resource;
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		
		
		echo json_encode(JText::_('RS1_ADMIN_BOOK_DATES_ENABLE_CHANGE'));
		jExit();
	}


	function ajax_purge_old_dates($cachable=false, $urlparams=false) {
		
		$jinput = JFactory::getApplication()->input;
		$resource = $jinput->getInt('res',0); 
		
		$row_deleted = null;
		
		$database = JFactory::getDBO(); 
		$sql = "DELETE FROM #__sv_apptpro3_book_dates WHERE resource_id=".$resource.
			" AND book_date < CURDATE();";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_bok_dates", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		
		$sql = "SELECT ROW_COUNT();";
		try{
			$database->setQuery( $sql );
			$row_deleted = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_bok_dates", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		
		
		
		echo json_encode(JText::_('RS1_ADMIN_BOOK_DATES_PURGE_COMPLETE').": ".$row_deleted);
		jExit();
	}

	
}
?>

