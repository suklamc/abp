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

	
	jimport( 'joomla.application.helper' );
	JHtml::_('jquery.framework');
	
	$mainframe = JFactory::getApplication();
	$session = JSession::getInstance($handler=null, $options=null);
	$jinput = JFactory::getApplication()->input;

	$option = $jinput->getString( 'option', '' );
	$user = JFactory::getUser();
	$itemId = $jinput->getInt('Itemid');

	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );
	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	require_once( JPATH_CONFIGURATION.DIRECTORY_SEPARATOR.'configuration.php' );
	$CONFIG = new JConfig();
	$timezone_identifier = $CONFIG->offset;
	date_default_timezone_set($timezone_identifier);
	//echo date("Y-m-d H:i:s");

//	jimport( 'joomla.version' );
//	$VERSION = new JVersion();
//	echo "Version:". substr( $VERSION->getShortVersion(), 0, 3 );

	// -----------------------------------------------------------------------
	// see if we need to switch into single-resource or single-category mode.
	$single_resource_mode = false;
	$single_resource_id = "";
	$single_category_mode = false;
	$single_category_id = "";
	$single_service_mode = false;
	$single_service_id = "";
	$default_resource_specified = false;
	$default_resource_id = "";
	$default_category_specified = false;
	$default_category_id = "";
	$single_service_resource = "";
	$res_cats = null;
	$accordion_hover_open = false;
	$service_resource_ids = "";
	
	$params = $mainframe->getParams('com_rsappt_pro3');
	if($params->get('res_or_cat') == 1 && $params->get('passed_id') != ""){
		// single resource mode on, set by menu parameter
		$single_resource_mode = true;
		$single_resource_id = $params->get('passed_id');
		//echo "single resource mode (menu), id=".$single_resource_id;
	}
	
	if($jinput->getInt('res','')!=""){
		// single resource mode on, set by querystring arg
		$single_resource_mode = true;
		$single_resource_id = $jinput->getInt('res','');
		//echo "single resource mode (querystring), id=".$single_resource_id;
	}

	if($params->get('res_or_cat') == 2 && $params->get('passed_id') != ""){
		// single category mode on, set by menu parameter
		$single_category_mode = true;
		$single_category_id = $params->get('passed_id');
		//echo "single category mode (menu), id=".$single_category_id;
	}

	if($jinput->getInt('cat','')!=""){
		// single category mode on, set by querystring arg
		$single_category_mode = true;
		$single_category_id = $jinput->getInt('cat','');
		//echo "single category mode (querystring), id=".$single_category_id;
	}
	
	if($params->get('res_or_cat') == 3 && $params->get('passed_id') != ""){
		// single service mode on, set by menu parameter
		$single_service_mode = true;
		$single_service_id = $params->get('passed_id');
		//echo "single resource mode (menu), id=".$single_resource_id;
	}
	
	if($jinput->getInt('srv','')!=""){
		// single service mode on, set by querystring arg
		// single service overrides all else, it will force single resource
		$single_service_mode = true;
		$single_service_id = $jinput->getInt('srv','');
		//echo "single service mode (querystring), id=".$single_service_id;		
	}

	if($params->get('res_or_cat') == 4 && $params->get('passed_id') != ""){
		// default resource specified, set by menu parameter
		$default_resource_specified = true;
		$default_resource_id = $params->get('passed_id');
		//echo "default resource specified(menu), id=".$default_resource_id;
	}

	if($params->get('res_or_cat') == 5 && $params->get('passed_id') != ""){
		// default category specified, set by menu parameter
		$default_category_specified = true;
		$default_category_id = $params->get('passed_id');
		//echo "default category specified(menu), id=".$default_category_id;
	}

	// get accordion ordering from menu params
	$accordion_sections = array(
		$params->get('accord_1', $jinput->getString('ac_1','accord_res')), // use menu param, OR look for querystring (from iFrames)
		$params->get('accord_2', $jinput->getString('ac_2','accord_basic')),
		$params->get('accord_3', $jinput->getString('ac_3','accord_udfs')),
		$params->get('accord_4', $jinput->getString('ac_4','accord_extras')),
		$params->get('accord_5', $jinput->getString('ac_5','accord_seats')),
		$params->get('accord_6', $jinput->getString('ac_6','accord_grid')),
		$params->get('accord_7', $jinput->getString('ac_7','accord_submit')) );
	//print_r($accordion_sections);

	if($params->get('hover_open') == "Yes"){
		$accordion_hover_open = true;
	}

	// -----------------------------------------------------------------------

	$comment = "";
	$grand_total = "0.00";
	$api_login_id = "";
	$fingerprint = "";
	$amount = "0.00";
	$fp_timestamp = "";
	$fp_sequence = "";	
	// get data for dropdownlist
	$database =JFactory::getDBO(); 

	$andClause = "";

	$required_symbol = "<span style='color:#F00'>*</span>";

	// if single service mode, find resource for the service and set single resource mode as well..
	if($single_service_mode){
		// get resource for the service
//		$sql = 'SELECT resource_id FROM #__sv_apptpro3_services WHERE id_services = '.(int)$single_service_id.' AND published = 1;';
		$sql = 'SELECT resource_scope FROM #__sv_apptpro3_services WHERE id_services = '.(int)$single_service_id.' AND published = 1;';
		try{
			$database->setQuery($sql);
			$single_service_resource = null;
			$single_service_resource = $database -> loadResult();
	//		$single_service_resource = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($single_service_resource != null){
			//$single_resource_mode = true;
			// make a sting for the IN clause
			$temp = str_replace("||",",",$single_service_resource);
			$temp = str_replace("|","",$temp);			
			//echo $temp;
			$service_resource_ids = $temp;
		}
	}
	$res_cats = null;
	if(!$single_resource_mode){
		// get categories
		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_rescats.php";	
	}
	
	// get resources
	if(sv_count_($res_cats) == 0 || $single_resource_mode || $single_service_mode){
		if($user->guest){
			// access must contain '|1|'
			$andClause = " AND access LIKE '%|1|%' ";
		} else {
			$andClause = " AND access != '' ";
		}
		if($single_resource_mode){
			$andClause .= " AND id_resources = ". (int)$single_resource_id;
		} else if($single_service_mode && $service_resource_ids != ""){
			$andClause .= " AND id_resources IN(".$service_resource_ids.")";
		}
		if($single_category_mode){
			$safe_search_string = '%|' . $database->escape( $single_category_id, true ) . '|%' ;
			$andClause .= " AND category_scope LIKE ".$database->quote( $safe_search_string, false );
		}

		if($single_resource_mode){
			$sql = 'SELECT id_resources,name,description,ordering,disable_dates_before,cost,access,gap,ddslick_image_path,ddslick_image_text FROM #__sv_apptpro3_resources WHERE published=1 '.$andClause.' ORDER BY ordering';
		} else {
			$sql = '(SELECT 0 as id_resources, \''.JText::_('RS1_GAD_SCRN_RESOURCE_DROPDOWN').'\' as name, \''.JText::_('RS1_GAD_SCRN_RESOURCE_DROPDOWN').'\' as description, 0 as ordering, "" as cost, "" as access, 0 as gap, "" as ddslick_image_path, "" as ddslick_image_text) '.
			' UNION (SELECT id_resources,name,description,ordering,cost,access,gap,ddslick_image_path,ddslick_image_text FROM #__sv_apptpro3_resources WHERE published=1 '.$andClause.') ORDER BY ordering';
		}		
		try{
			$database->setQuery($sql);
			$res_rows_raw = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}				
		$res_rows_count = 0;
		for($i=0; $i < sv_count_($res_rows_raw ); $i++) {
			if(display_this_resource($res_rows_raw[$i], $user)){
				$res_rows[$res_rows_count] = $res_rows_raw[$i];
				$res_rows_count ++;
			}
		}
		if($res_rows_count == 0){
			// probably specified a non-existent, or unpublished resource or one to which the user is not allowed access.
			echo '<span style="color:red">Setup Error: No Resources, check you have not specified single resource mode with:<br>- a non-exsitent resource<br>- an unpublished resource<br>- a resource to which this user is not allowed access</span>';
		}
		
	}
	
	// get config stuff
	$database =JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$header_text = $params->get('header_text', $jinput->getString('header_text',''));
	if($header_text == ""){
		// use override from menu parameter
		$header_text = $apptpro_config->headerText;
	}
	$footer_text = $params->get('footer_text', $jinput->getString('footer_text',''));
	if($footer_text == ""){
		// use override from menu parameter
		$footer_text = $apptpro_config->footerText;
	}

	$use_gad2 = $params->get('gad_view2', $jinput->getString('gad_view2',''));
	if($use_gad2 == "" || $use_gad2 == "Use_config"){
		// use override from menu parameter
		$use_gad2 = $apptpro_config->use_gad2;
	}
