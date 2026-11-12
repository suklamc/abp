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


class SVCalendar
{
    
	// php 5 +
	public function __construct()
    {
		$appWeb      = JFactory::getApplication();
		if($appWeb->client->mobile){
			$this->mobile = true;
		};
    }
	
	var $mobile = false;

	// php 4
	function SVCalendar()
	{
		$appWeb      = JFactory::getApplication();
		if($appWeb->client->mobile){
			$this->mobile = true;
		};
		
	}
	
	var $Itemid = null;
	var $resAdmin = "";
	var $reqStatus = "";
	var $payStatus = "";
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

	
	var $printerView = "No";
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
	
	function setReqStatus($status){
		$this->reqStatus = $status;	
	}
	function setPayStatus($payment_status){
		$this->payStatus = $payment_status;	
	}
	
	function setResourceFilter($res_filter){
		$this->resource_filter = $res_filter;	
	}

	function setCategoryFilter($cat_filter){
		$this->category_filter = $cat_filter;	
	}

	function setSearchFilter($value){
		$this->user_search_filter = $value;	
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

		$bookings = $this->getBookings($this->resAdmin, $m, $y);
		$bookoffs = null;
		if($this->fd_show_bookoffs){
			$bookoffs = $this->getBookoffs($this->resAdmin, $m, $y);
		}
		$s = "";
		
		$a = $this->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];        
		
		$daysInMonth = $this->getDaysInMonth($month, $year);
		$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		
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
		$s .= "<td style=\"text-align:center\" valign=\"top\" class=\"calendarHeader\" >$header</td>\n"; 
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
//				$s .= "<td class=\"calendarCell$class\" align=\"left\" valign=\"top\">";       
				if ($d > 0 && $d <= $daysInMonth)
				{
					//$link = "javascript:goDayView('".$year."-".$month."-".$d."')";
					//$s .= (($link == "") ? "<span class=\"calendar_day_number\">".$d."</span>" : "<a href=\"".$link."\">$d</a>");
					$link = "# onclick='goDayView(\"".$year."-".$month."-".$d."\");return false;'";						
					$s .= "<a href=".$link.">".$d."</a>";
				}
				else
				{
					$s .= "&nbsp;";
				}
				// get todays bookings
				$strToday = strval($year)."-".($month<10 ? "0".
				strval($month):strval($month)) .
				"-".($d<10 ? "0".strval($d) : strval($d));
				foreach($bookings as $booking){
					if($booking->startdate == $strToday){
						if($this->fd_read_only){
							if($this->fd_detail_popup){
								$link = JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail').'&task=readonly&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid. " class=\"modal\" rel=\"{handler: 'iframe', onClose: function() {}}\" ";
							} else {
								$link = "'#' onclick=\"alert('".JText::_('RS1_DETAIL_VIEW_DISABLED')."');return true;\" ";
							}
						} else {
							$link = JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail').'&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid;
						}

						if($this->fd_display == "Resource"){
							$display = JText::_($booking->resname);
						} else {
							$display = $booking->name;
						}
						if($this->fd_tooltip == "Resource"){
							$title = trim($booking->display_starttime)."-".trim($booking->display_endtime)."&nbsp;\n".JText::_($booking->resname)."&nbsp;\n".JText::_($booking->ServiceName);
						} else if($this->fd_tooltip == "Customer"){
							$title = trim($booking->display_starttime)."-".trim($booking->display_endtime)."&nbsp;\n".$booking->name."&nbsp;\n".JText::_($booking->ServiceName);
						}
						$s .= "<br><a href=".$link." ><span class='calendar_text_".$booking->request_status."'>".$booking->display_starttime."|".stripslashes($display)."</span></a>";
					}
				}

