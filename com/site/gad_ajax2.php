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
	$category = $jinput->getint('category');
	$mode = $jinput->getString('mode');
	$grid_date = $jinput->getString('grid_date');
	$gridwidth = $jinput->getString('gridwidth');
	$namewidth = $jinput->getString('namewidth');
	$mobile = $jinput->getWord('mobile', 'No');
	if($mobile == "Yes"){
		$gridwidth = $gridwidth+$namewidth;
	}

	$browser = $jinput->getString('browser');
	$reg = $jinput->getWord('reg', 'No');
	$front_desk = $jinput->getWord('fd', 'No');
	
	$resource = $jinput->getint('resource');
	$grid_days = $jinput->getString('grid_days');
	$gap = $jinput->getInt('gap', 0);
	$column_count = 0;
	$max_seats = 1;
	$adjusted_max_seats = 0;

	$slottop = null;
	$slotheight = null;
	$slotleft = null;
	$show_time = false; // set to true if you want to have the slot display its start/end times

	$service_resource_ids = $jinput->getString('srv_res_ids', "");
	$who_booked = "";
	
	require_once( JPATH_CONFIGURATION.DIRECTORY_SEPARATOR.'configuration.php' );
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
	if($lang->getTag() == "el-GR"){
		setlocale(LC_TIME, array('el_GR.UTF-8','el_GR','greek'));
	}

	$database =JFactory::getDBO(); 
	$sql = "SET NAMES 'utf8';";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_ajax2", "", "");
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

	
	$database =JFactory::getDBO(); 
	
	if($mode == "single_day"){
		$grid_previous = date("Y-m-d", strtotime("- 1 day", strtotime($grid_date)));
		$grid_next = date("Y-m-d", strtotime("+1 day", strtotime($grid_date)));
	} else {
		$grid_previous = date("Y-m-d", strtotime("-".$grid_days." day", strtotime($grid_date)));
		$grid_next = date("Y-m-d", strtotime("+".$grid_days." day", strtotime($grid_date)));
	}

	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_ajax2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
	
	// how many colums 700px - 100 (res name) = 600/num hours btween grid start and end
	$startpoint = intval(substr($gridstarttime,0,2)); 
	$endpoint = intval(substr($gridendtime,0,2)); 

	$rowcount = $endpoint - $startpoint;
	if($rowcount <1){echo JText::_('RS1_GAD_SCRN_GRID_START_BEFORE_END'); exit;}
	$rowheight = $apptpro_config->gad2_row_height;
	//$rowheight_header = $rowheight;
	// To keep the header row from growing when you set larger row height, it is hard coded here.
	// If you require more header space for wrapping names, you can increase the value below.
	$rowheight_header = 50;

	if($apptpro_config->enable_ddslick == "Yes" && $mobile != "Yes" && $mode == "single_day"){
		$rowheight_header += 64;
	}
	// if you are using timeslots less that 60 min long, the row hight may need to be increased for mobile bookings.
	// Uncomment code below and set to produce a slot height that is suitable for your timeslot sizes (40 is the default)
//	if($mobile == "Yes"){
//		// here we are making it 60 px
//		$rowheight = 60;
//	}

	$window_start_minute = $startpoint * 60;
	$window_end_minute = $endpoint * 60;
	// We need to position each timeslot withing the table row. To do this we need to divide the tabel row into px/minutes
	// Once we know how many px/min, we can place timeslots the righ number of px from the left.