//	if($this->device == "mobile"){
//		// always use gad2 on a mobile device
//		$use_gad2 = "Yes";
//	}
	
	// purge stale paypal bookings
	if($apptpro_config->purge_stale_paypal == "Yes"){
		purgeStalePayPalBookings($apptpro_config->minutes_to_stale);
	}

	$gridstarttime = $apptpro_config->def_gad_grid_start;
	$gridendtime = $apptpro_config->def_gad_grid_end;
	
	// override with menu param
	if($params->get('grid_start')!=""){
	   $gridstarttime = $params->get('grid_start');
		$pos_temp = strpos($gridendtime, ":");	
		if ($pos_temp === false) {
			echo "Warning: Menu setting 'Grid Start Time' is missing it's semi-colon and will be ignored.";
			$gridstarttime = $apptpro_config->def_gad_grid_start;
		}	   
	}
	if($params->get('grid_end')!=""){
	   $gridendtime = $params->get('grid_end');
		$pos_temp = strpos($gridendtime, ":");	
		if ($pos_temp === false) {
			echo "Warning: Menu setting 'Grid End Time' is missing it's semi-colon and will be ignored.";
			$gridendtime = $apptpro_config->def_gad_grid_end;
		}	   
	}

	// override with command line params
	if($jinput->getString('mygridstarttime','')!=""){
	   $gridstarttime = $jinput->getString('mygridstarttime','');
	}
	if($jinput->getString('mygridendtime','')!=""){
	   $gridendtime = $jinput->getString('mygridendtime','');
	}

	date_default_timezone_set($timezone_identifier);
	
	$mindate = "1";
	switch($apptpro_config->gad_grid_start_day){
		case "Today": {
			$grid_date = date("Y-m-d");
			$mindate = 0;
			break;
		}
		case "Tomorrow": {
			$grid_date = date("Y-m-d", strtotime("+1 day"));
			$mindate = 1;
			break;
		}
		case "Monday": {
			if(date("N") == 1){
				$grid_date = date("Y-m-d");
				$mindate = 0;
//			} else if(date("N") == 6 || date("N") == 7 ){
//				// If you are not open weekends and it is saturday or sunday skip to next monday
//				$grid_date = date("Y-m-d", strtotime("next monday"));
			} else {		
				$grid_date = date("Y-m-d", strtotime("previous monday"));
				$now = time(); 
				$spec_date = strtotime($grid_date);
				$datediff = $spec_date - $now;
				$mindate = floor($datediff/(60*60*24))+1;			
			}
			break;
		}
		case "XDays": {
			$grid_date = date("Y-m-d", strtotime("+".strval($apptpro_config->gad_grid_start_day_days)." day"));
			$mindate = $apptpro_config->gad_grid_start_day_days;
			break;
		}
		default: {
			// specific date
			$grid_date = $apptpro_config->gad_grid_start_day;
			$now = time(); 
			$spec_date = strtotime($apptpro_config->gad_grid_start_day);
			$datediff = $spec_date - $now;
			$mindate = floor($datediff/(60*60*24))+1;			
			break;
		}
	}
	
	if($single_resource_mode == true && strpos($res_rows[0]->disable_dates_before, "-")>0){
		$grid_date = $res_rows[0]->disable_dates_before;
	}

	// this overrides the disable-dates-before setting
	// via menu
	if($params->get('grid_date')!=""){
	   $grid_date = $params->get('grid_date');
	}
	// via querystring
	if($jinput->getString('mystartdate','')!=""){
   		$grid_date = $jinput->getString('mystartdate',''); // usage http://....&mystartdate=2009-09-14
	}	
	
	
	$display_picker_date = "";	

	$gridwidth = $apptpro_config->gad_grid_width;//."px";
	$namewidth = $apptpro_config->gad_name_width;//."px";
	$mode = "single_day"; 
	//$mode = "single_resource";
	$griddays = intval($apptpro_config->gad_grid_num_of_days);
	if($griddays < 1){
		$griddays = 7;
	}

	if($params->get('gad_grid_num_of_days') != ""){
		$griddays = intval($params->get('gad_grid_num_of_days'));
	}
	
	if($this->device == "mobile"){
		// mobile show only one day
		$griddays = 1;
	}
		
	$display_grid_date = "";	
	switch ($apptpro_config->date_picker_format) {
		case "yy-mm-dd":
			$display_grid_date = date("Y-m-d", strtotime($grid_date));
			break;
		case "dd-mm-yy":
			$display_grid_date = date("d-m-Y", strtotime($grid_date));
			break;
		case "mm-dd-yy":
			$display_grid_date = date("m-d-Y", strtotime($grid_date));
			break;
		default:	
			$display_grid_date = date("Y-m-d", strtotime($grid_date));
			break;
	}
	$grid_date_floor = $grid_date;
	
	include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_common.php";

