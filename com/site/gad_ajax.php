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

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	jimport( 'joomla.utilities.date' );

//	header('Content-Type: text/xml'); 
	header('Content-Type: text/html; charset=utf-8'); 
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: No-cache");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	// recieve the variables from the caller
	$user = JFactory::getUser();
	$jinput = JFactory::getApplication()->input;

	$gridstarttime = $jinput->getString('gridstarttime');
	$gridendtime = $jinput->getString('gridendtime');
	$category = $jinput->getInt('category');
	$mode = $jinput->getWord('mode');
	$grid_date = $jinput->getString('grid_date');
	$gridwidth = $jinput->getString('gridwidth');
	$namewidth = $jinput->getString('namewidth');
	$browser = $jinput->getString('browser');
	$reg = $jinput->getWord('reg', 'No');
	$mobile = $jinput->getWord('mobile', 'No');
	$front_desk = $jinput->getWord('fd', 'No');
	
	$resource = $jinput->getInt('resource');
	$grid_days = $jinput->getInt('grid_days');

	$appWeb      = JFactory::getApplication();
	$mobile = false;
	if($appWeb->client->mobile){
		$mobile = true;
		$namewidth = 0;
	};
	$gap = $jinput->getInt('gap', 0);
	$max_seats = 1;
	$adjusted_max_seats = 0;

	$timeslot_available_class = "sv_gad_timeslot_available";
	$service_resource_ids = $jinput->getString('srv_res_ids', "");

	$who_booked = "";

//	fix for Joomla 1.6 bug(?) whereby the JDate switches timezones for a toFormat but does not put it back.
//	http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=24604	
//	pass the grid date as UTC to JDate
//
//	Joomla bug tracker reports fixed in June 2011 - Joomla release??
//
//	AArrggghhh this JDate problem is driving me crazy.
//	This verison of gad_ajax.php has all the JDate calls overridden by put php date functions to get around a problem with 
//	Joomla's JDate and UTC+1 and DST.
	
	require_once( JPATH_CONFIGURATION.'/configuration.php' );
	$CONFIG = new JConfig();
	$timezone_identifier = $CONFIG->offset;
	
	$lang = JFactory::getLanguage();
	setlocale(LC_TIME, str_replace("-", "_", $lang->getTag()).".utf8");
	// on a Windows server you need to spell it out
	// offical names can be found here..
	// http://msdn.microsoft.com/en-ca/library/39cwe7zf(v=vs.80).aspx
	//setlocale(LC_TIME,"swedish");
	// Using the first two letteres seems to work in many cases.
	if(WINDOWS){	
		setlocale(LC_TIME, substr($lang->getTag(),0,2)); 
	}
	// Greek is a problem, this works on a Linux server..
	//setlocale(LC_TIME, array('el_GR.UTF-8','el_GR','greek'));
	if($lang->getTag() == "el-GR"){
		setlocale(LC_TIME, array('el_GR.UTF-8','el_GR','greek'));
	}

	$database = JFactory::getDBO(); 
	$sql = "SET NAMES 'utf8';";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_ajax", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}
	$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";		
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_ajax", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	$ts_id = 0;

	$database = JFactory::getDBO(); 
	