//	$pxminute = ($gridheight-$nameheight)/($window_end_minute - $window_start_minute);
	$pxminute = ($rowheight*$rowcount)/($window_end_minute - $window_start_minute);
	
	if($mode == "single_day"){
		if(WINDOWS){
			$display_grid_date = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format, strtotime($grid_date)));
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
    <div id='cell_container' style='position:relative;  width:<?php echo $gridwidth ?>px; height:<?php echo (($rowheight_header*2)+($rowheight*$rowcount)) ?>px;'>
	<table id="sv_gad_outer_table" width="100%" border="0" cellpadding="0" cellspacing="0">
              <?php 
			  	//echo "<div class='sv_gad_master_container' style='position: relative; width:".$gridwidth."px;'>";

				//resource rows
				// **********************************************************
				// if mode = single_day we show all resources, for $grid_date
				// **********************************************************
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
				if($service_resource_ids != "" ){
					$safe_search_string = $database->escape( $service_resource_ids, true );
					$andClause .= " AND id_resources IN(".$safe_search_string.")";
				}
				
				//*************************************************************
				//  single_day 			
				//*************************************************************

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
						$res_rows_raw = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
					$res_rows_count = 0;
					$res_rows = null;
					for($i=0; $i < sv_count_($res_rows_raw ); $i++) {
						if(display_this_resource($res_rows_raw[$i], $user)){
							$res_rows[$res_rows_count] = $res_rows_raw[$i];
							$res_rows_count ++;
						}
					}
					$column_count = $i;
	
					//*************************************************************
					// draw table 			
					//*************************************************************
	echo "<div id='timeslot_resize_container'>";					

					// top row is res names
					echo "<tr class='gad_header_row' height='".$rowheight_header."px'><td width='".$namewidth."' align='center' ><b><span id='display_grid_date' ><label class=\"sv_apptpro_request_label\" style=\"text-align:center\">".$display_grid_date."</label></span></b></td>\n";
					if(sv_count_($res_rows) >0){
						$cell_width = round(($gridwidth-$namewidth)/sv_count_($res_rows));
					} else {
						$cell_width = round(($gridwidth-$namewidth));
					}
					foreach($res_rows as $res_row){
						echo "<td width='".$cell_width."px' style='border-bottom:solid 1px' align='center'><label class=\"sv_apptpro_request_label\" style=\"text-align:center\">".JText::_($res_row->name)."</label>";
						if($apptpro_config->enable_ddslick == "Yes" && $res_row->ddslick_image_path !="" && $res_row->show_image_in_grid == "Yes"){
							echo "<br/><a href=javascript:changeMode(".$res_row->id_resources.")><img src=\"".getResourceImageURL($res_row->ddslick_image_path)."\" style='max-height: 64px;'></a>";
						}
						echo "</td>\n";
					}
					echo "</tr>\n ";
					// rowcount is actually row count or number of hours to show
					$cell_width -=4; // remove 4 px for border and padding.
					for($i=0; $i<$rowcount; $i++){
	//					$rowtop = ($i)*60*$pxminute;
	//					$rowheight = 60*$pxminute;
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
							//if($mobile != "Yes"){
								$strTime .= ":00";
							//}
						}
						//echo "<tr class='gad2_grid' valign='top' height='".$rowheight."px'><td align='center' style='border-top:solid 1px' class='gad2_row'>".$strTime."</td><td class='gad2_row' colspan='".sv_count_($res_rows)."'></td></tr>\n"; 
						echo "<tr class='gad2_grid' valign='top' height='".$rowheight."px' ><td colspan='".(sv_count_($res_rows)+1)."' class='gad2_row'><label class=\"sv_apptpro_request_label\">".$strTime."</label></td></tr>\n"; 
					}
					echo "</td></tr>"; // end of table draw
	echo "</div>";
	
					//*************************************************************
					// draw end			
					//*************************************************************
						
					// get get bookings
					$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE startdate = '".$grid_date."'";
					$sql .=" AND (request_status='accepted' OR request_status='pending'".($apptpro_config->block_new=="Yes"?" OR request_status='new'":"").") ORDER BY starttime";
					try{
						$database->setQuery($sql);
						$bookings = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// get get part day book-offs
					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE ".
						"((off_date = '".$grid_date."' AND full_day='No') ".
						" OR ".
						"(rolling_bookoff != 'No'))". 					
						" AND published=1 ORDER BY rolling_bookoff, bookoff_starttime";
//					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE off_date = '".$grid_date."'";
//					$sql .=" AND full_day='No' AND published=1 ORDER BY bookoff_starttime";
					try{
						$database->setQuery($sql);
						$part_day_bookoffs = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// walk through resources, getting timeslots and bookings
					$column_index = -1;
					if(sv_count_($res_rows) > 0){
						foreach($res_rows as $res_row){
							$max_seats = $res_row->max_seats;
							$column_index++;
						
						$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$res_row->id_resources.' ORDER BY ordering';
						try{
							$database->setQuery($sql);
							$res_detail = NULL;
							$res_detail = $database -> loadObject();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "gad_ajax2", "", "");
							echo JText::_('RS1_SQL_ERROR');
							exit;
						}		

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
							logIt($e->getMessage(), "gad_ajax2", "", "");
							echo JText::_('RS1_SQL_ERROR');
							exit;
						}		
						
						date_default_timezone_set($timezone_identifier);
						$sr = showrow($res_row, $grid_date, $weekday["wday"]);
						//showrow return values: past, bookoff, dayoff, disabled, yes
						// to not display book-offs on front-desk booking screen (ie let staff override them)
						// uncomment next 3 lines.
						// If the next lines are commented out, bookings in the past are possible.
						if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
							if($sr != "bookoff"){
								$sr = "yes";
							}
						} 	
						if($sr == "yes"){ 						
							// Timeslots first
							foreach($slot_rows as $slot_row){
								if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
									$time_adjusted_for_lead = time() - ($apptpro_config->staff_booking_in_the_past * 86400); 
								} else {
									$time_adjusted_for_lead = time() + ($res_row->min_lead_time * 60 * 60);
								}
								if(strtotime($grid_date." ".$slot_row->timeslot_starttime) > $time_adjusted_for_lead){ // hide slots where time has passed

									if($apptpro_config->show_available_seats == "Yes" && $res_row->max_seats>1){
										$currentcount = getCurrentSeatCount($grid_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_row->id_resources);
										$adjusted_max_seats = getSeatAdjustments($grid_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_row->id_resources, $res_row->max_seats);
										if($currentcount > 0){
											$timeslot_insert = "<span class='sv_apptpro_low_seats'>".strval($res_row->max_seats + $adjusted_max_seats - $currentcount)."</span></a>";
										} else {
											$timeslot_insert = strval($res_row->max_seats + $adjusted_max_seats - $currentcount)."</a>";
										}
									} else {
										if($slot_row->timeslot_description != ""){
											$timeslot_insert = JText::_($slot_row->timeslot_description)."</a>";
										} elseif ($show_time){
											$timeslot_insert = $slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."</a>";
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
									//$timeslot_tooltip = " title='".$grid_date."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
									// or if you want a different date format..
									//$timeslot_tooltip = " title='".date("d-m-Y",strtotime($grid_date))."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
									// This one shows time only
									//$timeslot_tooltip = " title='".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
									
									//--------------------------------------------------------------------
									// no shows
									if($slotstart_minute >= $window_end_minute || $slotend_minute <= $window_start_minute){
										// outside of window do not show
										
									//--------------------------------------------------------------------
									} else if($slotstart_minute >= $window_start_minute && $slotend_minute <= $window_end_minute){
										// starts and ends inside window
										$slottop = (($slotstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
										$slotheight = ($slotend_minute-$slotstart_minute)*$pxminute-3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=# onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
//										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($image_padding*2)."px;' href=# onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
									
										$res_row->id_resources."|".
										sv_encode(JText::_($res_row->name))."|".
										$grid_date."|";
										if(WINDOWS){
											echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format, strtotime($grid_date))))."|";
											//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
											//echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format)))."|";
										} else {
											echo sv_encode(strftime($apptpro_config->gad_date_format, strtotime($grid_date)))."|";
											//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
											//echo sv_encode($jdayname->toFormat($apptpro_config->gad_date_format))."|";
										}

										echo $slot_row->timeslot_starttime."|".
										sv_encode($slot_row->display_timeslot_starttime)."|".
										$slot_row->timeslot_endtime."|".
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_row->gap."|".$res_row->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
										
									//--------------------------------------------------------------------
									} else if($slotend_minute > $window_end_minute && $slotstart_minute >= $window_start_minute){
										// start inside but goes beyond window
										$slottop = (($slotstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
										$slotheight = ($window_end_minute-$slotstart_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;										
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
										$res_row->id_resources."|".
										sv_encode(JText::_($res_row->name))."|".
										$grid_date."|";
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
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_row->gap."|".$res_row->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
										
									//--------------------------------------------------------------------
									} else if($slotstart_minute < $window_start_minute && $slotend_minute <= $window_end_minute){	
										// starts before window but ends inside										
										$slottop = $rowheight_header-1;
										$slotheight = ($slotend_minute-$window_start_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;										
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
										$res_row->id_resources."|".
										sv_encode(JText::_($res_row->name))."|".
										$grid_date."|";
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
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_row->gap."|".$res_row->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
										
									//--------------------------------------------------------------------
									} else {										
										// bigger than grid, fill'er up
										$slottop = $rowheight_header-1;
										$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
										$res_row->id_resources."|".
										sv_encode(JText::_($res_row->name))."|".
										$grid_date."|";
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
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_row->gap."|".$res_row->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
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

												//--------------------------------------------------------------------
												// no shows
												if($bookingstart_minute >= $window_end_minute || $bookingend_minute <= $window_start_minute){
													// outside of window do not show
												
												//--------------------------------------------------------------------
												} else if($bookingstart_minute >= $window_start_minute && $bookingend_minute <= $window_end_minute){
													// starts and ends inside window
													
													$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
													$slotheight = ($bookingend_minute-$bookingstart_minute)*$pxminute -3;
													$slotleft = (($cell_width+3)*$column_index) + $namewidth;
													$image_padding = (int)(intval($slotheight) - 20)/2;									
													$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' style='padding-top:".$image_padding."px'/>";
													
													if($booking->request_status == 'accepted'){
														echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
														echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked." style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
														echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
													} else {
														echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													}											
													$res_spec_gap = $gap;  //$gap = component level
													if($res_row->gap != 0){ $res_spec_gap = $res_row->gap;} 
													if($res_spec_gap > 0 && $max_seats == 1){
														// add gap	
														if($bookingend_minute <$window_end_minute){								
															$gap_top = $slottop + $slotheight-1;
															$gap_height = ($res_spec_gap-1)*$pxminute;
															echo "<div class='sv_gad_timeslot_gap' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$gap_top."px; height:".$gap_height."px; position:absolute; text-align:center;' >  </div>"; 
														}
													}
												//--------------------------------------------------------------------
												} else if($bookingend_minute > $window_end_minute && $bookingstart_minute >= $window_start_minute){													
													// starts inside but goes beyond window
													
													$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
													$slotheight = ($window_end_minute-$bookingstart_minute)*$pxminute -3;
													$image_padding = (int)(intval($slotheight) - 20)/2;									
													$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' style='padding-top:".$image_padding."px'/>";
													
													if($booking->request_status == 'accepted'){
														echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
														echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
														echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
													} else {
														echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													}
													
												//--------------------------------------------------------------------
												} else if($bookingstart_minute < $window_start_minute && $bookingend_minute <= $window_end_minute){	
													// starts before window but ends inside
												
													$slottop = $rowheight_header-1;
													$slotheight = ($bookingend_minute-$window_start_minute)*$pxminute -3;
													$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
													$image_padding = (int)(intval($slotheight) - 20)/2;									
													$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' style='padding-top:".$image_padding."px'/>";
													
													if($booking->request_status == 'accepted'){
														echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
														echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
														echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
													} else {
														echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													}
													$res_spec_gap = $gap;  //$gap = component level
													if($res_row->gap != 0){ $res_spec_gap = $res_row->gap;} 
													if($res_spec_gap > 0 && $max_seats == 1){
														// add gap	
														if($bookingend_minute <$window_end_minute){								
															$gap_top = $slottop + $slotheight-1;
															$gap_height = ($res_spec_gap-1)*$pxminute;
															echo "<div class='sv_gad_timeslot_gap' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$gap_top."px; height:".$gap_height."px; position:absolute; text-align:center;' >  </div>"; 
														}
													}
													
												//--------------------------------------------------------------------
												} else {
													// bigger than grid, fill'er up

													$slottop = $rowheight_header-1;
													$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
													$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
													$image_padding = (int)(intval($slotheight) - 20)/2;									
													$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' style='padding-top:".$image_padding."px'/>";
													
													if($booking->request_status == 'accepted'){
														echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
														echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
														echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
													} else {
														echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
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
	
												//--------------------------------------------------------------------
												// no shows
												if($bookingstart_minute >= $window_end_minute || $bookingend_minute <= $window_start_minute){
													// outside of window do not show
												
												//--------------------------------------------------------------------
												} else if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
													// starts and ends inside window
													
													$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
													$slotheight = ($bookingend_minute-$bookingstart_minute)*$pxminute -3;
													echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
													
												//--------------------------------------------------------------------
												} else if($bookingend_minute > $window_end_minute && $bookingstart_minute >= $window_start_minute){
													// starts inside but goes beyond window
													
													$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
													$slotheight = ($window_end_minute-$bookingstart_minute)*$pxminute -3;
													echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
	
												//--------------------------------------------------------------------
												} else if($bookingstart_minute < $window_start_minute && $bookingend_minute <= $window_end_minute){	
													// starts before window but ends inside
													
													$slottop = $rowheight_header-1;
													$slotheight = ($bookingend_minute-$window_start_minute)*$pxminute -3;
													$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
													echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
												
												//--------------------------------------------------------------------
												} else {
													// bigger than grid, fill'er up
													
													$slottop = $rowheight_header-1;
													$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
													$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
													echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
												}				
											}
											}
										}								
									}															

						} else if($sr == "bookoff"){ 							
							$bo = getBookOffDescription($res_row, $grid_date);
							if($bo->description !=""){
								$slottop = $rowheight_header-1;
								$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -1;
								$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
								echo "<div class='sv_gad_timeslot_book-off_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute;  text-align:center'>".JText::_(stripslashes($bo->description))."</div>";
							} 
						} else if($sr == "dayoff"){ 							
							$slottop = $rowheight_header-1;
							$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
							$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
							if($res_row->non_work_day_message != "" && (strtotime($grid_date) >= date('Y-m-d'))){
								echo "<div class='sv_gad_non_work_day' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center'>".JText::_(stripslashes($res_row->non_work_day_message))."</div>";
							}
							
						}
							echo "</div></td></tr>";
						}
					}				

				} else {
			// **********************************************************
			// single_resource 
			// **********************************************************
					// get resource details
					$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources=".$resource;
					try{
						$database->setQuery($sql);
						$res_detail = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
				$column_count = $grid_days;
				$max_seats = $res_detail->max_seats;
				//*************************************************************
			  	// draw table 			
				//*************************************************************

				// top row is dates
				echo "<tr class='gad_header_row' height='".$rowheight_header."px' ><td width='".$namewidth."'>&nbsp;</td>\n";
				$cell_width = round(($gridwidth-$namewidth)/$grid_days);
				for($day=0; $day<$grid_days; $day++){	
					if(WINDOWS){
						$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE', strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date)))));
						//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
						//date_default_timezone_set($timezone_identifier); 
						//$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format));
						//date_default_timezone_set($timezone_identifier); 
					} else {
						$dayname = strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date))));
						//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
						//date_default_timezone_set($timezone_identifier); 
						//$dayname = $jdayname->toFormat($apptpro_config->gad_date_format);
						//date_default_timezone_set($timezone_identifier); 
					}
					// Problem with some sites located in UTC+1 (GMT+1) timezone, JDate retruns different date than php (date).
					// Un-comment the $dayname line below to override JDate and use php for both in the single-resource-multi-day view.
					// Note: the JDate's toFormat() uses a different type of format string than does php date(), so the format string stored in your ABPro config will be wrong. I have set the format in the date call to 'D d-M-Y'.
	      			//$dayname = date('D d-M-Y',(DateAdd("d", $day, strtotime($grid_date))));
					
					echo "<td width='".$cell_width."px' style='text-align:center; border-bottom:solid 1px;'>".$dayname."</td>\n";
				}
				echo "</tr>\n ";
		$cell_width -=4; // remove 4 px, borders and padding.
				// rowcount is actually row count or number of hours to show
					for($i=0; $i<$rowcount; $i++){
	//					$rowtop = ($i)*60*$pxminute;
	//					$rowheight = 60*$pxminute;
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
							//if($mobile != "Yes"){
								$strTime .= ":00";
							//}
						}
				echo "<tr class='gad2_grid' valign='top' height='".$rowheight."px' ><td colspan='".($grid_days+1)."' class='gad2_row'>".$strTime."</td></tr>\n"; 
				}
				echo "</td></tr>"; // end of table draw

				//*************************************************************
			  	// draw end			
				//*************************************************************

				// get get bookings
					$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE resource=".$resource.
					" AND (request_status='accepted' OR request_status='pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") AND startdate >= '".$grid_date."' ".
					" AND startdate <= DATE_ADD(startdate,INTERVAL ".$grid_days." DAY) ".
					" ORDER BY startdate";
					try{
						$database->setQuery($sql);
						$bookings = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		

					// get get part day book-offs
					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE resource_id=".$resource. 
						" AND ((off_date >= '".$grid_date."' AND off_date <= DATE_ADD('".$grid_date." 23:59:59',INTERVAL ".($grid_days-1)." DAY))".
						" OR (rolling_bookoff != 'No'))". 									
						" AND full_day='No' AND published=1 ORDER BY off_date";
//					$sql = "SELECT * FROM #__sv_apptpro3_bookoffs WHERE resource_id=".$resource. " AND off_date >= '".$grid_date."'";
//					$sql .=" AND off_date <= DATE_ADD(off_date,INTERVAL ".$grid_days." DAY) ";
//					$sql .=" AND full_day='No' AND published=1 ORDER BY off_date";
					try{
						$database->setQuery($sql);
						$part_day_bookoffs = $database -> loadObjectList();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "gad_ajax2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						return false;
					}		
					
					// walk through days, getting timeslots and bookings
					$column_index = -1;
					for($day=0; $day<$grid_days; $day++){	
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

						$column_index++;

						if(WINDOWS){
							$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date)))));
							//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
		           			//date_default_timezone_set($timezone_identifier); 
							//$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format));
		           			//date_default_timezone_set($timezone_identifier); 
						} else {
							$dayname = strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date))));
							//$jdayname = new JDate( DateAdd("d", $day, strtotime($grid_date.$jdate_fix)));
		           			//date_default_timezone_set($timezone_identifier); 
							//$dayname = $jdayname->toFormat($apptpro_config->gad_date_format);
		           			//date_default_timezone_set($timezone_identifier); 
						}
						$weekday = date("w",(DateAdd("d", $day, strtotime($grid_date))));
						$strDate = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));

