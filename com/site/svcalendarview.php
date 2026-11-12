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
include_once( JPATH_SITE."/components/com_rsappt_pro3/getSlots2.php" );


class SVCalendarView
{

	var $mobile = false;

	function __construct()
	{
		$appWeb      = JFactory::getApplication();
		if($appWeb->client->mobile){
			$this->mobile = true;
		};				
	}
	
	// some of the following parameters are for the front desk and not used 
	var $Itemid = null;
	var $resAdmin = "";
	var $resource_filter = "";
	var $category_filter = "";
//	var $week_view_header_date_format = "F d, Y";
	var $week_view_header_date_format = "%B %d, %Y";
	var $user_search_filter = "";
	var $startDay = 0;
	var $isMobile = false;
	var $showSeatTotals = false;
	var $fd_allow_show_seats = true;
	var $fd_res_admin_only = true;
	var $fd_read_only = false;
	var $fd_detail_popup = false;
	var $fd_show_contact_info = true;
	var $fd_allow_manifest = true;
	var $fd_display = "Customer";
	var $fd_tooltip = "Resource";
	var $fd_show_bookoffs = true;
	var	$fd_show_financials = true;
	var $service_resource_ids = "";

	
	var $printerView = "No";
	
	var $booking_screen = "gad";
	
	var $apptpro_config = null;
	
	function setItemid($id){
		$this->Itemid = $id;	
	}

	function setMenuid($id){
		
		// This is the sort of thing that drives me #~!@!#@% crazy about Joomla 1.7 SEO (1.5 does not display this bizare mis-behavior).
		// With SEO disabled, the active menu is set and the parameters can be read.
		// With SEO enabled, the active menu is NOT set yet, it is the last menu item not the Front Desk.
		// The work around is to grab the active menu from the view (before this code is called) and pass
		// the id into here. AArrhhgg
		
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getItem($id);
		//$active = $menu->getActive(); 

		if($active->getParams('fd_allow_show_seats') == 'No'){
			$this->fd_allow_show_seats = false;
		}

		if($active->getParams('fd_res_admin_only') == 'No'){
			$this->fd_res_admin_only = false;
		}

		if($active->getParams('fd_read_only') == 'Yes'){
			$this->fd_read_only = true;
		}
		
		if($active->getParams('fd_detail_popup') == 'Yes'){
			$this->fd_detail_popup = true;
		}
		
		if($active->getParams('fd_show_contact_info') == 'No'){
			$this->fd_show_contact_info = false;
		}

		if($active->getParams('fd_allow_manifest') == 'No'){
			$this->fd_allow_manifest = false;
		}

		if($active->getParams('fd_allow_manifest') == 'No'){
			$this->fd_allow_manifest = false;
		}

		if($active->getParams('fd_display') == 'Resource'){
			$this->fd_display = "Resource";
		} else {
			$this->fd_display = "Customer";
		}

		$this->fd_tooltip = $active->getParams('fd_tooltip');

		if($active->getParams('fd_show_bookoffs') == 'No'){
			$this->fd_show_bookoffs = false;
		}

		if($active->getParams('fd_show_financials') == 'No'){
			$this->fd_show_financials = false;
		}
			
	}

	function setResAdmin($id){
		$this->resAdmin = $id;	
	}
		
	function setResourceFilter($res_filter){
		$this->resource_filter = $res_filter;	
	}

	function setCategoryFilter($cat_filter){
		$this->category_filter = $cat_filter;	
	}


	function setWeekViewDateFormat($value){
		$this->week_view_header_date_format = $value;	
	}

	function setWeekStartDay($value){
		$this->startDay = $value;	
	}

	function setIsMobile($value){
		$this->isMobile = $value;	
	}

	function setShowSeatTotals($value){
		$this->showSeatTotals = $value;	
	}

	function setPrinterView($value){
		$this->printerView = $value;	
	}


	function setServiceResourceIds($value){
		$this->service_resource_ids = $value;	
	}

	function setBookingScreen($value){
		$this->booking_screen = $value;	
		//echo $this->booking_screen;
	}

	function getDayNames()
	{
		return $this->dayNames;
	}
	
		
	function getDateLink($day, $month, $year)
	{
		return "";
	}
	
	
	function getCurrentMonthView()
	{
		$d = getdate(time());
		return $this->getMonthView($d["mon"], $d["year"]);
	}
	
		
	function getMonthView($month, $year)
	{
		return $this->getMonthHTML($month, $year);
	}
	
	function getWeekView($wo, $m, $y)
	{
		return $this->getWeekHTML($wo, $m, $y);
	}