//	$debug_grid_date = strtotime($grid_date);
//	$debug_grid_jdate = new JDate($debug_grid_date);
//	$debug_local_date = new JDate();
//	
//	echo "DEBUG: Grid JDate = ".$debug_grid_jdate->toFormat("%a %d-%b-%Y")."<br/>";
//	echo "DEBUG: Server date/time = ".date("D M j G:i:s T Y")."<br/>";
//	echo "DEBUG: Local date/time = ".$debug_local_date->toFormat('%Y-%m-%d %H:%M:%S', true)."<br/>";
//	echo "DEBUG: Local date/time = ".$debug_local_date->toFormat('%Y-%m-%d %H:%M:%S', false)."<br/>";
	
	if($mode == "single_day"){
		$grid_previous = date("Y-m-d", strtotime("-1 day", strtotime($grid_date)));
		$grid_next = date("Y-m-d", strtotime("+1 day", strtotime($grid_date)));
	} else {
// This works but then the grid is out of sync with the date selector		
//		// single resource, if the resource has disable_dates_before set and it is in the future, set grid_date to that date.
//		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id = '.$resource;
//		$database->setQuery($sql);
//		$res_detail = NULL;
//		$res_detail = $database -> loadObject();
//		if(strpos($res_detail->disable_dates_before, "-")>0){
//			if(strtotime($res_detail->disable_dates_before." 23:59:00") > strtotime('now')){
//				$grid_date = $res_detail->disable_dates_before;
//			}
//		}

		$grid_previous = date("Y-m-d", strtotime("-".$grid_days." day", strtotime($grid_date)));
		$grid_next = date("Y-m-d", strtotime("+".$grid_days." day", strtotime($grid_date)));
	}

	
	
	// how many colums 700px - 100 (res name) = 600/num hours btween grid start and end
	$startpoint = intval(substr($gridstarttime,0,2)); 
	$endpoint = intval(substr($gridendtime,0,2)); 
	$columncount = $endpoint - $startpoint;
	if($columncount <1){echo JText::_('RS1_GAD_SCRN_GRID_START_BEFORE_END'); exit;}
	$colwidth = ($gridwidth-$namewidth)/$columncount;
	$window_start_minute = $startpoint * 60;
	$window_end_minute = $endpoint * 60;
	// We need to position each timeslot withing the table row. To do this we need to divide the tabel row into px/minutes
	// Once we know how many px/min, we can place timeslots the righ number of px from the left.
	$pxminute = ($gridwidth-$namewidth)/($window_end_minute - $window_start_minute);
	
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_ajax", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		
	
	// images
	$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' border='0'/>";
	$timeslot_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'/>";
	
	if($mode == "single_day"){
		if(WINDOWS){			
			//$display_grid_date = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,strtotime($grid_date)));
			$display_grid_date = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,strtotime($grid_date)));

			//$jdisplay_grid_date = new JDate($grid_date);
			//$display_grid_date = iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdisplay_grid_date->toFormat($apptpro_config->gad_date_format));
		} else {
			$display_grid_date = strftime($apptpro_config->gad_date_format,(strtotime($grid_date)));
			//$jdisplay_grid_date = new JDate($grid_date);
			//$display_grid_date = $jdisplay_grid_date->toFormat($apptpro_config->gad_date_format);

		}
	} else {
		$display_grid_date = "&nbsp;";
	}
		?>
		<table id="sv_gad_outer_table" width="100%" border="0" cellpadding="0" cellspacing="0" class="table table-striped">
              <?php 
			  	echo "<div class='sv_gad_master_container' style='position: relative; width:".$gridwidth."px;'>";

				//*************************************************************
			  	// header row start			
				//*************************************************************
				if($mobile){
				  	echo "<tr><td>".
					"<div class='sv_gad_row_wrapper' style='position: relative; width:100%'>";
				} else {
				  	echo "<tr><td width='".$namewidth."' align='center'><span id='display_grid_date' ><label class=\"sv_apptpro_request_label\">".$display_grid_date."</label></span></td><td colspan='".$columncount."' >".
					"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth-$namewidth)."px; '>";
				}
				for($i=0; $i<$columncount; $i++){
					if($mobile == "Yes"){
						$left = round(($i)*60*$pxminute);
						$width = round(60*$pxminute);
					} else {
						$left = ($i)*60*$pxminute;
						$width = 60*$pxminute;
					}
					$strTime = "";
					if($apptpro_config->timeFormat == "12"){
						$timedisplay = ($i+$startpoint);
						if($timedisplay == 12){
							$strTime = JText::_('RS1_INPUT_SCRN_NOON');
						} else if($timedisplay > 12){
							$strTime = strval($timedisplay - 12);
							$strTime .= " PM";
						} else {
							$strTime = strval($timedisplay);
							$strTime .= " AM";
						}
					} else {				
						$strTime = strval($i+$startpoint);
						if($mobile != "Yes"){
							$strTime .= ":00";
						}
					}
					echo "<div class='sv_gad_timeslot_header' style='left:".$left."px; width:".$width."px; position:absolute; float:left'>&nbsp;".$strTime."</div>"; 
				}
				echo "</td></tr>"; // end of header row

				//*************************************************************
			  	// header row end			
				//*************************************************************
						

				//resource rows
				// **********************************************************
				// if mode = single_day we show all resources, for $grid_date
				// **********************************************************
				// Because each resource can have different timeslots on a given day, 
				// each row (or resource) will need to be a seperate table ;-(
				if($reg=='No'){ // No = #user->guest
					// access must contain '|1|'
					$andClause = " AND access LIKE '%|1|%' ";
				} else {
					$andClause = " AND access != 'public_only' ";
				}
				if($front_desk == "Yes"){
					// only resources for which user is res admin
					$andClause .= " AND resource_admins LIKE '%|".$user->id."|%' ";
				}
				if($service_resource_ids != ""){
					$safe_search_string = $database->escape( $service_resource_ids, true );
					$andClause .= " AND id_resources IN(".$safe_search_string.")";
				}
				
				if($mode == "single_day"){
					// get resources
					$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE published=1 ".$andClause;
					if($category != 0 && $service_resource_ids == ""){
						$safe_search_string = '%|' . $database->escape( $category, true ) . '|%' ;							
						$sql .=" AND category_scope LIKE ".$database->quote( $safe_search_string, false )." ";
					}
					$sql .=" ORDER BY ordering";
					
					try{
						$database->setQuery($sql);
						$res_rows = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
					// get get bookings
					$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE startdate = '".$grid_date."'";
					$sql .=" AND (request_status='accepted' OR request_status='pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") ORDER BY starttime";
					try{
						$database->setQuery($sql);
						$bookings = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// get get part day book-offs
					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE ".
						"((off_date = '".$grid_date."' AND full_day='No') ".
						" OR ".
						"(rolling_bookoff != 'No'))". 					
						" AND published=1 ORDER BY rolling_bookoff, bookoff_starttime";
					try{
						$database->setQuery($sql);
						$part_day_bookoffs = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// walk through resources, getting timeslots and bookings
					if(sv_count_($res_rows) > 0){
						foreach($res_rows as $res_row){
							$res_detail = $res_row;		
							$max_seats = $res_detail->max_seats;			
							$display_this_resource = true;
							// is this resource restricted to a specific group?
							if($res_detail->access == 'everyone' || stripos($res_detail->access, "|1|") > -1){
								$display_this_resource = true;							
							} else {
								// yes further checking is reqiuired..
								if($res_detail->access == "public_only"){ 
									// do not show if user logged in
									if(!$user->guest){
										$display_this_resource = false;
									}
								} else {
									// access is not everyone and not public_only so we need to see if the user is a member of the group specified
									$groups = str_replace("||", ",", $res_detail->access);
									$groups = str_replace("|", "", $groups);
	
									$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE group_id IN (".$groups.") AND user_id = ".$user->id;
									//echo $sql;
									$database->setQuery($sql);
									$match = $database->loadResult();
									if($match < 1){
										$display_this_resource = false;
									}
								}
								
							}
							if($display_this_resource){
								// get timeslots for $grid_date
								$weekday = getdate(strtotime($grid_date)); 
								$weekday["wday"];
		
								$sql = "SELECT *, ";
								if($apptpro_config->timeFormat == '12'){							
								$sql .=" DATE_FORMAT(timeslot_starttime, '%l:%i %p') as display_timeslot_starttime, ".
									" DATE_FORMAT(timeslot_endtime, '%l:%i %p') as display_timeslot_endtime ";						
								} else {
								$sql .=" DATE_FORMAT(timeslot_starttime, '%H:%i') as display_timeslot_starttime, ".
									" DATE_FORMAT(timeslot_endtime, '%H:%i') as display_timeslot_endtime ";						
								}	
								$sql .=	"FROM #__sv_apptpro3_timeslots WHERE published=1 ";
								$sql .=	($front_desk=='Yes'?"":" AND staff_only = 'No' ");
								if($res_detail->timeslots == "Global"){
									$sql .=	" AND (resource_id is null or resource_id = 0) AND day_number = ".$weekday["wday"].
										" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$grid_date."' >= start_publishing ) ".
										" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$grid_date."' <= end_publishing ) ".
										" ORDER BY timeslot_starttime";
								} else {
									$sql .=	" AND resource_id = ".$res_row->id_resources." AND day_number = ".$weekday["wday"].
										" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$grid_date."' >= start_publishing ) ".
										" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$grid_date."' <= end_publishing ) ".
										" ORDER BY timeslot_starttime";
								} 
								try{					
									$database->setQuery($sql);
									$slot_rows = $database -> loadObjectList();
								} catch (RuntimeException $e) {
									logIt($e->getMessage(), "gad_ajax", "", "");
									echo JText::_('RS1_SQL_ERROR');
									exit;
								}									
								
								if($mobile){
									echo "<tr class='sv_tablet_row' >".
										"<td class='sv_gad_row_wrapper_mobile' colspan='".$columncount."'>".
//										"<div style='display:inline-block'>".stripslashes($res_row->name)."</div>". 
										"<div style='display:inline-block'>
										<a href=javascript:changeMode(".$res_row->id_resources.") class=\"resource_label\">".JText::_(stripslashes($res_row->name))."</a></div>". 
											"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth)."px; '>";
								} else {
									echo "<tr class='sv_gad_timeslot_yaxis_header'>".
										"<td style=\"text-align:center;\" ><a href=javascript:changeMode(".$res_row->id_resources.") class=\"resource_label\">";
									if($apptpro_config->enable_ddslick == "Yes" && $res_row->ddslick_image_path !="" && $res_row->show_image_in_grid == "Yes"){
										echo "<img src=\"".getResourceImageURL($res_row->ddslick_image_path)."\" class=\"resource_image\"><br/>";
										//echo "<a href=javascript:changeMode(".$res_row->id_resources.")><img src=\"".getResourceImageURL($res_row->ddslick_image_path)."\" class=\"resource_image\"></a><br/>";
									}									
									echo JText::_(stripslashes($res_row->name))."</a></td>".
										"<td class='sv_gad_slots_row_wrapper' colspan='".$columncount."'>".
											"<div style='position: relative; width:".($gridwidth-$namewidth)."px;'>";
								}
								
								date_default_timezone_set($timezone_identifier);
								$sr = showrow($res_row, $grid_date, $weekday["wday"]);
								//showrow return values: past, bookoff, dayoff, disabled, yes
							
								// to not display book-offs on front-desk booking screen (ie let staff override them)
								// uncomment next 3 lines.
								if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
									if($sr != "bookoff"){
										$sr = "yes";
									}
								} 	
								
								if($sr == "yes"){ 
										// Timeslots first
										if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
											$time_adjusted_for_lead = time() - ($apptpro_config->staff_booking_in_the_past * 86400);
										} else {
											$time_adjusted_for_lead = time() + ($res_row->min_lead_time * 60 * 60);
										}
				echo "<div id='timeslot_resize_container' style='height:40px'>";					
										foreach($slot_rows as $slot_row){
											$timeslot_available_class = "sv_gad_timeslot_available".($slot_row->staff_only == "Yes"?"_staff":"");
											$adjusted_max_seats = getSeatAdjustments($grid_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_row->id_resources, $res_row->max_seats);
											if(strtotime($grid_date." ".$slot_row->timeslot_starttime) > $time_adjusted_for_lead){ // hide slots where time has passed
												if($apptpro_config->show_available_seats == "Yes" && $res_row->max_seats>1){
													$currentcount = getCurrentSeatCount($grid_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_row->id_resources);
													if($currentcount > 0){
														$timeslot_insert = "<span class='sv_apptpro_low_seats'>".strval($res_row->max_seats + $adjusted_max_seats - $currentcount)."</span></a>";
													} else {
														$timeslot_insert = strval($res_row->max_seats + $adjusted_max_seats - $currentcount)."</a>";
													}
												} else {
													if($slot_row->timeslot_description != ""){
														$timeslot_insert = JText::_($slot_row->timeslot_description)."</a>";
													} else {
														$timeslot_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'/></a>";
													}
												}
												$ts_id ++;
												$timeslot_tooltip = "";
												// If you want a tooltip on the timeslots you can uncomment one of the $timeslot_tooltip lines below. 
												// You will also need to comment out further down for the single resource multi day view around line 662
												// This is not compatible with the 'Who Booked in Tooltip' for Max Seats > 1
												// This one shows date and time 
												//$timeslot_tooltip = " title='".$grid_date."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
												// or if you want a different date format..
												//$timeslot_tooltip = " title='".date("d-m-Y",strtotime($grid_date))."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
												// This one shows time only
												//$timeslot_tooltip = " title='".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
		
												// get start minute, end minute								
												$slotstart_minute = getMinute($slot_row->timeslot_starttime);
												$slotend_minute = getMinute($slot_row->timeslot_endtime);
												if($slotstart_minute >= $window_start_minute && $slotend_minute <=$window_end_minute){
													if($mobile == "Yes"){
														$left = round(($slotstart_minute-$window_start_minute)*$pxminute);
														$width = round(($slotend_minute-$slotstart_minute - 2)*$pxminute);
													} else {
														$left = ($slotstart_minute-$window_start_minute)*$pxminute;
														$width = ($slotend_minute-$slotstart_minute - 2)*$pxminute;
													}
													echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;'>".
													"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' href=# onmouseenter='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
													$res_row->id_resources."|".
													sv_encode(JText::_($res_row->name))."|".
													$grid_date."|";
													if(WINDOWS){
														echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE', strftime($apptpro_config->gad_date_format, strtotime($grid_date))))."|";
														//date_default_timezone_set($timezone_identifier); 
														//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
														//date_default_timezone_set($timezone_identifier); 
														//echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format)))."|";
														//date_default_timezone_set($timezone_identifier); 
													} else {
														echo sv_encode(strftime($apptpro_config->gad_date_format, strtotime($grid_date)))."|";
														//date_default_timezone_set($timezone_identifier); 
														//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
														//date_default_timezone_set($timezone_identifier); 
														//echo sv_encode($jdayname->toFormat($apptpro_config->gad_date_format))."|";
														//date_default_timezone_set($timezone_identifier); 
													}
			
													echo $slot_row->timeslot_starttime."|".
													sv_encode($slot_row->display_timeslot_starttime)."|".
													$slot_row->timeslot_endtime."|".
													sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_row->gap."|".$res_row->free_booking."\",event);return false;'>".
													$timeslot_insert.
													"</div>\n"; 
												} else if($slotend_minute > $window_end_minute){
													// goes beyond window
													if($slotstart_minute < $window_end_minute){
														// but starts in window
														$left = ($slotstart_minute-$window_start_minute)*$pxminute;
														$width = ($window_end_minute - $slotstart_minute - 4)*$pxminute;								
														echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>\n"; 								
													} // else starts beyond window, do not show
												} else if($slotstart_minute < $window_start_minute){	
													// starts before window
													$left = 0;
													// width = full width - amount before window									
													$width = (($slotend_minute-$slotstart_minute) - ($window_start_minute - $slotstart_minute) )*$pxminute;
													echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>\n"; 
												} else {
													// bigger than grid, fill'er up
													$left = 0;
													// width = full width - amount before window									
													$width = (window_end_minute - $window_start_minute)*$pxminute;
													echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>\n"; 									
												}									
											}	
										}
										
										// bookings now
										if(sv_count_($bookings) > 0){
											foreach($bookings as $booking){
												if($apptpro_config->gad_who_booked == "Yes"){
													$who_booked = " title='".$booking->name."'";
												}
												if($booking->resource == $res_row->id_resources){
													// booking is for this resource
													// has max_seats been reached?
													if(fullyBooked($booking, $res_row, $apptpro_config)){
														$bookingstart_minute = getMinute($booking->starttime);
														$bookingend_minute = getMinute($booking->endtime);
														if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
															$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
															$width = ($bookingend_minute-$bookingstart_minute-2)*$pxminute;
															if($booking->request_status == 'accepted'){
																if($apptpro_config->enable_notification_list == "Yes"){
																	echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
																} else {
																	echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div>"; 
																}
															} else {
																echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div>"; 
															}
															$res_spec_gap = $gap;  //$gap = component level
															if($res_row->gap != 0){ $res_spec_gap = $res_row->gap;} 
															if($res_spec_gap > 0 && $max_seats == 1){
																// add gap	
																if($bookingend_minute <$window_end_minute){								
																	$gap_start = $left + $width+4;
																	$gap_width = ($res_spec_gap-6)*$pxminute;
																	echo "<div class='sv_gad_timeslot_gap' style='left:".$gap_start."px; width:".$gap_width."px; position:absolute; float:left; text-align:center;' >  </div>"; 
																}
															}
																									
														} else if($bookingend_minute > $window_end_minute){
															// goes beyond window
															if($slotstart_minute < $window_end_minute){
																// but starts in window
																$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
																$width = ($window_end_minute - $bookingstart_minute - 4)*$pxminute;								
																if($booking->request_status == 'accepted'){
																	if($apptpro_config->enable_notification_list == "Yes"){
																		echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
																	} else {
																		echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
																	}
																} else {
																	echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
																}																		
															} // else starts beyond window, do not show
														} else if($bookingstart_minute < $window_start_minute){	
															// starts before window
															$left = 0;
															// width = full width - amount before window									
															$width = (($bookingend_minute-$bookingstart_minute) - ($window_start_minute - $bookingstart_minute))*$pxminute;
															if($booking->request_status == 'accepted'){
																if($apptpro_config->enable_notification_list == "Yes"){
																	echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
																} else {
																	echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
																}
															} else {
																echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
															}	
															$res_spec_gap = $gap;  //$gap = component level
															if($res_row->gap != 0){ $res_spec_gap = $res_row->gap;} 
															if($res_spec_gap > 0 && $max_seats == 1){
																// add gap									
																$gap_start = $left + $width+4;
																$gap_width = ($res_spec_gap-6)*$pxminute;
																echo "<div class='sv_gad_timeslot_gap' style='left:".$gap_start."px; width:".$gap_width."px; position:absolute; float:left; text-align:center;' >  </div>"; 
															}
														} else {
															// bigger than grid, fill'er up
															$left = 0;
															// width = full width - amount before window									
															$width = (window_end_minute - $window_start_minute)*$pxminute;
															if($booking->request_status == 'accepted'){
																if($apptpro_config->enable_notification_list == "Yes"){
																	echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
																} else {
																	echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
																}
															} else {
																echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
															}																		
														}				
													}
												}
											}
										}
										// part day book-offs now
										if(sv_count_($part_day_bookoffs) > 0){
											foreach($part_day_bookoffs as $part_day_bookoff){

												if( ( $part_day_bookoff->rolling_bookoff != 'No' 
													&& rb_day($part_day_bookoff->rolling_bookoff,  date("w",(strtotime($grid_date))))) 
													|| ($part_day_bookoff->rolling_bookoff == 'No') ){ 
													
													if($part_day_bookoff->resource_id == $res_row->id_resources){
														$bookingstart_minute = getMinute($part_day_bookoff->bookoff_starttime);
														$bookingend_minute = getMinute($part_day_bookoff->bookoff_endtime);
														if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
															$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
															$width = ($bookingend_minute-$bookingstart_minute-2)*$pxminute;
															echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
														} else if($bookingend_minute > $window_end_minute){
															// goes beyond window
															if($slotstart_minute < $window_end_minute){
																// but starts in window
																$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
																$width = ($window_end_minute - $bookingstart_minute - 4)*$pxminute;								
																echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
															} // else starts beyond window, do not show
														} else if($bookingstart_minute < $window_start_minute){	
															// starts before window
															$left = 0;
															// width = full width - amount before window									
															$width = (($bookingend_minute-$bookingstart_minute) - ($window_start_minute - $bookingstart_minute))*$pxminute;
															echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
														} else {
															// bigger than grid, fill'er up
															$left = 0;
															// width = full width - amount before window									
															$width = (window_end_minute - $window_start_minute)*$pxminute;
															echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
														}				
												}
												}
											}
										
										}
				echo "</div>";
								} else if($sr == "bookoff"){ 							
									$bo = getBookOffDescription($res_row, $grid_date);
									$grid_only_width = intVal($gridwidth) - intVal($namewidth);							
									if($bo != null && $bo->description !=""){
										echo "<div class='sv_gad_timeslot_book-off' style='width:".$grid_only_width ."px; text-align:center'>".JText::_(stripslashes($bo->description))."</div>";
									} 
		
								} else if($sr == "dayoff"){ 							
									$grid_only_width = intVal($gridwidth) - intVal($namewidth);							
									if($res_row->non_work_day_message != "" && (strtotime($grid_date) >= date('Y-m-d'))){
										echo "<div class='sv_gad_non_work_day' style='width:".$grid_only_width ."px; text-align:center'>".JText::_(stripslashes($res_row->non_work_day_message))."</div>";
									}
									
								}
									echo "<div></td></tr>";
								} 
						} // if($display_this_resource)
					}				

				} else {
				// **********************************************************
				// if mode = single_resource we show $show_days of $resource
				// **********************************************************
					// get resource details
					$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources=".$resource;
					try{
						$database->setQuery($sql);
						$res_detail = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
					$max_seats = $res_detail->max_seats;			

					// get get bookings
					$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE resource=".$resource.
					" AND (request_status='accepted' OR request_status='pending'".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") AND startdate >= '".$grid_date."' ".
					" AND startdate <= DATE_ADD(startdate,INTERVAL ".$grid_days." DAY) ".
					" ORDER BY startdate";
					try{
						$database->setQuery($sql);
						$bookings = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// get part day book-offs
					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE resource_id=".$resource. 
						" AND ((off_date >= '".$grid_date."' AND off_date <= DATE_ADD('".$grid_date." 23:59:59',INTERVAL ".($grid_days-1)." DAY))".
						" OR (rolling_bookoff != 'No'))". 									
						" AND full_day='No' AND published=1 ORDER BY off_date";
					try{
						$database->setQuery($sql);
						$part_day_bookoffs = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
					// walk through days, getting timeslots and bookings
					$day=0;
					for($day=0; $day<$grid_days; $day++){	
					
					//$lang = JFactory::getLanguage();
					//setlocale(LC_TIME, $lang->getTag()); 
					if(WINDOWS){
						$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date)))));
						//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
						//$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE', $jdayname->toFormat($apptpro_config->gad_date_format));
					} else {
						$dayname = strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date))));
						//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
						//$dayname = $jdayname->toFormat($apptpro_config->gad_date_format);
					}
					$weekday = date("w",(DateAdd("d", $day, strtotime($grid_date))));
					$strDate = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));
					
					$y_axis_header = "";
					if($mobile){
						//$dayname = $jdayname->format("D M j", true, true);
						$y_axis_header = "<tr class='sv_tablet_row'><td colspan='".$columncount."'>".
							"<div style='display:inline-block'><a href=javascript:changeMode2('".$strDate."') class=\"resource_label\"> ".JText::_($dayname)."</a></div>".
							"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth-$namewidth)."px; '>";
					} else {
						$y_axis_header = "<tr class='sv_gad_timeslot_yaxis_header'><td align='center' class='sv_gad_timeslot_yaxis_header'><a href=javascript:changeMode2('".$strDate."') class=\"resource_label\"> ".JText::_($dayname)."</a></td>
						<td class='sv_gad_slots_row_wrapper' colspan='".$columncount."'>".
							"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth-$namewidth)."px; '>";
					}
					if($res_detail->non_work_day_hide == "No"){
						// always show the row
						echo $y_axis_header;						
					}
					
					date_default_timezone_set($timezone_identifier);
					$sr = showrow($res_detail, $strDate, $weekday);
					//showrow return values: past, bookoff, dayoff, disabled, yes
					// to not display book-offs on front-desk booking screen (ie let staff override them)
					// uncomment next 3 lines.
					if($front_desk == "Yes"  && $apptpro_config->staff_booking_in_the_past > 0){
						if($sr != "bookoff"){
							$sr = "yes";
						}
					} 	
					if($sr == "yes"){ 
						if($res_detail->non_work_day_hide == "Yes"){
							// only show if row has $sr==true
							echo $y_axis_header;
						}
						// get timeslots for each day
						$slots_day = DateAdd("d", $day, strtotime($grid_date));						
							$sql = "SELECT *, ";
							if($apptpro_config->timeFormat == '12'){							
							$sql .=" DATE_FORMAT(timeslot_starttime, '%l:%i %p') as display_timeslot_starttime, ".
								" DATE_FORMAT(timeslot_endtime, '%l:%i %p') as display_timeslot_endtime ";						
							} else {
							$sql .=" DATE_FORMAT(timeslot_starttime, '%H:%i') as display_timeslot_starttime, ".
								" DATE_FORMAT(timeslot_endtime, '%H:%i') as display_timeslot_endtime ";						
							}	

							$sql .=	"FROM #__sv_apptpro3_timeslots WHERE published=1 ";
							$sql .=	($front_desk=='Yes'?"":" AND staff_only = 'No' ");
							if($res_detail->timeslots == "Global"){
								$sql .=	" AND (resource_id is null or resource_id = 0) AND day_number = ".$weekday.
									" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$strDate."' >= start_publishing ) ".
									" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$strDate."' <= end_publishing ) ".
									" ORDER BY timeslot_starttime";
							} else {
								$sql .=	" AND resource_id = ".$resource." AND day_number = ".$weekday.
									" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$strDate."' >= start_publishing ) ".
									" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$strDate."' <= end_publishing ) ".
									" ORDER BY timeslot_starttime";
							} 										
							try{
								$database->setQuery($sql);
								$slot_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "gad_ajax", "", "");
								echo JText::_('RS1_SQL_ERROR');
								exit;
							}		
							date_default_timezone_set($timezone_identifier);
							$row_date = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));						
							
							// Timeslots first
				echo "<div id='timeslot_resize_container' style='height:40px'>";					

							foreach($slot_rows as $slot_row){	
								$timeslot_available_class = "sv_gad_timeslot_available".($slot_row->staff_only == "Yes"?"_staff":"");
								$adjusted_max_seats = getSeatAdjustments($row_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_detail->id_resources, $res_detail->max_seats);
								if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
									$time_adjusted_for_lead = time() - ($apptpro_config->staff_booking_in_the_past * 86400); 
								} else {
									$time_adjusted_for_lead = time() + ($res_detail->min_lead_time * 60 * 60);							
								}
								if(strtotime($row_date." ".$slot_row->timeslot_starttime) > $time_adjusted_for_lead){

									if($apptpro_config->show_available_seats == "Yes" && $res_detail->max_seats>1){
										$row_date = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));
										$currentcount = getCurrentSeatCount($row_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_detail->id_resources);
										if($currentcount > 0){
											$timeslot_insert = "<span class='sv_apptpro_low_seats'>".strval($res_detail->max_seats + $adjusted_max_seats - $currentcount)."</span></a>";
										} else {
												$timeslot_insert = strval($res_detail->max_seats + $adjusted_max_seats - $currentcount)."</a>";
										}
									} else {
										if($slot_row->timeslot_description != ""){
											$timeslot_insert = JText::_($slot_row->timeslot_description)."</a>";
										} else {
											$timeslot_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'/></a>";
										}
									}
									$ts_id ++;
									// get start minute, end minute								
									$slotstart_minute = getMinute($slot_row->timeslot_starttime);
									$slotend_minute = getMinute($slot_row->timeslot_endtime);
									$timeslot_tooltip = "";
									// If you want a tooltip on the timeslots you can uncomment one of the $timeslot_tooltip lines below. 
									// You will also need to comment out further up for the single resource multi day view around line 365
									// This is not compatible with the 'Who Booked in Tooltip' for Max Seats > 1
									// This one shows date and time 
									//$timeslot_tooltip = " title='".$strDate."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
									// This one shows time only
									//$timeslot_tooltip = " title='".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
	
									if($slotstart_minute >= $window_start_minute && $slotend_minute <=$window_end_minute){
										if($mobile == "Yes"){
											$left = round(($slotstart_minute-$window_start_minute)*$pxminute);
											$width = round(($slotend_minute-$slotstart_minute - 2)*$pxminute);
										} else {
											$left = ($slotstart_minute-$window_start_minute)*$pxminute;
											$width = ($slotend_minute-$slotstart_minute - 2)*$pxminute;
										}
											echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;'>".
	//										"<a class='sv_gad_timeslot_clickable".($mobile=="Yes"?"_mobile":"")."' href=javascript:selectTimeslot('".
											"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' href=# onmouseenter='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick=\"selectTimeslot('".
											$res_detail->id_resources."|".
											sv_encode(JText::_($res_detail->name))."|".
											$strDate."|";
											if(WINDOWS){
												echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date))))))."|";
												//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
												//echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format)))."|";
											} else {
												echo sv_encode(strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date)))))."|";
												//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
												//echo sv_encode($jdayname->toFormat($apptpro_config->gad_date_format))."|";
											}
											echo $slot_row->timeslot_starttime."|".
											sv_encode($slot_row->display_timeslot_starttime)."|".
											$slot_row->timeslot_endtime."|".
											sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_detail->gap."|".$res_detail->free_booking."', event);return false;\">".
											$timeslot_insert.
											"</div>\n"; 
									} else if($slotend_minute > $window_end_minute){
										// goes beyond window
										if($slotstart_minute < $window_end_minute){
											// but starts in window
											$left = ($slotstart_minute-$window_start_minute)*$pxminute;
											$width = ($window_end_minute - $slotstart_minute - 4)*$pxminute;								
											echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>\n"; 								
										} // else starts beyond window, do not show
									} else if($slotstart_minute < $window_start_minute){	
										// starts before window
										$left = 0;
										// width = full width - amount before window									
										$width = (($slotend_minute-$slotstart_minute) - ($window_start_minute - $slotstart_minute) )*$pxminute;
										echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>\n"; 
									} else {
										// bigger than grid, fill'er up
										$left = 0;
										// width = full width - amount before window									
										$width = (window_end_minute - $window_start_minute)*$pxminute;
										echo "<div id='ts".$ts_id."' ".$timeslot_tooltip." class='".$timeslot_available_class."' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>\n"; 									
									}									
								}
							}
							// bookings now
							if(sv_count_($bookings) > 0){
								foreach($bookings as $booking){
									if($apptpro_config->gad_who_booked == "Yes"){
										$who_booked = " title='".$booking->name."'";
									}
									if($booking->startdate == date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))))){
										// booking is for this resource
										if(fullyBooked($booking, $res_detail, $apptpro_config)){
											$bookingstart_minute = getMinute($booking->starttime);
											$bookingend_minute = getMinute($booking->endtime);
											if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
												$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
												$width = ($bookingend_minute-$bookingstart_minute-2)*$pxminute;
												if($booking->request_status == 'accepted'){
													if($apptpro_config->enable_notification_list == "Yes"){
														echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
													} else {
														echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div>"; 
													}
												} else {
													echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div>"; 
												}		
												$res_spec_gap = $gap;  //$gap = component level
												if($res_detail->gap != 0){ $res_spec_gap = $res_detail->gap;} 
												if($res_spec_gap > 0 && $max_seats == 1){
													// add gap	
													if($bookingend_minute <$window_end_minute){								
														$gap_start = $left + $width+4;
														$gap_width = ($res_spec_gap-6)*$pxminute;
														echo "<div class='sv_gad_timeslot_gap' style='left:".$gap_start."px; width:".$gap_width."px; position:absolute; float:left; text-align:center;' >  </div>"; 
													}
												}
											} else if($bookingend_minute > $window_end_minute){
												// goes beyond window
												if($slotstart_minute < $window_end_minute){
													// but starts in window
													$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
													$width = ($window_end_minute - $bookingstart_minute - 4)*$pxminute;	
													if($booking->request_status == 'accepted'){
														if($apptpro_config->enable_notification_list == "Yes"){
															echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
														} else {
															echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
														}
													} else {
														echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
													}																		
												} // else starts beyond window, do not show
											} else if($bookingstart_minute < $window_start_minute){	
												// starts before window
												$left = 0;
												// width = full width - amount before window									
												$width = (($bookingend_minute-$bookingstart_minute) - ($window_start_minute - $bookingstart_minute))*$pxminute;
												if($booking->request_status == 'accepted'){
													if($apptpro_config->enable_notification_list == "Yes"){
														echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
													} else {
														echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
													}
												} else {
													echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
												}
												$res_spec_gap = $gap;  //$gap = component level
												if($res_detail->gap != 0){ $res_spec_gap = $res_detail->gap;} 
												if($res_spec_gap > 0 && $max_seats == 1){
													// add gap									
													$gap_start = $left + $width+4;
													$gap_width = ($res_spec_gap-6)*$pxminute;
													echo "<div class='sv_gad_timeslot_gap' style='left:".$gap_start."px; width:".$gap_width."px; position:absolute; float:left; text-align:center;' >  </div>"; 
												}
											} else {
												// bigger than grid, fill'er up
												$left = 0;
												// width = full width - amount before window									
												$width = (window_end_minute - $window_start_minute)*$pxminute;
												if($booking->request_status == 'accepted'){
													if($apptpro_config->enable_notification_list == "Yes"){
														echo "<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\"><div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".$booked_insert."</div></a>"; 
													} else {
														echo "<div class='sv_gad_timeslot_booked' ".$who_booked." style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
													}
												} else {
													echo "<div class='sv_gad_timeslot_pending' style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
												}																		
											}				
										}
									}
								}
							}
							
							// part day book-offs now
							if(sv_count_($part_day_bookoffs) > 0){
								foreach($part_day_bookoffs as $part_day_bookoff){
									if(($part_day_bookoff->off_date == date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))))
										&& $part_day_bookoff->rolling_bookoff == 'No')
										||($part_day_bookoff->rolling_bookoff != 'No' 
										&& rb_day($part_day_bookoff->rolling_bookoff,  date("w",(DateAdd("d", $day, strtotime($grid_date))))))){
											$bookingstart_minute = getMinute($part_day_bookoff->bookoff_starttime);
											$bookingend_minute = getMinute($part_day_bookoff->bookoff_endtime);
											if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
												$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
												$width = ($bookingend_minute-$bookingstart_minute-2)*$pxminute;
												echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center;' >".JText::_($part_day_bookoff->description)."</div>"; 
											} else if($bookingend_minute > $window_end_minute){
												// goes beyond window
												if($slotstart_minute < $window_end_minute){
													// but starts in window
													$left = ($bookingstart_minute-$window_start_minute)*$pxminute;
													$width = ($window_end_minute - $bookingstart_minute - 4)*$pxminute;	
													echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:right'>>></div>"; 								
												} // else starts beyond window, do not show
											} else if($bookingstart_minute < $window_start_minute){	
												// starts before window
												$left = 0;
												// width = full width - amount before window									
												$width = (($bookingend_minute-$bookingstart_minute) - ($window_start_minute - $bookingstart_minute))*$pxminute;
												echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:left'><<</div>"; 
											} else {
												// bigger than grid, fill'er up
												$left = 0;
												// width = full width - amount before window									
												$width = (window_end_minute - $window_start_minute)*$pxminute;
												echo "<div class='sv_gad_timeslot_book-off'  style='left:".$left."px; width:".$width."px; position:absolute; float:left; text-align:center'><< >></div>"; 									
											}				
									}
								}						
							}	
				echo "</div>";
													
						} else if($sr == "bookoff"){ 							
							$bo = getBookOffDescription($res_detail, $strDate);
							$grid_only_width = intVal($gridwidth) - intVal($namewidth);
							if($bo != null && $bo->description !=""){
								if($res_detail->non_work_day_hide == "Yes"){
									// only show if row has $sr==true
									echo $y_axis_header;
								}
								echo "<div class='sv_gad_timeslot_book-off' style='width:".$grid_only_width ."px; text-align:center'>".JText::_(stripslashes($bo->description))."</div>";
							}
						} else if($sr == "dayoff"){ 							
							$grid_only_width = intVal($gridwidth) - intVal($namewidth);
							if($res_detail->non_work_day_message != "" && (strtotime($strDate) >= date('Y-m-d'))){
								if($res_detail->non_work_day_hide == "Yes"){
									// only show if row has $sr==true
									echo $y_axis_header;
								}
								echo "<div class='sv_gad_non_work_day' style='width:".$grid_only_width ."px; text-align:center'>".JText::_(stripslashes($res_detail->non_work_day_message))."</div>";
							}
							
						}
					
					}
				
				}
				
				echo "</div>"; // end master container 
				
				//*************************************************************
			  	// footer row start
				//*************************************************************
				if($mobile){
				  	echo "<tr><td colspan='".$columncount."'><div style='position: relative; width:".($gridwidth-$namewidth)."px; '>";
				} else {
				  	echo "<tr><td width='".$namewidth."'>&nbsp;</td><td colspan='".$columncount."' >".
					"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth-$namewidth)."px; '>";
				}
				for($i=0; $i<$columncount; $i++){
					if($mobile == "Yes"){
						$left = round(($i)*60*$pxminute);
						$width = round(60*$pxminute);
					} else {
						$left = ($i)*60*$pxminute;
						$width = 60*$pxminute;
					}
					$strTime = "";
					if($apptpro_config->timeFormat == "12"){
						$timedisplay = ($i+$startpoint);
						if($timedisplay == 12){
							$strTime = JText::_('RS1_INPUT_SCRN_NOON');
						} else if($timedisplay > 12){
							$strTime = strval($timedisplay - 12);
							$strTime .= " PM";
						} else {
							$strTime = strval($timedisplay);
							$strTime .= " AM";
						}
					} else {				
						$strTime = strval($i+$startpoint);
						if($mobile != "Yes"){
							$strTime .= ":00";
						}
					}
					echo "<div class='sv_gad_timeslot_header' style='left:".$left."px; width:".$width."px; position:absolute; float:left".($mobile=="Yes"?"; font-size:11px":"")."'>&nbsp;".$strTime."</div>"; 
				}
				echo "</td></tr>";  // end of footer row
				//*************************************************************
			  	// footer row end
				//*************************************************************

				//*************************************************************
			  	// legend
				//*************************************************************
				?>  
                <?php if($mobile){ ?>
                	<tr><td class='sv_gad_legend'><?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND');?><br/>
                    <span class='sv_gad_timeslot_available' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_AVAILABLE');?>
                    &nbsp;&nbsp;<br /><span class='sv_gad_timeslot_booked' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_BOOKED');?>
                    </td></tr>
                <?php } else { ?>
                	<tr><td><?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND');?></td>
                    <td class='sv_gad_legend' ><span class='sv_gad_timeslot_available' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_AVAILABLE');?>
                    &nbsp;&nbsp;<br /><span class='sv_gad_timeslot_booked' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_BOOKED');?>
                    </td></tr>
                <?php } ?>    
				</td></tr>				
            </table>
            <input type="hidden" name="grid_previous" id="grid_previous" value="<?php echo $grid_previous ?>">
            <input type="hidden" name="grid_next" id="grid_next" value="<?php echo $grid_next ?>">
            <input type="hidden" name="pxm" id="pxm" value="<?php echo $pxminute ?>">
            <?php 
				$tzo = new DateTimeZone($timezone_identifier);
				$int_tz_offset = timezone_offset_get ( $tzo , new DateTime("now", $tzo));
			?>	
            <input type="hidden" id="tzo" value="<?php echo $int_tz_offset/60;?>" /> 
