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


//			JFactory::getDocument()->setMimeEncoding( 'text/html' );
	JFactory::getDocument()->setMimeEncoding( 'text/html' );
	$jinput = JFactory::getApplication()->input;
	
	// what this module does..
	// recives the user's selected resource and date
	// determine what day the date is
	// select timeslots for that day
	// select bookings for that date & resource
	// return a dataset of timeslot | availability
	// ex:
	//	08:00-09:30 | available
	//	09:30-11:00 | booked
	//	etc
	// OR
	// if caldays=yes, get the available days for a resource
	// if serv=yes, get the services for a resource
	
	
	// recives the user's selected resource and date
	$resource = $jinput->getInt('res');
	$cat = $jinput->getInt('cat',-1);
	$startdate = $jinput->getString('startdate');
	$browser = $jinput->getString('browser');
	$gad = $jinput->getWord('gad');
	$reg = $jinput->getWord('reg', 'No');
	$mobile = $jinput->getWord('mobile', 'No');
	$getcoup = $jinput->getWord('getcoup', 'No');
	$coupon_code = $jinput->getString('cc', '');
	$parent_cat_id = $jinput->getInt('cat', '');
	$service = $jinput->getInt('srv', '');
	$fd = $jinput->getWord('fd', 'No');
	$preset_service = $jinput->getInt('preset_service', '');
	$bk_date = $jinput->getString('bk_date');
	$element_name = $jinput->getWord('el_name', '');
	$getcert = $jinput->getWord('getcert', 'No');
	$gift_cert_code = $jinput->getString('gc', '');
	
	$service_selector = $jinput->getInt('srv_id', '');
	
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		echo JText::_('RS1_SQL_ERROR');
		logIt($e->getMessage(), "getSlots", "", "");
		return false;
	}		

	if($jinput->getString('caldays') == "yes"){
		// ************************************
		// get calendar days for the resource
		// ************************************
		$ret_val = "";
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$resource;
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			$ret_val .= JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		

		// clearDisabledDates added to CalendarPopup.js by rob, not in standard verison
		//$ret_val .= "cal.clearDisabledDates();"; 

		//$ret_val .= "cal.setWeekStartDay(".$apptpro_config->popup_week_start_day.");";
		$ret_val .= "jQuery( \"#".$element_name."\" ).datepicker( \"option\", \"firstDay\", ".$apptpro_config->popup_week_start_day."  );";

		// build list of days to disable on calendar
		$disableDays = "";
		if(	$res_detail->allowSunday=="No" ) $disableDays = $disableDays.("0");
		if(	$res_detail->allowMonday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("1");
		}
		if(	$res_detail->allowTuesday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("2");
		}
		if(	$res_detail->allowWednesday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("3");
		}
		if(	$res_detail->allowThursday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("4");
		}
		if(	$res_detail->allowFriday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("5");
		}
		if(	$res_detail->allowSaturday=="No" ) {
			if( $disableDays != "") $disableDays = $disableDays.",";
			$disableDays = $disableDays.("6");
		}
		
//		$ret_val .= "cal.setDisabledWeekDays(".$disableDays.");";
		$ret_val .= "non_booking_days = [".$disableDays."];";

		// check for book-offs
		$sql = "SELECT * FROM #__sv_apptpro3_bookoffs where resource_id = ".$resource.
		" AND off_date >= CURDATE() AND full_day='Yes' AND Published=1";
		try{
			$database->setQuery($sql);
			$bookoffs = NULL;
			$bookoffs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			$ret_val .= JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}	
		$bo_dates = "";	
		for($i=0; $i < sv_count_($bookoffs ); $i++) {
			$bookoff = $bookoffs[$i];
			//$ret_val .= "cal.addDisabledDates('".$bookoff->off_date."');"; 
			$bo_dates .= "'".$bookoff->off_date."',";
		}
		$ret_val .= "bookoff_dates = [".$bo_dates."];\n";

		$mindate = 999;
		$maxdate = 999;		
		if($res_detail->disable_dates_before != "Tomorrow" AND $res_detail->disable_dates_before != "Today" AND $res_detail->disable_dates_before != "XDays"){
			// use specific date
			// cal function actually disables up to the date, not date before
//			$day = strtotime($res_detail->disable_dates_before);
//			$day = $day - 86400;
//			$ret_val .= "cal.addDisabledDates(null,'".strftime("%Y-%m-%d", $day)."');"; 
			$now = time(); 
			$spec_date = strtotime($res_detail->disable_dates_before);
			$datediff = $spec_date - $now;
			$mindate = floor($datediff/(60*60*24))+1;			
		}
		if($res_detail->disable_dates_before == "XDays"){
//			$ret_val .= "var now = new Date();";
//			$ret_val .= "now.setDate(now.getDate()+".strval($res_detail->disable_dates_before_days).");";  
//			$ret_val .= "cal.addDisabledDates(null,formatDate(now,'yyyy-MM-dd'));"; 
			$mindate = $res_detail->disable_dates_before_days;			
		}
		if($res_detail->disable_dates_before == "Tomorrow"){
//			$ret_val .= "var now = new Date();";
//			$ret_val .= "cal.addDisabledDates(null,formatDate(now,'yyyy-MM-dd'));"; 
			$mindate = 1;
		}
		if($res_detail->disable_dates_before == "Today"){
//			$ret_val .= "var now = new Date();";
//			$ret_val .= "now.setDate(now.getDate()-1);";  
//			$ret_val .= "cal.addDisabledDates(null,formatDate(now,'yyyy-MM-dd'));"; 
			$mindate = 0;			
		}
		$ret_val .= "jQuery( \"#".$element_name."\" ).datepicker( \"option\", \"minDate\", ".$mindate." );";
		
		// set disable after as required
		if($res_detail->disable_dates_after != "Not Set" && $res_detail->disable_dates_after != "XDays"){
//			$day = strtotime($res_detail->disable_dates_after);
//			$day = $day + 86400;
//			$ret_val .= "cal.addDisabledDates('".strftime("%Y-%m-%d", $day)."', null);"; 
			$now = time(); 
			$spec_date = strtotime($res_detail->disable_dates_after);
			$datediff = $spec_date - $now;
			$maxdate = floor($datediff/(60*60*24))+1;						
		}
		if($res_detail->disable_dates_after == "XDays"){
//			$day = strtotime("now");
//			$day = $day + (86400*$res_detail->disable_dates_after_days);
//			$ret_val .= "cal.addDisabledDates('".strftime("%Y-%m-%d", $day)."', null);"; 
			$maxdate = $res_detail->disable_dates_after_days;
		}
		$ret_val .= "jQuery( \"#".$element_name."\" ).datepicker( \"option\", \"maxDate\", ".$maxdate." );";
		
		$ret_val .= "jQuery( \"#display_startdate\" ).val(\"".JText::_('RS1_INPUT_SCRN_DATE_PROMPT')."\");";

		if($res_detail->date_specific_booking == "Yes"){ 
			$ret_val .=	"\ndate_specific_booking = true;\n";
			$sql = "SELECT * FROM #__sv_apptpro3_book_dates WHERE resource_id=".$res_detail->id_resources." AND book_date >= CURDATE() AND Published=1";
			try{
				$database->setQuery($sql);
				$bookdates = NULL;
				$bookdates = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "getSlots", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			$book_dates = "";	
			for($i=0; $i < sv_count_($bookdates ); $i++) {
				$bookdate = $bookdates[$i];
				$book_dates .= "'".$bookdate->book_date."',";
			}
			$ret_val .= "book_dates = [".$book_dates."];\n";
			
		} else {
			$ret_val .=	"\ndate_specific_booking = false;\n";
		}

		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		$data = array(
   			'msg' => $ret_val
	    );
	    echo json_encode( $data );				
		jExit();

		
	} else if($jinput->getString('res') == "yes"){
		// ************************************
		// get resources for a category (or service)
		// ************************************
		$database = JFactory::getDBO(); 
		if($reg=='No'){
			//$andClause = " AND access != 'registered_only' ";
			$andClause = " AND access LIKE '%|1|%' ";
		} else {
			$andClause = " AND access != 'public_only' ";
		}

		// new in 4.0.5
		if($service_selector != "" and $service_selector != 0){
			// A service selector has been passed in, only return resources that have that service assigned
			// Get resources for the service..

			$sql = 'SELECT resource_scope FROM #__sv_apptpro3_services WHERE id_services = '.$service_selector.' AND published = 1;';
			try{
				$database->setQuery($sql);
				$service_selector_resources = null;
				$service_selector_resources = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "getSlots", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			if($service_selector_resources != null){
				// make a sting for the IN clause
				$temp = str_replace("||",",",$service_selector_resources);
				$temp = str_replace("|","",$temp);			
				//echo $temp;
				$service_resource_ids = $temp;
			}
			$andClause .= " AND id_resources IN(".$service_resource_ids.")";
		}		
		
		$user = JFactory::getUser();		
		if($jinput->getString('fd', 'No') == "Yes"){
			// only resources for which user is res admin
			$andClause .= " AND resource_admins LIKE '%|".$user->id."|%' ";
		}
		$res_top_row = ( $gad=="Yes" ? JText::_('RS1_GAD_SCRN_RESOURCE_DROPDOWN'): JText::_('RS1_INPUT_SCRN_RESOURCE_PROMPT'));
		$sql = '(SELECT 0 as id_resources, \''.$res_top_row.'\' as name, \''.
		$res_top_row.'\' as description, 0 as ordering, "" as cost, \'|-1|\' as access, 0 as gap, "" as ddslick_image_path, "" as ddslick_image_text) '.
		'UNION (SELECT id_resources,name,description,ordering,cost,access,gap,ddslick_image_path,ddslick_image_text '.
		'FROM #__sv_apptpro3_resources WHERE published=1 '.$andClause;
		if($cat != ""){
			$safe_search_string = '%|' . $database->escape( $cat, true ) . '|%' ;							
			$sql .= ' AND category_scope LIKE '.$database->quote( $safe_search_string, false );
		}
		$sql .=' ) ORDER BY ordering';		
	//logIt($sql, "getSlots", "", "");
		try{
			$database->setQuery($sql);
			$res_rows = NULL;
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}	
		echo '<select name="resources" id="resources" class="sv_apptpro_request_dropdown" onchange="changeResource()" '.
				($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"") .
	  	    	'title="'.(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_TOOLTIP')).'">';
					$k = 0;
					for($i=0; $i < sv_count_($res_rows ); $i++) {
					$res_row = $res_rows[$i];
						if(display_this_resource($res_row, $user)){					
				          	echo '<option value='.$res_row->id_resources.'>'.JText::_(stripslashes($res_row->name));  echo ($res_row->cost==""?"":" - "); echo JText::_(stripslashes($res_row->cost)).'</option>\n';
						}
          			$k = 1 - $k; 
					} 
        echo '</select>';
		if($apptpro_config->enable_ddslick == "Yes"){
			echo '<select id="resources_slick" >';
			$k = 0;
			for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
			
	            echo '<option value="'.$res_row->id_resources.'"'.
    	            ' data-imagesrc="'.($res_row->ddslick_image_path!=""?getResourceImageURL($res_row->ddslick_image_path):"").'" '.
                    ' data-description="'.$res_row->ddslick_image_text.'"> ';
                echo JText::_(stripslashes($res_row->name)).($res_row->cost==""?"":" - ").JText::_(stripslashes($res_row->cost)).'</option>';
				$k = 1 - $k; 
			 }
            echo '</select>';
      	}
		
	} else if($jinput->getString('getsubcats') == "yes"){
		// ************************************
		// get subcategory for a category
		// ************************************
		$database = JFactory::getDBO(); 
		$user = JFactory::getUser();		

		$andClause = "";		
		if(!$user->guest){
			// logged in user, show categories based on groups	
			$sql = "SELECT group_id FROM #__user_usergroup_map WHERE ".
				"user_id=".$user->id;	
			try{		
				$database->setQuery($sql);
				$ary_my_groups = $database -> loadColumn();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "gad_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}	
			$andClause .= " AND (";
			for ($x=0; $x<sv_count_($ary_my_groups); $x++){
				$safe_search_string = '%|' . $database->escape( $ary_my_groups[$x], true ) . '|%' ;
				$andClause .= ' group_scope LIKE '.$database->quote( $safe_search_string, false );
				if($x < sv_count_($ary_my_groups)-1){
					$andClause .= " OR ";
				}
			}
			$andClause .= ")";	
		} else {
			// not logged in, show only public categories
			$andClause .= " AND  group_scope LIKE '%|1|%' ";
		}

		$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE parent_category = '.$parent_cat_id.' AND published = 1 '.$andClause.' order by ordering';
		//echo $sql;
		try{
			$database->setQuery($sql);
			$res_cats = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		if(sv_count_($res_cats) == 0){
			echo "";
		} else {
			echo "<select name=\"sub_category_id\" id=\"sub_category_id\" class=\"sv_apptpro_request_dropdown\" onchange=\"changeSubCategory('".$fd."');\" ".
			($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"") .
	      	"title=\"".(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_SUB_CATEGORIES_TOOLTIP'))."\" >\n ".
          	"<option value=\"0\">".JText::_('RS1_INPUT_SCRN_RESOURCE_SUB_CATEGORIES_PROMPT')."</option>\n";
			$k = 0;
			for($i=0; $i < sv_count_($res_cats ); $i++) {
				$res_cat = $res_cats[$i];
          		echo "<option value=\"".$res_cat->id_categories."\" >".JText::_(stripslashes($res_cat->name))."</option>\n";
          		$k = 1 - $k; 
			}
        	echo "</select>\n";
			if($apptpro_config->enable_ddslick == "Yes"){
				echo '<select id="sub_category_id_slick" >';
	          	echo "<option value=\"0\">".JText::_('RS1_INPUT_SCRN_RESOURCE_SUB_CATEGORIES_PROMPT')."</option>";
				$k = 0;
				for($i=0; $i < sv_count_($res_cats ); $i++) {
					$res_cat = $res_cats[$i];				
					echo '<option value="'.$res_cat->id_categories.'"'.
						' data-imagesrc="'.($res_cat->ddslick_image_path!=""?getResourceImageURL($res_cat->ddslick_image_path):"").'" '.
						' data-description="'.$res_cat->ddslick_image_text.'"> ';
					echo JText::_(stripslashes($res_cat->name)).'</option>';
					$k = 1 - $k; 
				 }
				echo '</select>';
			}
			
			echo "<div align=\"right\"></div>\n"; 
		}	


	} else if($jinput->getString('serv') == "yes"){
		// ************************************
		// get services for the resource
		// ************************************
		$user_id = $jinput->getString('uid', "");
		if($user_id == "" ){
			$user = JFactory::getUser();
			$user_id = $user->id;		
		}
		$ret_val = "";
			
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_services where published = 1 ';
		if($fd == 'No'){
			$sql .= ' AND staff_only = "No"';
		}
		if($preset_service > 0){
			$sql .= ' AND id_services = '.$preset_service.' ';
		}
		if($cat > -1){
			$safe_search_string = '%|' . $database->escape( $cat, true ) . '|%' ;
			$sql .= ' AND (category_scope = \'\' OR category_scope LIKE '.$database->quote( $safe_search_string, false ).')';
		}
		
		// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
		//$sql .= ' AND resource_id = '.$resource.' ORDER BY ordering' ;
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;
		$sql .= ' AND (resource_scope = \'\' OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).')'; 
		
		$sql .= 'ORDER BY ordering' ;
		//logIt($sql, "getSlots", "", "");
		
		try{
			$database->setQuery($sql);
			$service_rows = NULL;
			$service_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			$ret_val = JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		if(sv_count_($service_rows) == 0){
			$ret_val .= "<input type='hidden' id='has_services' value='no' />";
		} else {
			$ret_val .= '<select name="service_name" id="service_name" class="sv_apptpro_request_dropdown'.($mobile=="Yes"?"_mobile":"").'" onchange="setDuration();calcTotal()"'.
					($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"") .
					'title="'.(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_SERVICE_TOOLTIP')).'">';
						$k = 0;
						//echo '<option value="-1">Select a Service</option>';
						for($i=0; $i < sv_count_($service_rows ); $i++) {
						$service_row = $service_rows[$i];
							if($preset_service != ""){
								$ret_val .= '<option value='.$service_row->id_services.' '.($preset_service==$service_row->id_services?' selected ':'').'>'.JText::_(stripslashes($service_row->name)).'</option>';
							} else {
								$ret_val .= '<option value='.$service_row->id_services.'>'.JText::_(stripslashes($service_row->name)).'</option>';
							}
						$k = 1 - $k; 
						} 
			$ret_val .= '</select>';
			if($apptpro_config->enable_ddslick == "Yes"){
				$ret_val .=  '<select id="service_name_slick" >';
				$k = 0;
				for($i=0; $i < sv_count_($service_rows ); $i++) {
					$service_row = $service_rows[$i];
				
					$ret_val .=  '<option value="'.$service_row->id_services.'"'.
						' data-imagesrc="'.($service_row->ddslick_image_path!=""?getResourceImageURL($service_row->ddslick_image_path):"").'" '.
						' data-description="'.$service_row->ddslick_image_text.'"> ';
					$ret_val .=  JText::_(stripslashes($service_row->name)).'</option>';
					$k = 1 - $k; 
				 }
				$ret_val .=  '</select>';
			}			
		}	
	 			
		// get service rates and durations
		$database = JFactory::getDBO(); 
		$sql = 'SELECT id_services,service_rate,service_rate_unit,service_duration,service_duration_unit,resource_id,'.
		'service_eb_discount,service_eb_discount_unit,service_eb_discount_lead FROM #__sv_apptpro3_services'; //WHERE resource_id = '.$resource;
		// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;
		$sql .= ' WHERE (resource_scope = \'\' OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).')'; 

		try{
			$database->setQuery($sql);
			$service_rates = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			$ret_val = JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		$serviceRatesArrayString = "<input type='hidden' id='service_rates' value='";
		$serviceDurationsArrayString = "<input type='hidden' id='service_durations' value='";
		$serviceEBDiscountArrayString = "<input type='hidden' id='service_eb_discount' value='";
		$base_rate = "0.00";
		for($i=0; $i<sv_count_($service_rates); $i++){
			if($apptpro_config->enable_overrides == "Yes"){
				$base_rate = getOverrideRate("service", $service_rates[$i]->id_services, $service_rates[$i]->service_rate, $user_id, "rate");
			} else {
				$base_rate = $service_rates[$i]->service_rate;
			}
			$serviceRatesArrayString = $serviceRatesArrayString.$service_rates[$i]->id_services.":".$base_rate.":".$service_rates[$i]->service_rate_unit."";
			if($i<sv_count_($service_rates)-1){
				$serviceRatesArrayString = $serviceRatesArrayString.",";
			}

			$serviceDurationsArrayString = $serviceDurationsArrayString.$service_rates[$i]->id_services.":".$service_rates[$i]->service_duration.":".$service_rates[$i]->service_duration_unit."";
			if($i<sv_count_($service_rates)-1){
				$serviceDurationsArrayString = $serviceDurationsArrayString.",";
			}

			if($apptpro_config->enable_eb_discount == "No"){
				$serviceEBDiscountArrayString = $serviceEBDiscountArrayString.$service_rates[$i]->id_services.":0.00:".$service_rates[$i]->service_eb_discount_unit.":".$service_rates[$i]->service_eb_discount_lead."";				
			} else {
				$serviceEBDiscountArrayString = $serviceEBDiscountArrayString.$service_rates[$i]->id_services.":".$service_rates[$i]->service_eb_discount.":".$service_rates[$i]->service_eb_discount_unit.":".$service_rates[$i]->service_eb_discount_lead."";
			}
			if($i<sv_count_($service_rates)-1){
				$serviceEBDiscountArrayString = $serviceEBDiscountArrayString.",";
			}

		}
		$serviceRatesArrayString = $serviceRatesArrayString."'>";
		$ret_val .= $serviceRatesArrayString."\n";
		$serviceDurationsArrayString = $serviceDurationsArrayString."'>";
		$ret_val .= $serviceDurationsArrayString."\n";
		$serviceEBDiscountArrayString = $serviceEBDiscountArrayString."'>";
		$ret_val .= $serviceEBDiscountArrayString."\n";

		echo $ret_val;
		jExit();

	} else if($jinput->getString('res_udfs') == "yes"){
		// ************************************
		// get udfs for the resource
		// ************************************
		$required_symbol = "<span style='color:#F00'> * </span>";
		$udf_help_icon = "<img alt=\"\" src='".getImageSrc("help_udf2.png")."' class='sv_help_icon' ";
//		$out = "<table width='95%' cellpadding='0' cellspacing='0' class='table table-striped'>";
		$out = "<div class=\"sv_table\">\n";
		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}
		$udf_date_picker_format = "";
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$udf_date_picker_format = "yyyy-MM-dd";
				break;
			case "dd-mm-yy":
				$udf_date_picker_format = "dd-MM-yyyy";
				break;
			case "mm-dd-yy":
				$udf_date_picker_format = "MM-dd-yyyy";
				break;
			default:	
				$udf_date_picker_format = "yyyy-MM-dd";
				break;
		}

		$database = JFactory::getDBO(); 
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;							
		$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1'.
		' AND udf_show_on_screen="Yes" '.
		' AND scope LIKE '.$database->quote( $safe_search_string, false ).' ';
//		' AND scope LIKE \'%|'.$resource.'|%\' ';
		if($fd != "Yes"){ 
			$sql .= ' AND staff_only != "Yes" ';
		}		
		$sql .=	' ORDER BY ordering';
		try{
			$database->setQuery($sql);
			$udf_rows = NULL;
			$udf_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		if(sv_count_($udf_rows) == 0){
			echo "";
		} else {
			// these are res specific udfs, there may be global ones above these so we need to adjust the start count
			$sql = 'SELECT count(*) FROM #__sv_apptpro3_udfs WHERE published=1 AND udf_show_on_screen="Yes" ';
			if($fd != "Yes"){ 
				$sql .= ' AND staff_only != "Yes" ';
			}		
			$sql .= ' AND scope = "" ';
			try{
				$database->setQuery($sql);
				$udf_offset = $database -> loadResult();
			} catch (RuntimeException $e) {
				echo JText::_('RS1_SQL_ERROR');
				logIt($e->getMessage(), "getSlots", "", "");
				return false;
			}		

			$k = 0;
			for($i=0; $i < sv_count_($udf_rows ); $i++) {
				$udf_row = $udf_rows[$i];
				$udf_number = $i + intval($udf_offset);
				// if cb_mapping value specified, fetch the cb data
				$user = JFactory::getUser();
				if($user->guest == false and $udf_row->cb_mapping != "" and $jinput->getString('user_field'.$udf_number.'_value', '') == ""){
					$udf_value = getCBdata($udf_row->cb_mapping, $user->id);
				} else if($user->guest == false and $udf_row->profile_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
					$udf_value = getProfiledata($udf_row->profile_mapping, $user->id);
				} else if($user->guest == false and $udf_row->js_mapping != "" and $jinput->getString('user_field'.$udf_number.'_value', '') == ""){
					$udf_value = getJSdata($udf_row->js_mapping, $user->id);
				} else {
					$udf_value = $jinput->getString('user_field'.$udf_number.'_value', '');
				}
					
				$out .= "<div class=\"sv_table_row\">";
				if($mobile=="Yes"){
					$out .= "<div class=\"sv_table_cell_name\"><label id=user_field".$udf_number."_label  class=\"sv_apptpro_request_text\">".JText::_(stripslashes($udf_row->udf_label))."</label>";
				} else {
					$out .= "<div class=\"sv_table_cell_name\"><label id=user_field".$udf_number."_label  class=\"sv_apptpro_request_text\">".JText::_(stripslashes($udf_row->udf_label))."</label></div>".
							"<div class=\"sv_table_cell_value\">";
				}
					if($udf_row->udf_type == "Textbox"){ 
						$out .= "<input name=\"user_field".$udf_number."_value\" id=\"user_field".$udf_number."_value\" type=\"text\" value=\"".$udf_value."\"". 
						"size=\"".$udf_row->udf_size."\" maxlength=\"255\"";
                     				if($udf_row->udf_placeholder_text != ""){$out.=" placeholder='".JText::_($udf_row->udf_placeholder_text)."' ";} 						
						if($udf_row->read_only == "Yes" && $udf_row->cb_mapping != "" && $user->guest == false){$out.=" readonly=\"readonly\" ";}
						$out .= " class=\"sv_apptpro_request_text\" title=\"".JText::_(stripslashes($udf_row->udf_tooltip))."\"/>".
						($udf_row->udf_required == "Yes"?$required_symbol:"").
						"<input type=\"hidden\" name=\"user_field".$udf_number."_is_required\" id=\"user_field".$udf_number."_is_required\" value=\"".$udf_row->udf_required."\" />";
					} else if($udf_row->udf_type == "Textarea"){
						$out .= "<textarea name=\"user_field".$udf_number."_value\" id=\"user_field".$udf_number."_value\""; 
                     				if($udf_row->udf_placeholder_text != ""){$out.=" placeholder='".JText::_($udf_row->udf_placeholder_text)."' ";} 						
						if($udf_row->read_only == "Yes" && $udf_row->cb_mapping != "" && $user->guest == false){$out.=" readonly=\"readonly\" ";}
						$out.=" rows=\"".$udf_row->udf_rows."\" cols=\"".$udf_row->udf_cols."\" ". 
						" class=\"sv_apptpro_request_text\" title=\"".JText::_(stripslashes($udf_row->udf_tooltip))."\"/>".$udf_value."</textarea> ".
						($udf_row->udf_required == "Yes"?$required_symbol:"").
						" <input type=\"hidden\" name=\"user_field".$udf_number."_is_required\" id=\"user_field".$udf_number."_is_required\" value=\"".$udf_row->udf_required."\" />";
					} else if($udf_row->udf_type == "Radio"){ 
						$col_count = 0;
						$aryButtons = explode(",", JText::sprintf("%s",stripslashes($udf_row->udf_radio_options)));
						$out .="<table class='sv_udf_radio_table'><div><td>";
						foreach ($aryButtons as $button){ 
							$col_count++; 
							$out .="<input name=\"user_field".$udf_number."_value\" type=\"radio\" id=\"user_field".$udf_number."_value\""; 
							if(strpos($button, "(d)")>-1){
								$out .=	" checked=checked ";
								$button = str_replace("(d)","", $button);
							}
							$out .= " value=\"".stripslashes(trim($button))."\" title=\"".JText::_(stripslashes($udf_row->udf_tooltip))."\"/> ";
							$out .= "<span class='sv_udf_radio_text'>".JText::_(stripslashes(trim($button)))."</span>";
                            if($col_count >= $udf_row->udf_cols){$col_count = 0; $out .= "</td></div><tr><td>";}else{$out .= "</td><td>";}
							//JText::_(stripslashes(trim($button)))."<br /> ";
						}
                        $out .="</tr></table>";
						$out .= ($udf_row->udf_required == "Yes"?$required_symbol:"");
						$out .= " <input type=\"hidden\" name=\"user_field".$udf_number."_is_required\" id=\"user_field".$udf_number."_is_required\" value=\"".$udf_row->udf_required."\" />";
					} else if($udf_row->udf_type == "List"){ 
							$aryOptions = explode(",", JText::sprintf("%s",stripslashes($udf_row->udf_radio_options)));
							$out .= " <select name=\"user_field".$udf_number."_value\" id=\"user_field".$udf_number."_value\" class=\"sv_apptpro_request_dropdown\" ".
							"title=\"".(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_(stripslashes($udf_row->udf_tooltip)))."\"> "; 
							foreach ($aryOptions as $option){
								$out .= "<option value=\"".$option."\"";
								if(strpos($option, "(d)")>-1){
									$out .= " selected=true ";
									$option = str_replace("(d)","", $option);
								}
								$out .= ">".JText::_(stripslashes($option))."</option>";
							}              
							$out .= "</select>";                 
					} else if($udf_row->udf_type == 'Date'){
						$out .= "<label>Sorry, 'Date' UDFs are not supported for use as <b>resource specific</b> UDFs!</label>";
					} else if($udf_row->udf_type == 'Content'){ 
	                    $out .= "<label>".JText::_($udf_row->udf_content)."</label>";
                    	$out .= "<input type=\"hidden\" name=\"user_field".$udf_number."_value\" id=\"user_field".$udf_number."_value\" value=\"".JText::_(htmlentities($udf_row->udf_content, ENT_QUOTES, "UTF-8"))."\"> ";
	   					$out .= "<input type=\"hidden\" name=\"user_field".$udf_number."_type\" id=\"user_field".$udf_number."_type\" value='Content'> ";
					} else {
						$out .= "<input name=\"user_field".$udf_number."_value\" id=\"user_field".$udf_number."_value\" type=\"checkbox\" value=\"Checked\" ".
						" title=\"".JText::_(stripslashes($udf_row->udf_tooltip))."\"/>".
						" <input type=\"hidden\" name=\"user_field".$udf_number."_is_required\" id=\"user_field".$udf_number."_is_required\" ".
						" value=\"".$udf_row->udf_required."\" /> ";
					}    
					$out .= " <input type=\"hidden\" name=\"user_field".$udf_number."_udf_id\" id=\"user_field".$udf_number."_udf_id\" ".
					"value=\"".$udf_row->id_udfs."\" /> ";

					if($udf_row->udf_help != "" && $udf_row->udf_help_as_icon == "Yes" ){      
						//$out.= $udf_help_icon." title='".JText::_(stripslashes($udf_row->udf_help))."'>";
						$out .= $udf_help_icon." id='opener".$udf_number."' title='".JText::_('RS1_INPUT_SCRN_CLICK_FOR_HELP')."'>";		
						$out .= "<div id=\"udf_help".$udf_number."\" title=\"".JText::_(stripslashes($udf_row->udf_label))."\">".JText::_(stripslashes($udf_row->udf_help))."</div>";	
							$out .= "<script>";
							$out .= "jQuery( \"#udf_help".$udf_number."\" ).dialog({ autoOpen: false, ";
							$out .= "  position:{";
							$out .= "    my: \"left+10 top+5\",";
							$out .= "    of: \"#opener".$udf_number."\",";
							$out .= "    collision: \"fit\"";
							$out .= "  }";
							$out .= "});";							
							$out .= "jQuery( \"#opener".$udf_number."\" ).click(function() { ";
							$out .= "   jQuery( \"#udf_help".$udf_number."\" ).dialog( \"open\" );";
							if($udf_row->udf_help_format == "Link"){					
								$out .= "jQuery( \"#udf_help".$udf_number."\" ).load(\"".JText::_(stripslashes($udf_row->udf_help))."\", function() {});";
							}
							$out .= "});";	
							$out .= "</script>";
					} 	
					
				$out .= "</div>".	
				"</div>";
            	if($udf_row->udf_help_as_icon == "No" && $udf_row->udf_help != ""){ 				
					$out .=	"<div class=\"sv_table_row\">".
						"<div class=\"sv_table_cell_name\"></div><div class=\"sv_table_cell_value\" class=\"sv_apptpro_request_helptext\">".JText::_(stripslashes($udf_row->udf_help))."</div>".
					"</div>";
				}
				$k = 1 - $k; 
			}	
	 	}
		if($out == "<div class=\"sv_table\">\n"){
			$out="";
		} else {
			$out .= "</div>";
			$out .= "<input type=\"hidden\" id=\"res_udf_count\" name=\"res_udf_count\" value=\"".sv_count_($udf_rows)."\" />";
		}
		echo $out;				
		jExit();

	} else if($jinput->getString('res_seats') == "yes"){
		// ************************************
		// get seat types for the resource
		// ************************************
		$user_id = $jinput->getInt('uid', "");
		if($user_id == "" ){
			$user = JFactory::getUser();
			$user_id = $user->id;		
		}
		$out = "<div class=\"sv_table\">\n";

		// get seat types
		$database = JFactory::getDBO(); 
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;							
		$sql = 'SELECT * FROM #__sv_apptpro3_seat_types WHERE published=1 AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';
		try{
			$database->setQuery($sql);
			$seat_type_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		$si = 0; 
		$base_rate = "0.00";
		$user = JFactory::getUser();		
		foreach($seat_type_rows as $seat_type_row){
			if($apptpro_config->enable_overrides == "Yes"){
				$base_rate = getOverrideRate("seat", $seat_type_row->id_seat_types, $seat_type_row->seat_type_cost, $user_id, "rate");
			} else {
				$base_rate = $seat_type_row->seat_type_cost;
			}
			if($mobile=="Yes"){			
				$out .= "<div class=\"sv_table_row\"> \n".
						"<div class=\"sv_table_cell_name\"><label class=\"sv_apptpro_request_label\">".JText::_($seat_type_row->seat_type_label)."</label></div>\n".
					"</div> \n".	
					"<div class=\"sv_table_row\"> \n".
						"<div class=\"sv_table_cell_value\">\n".
							"<select name=\"seat_".$si."\" id=\"seat_".$si."\" onChange=\"calcTotal();\" class=\"sv_apptpro_request_dropdown\" ". 
							"title=\"".(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_($seat_type_row->seat_type_tooltip))."\" style=\"width:auto; min-width:50px; text-align:center\"/>\n";
							for($i=$seat_type_row->default_quantity; $i<=$seat_type_row->seat_group_max; $i++){ 
								$out .=	"<option value=".$i.">".$i."</option>\n";	        
							}
						   $out .= "</select>\n". 
						"</div> \n".											   
					"</div> \n".											   
					"<div class=\"sv_table_row\"> \n".
						"<div class=\"sv_table_cell_value\" style=\"width:100%; padding-bottom:10px\" >\n".
							"&nbsp;".JText::_($seat_type_row->seat_type_help)." \n".
						"</div> \n".											   
					"</div> \n".											   
					"<input type=\"hidden\" name=\"seat_type_cost_".$si."\" id=\"seat_type_cost_".$si."\" value=\"".$base_rate."\"/>\n".  
					"<input type=\"hidden\" name=\"seat_type_id_".$si."\" id=\"seat_type_id_".$si."\" value=\"".$seat_type_row->id_seat_types."\"/>\n".  
					"<input type=\"hidden\" name=\"seat_group_".$si."\" id=\"seat_group_".$si."\" value=\"".$seat_type_row->seat_group."\"/>\n".  
				"\n";
			} else {
				$out .= "<div class=\"sv_table_row\"  class=\"seats_block\"> \n".
					"<div class=\"sv_table_cell_name\"><label class=\"sv_apptpro_request_label\">".JText::_($seat_type_row->seat_type_label)."</label></div>\n".
					"<div class=\"sv_table_cell_value\">\n".
					"<select name=\"seat_".$si."\" id=\"seat_".$si."\" onChange=\"calcTotal();\" class=\"sv_apptpro_request_dropdown\" ". 
					"title=\"".(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_($seat_type_row->seat_type_tooltip))."\" style=\"width:auto; min-width:50px; text-align:center\"/>\n";
					for($i=$seat_type_row->default_quantity; $i<=$seat_type_row->seat_group_max; $i++){ 
						$out .=	"<option value=".$i.">".$i."</option>\n";	        
					}
				   $out .= "</select>\n". 
					"&nbsp;".JText::_($seat_type_row->seat_type_help)." \n".
					"<input type=\"hidden\" name=\"seat_type_cost_".$si."\" id=\"seat_type_cost_".$si."\" value=\"".$base_rate."\"/>\n".  
					"<input type=\"hidden\" name=\"seat_type_id_".$si."\" id=\"seat_type_id_".$si."\" value=\"".$seat_type_row->id_seat_types."\"/>\n".  
					"<input type=\"hidden\" name=\"seat_group_".$si."\" id=\"seat_group_".$si."\" value=\"".$seat_type_row->seat_group."\"/>\n".  
				  " </div>\n".
				"</div>\n";
			}
			$si += 1; 
		} 
		if($si>0){  
			$out .= "</div>\n";
			$out .= "<div class=\"sv_table\">\n";
			$out .= "<div class=\"sv_table_row\">\n".
			 	"<div class=\"sv_table_cell_name\"><label class=\"sv_apptpro_request_label\">".JText::_('RS1_INPUT_SCRN_TOTAL_SEATS').":</label></div>\n".
			 	"<div class=\"sv_table_cell_value sv_apptpro_request_label \" id=\"booked_seats_div\" name=\"booked_seats_div\" style=\"text-align:left\" \"></div>\n".
			 	"<input type=\"hidden\" name=\"booked_seats\" id=\"booked_seats\" value=\"1\"/> \n".
				"</div>\n";
			$out .= "</div>\n";
		}

		if($out == "<div class=\"sv_table\">\n"){
			$out="";
		} else {
			$out .= "<input type=\"hidden\" name=\"seat_type_count\" id=\"seat_type_count\" value=\"".sv_count_($seat_type_rows)."\">\n";
		}
		echo $out;				
		jExit();
		

	} else if($jinput->getString('extras') == "yes"){
		// ************************************
		// get extras for the resource
		// ************************************
		$user_id = $jinput->getInt('uid', "");
		if($user_id == "" ){
			try{
				$user = JFactory::getUser();
				$user_id = $user->id;		
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "getSlots, get extras, get user", "", "");
				echo json_encode("Error on getting user id");
				jExit();
			}			
		}

		$out = "<div class=\"sv_table\">\n";

		$database = JFactory::getDBO(); 
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;							
		$sql = 'SELECT * FROM #__sv_apptpro3_extras WHERE published=1 ';
		if($fd == 'No'){
			$sql .= ' AND staff_only = "No" ';
		}
		$sql .= 'AND (resource_scope = "" OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).' ';
		$sql .= ") ORDER BY ordering";
		try{
			$database->setQuery($sql);
			$extras_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			jExit();
		}		

		$si = 0; 
		if(sv_count_($extras_rows)>0){  
//			if($mobile=="Yes"){
//				$out .= "<div class=\"sv_table_row\" \"extras_block\">\n".
//				  "<div class=\"sv_table_cell_value\">".JText::_('RS1_INPUT_SCRN_EXTRAS_LABEL')."\n</div>\n".
//				"</div>\n";
//			} else {
//				$out .= "<div class=\"sv_table_row\" \"extras_block\">\n".
//				  "<div class=\"sv_table_cell_name\">".JText::_('RS1_INPUT_SCRN_EXTRAS_LABEL')."</div>\n".
//				  "<div class=\"sv_table_cell_value\"></div>\n".
//				"</div>\n";
//			}
			$base_rate = "0.00";
			$user = JFactory::getUser();		
			foreach($extras_rows as $extras_row){
				if($apptpro_config->enable_overrides == "Yes"){
					$base_rate = getOverrideRate("extra", $extras_row->id_extras, $extras_row->extras_cost, $user_id, "rate");
				} else {
					$base_rate = $extras_row->extras_cost;
				}
				if($mobile=="Yes"){
					$out .= "<div class=\"sv_table_row\" class=\"extras_block\"> \n".
						"<div class=\"sv_table_cell_value\" style=\"padding-top:10px;\"><div id=\"extras_label_".$si."\"><label class=\"sv_apptpro_request_label\">".JText::_($extras_row->extras_label)."</label></div></div>\n".
						"</div>";
				} else {
					$out .= "<div class=\"sv_table_row\" class=\"extras_block\"> \n".
						"<div class=\"sv_table_cell_name\"><div id=\"extras_label_".$si."\"><label class=\"sv_apptpro_request_label\">".JText::_($extras_row->extras_label)."</label></div></div>\n".
						"<div class=\"sv_table_cell_value\">\n";
				}
					if($extras_row->max_quantity == 1){
						// display as checkbox
						$out .= "<input type=\"checkbox\"  name=\"extra_".$si."\" id=\"extra_".$si."\" onChange=\"changeExtra();\" ". 
						($extras_row->default_quantity==1?" checked ":"").
						"title='".JText::_($extras_row->extras_tooltip)."'  />\n";
						
					} else {
						// display as dropdown list
						$out .= "<select name=\"extra_".$si."\" id=\"extra_".$si."\" onChange=\"changeExtra();\" class=\"sv_apptpro_request_dropdown\" ". 
						"title='".(blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_($extras_row->extras_tooltip))."' style=\"width:auto; min-width:50px; text-align:center\" />\n";
						for($i=$extras_row->min_quantity; $i<=$extras_row->max_quantity; $i++){ 
							$out .=	"<option value=".$i.($i==$extras_row->default_quantity?" selected":"").">".$i."</option>\n";	        
						}
					   $out .= "</select>\n";
					}
					if($mobile=="Yes"){							
						$out .= "<div class=\"sv_table_row\" class=\"extras_block\" > \n";
					}
					$out .= "&nbsp;<span id=extras_help_".$si." >".JText::_($extras_row->extras_help)." </span>\n";
					if($mobile=="Yes"){							
						"</div>";
					}
					$out .= "<input type=\"hidden\" name=\"extras_cost_".$si."\" id=\"extras_cost_".$si."\" value=\"".$base_rate."\"/>\n".  
					"<input type=\"hidden\" name=\"extras_cost_unit_".$si."\" id=\"extras_cost_unit_".$si."\" value=\"".$extras_row->cost_unit."\"/>\n".  
					"<input type=\"hidden\" name=\"extras_id_".$si."\" id=\"extras_id_".$si."\" value=\"".$extras_row->id_extras."\"/>\n".  
					"<input type=\"hidden\" name=\"extras_duration_".$si."\" id=\"extras_duration_".$si."\" value=\"".$extras_row->extras_duration."\"/>\n".  
					"<input type=\"hidden\" name=\"extras_duration_unit_".$si."\" id=\"extras_duration_unit_".$si."\" value=\"".$extras_row->extras_duration_unit."\"/>\n".  
					"<input type=\"hidden\" name=\"extras_duration_effect_".$si."\" id=\"extras_duration_effect_".$si."\" value=\"".$extras_row->extras_duration_effect."\"/>\n".  
				  " </div>\n".
				"</div>\n";
				$si += 1; 
			} 
	
			if($out == "<div class=\"sv_table\">\n"){
				$out="";
			} else {
				$out .= "</div>\n";
			}
			$out .= "<input type=\"hidden\" name=\"extras_count\" id=\"extras_count\" value=\"".sv_count_($extras_rows)."\">\n";
		    echo $out;				
		}
		jExit();


	} else if($jinput->getString('adminserv') == "yes"){
		// ************************************
		// get services for the resource (admin side)
		// ************************************
	
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_services where published = 1 '; //AND resource_id = '.$resource;

		// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
		//$sql .= ' AND resource_id = '.$resource.' ORDER BY ordering' ;
		$safe_search_string = '%|' . $database->escape( $resource, true ) . '|%' ;
		$sql .= ' AND (resource_scope = \'\' OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).')'; 

		$database->setQuery($sql);
		try{
			$service_rows = NULL;
			$service_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		if(sv_count_($service_rows) > 0){
			// new Option(text, value, defaultSelected, selected)
			$k = 0;
			for($i=0; $i < sv_count_($service_rows ); $i++) {
				$service_row = $service_rows[$i];
					echo 'document.getElementById("service").options['.$i.']=new Option("'.stripslashes($service_row->name).'", "'.$service_row->id_services.'", false, false);';
				$k = 1 - $k; 
			} 
		}	
		exit;

	} else if($jinput->getString('getcoup') == "yes"){
		// ************************************
		// get coupon details
		// ************************************
		// also in json
		
		// To make coupon code case insensitive remove the BINARY from the three(3) queries below.
		$database = JFactory::getDBO(); 
		$sql = "SELECT *, DATE_FORMAT(expiry_date, '%Y-%m-%d') as expiry FROM #__sv_apptpro3_coupons where BINARY coupon_code = '".$coupon_code."' and published=1";
		try{
			$database->setQuery($sql);
			$coupon_detail = NULL;
			$coupon_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		$coupon_refused = false;
		
		// check scope
		if($coupon_detail != NULL && $coupon_detail->scope != ""){
			// one or more resources hae been specified
			if(strpos($coupon_detail->scope, '|'.$resource.'|') === false){
				// coupon not valis for this resource
				echo JText::_('RS1_INPUT_SCRN_COUPON_INVALID_4_RESOURCE')."|0|";
				$coupon_refused = true;
			}				 			
		}
		if($coupon_detail == NULL){
			echo JText::_('RS1_INPUT_SCRN_COUPON_INVALID')."|0|";
			$coupon_refused = true;
		} else if(!strncmp($coupon_detail->expiry, "0000-00-00", 10) != "0000-00-00" && strtotime("now") > strtotime($coupon_detail->expiry)){
			echo JText::_('RS1_INPUT_SCRN_COUPON_EXPIRED')."|0|";
			$coupon_refused = true;
		} else if($coupon_detail->valid_range_start != "" && $coupon_detail->valid_range_start != "0000-00-00" && strtotime($bk_date) < strtotime($coupon_detail->valid_range_start)){
				echo JText::_('RS1_INPUT_SCRN_COUPON_NOT_IN_RANGE')."|0|";
				$coupon_refused = true;
		} else if($coupon_detail->valid_range_end != "" && $coupon_detail->valid_range_end != "0000-00-00" && strtotime($bk_date) > strtotime($coupon_detail->valid_range_end)){
				echo JText::_('RS1_INPUT_SCRN_COUPON_NOT_IN_RANGE')."|0|";
				$coupon_refused = true;
		} else {		
			// Check for Max Total Usage
			if($coupon_detail->max_total_use > 0){
				// get total useage count
				$sql = "SELECT count(*) FROM #__sv_apptpro3_requests WHERE BINARY coupon_code = '".$coupon_code."' ".
					" AND (".
					"	request_status = 'accepted' ".
					" 	OR request_status = 'attended' ".
					" 	OR request_status = 'completed' ".
					")";
				try{
					$database->setQuery($sql);
					$coupon_count = NULL;
					$coupon_count = $database -> loadResult();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
				if($coupon_count >= $coupon_detail->max_total_use){
					echo JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."|0|";
					$coupon_refused = true;
				}
			}		

			// Check for Max User Usage
			$user = JFactory::getUser();
			if($coupon_detail->max_user_use > 0 and $user->guest == false){
				if($jinput->getString('uid',"-1") != "-1"){
					// call is from the staff booking screen so we check the user_id passed in rather than than operator.
					$user_to_check = $jinput->getInt('uid', -1);
				} else {
					$user_to_check = $user->id;
				}
				// get total useage count
				$sql = "SELECT count(*) FROM #__sv_apptpro3_requests WHERE BINARY coupon_code = '".$coupon_code."' ".
					" AND user_id = ".$user_to_check." ".
					" AND (".
					"	request_status = 'accepted' ".
					" 	OR request_status = 'attended' ".
					" 	OR request_status = 'completed' ".
					")";
				try{
					$database->setQuery($sql);
					$coupon_count = NULL;
					$coupon_count = $database -> loadResult();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
				if($coupon_count >= $coupon_detail->max_user_use){
					echo JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."|0|";
					$coupon_refused = true;
				}
			}		
			
		}
					
		if($coupon_refused == false){
			echo JText::_($coupon_detail->description)."|".$coupon_detail->discount."|".$coupon_detail->discount_unit;
		}
		exit;

	} else if($jinput->getString('getcert') == "yes"){
		// ************************************
		// get gift certificate balance
		// ************************************
		// not yet..also in json
		
		// To make coupon code case insensitive remove the BINARY from the three(3) queries below.
		$database = JFactory::getDBO(); 
		$sql = "SELECT balance, gift_cert_name FROM #__sv_apptpro3_user_credit where BINARY gift_cert = '".$gift_cert_code."' ";//and published=1";
		try{
			$database->setQuery($sql);
			$gift_cert_detail = NULL;
			$gift_cert_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		
		if($gift_cert_detail == NULL){
			echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_INVALID')."|-1|";
		} else {
			echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_BALANCE')."|".$gift_cert_detail->balance."|";
		}
		exit;

	} else {
		// ************************************
		// get slots
		// ************************************
		
		// Moved to funtion getSlots() in getSlots2.php so that it can be called by calview
		
		include_once( JPATH_SITE."/components/com_rsappt_pro3/getSlots2.php" );
		
		$ret_val = getSlots($resource, $startdate, 0);
		
		
		JFactory::getDocument()->setMimeEncoding( 'text/html' );
	    echo $ret_val;				
		jExit();

	}

?>