	function getDayView($day)
	{
		return $this->getDayHTML($day);
	}
	

	function getDaysInMonth($month, $year)
	{
		if ($month < 1 || $month > 12)
		{
			return 0;
		}
		
		$d = $this->daysInMonth[$month - 1];
		
		if ($month == 2)
		{
			// Check for leap year
			// Forget the 4000 rule, I doubt I'll be around then...
			
			if ($year%4 == 0)
			{
				if ($year%100 == 0)
				{
					if ($year%400 == 0)
					{
						$d = 29;
					}
				}
				else
				{
					$d = 29;
				}
			}
		}
		
		return $d;
	}
	
	
	/*
		------------------------------------------------------------------------------------------------
	    Generate the HTML for a given month
		------------------------------------------------------------------------------------------------
	*/
	function getMonthHTML($m, $y, $showYear = 1){
		$user = JFactory::getUser();
		$resources = $this->getResources($m, $y);
		//print_r($resources);		

		$bookoffs = null;
		$bookoffs = $this->getBookoffs($m, $y);
		//print_r($bookoffs);
		$s = "";
		
		$a = $this->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];        
		
		$daysInMonth = $this->getDaysInMonth($month, $year);
		$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		$today_month = date("m");
		
		$first = $date["wday"];
		$array_monthnames = getMonthNamesArray();
		$monthName = $array_monthnames[$month - 1];
		
		$prev = $this->adjustDate($month - 1, $year);
		$next = $this->adjustDate($month + 1, $year);
		
		if ($showYear == 1)
		{
			$prevMonth = $this->getCalendarLinkOnClick($prev[0], $prev[1]);
			$nextMonth = $this->getCalendarLinkOnClick($next[0], $next[1]);
//			$prevMonth = $this->getCalendarLink($prev[0], $prev[1]);
//			$nextMonth = $this->getCalendarLink($next[0], $next[1]);
		}
		else
		{
			$prevMonth = "";
			$nextMonth = "";
		}
		
		$header = $monthName . (($showYear > 0) ? " " . $year : "");

		$array_daynames = getDayNamesArray();
		$s .= "<table width=\"100%\" align=\"center\" class=\"calendar\" cellspacing=\"1\" style=\"border: solid 1px\">\n";
		$s .= "<tr>\n";
		$s .= "<td colspan=\"7\" align=\"center\">\n";
		$s .= "<table width=\"100%\" >\n";
		$s .= "<tr>\n";
		$s .= "<td width=\"5%\" align=\"center\" valign=\"top\"><input type=\"button\" onclick=\"$prevMonth\" value=\"<<\"></td>\n";
		$s .= "<td style=\"text-align:center\" valign=\"top\" class=\"calendarHeader\" >$header";
		if ($month != $today_month){
			 $s .= "<br/><input type=\"button\" onclick=\"goToday();\" value=\"".JText::_('RS1_CAL_VIEW_TODAY')."\">";
		}
		$s .= "</td>\n"; 
		$s .= "<td width=\"5%\" align=\"center\" valign=\"top\"><input type=\"button\" onclick=\"$nextMonth\" value=\">>\" ></td>\n";
		$s .= "</td>\n"; 
		$s .= "</tr>\n";
		$s .= "</table>\n";
		$s .= "</tr>\n";
		
		$s .= "<tr>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+1)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+2)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+3)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+4)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+5)%7] . "</td>\n";
		$s .= "<td width=\"14%\" align=\"center\" valign=\"top\" class=\"calendarHeaderDays\">" . $array_daynames[($this->startDay+6)%7] . "</td>\n";
		$s .= "</tr>\n";
		
		// We need to work out what date to start at so that the first appears in the correct column
		$d = $this->startDay + 1 - $first;
		while ($d > 1)
		{
			$d -= 7;
		}
		
		// Make sure we know when today is, so that we can use a different CSS style
		$CONFIG = new JConfig();
		date_default_timezone_set($CONFIG->offset);
		$today = getdate(time());
		