//					$y_axis_header = "<tr><td align='center' class='sv_gad_timeslot_yaxis_header'><a href=javascript:changeMode2('".$strDate."')> ".JText::_($dayname)."</a></td><td colspan='".$rowcount."'>".
//						"<div class='sv_gad_row_wrapper' style='position: relative; width:".($gridwidth-$namewidth)."px; '>";

					if($res_detail->non_work_day_hide == "No"){
						// always show the row
						//echo $y_axis_header;						
					}
					
					date_default_timezone_set($timezone_identifier);					
					$sr = showrow($res_detail, $strDate, $weekday);
					//showrow return values: past, bookoff, dayoff, disabled, yes
					// to not display book-offs on front-desk booking screen (ie let staff override them)
					// uncomment next 3 lines.
					// If the next lines are commented out, bookings in the past are possible.
					if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
						if($sr != "bookoff"){
							$sr = "yes";
						}
					} 	
					if($sr == "yes"){ 
						if($res_detail->non_work_day_hide == "Yes"){
							// only show if row has $sr==true
							//echo $y_axis_header;
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
								logIt($e->getMessage(), "gad_ajax2", "", "");
								echo JText::_('RS1_SQL_ERROR');
								exit;
							}		

							date_default_timezone_set($timezone_identifier);
							$row_date = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));						
							
							// Timeslots first
							foreach($slot_rows as $slot_row){
								if($front_desk == "Yes" && $apptpro_config->staff_booking_in_the_past > 0){
									$time_adjusted_for_lead = time() - ($apptpro_config->staff_booking_in_the_past * 86400); 
								} else {
									$time_adjusted_for_lead = time() + ($res_detail->min_lead_time * 60 * 60);							
								}
								if(strtotime($row_date." ".$slot_row->timeslot_starttime) > $time_adjusted_for_lead){
									$slotwidth = $gridwidth - $namewidth - 15;
									$slotleft = $namewidth+30;
									if($apptpro_config->show_available_seats == "Yes" && $res_detail->max_seats>1){
										$row_date = date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))));
										$currentcount = getCurrentSeatCount($row_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_detail->id_resources);
										$adjusted_max_seats = getSeatAdjustments($row_date, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_detail->id_resources, $res_detail->max_seats);
										if($currentcount > 0){
											$timeslot_insert = "<span class='sv_apptpro_low_seats'>".strval($res_detail->max_seats + $adjusted_max_seats - $currentcount)."</span></a>";
										} else {
												$timeslot_insert = strval($res_detail->max_seats + $adjusted_max_seats - $currentcount)."</a>";
										}
										$image_padding = "0px";
									} else {
										if($slot_row->timeslot_description != ""){
											$timeslot_insert = JText::_($slot_row->timeslot_description)."</a>";
												$image_padding = "0px";
										} elseif ($show_time){
											$timeslot_insert = $slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."</a>";
										} else {
											$image_padding = (intval($rowheight) - 20)/2;
											$timeslot_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'/></a>";
										}
									}
									$ts_id ++;
									// get start minute, end minute								
									$slotstart_minute = getMinute($slot_row->timeslot_starttime);
									$slotend_minute = getMinute($slot_row->timeslot_endtime);
									
									$timeslot_tooltip = "";
									// If you want a tooltip on the timeslots you can uncomment one of the $timeslot_tooltip lines below. 
									// You will also need to comment out further down for the single resource multi day view around line 662
									// This is not compatible with the 'Who Booked in Tooltip' for Max Seats > 1
									// This one shows date and time 
									//$timeslot_tooltip = " title='".$strDate."&#10;".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
									// This one shows time only
									//$timeslot_tooltip = " title='".$slot_row->display_timeslot_starttime."-".$slot_row->display_timeslot_endtime."' ";
	
									//--------------------------------------------------------------------
									// no shows
									if($slotstart_minute >= $window_end_minute || $slotend_minute <= $window_start_minute){
										// outside of window do not show
											
									//--------------------------------------------------------------------
									} else if($slotstart_minute >= $window_start_minute && $slotend_minute <=$window_end_minute){
										// starts and ends inside window
										
										$slottop = (($slotstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
										$slotheight = ($slotend_minute-$slotstart_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth;
										$image_padding = (int)(intval($slotheight) - 20)/2;
										
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick=\"selectTimeslot('".
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
										"</div>"; 
	
									//--------------------------------------------------------------------
									} else if($slotend_minute > $window_end_minute && $slotstart_minute >= $window_start_minute){
										// start inside but goes beyond window
	
										$slottop = (($slotstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
										$slotheight = ($window_end_minute-$slotstart_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;
										
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
										$res_detail->id_resources."|".
										sv_encode(JText::_($res_detail->name))."|".
										$strDate."|";
										if(WINDOWS){
											echo sv_encode(iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date))))))."|";
											//$jdayname = new JDate(DateAdd("d", $day, strtotime($grid_date)));
											//$dayname = iconv(getIconvCharset(), 'UTF-8//IGNORE',$jdayname->toFormat($apptpro_config->gad_date_format));
										} else {
											echo sv_encode(strftime($apptpro_config->gad_date_format,(DateAdd("d", $day, strtotime($grid_date)))))."|";
										}
	
										echo $slot_row->timeslot_starttime."|".
										sv_encode($slot_row->display_timeslot_starttime)."|".
										$slot_row->timeslot_endtime."|".
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_detail->gap."|".$res_detail->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
	
									//--------------------------------------------------------------------
									} else if($slotstart_minute < $window_start_minute && $slotend_minute <= $window_end_minute){	
										// starts before window but ends inside
	
										$slottop = $rowheight_header-1;//(($slotstart_minute-$window_start_minute)*$pxminute)+$rowheight;
										$slotheight = ($slotend_minute-$window_start_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
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
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_detail->gap."|".$res_detail->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
										
									//--------------------------------------------------------------------
									} else {
										// bigger than grid, fill'er up
										$slottop = $rowheight_header-1;
										$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
										$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
										$image_padding = (int)(intval($slotheight) - 20)/2;									
										echo "\n<div id='ts".$ts_id."' ".$timeslot_tooltip." class='sv_gad_timeslot_available_timeony' style='width:".$cell_width."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;'>".
										"<a class='sv_gad_timeslot_clickable".($mobile==true?"_mobile":"")."' style='line-height:".($slotheight)."px;' href=#  onmouseover='checkWhoBooked(\"ts".$ts_id."\");return true;' onclick='selectTimeslot(\"".
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
										sv_encode($slot_row->display_timeslot_endtime)."|ts".$ts_id."|".$res_detail->gap."|".$res_detail->free_booking."\",event);return false;'>".
										$timeslot_insert.
										"</div>"; 
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
										
											//--------------------------------------------------------------------
											// no shows
											if($bookingstart_minute >= $window_end_minute || $bookingend_minute <= $window_start_minute){
												// outside of window do not show
												
											//--------------------------------------------------------------------
											} else if($bookingstart_minute >= $window_start_minute && $bookingend_minute <= $window_end_minute){
												// starts and ends inside window

												$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
												$slotheight = ($bookingend_minute-$bookingstart_minute)*$pxminute -3;
												$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
												$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
												$image_padding = (int)(intval($slotheight) - 20)/2;									
												$booked_insert = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' style='padding-top:".$image_padding."px'/>";

												if($booking->request_status == 'accepted'){
													echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
													echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked." style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
												} else {
													echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
												}																		
												$res_spec_gap = $gap;  //$gap = component level
												if($res_detail->gap != 0){ $res_spec_gap = $res_detail->gap;} 
												if($res_spec_gap > 0 && $max_seats == 1){
													// add gap	
													if($bookingend_minute <$window_end_minute){								
														$gap_top = $slottop + $slotheight-1;
														$gap_height = ($res_spec_gap-1)*$pxminute;
														echo "<div class='sv_gad_timeslot_gap' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$gap_top."px; height:".$gap_height."px; position:absolute; text-align:center;' >  </div>"; 
													}
												}

											//--------------------------------------------------------------------
											} else if($bookingend_minute > $window_end_minute && $bookingstart_minute >= $window_start_minute){													
												// starts inside but goes beyond window
												
												$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
												$slotheight = ($window_end_minute-$bookingstart_minute)*$pxminute -3;
												$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
												if($booking->request_status == 'accepted'){
													echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
													echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
												} else {
													echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
												}

											//--------------------------------------------------------------------
											} else if($bookingstart_minute < $window_start_minute && $bookingend_minute <= $window_end_minute){	
												// starts before window but ends inside
											
												$slottop = $rowheight_header-1;
												$slotheight = ($bookingend_minute-$window_start_minute)*$pxminute -3;
												$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
												if($booking->request_status == 'accepted'){
													echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
													echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
												} else {
													echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
												}
												$res_spec_gap = $gap;  //$gap = component level
												if($res_detail->gap != 0){ $res_spec_gap = $res_detail->gap;} 
												if($res_spec_gap > 0 && $max_seats == 1){
													// add gap	
													if($bookingend_minute <$window_end_minute){								
														$gap_top = $slottop + $slotheight-1;
														$gap_height = ($res_spec_gap-1)*$pxminute;
														echo "<div class='sv_gad_timeslot_gap' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$gap_top."px; height:".$gap_height."px; position:absolute; text-align:center;' >  </div>"; 
													}
												}
												
											//--------------------------------------------------------------------
											} else {
												// bigger than grid, fill'er up

												$slottop = $rowheight_header-1;
												$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
												$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
												if($booking->request_status == 'accepted'){
													echo ($apptpro_config->enable_notification_list == "Yes"?"<a href=# onclick=\"addtoNotificationList('".$booking->resource."','".$booking->startdate."','".$booking->starttime."');return false;\">":"");
													echo "<div class='sv_gad_timeslot_booked_timeony' ".$who_booked."  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
													echo ($apptpro_config->enable_notification_list == "Yes"?"</a>":"");
												} else {
													echo "<div class='sv_gad_timeslot_pending_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; text-align:center;' >".$booked_insert."</div>"; 
												}
											}				
										}
									}
								}
							}
							
							// part day book-offs now
							if(sv_count_($part_day_bookoffs) > 0){
								foreach($part_day_bookoffs as $part_day_bookoff){
//									if($part_day_bookoff->off_date == date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))))){
									if(($part_day_bookoff->off_date == date("Y-m-d",(DateAdd("d", $day, strtotime($grid_date))))
									   && $part_day_bookoff->rolling_bookoff == 'No')
									   ||($part_day_bookoff->rolling_bookoff != 'No' 
										&& rb_day($part_day_bookoff->rolling_bookoff,  date("w",(DateAdd("d", $day, strtotime($grid_date))))))){
										$bookingstart_minute = getMinute($part_day_bookoff->bookoff_starttime);
										$bookingend_minute = getMinute($part_day_bookoff->bookoff_endtime);
										
										//--------------------------------------------------------------------
										// no shows
										if($bookingstart_minute >= $window_end_minute || $bookingend_minute <= $window_start_minute){
											// outside of window do not show

										//--------------------------------------------------------------------
										} else if($bookingstart_minute >= $window_start_minute && $bookingend_minute <=$window_end_minute){
											// starts and ends inside window
											
											$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
											$slotheight = ($bookingend_minute-$bookingstart_minute)*$pxminute -3;
											$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
											echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; line-height:".($slotheight)."px; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 												

										//--------------------------------------------------------------------
										} else if($bookingend_minute > $window_end_minute && $bookingstart_minute >= $window_start_minute){
											// starts inside but goes beyond window
											
											$slottop = (($bookingstart_minute-$window_start_minute)*$pxminute)+$rowheight_header-5;
											$slotheight = ($window_end_minute-$bookingstart_minute)*$pxminute -3;
											$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
											echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; line-height:".($slotheight)."px; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 

										//--------------------------------------------------------------------
										} else if($bookingstart_minute < $window_start_minute && $bookingend_minute <= $window_end_minute){	
											// starts before window but ends inside
											
											$slottop = $rowheight_header-1;
											$slotheight = ($bookingend_minute-$window_start_minute)*$pxminute -3;
											$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
											echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; line-height:".($slotheight)."px; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
										
										//--------------------------------------------------------------------
										} else {
											// bigger than grid, fill'er up
											
											$slottop = $rowheight_header-1;
											$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
											$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
											echo "<div class='sv_gad_timeslot_book-off_timeony'  style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; position:absolute; line-height:".($slotheight)."px; text-align:center;' >".JText::_(stripslashes($part_day_bookoff->description))."</div>"; 
										}				
									}
								}						
							}							
						} else if($sr == "bookoff"){ 							
							$bo = getBookOffDescription($res_detail, $strDate);
							if($bo->description !=""){
								if($res_detail->non_work_day_hide == "Yes"){
									// only show if row has $sr==true
//									echo $y_axis_header;
								}
								$slottop = $rowheight_header-1;
								$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
								$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
								echo "<div class='sv_gad_timeslot_book-off_timeony' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center'>".JText::_(stripslashes($bo->description))."</div>";
							}
						} else if($sr == "dayoff"){ 							
							if($res_detail->non_work_day_message != "" && (strtotime($strDate) >= date('Y-m-d'))){
								if($res_detail->non_work_day_hide == "Yes"){
									// only show if row has $sr==true
									//echo $y_axis_header;
								}
								$slottop = $rowheight_header-1;
								$slotheight = ($window_end_minute - $window_start_minute)*$pxminute -3;
								$slotleft = (($cell_width+3)*$column_index) + $namewidth; 
								echo "<div class='sv_gad_non_work_day' style='width:".($cell_width)."px; left:".$slotleft."px; top:".$slottop."px; height:".$slotheight."px; line-height:".($slotheight)."px; position:absolute; text-align:center'>".JText::_(stripslashes($res_detail->non_work_day_message))."</div>";
							}
							
						}
					
					}
				
				}
				
				//echo "</div>"; // end master container 
				
				//*************************************************************
			  	// legend
				//*************************************************************
				?>  
                <?php if($mobile){ ?>               
                	<tr class='gad2_legend' height='<?php echo $rowheight_header."px"?>'>
                    	<td colspan=<?php echo $column_count + 1?>><span class='sv_gad_timeslot_available_timeony' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_AVAILABLE');?>
                        <br /><span class='sv_gad_timeslot_booked_timeony' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_BOOKED');?>
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr class='gad2_legend' height='<?php echo $rowheight_header."px"?>'>
                    	<td ><?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND');?></td>
                        <td colspan=<?php echo $rowcount?>><span class='sv_gad_timeslot_available_timeony' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_AVAILABLE');?>
                        	&nbsp;&nbsp;<br /><span class='sv_gad_timeslot_booked_timeony' >&nbsp;&nbsp;&nbsp;</span>&nbsp;-&nbsp;<?php echo JText::_('RS1_GAD_SCRN_GRID_LEGEND_BOOKED');?>
                        </td>
                    </tr>
                <?php } ?>    
                </table>
            <input type="hidden" name="grid_previous" id="grid_previous" value="<?php echo $grid_previous ?>">
            <input type="hidden" name="grid_next" id="grid_next" value="<?php echo $grid_next ?>">
            <input type="hidden" name="pxm2" id="pxm2" value="<?php echo $pxminute ?>">
    </div>       