/*	// get udfs
	$database =JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 AND udf_show_on_screen="Yes" AND scope = "" AND staff_only != "Yes" ORDER BY ordering';
	try{
		$database->setQuery($sql);
		$udf_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get users
	$sql = 'SELECT id,name FROM #__users order by name';
	try{
		$database->setQuery($sql);
		$user_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get user credit
	$sql = 'SELECT balance FROM #__sv_apptpro3_user_credit WHERE user_id = '.$user->id;
	try{
		$database->setQuery($sql);
		$user_credit = NULL;
		$user_credit = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	
	// check to see if any extras are published, if so show extras line in PayPal totals
	$sql = 'SELECT count(*) as count FROM #__sv_apptpro3_extras WHERE published = 1 AND staff_only != "Yes"';
	try{
		$database->setQuery($sql);
		$extras_row_count = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

*/	
/*	// get resource rates
	$database =JFactory::getDBO(); 
	$sql = 'SELECT id_resources,rate,rate_unit,deposit_amount,deposit_unit,res_user_drag_duration_enable,res_user_drag_duration_snap,res_stripe_pk FROM #__sv_apptpro3_resources';
	try{
		$database->setQuery($sql);
		$res_rates = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	$rateArrayString = "<script type='text/javascript'>".
	"var aryRates = {";
	$base_rate = "0.00";
	for($i=0; $i<sv_count_($res_rates); $i++){
		if($apptpro_config->enable_overrides == "Yes"){
			$base_rate = getOverrideRate("resource", $res_rates[$i]->id_resources, $res_rates[$i]->rate, $user->id, "rate");
		} else {
			$base_rate = $res_rates[$i]->rate;
		}
		$rateArrayString = $rateArrayString.$res_rates[$i]->id_resources.":".$base_rate."";
		if($i<sv_count_($res_rates)-1){
			$rateArrayString = $rateArrayString.",";
		}
	}
	$rateArrayString = $rateArrayString."}</script>";
	
	$rate_unitArrayString = "<script type='text/javascript'>".
	"var aryRateUnits = {";
	for($i=0; $i<sv_count_($res_rates); $i++){
		$rate_unitArrayString = $rate_unitArrayString.$res_rates[$i]->id_resources.":'".$res_rates[$i]->rate_unit."'";
		if($i<sv_count_($res_rates)-1){
			$rate_unitArrayString = $rate_unitArrayString.",";
		}
	}
	$rate_unitArrayString = $rate_unitArrayString."}</script>";

	$depositArrayString = "<script type='text/javascript'>".
	"var aryDeposit = {";
	for($i=0; $i<sv_count_($res_rates); $i++){
		$depositArrayString = $depositArrayString.$res_rates[$i]->id_resources.":'".$res_rates[$i]->deposit_amount."'";
		if($i<sv_count_($res_rates)-1){
			$depositArrayString = $depositArrayString.",";
		}
	}
	$depositArrayString = $depositArrayString."}</script>";

	$deposit_unitArrayString = "<script type='text/javascript'>".
	"var aryDepositUnits = {";
	for($i=0; $i<sv_count_($res_rates); $i++){
		$deposit_unitArrayString = $deposit_unitArrayString.$res_rates[$i]->id_resources.":'".$res_rates[$i]->deposit_unit."'";
		if($i<sv_count_($res_rates)-1){
			$deposit_unitArrayString = $deposit_unitArrayString.",";
		}
	}
	$deposit_unitArrayString = $deposit_unitArrayString."}</script>";

	$res_user_drag_durationArrayString = "<script type='text/javascript'>".
	"var aryUserDragEnable = {";
	for($i=0; $i<sv_count_($res_rates); $i++){
		$res_user_drag_durationArrayString = $res_user_drag_durationArrayString.$res_rates[$i]->id_resources.":'".$res_rates[$i]->res_user_drag_duration_enable."|".$res_rates[$i]->res_user_drag_duration_snap."'";
		if($i<sv_count_($res_rates)-1){
			$res_user_drag_durationArrayString = $res_user_drag_durationArrayString.",";
		}
	}
	$res_user_drag_durationArrayString = $res_user_drag_durationArrayString."}</script>";

	// check to see if Stripe is enabled
	$sql = 'SELECT stripe_enable as count FROM #__sv_apptpro3_stripe_settings';
	try{
		$database->setQuery($sql);
		$stripe_enable = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if($stripe_enable == "Yes"){
		$res_stripePublic_KeysArrayString = "<script type='text/javascript'>".
		"var aryStripePublicKeys = {";
		for($i=0; $i<sv_count_($res_rates); $i++){
			$res_stripePublic_KeysArrayString = $res_stripePublic_KeysArrayString.$res_rates[$i]->id_resources.":'".$res_rates[$i]->res_stripe_pk."'";
			if($i<sv_count_($res_rates)-1){
				$res_stripePublic_KeysArrayString = $res_stripePublic_KeysArrayString.",";
			}
		}
		$res_stripePublic_KeysArrayString = $res_stripePublic_KeysArrayString."}</script>";
	}
	
	if($apptpro_config->clickatell_show_code == "Yes"){
		// get dialing codes
		$database =JFactory::getDBO();
		try{
			$database->setQuery("SELECT * FROM #__sv_apptpro3_dialing_codes ORDER BY country" );
			$dial_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}

*/
	$startdate = JText::_('RS1_INPUT_SCRN_DATE_PROMPT');
	
	$user = JFactory::getUser();
	$name = "";
	$email = "";
	if(!$user->guest){
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE published=1 AND ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count >0){
			$show_admin = true;
		}
		$name = $user->name; 
	
		$email = $user->email;
		// if you want the user's email to be read-only change the above to:
		//$email = $user->email."\" readonly=readonly";

		$user_id = $user->id;

	} else {
		$show_admin = false;
		$user_id = "";
	}	
	$err = "";
	
	$pay_proc_enabled = isPayProcEnabled();
	$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1;';
	try{
		$database->setQuery($sql);
		$pay_procs = NULL;
		$pay_procs = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$udf_help_icon = "<img alt=\"\" src='".getImageSrc("help_udf2.png")."' class='sv_help_icon' ";

	$active_menu = JFactory::getApplication()->getMenu()->getActive();

	// init accordion	
	$block1_title = getAccordionTitle(1, $accordion_sections);
	$block1_codepath = getAccordionCodeblock(1, $accordion_sections, $this->device, $this->layout);
	$block2_title = getAccordionTitle(2, $accordion_sections);
	$block2_codepath = getAccordionCodeblock(2, $accordion_sections, $this->device, $this->layout);
	$block3_title = getAccordionTitle(3, $accordion_sections);
	$block3_codepath = getAccordionCodeblock(3, $accordion_sections, $this->device, $this->layout);
	$block4_title = getAccordionTitle(4, $accordion_sections);
	$block4_codepath = getAccordionCodeblock(4, $accordion_sections, $this->device, $this->layout);
	$block5_title = getAccordionTitle(5, $accordion_sections);
	$block5_codepath = getAccordionCodeblock(5, $accordion_sections, $this->device, $this->layout);
	$block6_title = getAccordionTitle(6, $accordion_sections);
	$block6_codepath = getAccordionCodeblock(6, $accordion_sections, $this->device, $this->layout);
	$block7_title = getAccordionTitle(7, $accordion_sections);
	$block7_codepath = getAccordionCodeblock(7, $accordion_sections, $this->device, $this->layout);

?>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/jquery.validate.min.js"></script>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/ddslick.js"></script>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/iframeResizer.contentWindow.min.js"></script>

<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<?php if($apptpro_config->use_jquery_tooltips == "Yes"){ ?>
		<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/sv_tooltip.css" rel="stylesheet">
		<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/sv_tooltip.js"></script>
<?php } ?>

<?php if($apptpro_config->cart_enable == "Yes" || $apptpro_config->cart_enable == "Public"){ ?>
    <script>
        var iframe = null;
        var cart_dialog = null;
        var cart_title = "<?php echo JText::_('RS1_VIEW_CART_SCRN_TITLE')?>"		
        var cart_close = "<?php echo JText::_('SV_CART_CLOSE')?>"		
    </script>
<?php } ?>

<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>

<script>
	var accordion_hover_open = false;
	<?php if($accordion_hover_open){?>
		accordion_hover_open = true;
	<?php } ?>    
</script>
<?php include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_js1.php"; // some common js ?>

  	<?php echo $rateArrayString; ?>            
    <?php echo $rate_unitArrayString; ?>            
    <?php echo $depositArrayString; ?>            
    <?php echo $deposit_unitArrayString; ?>            
    <?php echo $res_user_drag_durationArrayString; ?>            
    <?php echo $res_stripePublic_KeysArrayString; ?>            

<form name="frmRequest" id="frmRequest" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<div id="sv_apptpro_request_gad<?php echo ($this->device == "mobile"?"_mobile":"")?>">
  <table style="margin:auto; width:100%" <?php echo ($this->device == "mobile"?"align=\"center\"":"")?>>
	<?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "<tr><td colspan='8'><span class='sv_apptpro_errors'>".JText::_('RS1_INPUT_SCRN_LOGIN_REQUIRED')."</span></td></tr>";} ?> 
    <tr>
      <td style="vertical-align:top" ><h3><?php echo JText::_('RS1_INPUT_SCRN_TITLE');//$active_menu->title;?></h3></td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center"><div id="sv_header"><label><?php echo JText::_($header_text); ?></label></div></td>
    </tr>
    <tr><td>
	<?php if($this->layout == "accordion"){ ?>
	      <div id="sv_accordion">
          	  <?php if($block1_codepath != ""){ ?>
	          	  <sv_h3><?php echo $block1_title;?></sv_h3>
					<?php include $block1_codepath;  ?>            
              <?php } ?>  
          	  <?php if($block2_codepath != ""){ ?>
                  <sv_h3><?php echo $block2_title;?></sv_h3>
                    <?php include $block2_codepath;  ?>                          
              <?php } ?>                  
          	  <?php if($block3_codepath != ""){ ?>
                  <sv_h3><?php echo $block3_title;?></sv_h3>
                    <?php include $block3_codepath;  ?> 
              <?php } ?>                  
          	  <?php if($block4_codepath != ""){ ?>
                  <sv_h3><?php echo $block4_title;?></sv_h3>
                    <?php include $block4_codepath;  ?> 
              <?php } ?>                  
          	  <?php if($block5_codepath != ""){ ?>
                  <sv_h3><?php echo $block5_title;?></sv_h3>
                    <?php include $block5_codepath;  ?> 
              <?php } ?>                  
          	  <?php if($block6_codepath != ""){ ?>
                  <sv_h3><?php echo $block6_title;?></sv_h3>
                    <?php include $block6_codepath;  ?>
              <?php } ?>                  
          	  <?php if($block7_codepath != ""){ ?>
              <sv_h3><?php echo $block7_title;?></sv_h3>
				<?php include $block7_codepath;  ?>
              <?php } ?>                  
          </div>    
    <?php } else { ?>
		<?php if($block1_codepath != ""){include $block1_codepath;}?>
		<?php //include $block_selected_slot_codepath;  ?>
        <?php if($block2_codepath != ""){include $block2_codepath;}?>
        <?php if($block3_codepath != ""){include $block3_codepath;}?>
        <?php if($block4_codepath != ""){include $block4_codepath;}?>
        <?php if($block5_codepath != ""){include $block5_codepath;}?>
        <?php if($block6_codepath != ""){include $block6_codepath;}?>
        <!--<hr/>-->
        <?php if($block7_codepath != ""){include $block7_codepath;}?>
    <?php } ?>
	</td></tr>
	<?php
    if($jinput->getInt('frompage','') == "calendar_view"){ ?>
    	<tr><td align="center">
            <input type="button" class="button"  name="cancel" id="btncalreturn" onclick="return doReturntoCalendar();"  
              value="<?php echo JText::_('RS1_CALVIEW_SCRN_CANCEL');?>" title="<?php echo JText::_('RS1_CALVIEW_SCRN_CANCEL_HELP');?>" /> 
        </td></tr>      
    <?php } ?>
  <?php if($apptpro_config->allow_cancellation == 'Yes'){ ?>
	<tr>
    <td align="center">
		<table border="0" class="sv_apptpro_request_cancel_row">
        <tr >
          <td><?php echo JText::_('RS1_INPUT_SCRN_CANCEL_TEXT');?></td>
          <td valign="top"> 
          <input name="cancellation_id" type="text" id="cancellation_id" value="" size="50" maxlength="80" 
          title="<?php echo JText::_('RS1_INPUT_SCRN_CANCEL_TOOLTIP');?>" style="font-size:10px" />
          <input type="button" class="button"  name="btnCancel" onclick="doCancel();" 
          value="<?php echo JText::_('RS1_INPUT_SCRN_CANCEL_BUTTON');?>"
          <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?>></td>
        </tr>
        <tr>
          <td >&nbsp;</td>
          <td valign="top"><div id="cancel_results">      </div></td>
        </tr>
        </table>
	</td>
    </tr>
 <?php } ?>  
    <tr>
      <td style="vertical-align:top; text-align:center"><div id="sv_footer"><label><?php echo JText::_($footer_text) ?></label></div></td>
    </tr>
  </table>