				if($this->fd_show_bookoffs){
					foreach($bookoffs as $bookoff){
						if($bookoff->off_date == $strToday){
							if($bookoff->full_day == "Yes"){
								$display_timeoff = JText::_('RS1_FRONTDESK_BO_FULLDAY');
							} else {
								$display_timeoff = $bookoff->display_bo_starttime." - ".$bookoff->display_bo_endtime;
								if($bookoff->description != ""){
									$display_timeoff .= "\n".$bookoff->description;
								}
							}
							$s .= "<br/><label class='calendar_text_bookoff' title='".$display_timeoff."'>".$bookoff->name."</label>";
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
	

	/*
	------------------------------------------------------------------------------------------------
	    Generate the HTML for a given week
	--------------------------------------------------------------------------------------------------
	*/
	function getWeekHTML($wo, $m, $y){
		// Show sunday - saturday 
		// $wo = week offset, +1 next week, -1 = last week
		// $day for 1 day view

		$NumDays = 7;

		$cws = null;  // currentweekstart
		$dws = null;  // displayweekstart
		
		if($this->startDay == 0){
			if(date("w")==0){
				// today is Sunday $cws = today
				$cws = strtotime("now");
			} else {
				$cws = strtotime("last Sunday");
			}
		} else {
			// get current week's Sunday ($cws = currentweekstart)
			if(date("w")==1){
				// today is Sunday $cws = today
				$cws = strtotime("now");
			} else {
				$cws = strtotime("last Monday");
			}
		}

		if($wo == 0){
			$dws = $cws;
		} else {
			$dws = strtotime(strval($wo)." week", $cws); 
		}		
		
		$bookings = $this->getBookings($this->resAdmin, '', '', date("Y-m-d", $dws), $NumDays, "week");

		$bookoffs = null;
		if($this->fd_show_bookoffs){
			$bookoffs = $this->getBookoffs($this->resAdmin, date("m", $dws), date("y", $dws), date("Y-m-d", $dws), $NumDays, "week");
			//print_r($bookoffs);
		}
		$statuses = $this->getStatuses();
		
		$header = JText::_('RS1_FRONTDESK_SCRN_VIEW_WEEK');
		$prevWeek = $this->getWeekviewLinkOnClick($wo-1);
		$nextWeek = $this->getWeekviewLinkOnClick($wo+1);
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
		//setlocale(LC_TIME,"french");
		// for Greek you may need to hard code..
		//setlocale(LC_TIME, array('el_GR.UTF-8','el_GR','greek'));
		
		$s = "";
		$i2=1;
		$colspan = 8;
		if($this->mobile){
			$colspan = 5;
		}
		$array_daynames = getLongDayNamesArray($this->startDay);
		$s .= "<div id=\"sv_apptpro_front_desk_top\">\n";
		$s .= "<table width=\"100%\" align=\"center\" border=\"0\" class=\"calendar_week_view\" cellspacing=\"0\" >\n";
		$s .= "<tr class=\"calendar_week_view_header_row\">\n";
		$s .= "<td width=\"5%\" align=\"left\" ><input type=\"button\" onclick=\"$prevWeek\" value=\"<<\"></td>\n";
		$s .= "<td style=\"text-align:center\" colspan=\"".($colspan-2)."\" class=\"calendarHeader\" >$header</td>\n"; 
		$s .= "<td width=\"5%\" align=\"right\" ><input type=\"button\" onclick=\"$nextWeek\" value=\">>\"></td>\n";
		$s .= "</tr>\n";
		for($i=0; $i<$NumDays; $i++){
			// week day
			$link = "# onclick='goDayView(\"".date("Y-m-d", strtotime(strval($i)." day", $dws))."\");return false;'";						
			$s .= "<tr>\n";
			$s .= "  <td colspan=\"".$colspan."\">\n";
			$s .= "    <table class=\"week_day_table\" width=\"100%\" border=\"0\" cellspacing=\"0\" >\n";
			$s .= "      <tr >\n";

			if(WINDOWS){
				$s .= "        <td colspan=\"".$colspan."\">&nbsp;<a href=".$link.">".$array_daynames[$i]." ".iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($this->week_view_header_date_format, strtotime(strval($i)." day", $dws)))."</a></td>\n";
			} else {
				$s .= "        <td colspan=\"".$colspan."\">&nbsp;<a href=".$link.">".$array_daynames[$i]." ".strftime($this->week_view_header_date_format, strtotime(strval($i)." day", $dws))."</a></td>\n";
			}
			$s .= "      </tr>\n";		
			$day_to_check = date("Y-m-d", strtotime(strval($i)." day", $dws));
			$k = 0;
			foreach($bookings as $booking){
				if($booking->startdate == $day_to_check){
					if($this->fd_read_only){
						if($this->fd_detail_popup){
							$link = JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail').'&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid. "&format=readonly class=\"modal\" rel=\"{handler: 'iframe', size: {x: 800, y: 600}, onClose: function() {}}\" ";
						} else {
							$link = "'#' onclick=\"alert('".JText::_('RS1_DETAIL_VIEW_DISABLED')."');return true;\" ";
						}
					} else {
						$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail').'&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid;
					}

					$s .= "<tr class='week_row'>\n";
					$s .= "  <td width=\"5%\" align=\"center\"><input type=\"checkbox\" id=\"cb".$i2."\" name=\"cid[]\" value=\"".$booking->id_requests."\" /></td>\n";
					$s .= "  <td width=\"10%\" align=\"left\">".$booking->display_starttime."</td>\n";
					$s .= "  <td width=\"15%\" align=\"left\"> ".JText::_(stripslashes($booking->resname))."</td>\n";
					if(!$this->mobile){
						$s .= "  <td width=\"15%\" align=\"left\"> ".JText::_(stripslashes($booking->ServiceName))."</td>";
					}
					if($this->fd_allow_show_seats){
						if(!$this->mobile){
							$s .= "  <td width=\"5%\" align=\"left\"> ".$booking->booked_seats."</td>";
						}
					}
					$s .= "  <td width=\"15%\"  align=\"left\"> <a href=".$link.">".stripslashes($booking->name)."</a></td>";
					if($this->fd_show_contact_info){
						if(!$this->mobile){
							$s .= "  <td width=\"30%\" align=\"left\"><a href=\"mailto:".$booking->email."\">".$booking->email."</a></td>\n";
						}
					}
//						$s .= "  <td align=\"center\" width=\"10%\"><span class='color_".$booking->payment_status."'>".translated_status($booking->payment_status)."</span></td>\n";
				
					if($this->apptpro_config->status_quick_change == "No"){
						if(!$this->mobile){
							$s .= "  <td align=\"center\"><span class='color_".$booking->request_status."'>".translated_status($booking->request_status)."</span></td>\n";
						} else {
							$s .= "  <td width=\"10%\" align=\"center\"><span class='color_".$booking->request_status."'>".substr(translated_status($booking->request_status),0,3)."</span></td>\n";
						}	
					} else {
						$s .= "  <td align=\"center\">\n";
						$s .= "     <select id=\"booking_status_".$booking->id_requests."\" name=\"booking_status".$booking->id_requests."\" "; 
						$s .= "			onfocus=\"this.oldvalue = this.value;\" onchange=\"quick_status_change('".$booking->id_requests."',this); return false;\"";
						$s .= "			style=\"width:auto\">\n";
						foreach($statuses as $status_row){
							$s .= "		<option value=\"".$status_row->internal_value."\" class=\"color_".$status_row->internal_value."\" ";
								if($booking->request_status == $status_row->internal_value ? $s .=" selected='selected' ":"");
								$s .= ">".JText::_($status_row->status)."</option>\n";
						}
						$s .= "		</select>\n";
						$s .= "</td>\n";
					}
					if($this->fd_show_financials){
						$s .= "  <td align=\"right\">".translated_status($booking->payment_status).($booking->invoice_number != ""?"<br/>(".$booking->invoice_number.")":"")."</td>\n";
					}					
					$s .= "</tr>\n";
					$i2++;
				}
			}
			if($this->fd_show_bookoffs){
				foreach($bookoffs as $bookoff){
					if($bookoff->off_date == $day_to_check){
						if($bookoff->full_day == "Yes"){
							$display_timeoff = JText::_('RS1_FRONTDESK_BO_FULLDAY');
						} else {
							$display_timeoff = $bookoff->display_bo_starttime." - ".$bookoff->display_bo_endtime;
							if($bookoff->description != ""){
								$display_timeoff .= "\n".$bookoff->description;
							}
						}
						$s .= "<tr><td colspan=8><label class='calendar_text_bookoff' title='".$display_timeoff."'>".$bookoff->name." - ".$display_timeoff."</label></td></tr>";
					}
				}				
			}
			
		  $s .= "      </table>\n";
		  $s .= "    </td>\n";
		  $s .= "  </tr>\n";
		}
		$s .= "</table>\n";
		$s .= "</div>\n";
		$s .= "<input type=\"hidden\" name=\"cur_week_offset\" id=\"cur_week_offset\" value=\"".$wo."\">";

		if($this->printerView == "Yes"){
			// remove all links
			$s = preg_replace(array('"<a href(.*?)>"', '"</a>"'), array('',''), $s);
		}

		return $s;  	
	}


	/*
	------------------------------------------------------------------------------------------------	
	    Generate the HTML for a singe day
	------------------------------------------------------------------------------------------------		
	*/
	function getDayHTML($day){
			
		$bookings = $this->getBookings($this->resAdmin, '', '', $day, "", "day");
		
		$unix_day = strtotime($day);
		$prevDay = "buildFrontDeskView('".date("Y-m-d", strtotime('-1 day', $unix_day))."');";
		$nextDay = "buildFrontDeskView('".date("Y-m-d", strtotime('+1 day', $unix_day))."');";

		$bookoffs = null;
		if($this->fd_show_bookoffs){
			$bookoffs = $this->getBookoffs($this->resAdmin, date("m", $unix_day), date("y", $unix_day), date("Y-m-d", $unix_day), "1", "day");
		}
		$statuses = $this->getStatuses();

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
		// for Greek you may need to hard code..
		//setlocale(LC_TIME, array('el_GR.UTF-8','el_GR','greek'));
		
		if(WINDOWS){
			$header = iconv(getIconvCharset(), 'UTF-8//IGNORE',strftime($this->week_view_header_date_format, strtotime($day)));
		} else {
			$header = strftime($this->week_view_header_date_format, strtotime($day));		
		}

		$s = "";
		$i=1;
		
		$array_daynames = getLongDayNamesArray();
		$s .= "<div id=\"sv_apptpro_front_desk_top\">\n";
		$s .= "<table width=\"100%\" align=\"center\" border=\"0\" class=\"calendar_week_view\" cellspacing=\"0\">\n";
		$s .= "  <tr class=\"calendar_week_view_header_row\">\n";
		$s .= "    <td width=\"5%\" align=\"center\" ><input type=\"button\" onclick=\"$prevDay\" value=\"<<\"></td>\n";
		$s .= "    <td style=\"text-align:center\" class=\"calendarHeader\" >$header</td>\n"; 
		$s .= "    <td width=\"5%\" align=\"center\"><input type=\"button\" onclick=\"$nextDay\" value=\">>\"></td>\n";
		$s .= "  </tr>\n";
		// week day
		$s .= "  <tr>\n";
		$s .= "    <td colspan=\"3\">\n";
		$s .= "      <table class=\"week_day_table\" width=\"100%\" border=\"0\" cellspacing=\"0\">\n";
		$k = 0;
		$seat_tally = 0;
		$current_ts = "";
		$previous_ts = "";
		$current_res = 0;
		$previous_res = 0;
		$initial_pass = true;	
		foreach($bookings as $booking){
			if($this->showSeatTotals == "true"){
				if($initial_pass){
					$current_ts = $booking->display_starttime;
					$previous_ts = $current_ts;
					$current_res = $booking->res_id;
					$previous_res = $current_res;
					$initial_pass = false;
				}
				$current_ts = $booking->display_starttime;
				$current_res = $booking->res_id;
				if($current_ts == $previous_ts AND $previous_res == $current_res){
					if($booking->request_status == 'accepted'){
						$seat_tally  += $booking->booked_seats;
					}
				} else {
					//moved to next timeslot
					//write summary and move on
					$s .= "<tr class='week_row' >\n";
					$s .= "  <td colspan='4' align='right' style=\"border-bottom:solid 1px\">".JText::_('RS1_TS_TOTAL_SEATS')."&nbsp;</td>\n";
					$s .= "  <td width=\"5%\" align=\"center\" style=\"border-top:solid 1px;border-bottom:solid 1px\"> ".$seat_tally."</td>";
					$s .= "  <td colspan='3' style=\"border-bottom:solid 1px\" >&nbsp;</td>\n";
					$s .= "</tr>\n";
					$previous_ts = $current_ts;
					$seat_tally = $booking->booked_seats;				
					$previous_res = $current_res;
				}
			}

			if($this->fd_read_only){
				if($this->fd_detail_popup){
					$link = JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid). "&format=readonly class=\"modal\" rel=\"{handler: 'iframe'}\" ";
//					$link = 'index.php?option=com_rsappt_pro3&view=admin_detail&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid. "&format=readonly class=\"modal\" rel=\"{handler: 'iframe'}\" ";
				} else {
					$link = "'#' onclick=\"alert('".JText::_('RS1_DETAIL_VIEW_DISABLED')."');return true;\" ";
				}
			} else {
				$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&view=admin_detail&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid);
//				$link 	= 'index.php?option=com_rsappt_pro3&view=admin_detail&task=edit&cid='. $booking->id_requests.'&frompage=front_desk&Itemid='.$this->Itemid;
			}

			$s .= "<tr class='week_row'>\n";
			$s .= "  <td align=\"center\"><input type=\"checkbox\" id=\"cb".$i."\" name=\"cid[]\" value=\"".$booking->id_requests."\" /></td>\n";
			if($this->fd_allow_manifest && !$this->mobile){
				$s .= "  <td align=\"left\" width=\"10%\"><a href=# onclick='goManifest(\"".$booking->resid."\",\"".$booking->startdate."\", \"".$booking->starttime."\", \"".$booking->endtime."\");return false;' title='".JText::_('RS1_DAY_VIEW_TIMESLOT_TOOLTIP')."'>".$booking->display_starttime."</a></td>\n";
			} else {
				$s .= "  <td align=\"left\" width=\"10%\">".$booking->display_starttime."</td>\n";
			}
			$s .= "  <td align=\"left\"> ".JText::_(stripslashes($booking->resname))."</td>\n";
			if(!$this->mobile){
				$s .= "  <td align=\"left\"> ".JText::_(stripslashes($booking->ServiceName))."</td>\n";
			}
			if($this->fd_allow_show_seats){
				$s .= "  <td width=\"5%\" align=\"center\"> ".$booking->booked_seats."</td>\n";
			}
			$s .= "  <td align=\"left\"> <a href=".$link." title='".JText::_('RS1_DAY_VIEW_NAME_TOOLTIP')."'>".stripslashes($booking->name)."</a></td>\n";
			if($this->fd_show_contact_info){
				if(!$this->mobile){
					$s .= "  <td align=\"left\"><a href=\"mailto:".$booking->email."\">".$booking->email."</a></td>\n";
				}
			}
//				$s .= "  <td align=\"center\" width=\"10%\"><span class='color_".$booking->payment_status."'>".translated_status($booking->payment_status)."</span></td>\n";

			if($this->apptpro_config->status_quick_change == "No"){
				$s .= "  <td align=\"center\" width=\"10%\"><span class='color_".$booking->request_status."'>".translated_status($booking->request_status)."</span></td>\n";
			} else {
				$s .= "  <td align=\"center\">\n";
				$s .= "     <select id=\"booking_status_".$booking->id_requests."\" name=\"booking_status".$booking->id_requests."\" "; 
				$s .= "			onfocus=\"this.oldvalue = this.value;\" onchange=\"quick_status_change('".$booking->id_requests."',this); return false;\"";
				$s .= "			style=\"width:auto\">\n";
				foreach($statuses as $status_row){
					$s .= "		<option value=\"".$status_row->internal_value."\" class=\"color_".$status_row->internal_value."\" ";
						if($booking->request_status == $status_row->internal_value ? $s .=" selected='selected' ":"");
						$s .= ">".JText::_($status_row->status)."</option>\n";
				}
				$s .= "		</select>\n";
				$s .= "</td>\n";
			}
			if($this->fd_show_financials){
				$s .= "  <td align=\"right\">".translated_status($booking->payment_status).($booking->invoice_number != ""?"<br/>(".$booking->invoice_number.")":"")."</td>\n";
			}					
			$s .= "</tr>\n";
			$i++;
		}	
		if($this->showSeatTotals == "true"){
			$s .= "<tr class='week_row' >\n";
			$s .= "  <td colspan='4' align='right' style=\"border-bottom:solid 1px\">".JText::_('RS1_TS_TOTAL_SEATS')."&nbsp;</td>\n";
			$s .= "  <td width=\"5%\" align=\"center\" style=\"border-top:solid 1px;border-bottom:solid 1px\"> ".$seat_tally."</td>";
			$s .= "  <td colspan='3' style=\"border-bottom:solid 1px\" >&nbsp;</td>\n";
			$s .= "</tr>\n";
		}			  
	    $s .= "      </table>\n";
		$s .= "    </tr>\n";
		$s .= "</table>\n";
		$s .= "</div>\n";
		$s .= "<input type=\"hidden\" name=\"cur_day\" id=\"cur_day\" value=\"".$day."\">";

		if($this->fd_show_bookoffs){
			$strToday = date("Y-m-d", $unix_day);
			foreach($bookoffs as $bookoff){
				if($bookoff->off_date == $strToday){
					if($bookoff->full_day == "Yes"){
						$display_timeoff = JText::_('RS1_FRONTDESK_BO_FULLDAY');
					} else {
						$display_timeoff = $bookoff->display_bo_starttime." - ".$bookoff->display_bo_endtime;
						if($bookoff->description != ""){
							$display_timeoff .= "\n".$bookoff->description;
						}
					}
					$s .= "<br/><label class='calendar_text_bookoff' title='".$display_timeoff."'>".$bookoff->name." - ".$display_timeoff."</label>";
				}
			}				
		}

		if($this->printerView == "Yes"){
			// remove all links
//			$s = preg_replace(array('"<a href(.*?)>"', '"</a>"'), array('',''), $s);
		}			
		return $s;  	
	}
	
	
	// get bookings
	function getBookings($resAdmin, $month, $year, $startDay="", $NumDays="", $mode="month"){
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendar", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendar", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	
		$sql = "SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_admins, #__sv_apptpro3_resources.id_resources as res_id, ".
			"#__sv_apptpro3_resources.max_seats, #__sv_apptpro3_resources.name as resname, #__sv_apptpro3_services.name AS ServiceName,  ".		
//			"#__sv_apptpro3_categories.name AS CategoryName,  ".			
			"#__sv_apptpro3_resources.id_resources as resid, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e ') as display_startdate, ";
			if($apptpro_config->timeFormat == '24'){
				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %H:%i') as display_starttime, ";
				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.endtime, ' %H:%i') as display_endtime ";
			} else {
				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime, ";
				$sql .=" DATE_FORMAT(#__sv_apptpro3_requests.endtime, ' %l:%i %p') as display_endtime ";
			}			
			$sql .= " FROM ( ".
			'#__sv_apptpro3_requests LEFT JOIN '.
			'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
			'#__sv_apptpro3_resources.id_resources LEFT JOIN '.	
//			'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
//			'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
			'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
			'#__sv_apptpro3_services.id_services ) '.
			"WHERE ";
		if($this->fd_read_only){
			$sql .= " request_status IN('new', 'pending', 'accepted') ";
		} else {
			$sql .= " request_status!='deleted' ";
//			$sql .= " request_status NOT IN('deleted', 'canceled') ";
		}
		if($this->fd_res_admin_only){
			$safe_search_string = '%|' . $database->escape( $this->resAdmin, true ) . '|%' ;									
			$sql = $sql."AND #__sv_apptpro3_resources.resource_admins LIKE ".$database->quote( $safe_search_string, false )." ";
//			$sql = $sql."AND #__sv_apptpro3_resources.resource_admins LIKE '%|".$this->resAdmin."|%' ";
		}
		$user = JFactory::getUser();
		if($user->guest){
			// if not logged in, only show public resources
			$sql .= " AND #__sv_apptpro3_resources.access LIKE '%|1|%' ";
		}
		switch($mode){
			case "month":
				$sql = $sql." AND MONTH(startdate)=".strval($month)." AND YEAR(startdate)=".strval($year);
				break;
			case "week":
				$sql = $sql." AND startdate >='".$startDay."' AND startdate <= DATE_ADD('".$startDay."',INTERVAL ".$NumDays." DAY)";
				break;
			case "day":
				$sql = $sql." AND startdate ='".$startDay."' ";
				break;
		}
		if($this->reqStatus != ""){
			$sql .= " AND request_status='".$this->reqStatus."' ";
		}			
		if($this->payStatus != ""){
			$sql .= " AND payment_status='".$this->payStatus."' ";
		}			
		if($this->resource_filter != ""){
			$sql .= " AND resource=".$this->resource_filter." ";
		}
		if($this->category_filter != ""){
			$sql .= " AND category=".$this->category_filter." ";
		}
		if($this->user_search_filter != ""){
			$sql .= " AND LCASE(#__sv_apptpro3_requests.name) LIKE '%".strtolower($database->escape($this->user_search_filter))."%' ";
		}
		$sql .= " ORDER BY startdate, starttime";
		//echo $sql;
		try{
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendar", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $rows;
	}

	// get book-offs
	function getBookoffs($resAdmin, $month, $year, $startDay="", $NumDays="", $mode="month"){
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendar", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "svcalendar", "", "");
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
		if($this->fd_res_admin_only){
			$safe_search_string = '%|' . $database->escape( $this->resAdmin, true ) . '|%' ;									
			$sql = $sql."AND #__sv_apptpro3_resources.resource_admins LIKE ".$database->quote( $safe_search_string, false )." ";
		}
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
			logIt($e->getMessage(), "svcalendar", "", "");
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
			logIt($e->getMessage(), "svcalendar", "", "");
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
		return "buildFrontDeskView('', $month, $year)";
	}
	
	function getWeekviewLinkOnClick($wo)
	{
		return "buildFrontDeskView('', '', '', $wo)";
	}
	
}

?>