		while ($d <= $daysInMonth)
		{
			$s .= "<tr>\n";       
			
			for ($i = 0; $i < 7; $i++)
			{
				$class = ($year == $today["year"] && $month == $today["mon"] && $d == $today["mday"]) ? "calendarToday" : "calendar";
				$s .= "<td class=\"calendarCell $class\" width=\"14%\" align=\"left\" valign=\"top\">";       
				if ($d > 0 && $d <= $daysInMonth)
				{
					$link = "index.php?option=com_rsappt_pro3&view=".$this->booking_screen."&Itemid=".$this->Itemid;
					$link = $link."&frompage=calendar_view";							
					$link = $link."&mystartdate=".$year."-".($month<10?"0".$month:$month)."-".($d<10?"0".$d:$d);
					$s .= "<a href=".$link.">".$d."</a>";
				}
				else
				{
					$s .= "&nbsp;";
				}

				$strToday = strval($year)."-".($month<10 ? "0".
				strval($month):strval($month)) .
				"-".($d<10 ? "0".strval($d) : strval($d));

				if ($d > 0 && $d <= $daysInMonth){
					$day_of_the_week = date("w", strtotime($d.".".$month.".".$year));
					// using dot separator because, from php docs..
						// Note:
						// Dates in the m/d/y or d-m-y formats are disambiguated by looking at the separator between the various components: if the separator 
						// is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format 
						// is assumed. If, however, the year is given in a two digit format and the separator is a dash (-, the date string is parsed as y-m-d.

					// get resource by day
					foreach($resources as $resource){
						$show_resource = "Yes";
						if(!display_this_resource($resource, $user)){
							$show_resource = "No";
						}
						if(showrow($resource, date("Y-m-d", strtotime($d.".".$month.".".$year)), $day_of_the_week) != "yes"){
							$show_resource = "No";
						}
						
						foreach($bookoffs as $bookoff){
							if($bookoff->off_date == date("Y-m-d", strtotime($d.".".$month.".".$year)) && $bookoff->resource_id == $resource->id_resources){
								if($bookoff->full_day == "Yes" ){
									$show_resource = "No";
									$display_timeoff = JText::_('RS1_FRONTDESK_BO_FULLDAY');
								} else {
									$display_timeoff = $bookoff->description;
								}
								// to not show full day book-off, comment out the following line
								$s .= "<br/><label class='calendar_text_bookoff' title='".$display_timeoff."'>".$bookoff->name." - ".$bookoff->description."</label>";
							}
						}
						
						$strDate = $year."-".$month."-".$d;	
						$show_as_full = "no";
						$slots_data = getSlots($resource->id_resources, $strDate, 1);
						if($slots_data == 0){
							//$show_resource = "No";
							$show_as_full = "yes";
//						} else {
//							logIt("Value returned from getSlots: ".$slots_data, "svcalendarview", "", "");
						}
						

						if($show_resource == "Yes"){
							$link = "index.php?option=com_rsappt_pro3&view=".$this->booking_screen."&Itemid=".$this->Itemid;
							$link = $link."&frompage=calendar_view";							
							$link = $link."&mystartdate=".$year."-".($month<10?"0".$month:$month)."-".($d<10?"0".$d:$d);
							$link = $link."&res=".$resource->id_resources;
							if($show_as_full == "yes"){
								$s .= "<br><span class=\"sv_cal_view_fully_booked\">".$resource->name."</span>";
							} else {
//								$s .= "<br><a href=".$link." >".$resource->name."</a>";
								$s .= "<br><a href=".$link." onmouseenter=\"getAvailability(".$resource->id_resources.",'".$year."-".$month."-".$d."', event)\"
								onmouseleave=\"closeAvailability()\"><span class=\"sv_cal_res_".$resource->id_resources."\">".$resource->name."</span></a>";
							}
						}
					}
				}

				$s .= "<br>&nbsp;</td>\n";       
				$d++;
			}
			$s .= "</tr>\n";    
		}
		
		$s .= "</table>\n";
		$s .= "<input type=\"hidden\" name=\"cur_month\" id=\"cur_month\" value=\"".$month."\">";
		$s .= "<input type=\"hidden\" name=\"cur_year\" id=\"cur_year\" value=\"".$year."\">";
		