<?php 
	//=========================================================================
//	require_once('recaptchalib.php');
//	$publickey = "..."; // you got this from the signup page
//	echo recaptcha_get_html($publickey);
	//=========================================================================
?>

  </div>
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
	  <span style="font-size:9px; color:#999999">powered by <a href="http://www.AppointmentBookingPro.com" target="_blank">AppointmentBookingPro.com</a> v 4.0.5</span>
  <?php } ?>
  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" id="select_date_text" value="<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>" />
  <input type="hidden" id="beyond_end_of_day" value="<?php echo JText::_('RS1_INPUT_SCRN_BEYOND_EOD');?>" />
  <input type="hidden" id="udf_count" name="udf_count" value="<?php echo sv_count_($udf_rows);?>" />
  <input type="hidden" id="non_pay_booking_button" value="<?php echo $apptpro_config->non_pay_booking_button ?>" />
  <input type="hidden" id="flat_rate_text" name="flat_rate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_RES_FLAT_RATE'); ?>" />			             
  <input type="hidden" id="non_flat_rate_text" name="non_flat_rate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_UNITS'); ?>" />			             
  <input type="hidden" id="ppsubmit" name="ppsubmit" value="" />			             
  <input type="hidden" id="screen_type" name="screen_type" value="gad" />			             
  <input type="hidden" id="reg" name="reg" value="<?php echo ($user->guest?'No':'Yes')?>" />	
  <input type="hidden" id="adjusted_starttime" name="adjusted_starttime" value="" />			             
  <input type="hidden" id="timeFormat" value="<?php echo $apptpro_config->timeFormat ?>" />
  <input type="hidden" id="end_of_day" value="<?php echo $gridendtime ?>" />
  <input type="hidden" id="uc" value="<?php echo $user_credit ?>" />
  <input type="hidden" id="gad2" value="<?php echo $use_gad2 ?>" />

  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" id="controller" name="controller" value="booking_screen_gad" />
	<input type="hidden" name="id" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" id="frompage" name="frompage" value="booking_screen_gad" />
  	<input type="hidden" name="frompage_item" id="frompage_item" value="<?php echo $itemId ?>" />
    
    <input type='hidden' name="x_login" value="<?php echo $api_login_id?>" />
    <input type='hidden' name="x_fp_hash" value="<?php echo $fingerprint?>" />
    <input type='hidden' name="x_amount" value="<?php echo $amount?>" />
    <input type='hidden' name="x_fp_timestamp" value="<?php echo $fp_timestamp?>" />
    <input type='hidden' name="x_fp_sequence" value="<?php echo $fp_sequence?>" />
    <input type='hidden' name="x_version" value="3.1">
    <input type='hidden' name="x_show_form" value="payment_form">
    <input type='hidden' name="x_test_request" value="false" />
    <input type='hidden' name="x_method" value="cc">
	<input type="hidden" name="gad_who_booked" id="gad_who_booked" value="<?php echo $apptpro_config->gad_who_booked; ?>" />

	<input type="hidden" name="preset_service" id="preset_service" value="<?php echo $single_service_id; ?>" />
	<input type="hidden" name="validate_text" id="validate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>" />   
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" /> 
  <input type="hidden" id="enable_payproc" value="<?php echo ($pay_proc_enabled?"Yes":"No")?>" />
	<input type="hidden" name="res_spec_gap" id="res_spec_gap" value="0" /> 
	<input type="hidden" name="gap" id="gap" value="<?php echo $apptpro_config->gap; ?>" /> 
  <?php echo ($apptpro_config->enable_eb_discount=="Yes"?getResourceEBDiscounts():"") ?>
  <?php echo getCategoryDurations(); ?>
	<input type="hidden" name="jit_submit" id="jit_submit" value="<?php echo $apptpro_config->jit_submit; ?>" /> 
	<input type="hidden" name="uc_used" id="uc_used" value="0" /> 
	<input type="hidden" name="gc_used" id="gc_used" value="0" /> 
	<input type="hidden" name="applied_credit" id="applied_credit" value="0" />  
    <input type="hidden" name="grid_date_floor" id="grid_date_floor" value="<?php echo $grid_date_floor ?>" />        
    <input type="hidden" name="device" id="device" value="<?php echo $this->device ?>" />        
 <?php if($this->layout == "accordion"){ ?>
    <input type="hidden" name="accordion_view" id="accordion_view" value="Yes" />        
 <?php } ?>       
 <?php if($this->device == "mobile"){ ?>
		<input type="hidden" name="mobile" id="mobile" value="Yes" />  
 <?php } ?>       
 <?php if($apptpro_config->enable_ddslick == "Yes" && $apptpro_config->expand_timeslots > 0 ){ ?>
	<input type="hidden" id="ddslick_grid_image" value="<?php echo $apptpro_config->expand_timeslots; ?>" /> 
 <?php } ?>
	<input type="hidden" name="preset_service_res_ids" id="preset_service_res_ids" value="<?php echo $service_resource_ids; ?>" />
	<input type="hidden" name="preset_date" id="preset_date" value="<?php echo $startdate; ?>" />
	<input type="hidden" id="user_duration" value="0" />

</form>
<?php 
	if($apptpro_config->enable_notification_list == "Yes" && !$front_desk){
		// must go outside of the main form as it has its own form.
		include_once JPATH_SITE."/components/com_rsappt_pro3/add_to_notification_list.php";
    }
?>	
