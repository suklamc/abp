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
	$non_booking_days = "";
	$bo_dates = "";
	$mindate = 999;
	$maxdate = 999;
	$popupwidth = null;
	$popupheight = null;

	$appWeb      = JFactory::getApplication();
	$userAgent = $appWeb->client->userAgent;
	/* 
		When the screen is built the server has no device information other than the User Agent. Device orientation and 
		resolution is not available.
		There is no guaranteed way to detect the device from the user agent. In general if the agent contains:
			'Android' and 'Mobile' = phone
			'Android' only = tablet
		Note: This is not universal among manufactures, some use Android Mobile for tablets ;-(
		Also, iPad reports as 'mobile'.
	*/
		
	if(strpos($userAgent, "iPad") !== false){
		$popupwidth = 600;
		$popupheight = 700;		
	} else if (strpos($userAgent, "iPhone") !== false || strpos($userAgent, "Mobile") !== false){
		$popupwidth = 300;
		$popupheight = 350;
	} else {
		$popupwidth = $params->get('popup_width');
		$popupheight = $params->get('popup_height');		
	}


	if($params->get('screen_to_display') != "booking_screen_simple"){
		// set calendar up from grid settings
		// get config info so we can disable those dates on the calendar.
		$database = JFactory::getDBO(); 
		$sql = 'SELECT gad_grid_start_day, gad_grid_start_day_days FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$config = NULL;
			$config = $database -> loadObject();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		// Set min date from config grid start date.
		switch($config->gad_grid_start_day){
			case "Today":
				$mindate = 0;
				break;
			case "Tomorrow":
				$mindate = 1;
				break;
			case "XDays":
				$mindate = $config->gad_grid_start_day_days;
				break;
			default:
				// must be specific date
				$now = time(); 
				$spec_date = strtotime($res_detail->gad_grid_start_day);
				$datediff = $spec_date - $now;
				$mindate = floor($datediff/(60*60*24))+1;
		}
		
	}

	if($params->get('mode') == "single_resource" && $params->get('res_cat_id') != ""){
		// get resource work days and book-offs so we can disable those dates on the calendar.
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$params->get('res_cat_id');
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		if($res_detail->allowSunday == "No"){$non_booking_days.="0,";}
		if($res_detail->allowMonday == "No"){$non_booking_days.="1,";}
		if($res_detail->allowTuesday == "No"){$non_booking_days.="2,";}
		if($res_detail->allowWednesday == "No"){$non_booking_days.="3,";}
		if($res_detail->allowThursday == "No"){$non_booking_days.="4,";}
		if($res_detail->allowFriday == "No"){$non_booking_days.="5,";}
		if($res_detail->allowSaturday == "No"){$non_booking_days.="6,";}
		//echo $non_booking_days;	
		
		// get full day book-offs so we can block them also.
		$sql = 'SELECT off_date FROM #__sv_apptpro3_bookoffs where resource_id = '.$params->get('res_cat_id').
			' AND published = 1 AND off_date >= NOW() AND full_day = "Yes"';
		try{
			$database->setQuery($sql);
			$bookoffs = NULL;
			$bookoffs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
		//print_r($bookoffs);
		foreach($bookoffs as $bookoff){
			$bo_dates .= "'".$bookoff->off_date."',";
		}
		//echo $bo_dates;
		
		// Set max/min dates from resource's disable dates before and after.
		switch($res_detail->disable_dates_before){
			case "Today":
				$mindate = 0;
				break;
			case "Tomorrow":
				$mindate = 1;
				break;
			case "XDays":
				$mindate = $res_detail->disable_dates_before_days;
				break;
			default:
				// must be specific date
				$now = time(); 
				$spec_date = strtotime($res_detail->disable_dates_before);
				$datediff = $spec_date - $now;
				$mindate = floor($datediff/(60*60*24))+1;
		}
		
		switch($res_detail->disable_dates_after){
			case "Not Set":
				$maxdate = null;
				break;
			case "XDays":
				$maxdate = $res_detail->disable_dates_after_days;
				break;
			default:
				// must be specific date
				$now = time(); 
				$spec_date = strtotime($res_detail->disable_dates_after);
				$datediff = $spec_date - $now;
				$maxdate = floor($datediff/(60*60*24))+1;			
		}
		
	}
?>
	<link href="<?php echo JURI::base( true );?>/modules/mod_abp_booking/abpro_popup.css" rel="stylesheet">

	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<!-- datepicker language files are here.. https://code.google.com/p/jquery-ui/source/browse/branches/labs/datepicker2/ui/i18n/?r=3875 -->    
<!--	<script src="<?php echo JURI::base( true ); ?>/modules/mod_abp_booking/jquery.ui.datepicker-fr.js"></script>-->
<!--	<script src="<?php echo JURI::base( true ); ?>/modules/mod_abp_booking/jquery.ui.datepicker-de.js"></script>-->

<?php 
	echo JText::_($params->get('header_text')); 
	
?>
<div style="font-size:<?php echo $params->get('popup_fontsize')?>%";>
<div id="ab_datepicker"></div>
</div>
<?php 
	echo JText::_($params->get('footer_text')); 
?>
<script>
<?php 
	echo "var myTitle = '".JText::_($params->get('popup_title'))."';\n"; 
	echo "var myLayout = '".$params->get('layout')."';\n" ;
	echo "var myScreen = '".$params->get('screen_to_display')."';\n" ;
	echo "var myMode = '".$params->get('mode')."';\n";
	echo "var myResCatID = '".$params->get('res_cat_id')."';\n";
	if($mindate == 999){$mindate = $params->get('mindate');}
	echo "var myMinDate = '".$mindate."';\n";
	if($maxdate == 999){$maxdate = $params->get('maxdate');}
	echo "var myMaxDate = '".$maxdate."';\n";
	echo "var non_booking_days = [".$non_booking_days."];";
	echo "var bookoff_dates = [".$bo_dates."];\n";

	if($params->get('layout') != 0 ){
		echo "var accord_1 = '".$params->get('accord_1')."';\n";
		echo "var accord_2 = '".$params->get('accord_2')."';\n";
		echo "var accord_3 = '".$params->get('accord_3')."';\n";
		echo "var accord_4 = '".$params->get('accord_4')."';\n";
		echo "var accord_5 = '".$params->get('accord_5')."';\n";
		echo "var accord_6 = '".$params->get('accord_6')."';\n";
		echo "var accord_7 = '".$params->get('accord_7')."';\n";

		echo "var accord_1w = '".$params->get('accord_1w')."';\n";
		echo "var accord_2w = '".$params->get('accord_2w')."';\n";
		echo "var accord_3w = '".$params->get('accord_3w')."';\n";
		echo "var accord_4w = '".$params->get('accord_4w')."';\n";
	}
?>

	jQuery.noConflict();

	if(isNaN(myMinDate)){
		myMinDate = 1;
	}	
	if(isNaN(myMaxDate) || myMaxDate == ""){	
		myMaxDate = null;
	}	
	
	jQuery( "#ab_datepicker" ).datepicker({
		inline : true,
		minDate: myMinDate,
		maxDate: myMaxDate,
		beforeShowDay: checkday, 
		onSelect : function(){
			var day1 = jQuery("#ab_datepicker").datepicker('getDate').getDate();                 
			var month1 = jQuery("#ab_datepicker").datepicker('getDate').getMonth() + 1;             
			var year1 = jQuery("#ab_datepicker").datepicker('getDate').getFullYear();
			if(month1 < 10){
				month1 = "0"+month1;
			}
			if(day1 < 10){
				day1 = "0"+day1;
			}
			var fullDate = year1 + "-" + month1 + "-" + day1;
			var iframe = jQuery('<iframe frameborder="0" marginwidth="0" marginheight="0" allowfullscreen></iframe>');
			var dialog = jQuery("<div></div>").append(iframe).appendTo("body").dialog({
				autoOpen: false,
				modal: true,
				resizable: false,
				width: "auto",
				height: "auto",
				close: function () {
					iframe.attr("src", "");
				}			
			});			
			var src = "index.php?option=com_rsappt_pro3&view="+myScreen+"&mystartdate="+fullDate+"&tmpl=component";
			if(myMode != "normal" && myResCatID != "" && !isNaN(myResCatID)){
				switch(myMode){
					case "single_resource":
					  src += "&res="+myResCatID;
					  break;
					case "single_category":
					  src += "&cat="+myResCatID;
					  break;
					}
			}
			src += "&layout="+myLayout;
		<?php if($params->get('layout') != 0 ){ ?>
				src += "&ac_1="+accord_1;
				src += "&ac_2="+accord_2;
				src += "&ac_3="+accord_3;
				src += "&ac_4="+accord_4;
				src += "&ac_5="+accord_5;
				src += "&ac_6="+accord_6;
				src += "&ac_7="+accord_7;
				src += "&ac_1w="+accord_1w;
				src += "&ac_2w="+accord_2w;
				src += "&ac_3w="+accord_3w;
				src += "&ac_4w="+accord_4w;
		<?php } ?>		
			iframe.attr({
				width: <?php echo $popupwidth?>,
				height: <?php echo $popupheight?>,
				src: src
			});
			
			dialog.dialog( "option", "buttons", [ { text: "<?php echo JText::_('SV_POPUP_CLOSE')?>", click: function() { jQuery( this ).dialog( "close" ); } } ] );

			dialog.dialog("option", "title", myTitle).dialog("open");
		} // end of onSelect
		
	});
	
//	jQuery( "#ab_datepicker" ).datepicker( "option", jQuery.datepicker.regional[ "fr" ] );
//	jQuery( "#ab_datepicker" ).datepicker( "option", jQuery.datepicker.regional[ "de" ] );

	function checkday(mydate) {
		tocheck = mydate.getDay();
		if (jQuery.inArray(tocheck, non_booking_days) == -1) {
			// if it is a noraml booking day, check for book-offs
			var add_month_zero = "";
			var add_day_zero = "";
			if((mydate.getMonth() + 1)<10){
				add_month_zero = "0";
			}
			if(mydate.getDate() < 10){
				add_day_zero = "0";
			}
			dmy = mydate.getFullYear() + "-" + add_month_zero + (mydate.getMonth() + 1) + "-" + add_day_zero + mydate.getDate();
			if (jQuery.inArray(dmy, bookoff_dates) == -1) {
				return [true, ""];
			} else {
				return [false, "", ""];
			}
		} else {
			return [false, "", ""];
		}
	}


	</script>