		return $s;  
			
	}
	

	// get resources
	function getResources($month, $year, $startDay="", $NumDays="", $mode="month"){
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	
		$sql = "SELECT id_resources, category_scope, name, description, ".
		"allowSunday, allowMonday, allowTuesday, allowWednesday, allowThursday, allowFriday, allowSaturday, timeslots, ".
		"disable_dates_before, disable_dates_before_days, ".
		"disable_dates_after, disable_dates_after_days, date_specific_booking,".
		" access ".
		" FROM  #__sv_apptpro3_resources WHERE published = 1 ";
		if($this->service_resource_ids != ""){
			$sql .= " AND id_resources IN(".$database->escape($this->service_resource_ids).")";
		}
		//echo $sql;
		
		
//		switch($mode){
//			case "month":
//				$sql = $sql." AND MONTH(startdate)=".strval($month)." AND YEAR(startdate)=".strval($year);
//				break;
//			case "week":
//				$sql = $sql." AND startdate >='".$startDay."' AND startdate <= DATE_ADD('".$startDay."',INTERVAL ".$NumDays." DAY)";
//				break;
//			case "day":
//				$sql = $sql." AND startdate ='".$startDay."' ";
//				break;
//		}
		if($this->resource_filter != ""){
			$sql .= " AND id_resources=".$this->resource_filter." ";
		}
		if($this->category_filter != ""){
			$safe_search_string = '%|' . $database->escape( $this->category_filter, true ) . '|%' ;
			$sql .= " AND category_scope LIKE ".$database->quote( $safe_search_string, false );			
		}
		$sql .= " ORDER BY ordering";
		
		//echo $sql;
		try{
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $rows;
	}
	
	// get bookings
//	function getBookings($resAdmin, $month, $year, $startDay="", $NumDays="", $mode="month"){
//		$database = JFactory::getDBO();
//		$sql = 'SELECT * FROM #__sv_apptpro3_config';
//		try{
//			$database->setQuery($sql);
//			$apptpro_config = NULL;
//			$apptpro_config = $database -> loadObject();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "svcalendarview", "", "");
//			echo JText::_('RS1_SQL_ERROR');
//			return false;
//		}		
//	
//		$lang = JFactory::getLanguage();
//		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
//		try{
//			$database->setQuery($sql);
//			$database->execute();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "svcalendarview", "", "");
//			echo JText::_('RS1_SQL_ERROR');
//			exit;
//		}
//	
//		$sql = "SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_admins, #__sv_apptpro3_resources.id_resources as res_id, ".
//			"#__sv_apptpro3_resources.max_seats, #__sv_apptpro3_resources.name as resname, #__sv_apptpro3_services.name AS ServiceName,  ".		
////			"#__sv_apptpro3_categories.name AS CategoryName,  ".			
//			"#__sv_apptpro3_resources.id_resources as resid, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e ') as display_startdate, ";
//			if($apptpro_config->timeFormat == '24'){
//				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %H:%i') as display_starttime, ";
//				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.endtime, ' %H:%i') as display_endtime ";
//			} else {
//				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime, ";
//				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.endtime, ' %l:%i %p') as display_endtime ";
//			}			
//			$sql .= " FROM ( ".
//			'#__sv_apptpro3_requests LEFT JOIN '.
//			'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
//			'#__sv_apptpro3_resources.id_resources LEFT JOIN '.	
////			'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
////			'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
//			'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
//			'#__sv_apptpro3_services.id_services ) '.
//			"WHERE ";
//		if($this->fd_read_only){
//			$sql .= " request_status IN('new', 'pending', 'accepted') ";
//		} else {
//			$sql .= " request_status!='deleted' ";
//		}
//		if($this->fd_res_admin_only){
//			$safe_search_string = '%|' . $database->escape( $this->resAdmin, true ) . '|%' ;									
//			$sql = $sql."AND #__sv_apptpro3_resources.resource_admins LIKE ".$database->quote( $safe_search_string, false )." ";
////			$sql = $sql."AND #__sv_apptpro3_resources.resource_admins LIKE '%|".$this->resAdmin."|%' ";
//		}
//		$user = JFactory::getUser();
//		if($user->guest){
//			// if not logged in, only show public resources
//			$sql .= " AND #__sv_apptpro3_resources.access LIKE '%|1|%' ";
//		}
//		switch($mode){
//			case "month":
//				$sql = $sql." AND MONTH(startdate)=".strval($month)." AND YEAR(startdate)=".strval($year);
//				break;
//			case "week":
//				$sql = $sql." AND startdate >='".$startDay."' AND startdate <= DATE_ADD('".$startDay."',INTERVAL ".$NumDays." DAY)";
//				break;
//			case "day":
//				$sql = $sql." AND startdate ='".$startDay."' ";
//				break;
//		}
//		if($this->reqStatus != ""){
//			$sql .= " AND request_status='".$this->reqStatus."' ";
//		}			
//		if($this->payStatus != ""){
//			$sql .= " AND payment_status='".$this->payStatus."' ";
//		}			
//		if($this->resource_filter != ""){
//			$sql .= " AND resource=".$this->resource_filter." ";
//		}
//		if($this->category_filter != ""){
//			$sql .= " AND category=".$this->category_filter." ";
//		}
//		if($this->user_search_filter != ""){
//			$sql .= " AND LCASE(#__sv_apptpro3_requests.name) LIKE '%".strtolower($database->escape($this->user_search_filter))."%' ";
//		}
//		$sql .= " ORDER BY startdate, starttime";
//		//echo $sql;
//		try{
//			$database->setQuery($sql);
//			$rows = NULL;
//			$rows = $database -> loadObjectList();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "svcalendarview", "", "");
//			echo JText::_('RS1_SQL_ERROR');
//			return false;
//		}		
//		return $rows;
//	}

	// get book-offs
	function getBookoffs($month, $year, $startDay="", $NumDays="", $mode="month"){
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	
		$sql = "SELECT #__sv_apptpro3_bookoffs.*, ".
			"#__sv_apptpro3_resources.name, #__sv_apptpro3_resources.resource_admins, ";
			if($apptpro_config->timeFormat == '24'){
				$sql .=" DATE_FORMAT(#__sv_apptpro3_bookoffs.bookoff_starttime, '%H:%i') as display_bo_starttime, ";
				$sql .=" DATE_FORMAT(#__sv_apptpro3_bookoffs.bookoff_endtime, '%H:%i') as display_bo_endtime ";
			} else {
				$sql .=" DATE_FORMAT(#__sv_apptpro3_bookoffs.bookoff_starttime, '%l:%i %p') as display_bo_starttime, ";
				$sql .=" DATE_FORMAT(#__sv_apptpro3_bookoffs.bookoff_endtime, '%l:%i %p') as display_bo_endtime ";
			}			
			$sql .= " FROM ( ".
			'#__sv_apptpro3_bookoffs LEFT JOIN '.
			'#__sv_apptpro3_resources ON #__sv_apptpro3_bookoffs.resource_id = '.
			'#__sv_apptpro3_resources.id_resources) '.
			"WHERE #__sv_apptpro3_bookoffs.published = 1 ";
		// no rolling bookoffs shown
		$sql .= " AND rolling_bookoff = 'No'";
		$user = JFactory::getUser();
		if($user->guest){
			// if not logged in, only show public resources
			$sql .= " AND #__sv_apptpro3_resources.access LIKE '%|1|%' ";
		}
		switch($mode){
			case "month":
				$sql = $sql." AND MONTH(off_date)=".strval($month)." AND YEAR(off_date)=".strval($year);
				break;
			case "week":
				$sql = $sql." AND off_date >='".$startDay."' AND off_date <= DATE_ADD('".$startDay."',INTERVAL ".$NumDays." DAY)";
				break;
			case "day":
				$sql = $sql." AND off_date ='".$startDay."' ";
				break;
		}
		if($this->resource_filter != ""){
			$sql .= " AND resource_id = ".$this->resource_filter." ";
		}
		$sql .= " ORDER BY off_date, bookoff_starttime";
		//echo $sql;
		try{
			$database->setQuery($sql);
			$bo_rows = NULL;
			$bo_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $bo_rows;
	}
	
	// get statuses
	function getStatuses(){
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendarview", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		$this->apptpro_config = $apptpro_config;
		
//		if($this->fd_read_only){
//			$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value IN('new', 'pending', 'accepted') ORDER BY ordering ";
//		} else {
			$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value!='deleted' ORDER BY ordering ";
//		}
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $statuses;
	}
	
	/*
	    Adjust dates to allow months > 12 and < 0. Just adjust the years appropriately.
	    e.g. Month 14 of the year 2001 is actually month 2 of year 2002.
	*/
	function adjustDate($month, $year)
	{
		$a = array();  
		$a[0] = $month;
		$a[1] = $year;
		
		while ($a[0] > 12)
		{
			$a[0] -= 12;
			$a[1]++;
		}
		
		while ($a[0] <= 0)
		{
			$a[0] += 12;
			$a[1]--;
		}
		
		return $a;
	}
	
	/* 
	    The start day of the week. This is the day that appears in the first column
	    of the calendar. Sunday = 0.
	*/
	//var $startDay = 0;
	
	/* 
	    The start month of the year. This is the month that appears in the first slot
	    of the calendar in the year view. January = 1.
	*/
	var $startMonth = 1;
	
	
	/*
	    The number of days in each month. You're unlikely to want to change this...
	    The first entry in this array represents January.
	*/
	var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	function getCalendarLink($month, $year)
	{
		// Redisplay the current page, but with some parameters
		// to set the new month and year
		$s = getenv('SCRIPT_NAME');
		return "$s?month=$month&year=$year";
	}
	
	function getCalendarLinkOnClick($month, $year)
	{
		return "buildCalendarDeskView('', $month, $year)";
	}
	
	function getWeekviewLinkOnClick($wo)
	{
		return "buildCalendarDeskView('', '', '', $wo)";
	}
	
}

?>

