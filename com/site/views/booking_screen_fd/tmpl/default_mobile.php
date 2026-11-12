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

	$comment = "";
	$grand_total = "0.00";
	$api_login_id = "";
	$fingerprint = "";
	$amount = "0.00";

		$showform= true;
		$database = JFactory::getDBO();
		
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
			$showform = false;
		}	
		if($user->guest){
			// session timeout
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
			$showform = false;
		}
		
	// -----------------------------------------------------------------------
	// see if we need to switch into single-resource or single-category mode.
	$single_resource_mode = false;
	$single_resource_id = "";
	$single_category_mode = false;
	$single_category_id = "";
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
	
	// -----------------------------------------------------------------------
	// get data for dropdownlist
	$database = JFactory::getDBO(); 

	$andClause = "";
	$required_symbol = "<span style='color:#F00'>*</span>";
	if(!$single_resource_mode){
		// get resource categories
		$database = JFactory::getDBO(); 
		if($single_category_mode){
			$andClause .= " AND id_categories = ". (int)$single_category_id;
		} else {
			$andClause .= " AND (parent_category IS NULL OR parent_category = '') ";
		}	
//		$sql = 'SELECT DISTINCT #__sv_apptpro3_categories.* FROM #__sv_apptpro3_categories INNER JOIN #__sv_apptpro3_resources '.
//			' ON #__sv_apptpro3_categories.id_categories = #__sv_apptpro3_resources.category_id '.
//			' WHERE #__sv_apptpro3_categories.published = 1 AND #__sv_apptpro3_resources.published = 1 '.
//			' AND #__sv_apptpro3_resources.resource_admins LIKE \'%|'.$user->id.'|%\' '.

		// With the switch to category_scope for multiple cats per resource, we have lost the one-to-on relationship of resource to category.
		// To get the catgories for resources that the operator is res-admin for, we need two steps now.
		// First get all the category_scope valuse for resources that the operator is res-admin

//!! problem: (In the front-end only) As soon as operator has no resources in a category, the category disappraes so if they only had one 
// and accidently change it, the category will disappear. 
// Work around for now is show ALL categories. 
		$sql = "SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 Order By ordering ";
//If you want different uncomment the code below.
//		$sql1 = "SELECT category_scope FROM #__sv_apptpro3_resources ".
//			" WHERE resource_admins LIKE '%|".$user->id."|%' AND category_scope != ''";
//		//echo $sql1;
//		$database->setQuery($sql1);
//		$cat_scopes = $database -> loadObjectList();
//		// create a single string with all
//		$master_cat_scope = "";
//		for($i=0; $i < sv_count_($cat_scopes ); $i++) {
//			$tmp = str_replace("||",",",$cat_scopes[$i]->category_scope);
//			$master_cat_scope .= str_replace("|","",$tmp);
//			if($i+1 < sv_count_($cat_scopes )){
//				$master_cat_scope .=",";
//			}
//		}	
//		//echo $master_cat_scope;
//		$sql = 'SELECT DISTINCT * FROM #__sv_apptpro3_categories '.
//			' WHERE id_categories IN('.$master_cat_scope.')' .
//		
//		$andClause.' order by #__sv_apptpro3_categories.ordering';
		try{
			$database->setQuery($sql);
			$res_cats = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// check for sub-categories
		$sql = 'SELECT count(*) as count FROM #__sv_apptpro3_categories WHERE published = 1 AND (parent_category IS NOT NULL AND parent_category != "") ';
		try{
			$database->setQuery($sql);
			$sub_cat_count = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
	}
	
	// get resources
	if(sv_count_($res_cats) == 0 || $single_resource_mode){
		if($user->guest){
			// access must contain '|1|'
			$andClause = " AND access LIKE '%|1|%' ";
		} else {
			$andClause = " AND access != '' ";
		}
		if($single_resource_mode){
			$andClause .= " AND id_resources = ". (int)$single_resource_id;
		}
		if($single_category_mode){
			$safe_search_string = '%|' . $database->escape( $cat, true ) . '|%' ;
			$andClause .= " AND category_scope LIKE ".$database->quote( $safe_search_string, false );
		}

		// only resources for which user is res admin
		$andClause .= " AND resource_admins LIKE '%|".$user->id."|%' ";
		
		$sql = '(SELECT 0 as id_resources, \''.JText::_('RS1_INPUT_SCRN_RESOURCE_PROMPT').'\' as name, \''.JText::_('RS1_INPUT_SCRN_RESOURCE_PROMPT').'\' as description, 0 as ordering, "" as cost, 0 as gap, "" as ddslick_image_path, "" as ddslick_image_text) UNION (SELECT id_resources,name,description,ordering,cost,gap,ddslick_image_path,ddslick_image_text FROM #__sv_apptpro3_resources WHERE published=1 '.$andClause.') ORDER BY ordering';

		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}
	
	// get config stuff
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	
	// purge stale paypal bookings
	if($apptpro_config->purge_stale_paypal == "Yes"){
		purgeStalePayPalBookings($apptpro_config->minutes_to_stale);
	}

	$gridstarttime = $apptpro_config->def_gad_grid_start;
	$gridendtime = $apptpro_config->def_gad_grid_end;
	
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

	// this overrides the disable-dates-before setting
	if($jinput->getString('mystartdate','')!=""){
   		$grid_date = $jinput->getString('mystartdate',''); // usage http://....&mystartdate=2009-09-14
	}	
	
	$display_picker_date = "";	
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
	
	$gridwidth = $apptpro_config->gad_grid_width;//."px";
	$namewidth = $apptpro_config->gad_name_width;//."px";
	$mode = "single_day"; 
	//$mode = "single_resource";
	
//	$griddays = intval($apptpro_config->gad_grid_num_of_days);
//	if($griddays < 1){
//		$griddays = 7;
//	}
	// mobile show only one day
	$griddays = 1;
	

	// get udfs
	$database = JFactory::getDBO(); 
//	$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 AND scope = "" ORDER BY ordering';
	$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 AND udf_show_on_screen="Yes" AND scope = "" ORDER BY ordering';
	try{
		$database->setQuery($sql);
		$udf_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get users
	$sql = 'SELECT id,name,username FROM #__users WHERE block = 0 order by name';
	try{
		$database->setQuery($sql);
		$user_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// this will be picked up in fe_fetch when operator select a uuser
	$user_credit = NULL;

	// check to see if any extras are published, if so show extras line in PayPal totals
	$sql = 'SELECT count(*) as count FROM #__sv_apptpro3_extras WHERE published = 1';
	try{
		$database->setQuery($sql);
		$extras_row_count = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	
	// get resource rates
	$database = JFactory::getDBO(); 
	$sql = 'SELECT id_resources,rate,rate_unit,deposit_amount,deposit_unit,res_user_drag_duration_enable,res_user_drag_duration_snap FROM #__sv_apptpro3_resources';
	try{
		$database->setQuery($sql);
		$res_rates = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
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

	if($apptpro_config->clickatell_show_code == "Yes"){
		// get dialing codes
		$database = JFactory::getDBO();
		try{
			$database->setQuery("SELECT * FROM #__sv_apptpro3_dialing_codes ORDER BY country" );
			$dial_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}
	
	$startdate = JText::_('RS1_INPUT_SCRN_DATE_PROMPT');

	$name = "";
	$email = "";

	$user = JFactory::getUser();
	if(!$user->guest){
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE published=1 AND ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "bs_fd_tmpl_default_mobile", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count >0){
			$show_admin = true;
		}
//		$name = $user->name; 
//		$email = $user->email;
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
	
	// if cb_mapping value specified, fetch the cb data
	if($user->guest == false and $apptpro_config->phone_cb_mapping != "" and $jinput->getString('phone', '') == ""){
		$phone = getCBdata($apptpro_config->phone_cb_mapping, $user->id);
	} else if($user->guest == false and $apptpro_config->phone_profile_mapping != "" and $jinput->getString('phone', '') == ""){
		$phone = getProfiledata($apptpro_config->phone_profile_mapping, $user->id);
	} else if($user->guest == false and $apptpro_config->phone_js_mapping != "" and $jinput->getString('phone', '') == ""){
		$phone = getJSdata($apptpro_config->phone_js_mapping, $user->id);
	} else {
		$phone = $jinput->getString('phone');
	}

	$search_link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=user_search&frompage=front_desk'). " class=\"modal\" rel=\"{handler: 'iframe', size: {x: 500, y: 350}, onClose: function() {}}\" ";
	$udf_help_icon = "<img alt=\"\" src='".getImageSrc("help_udf2.png")."' class='sv_help_icon' ";

?>


<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/jquery.validate.min.js"></script>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/ddslick.js"></script>

<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!--
Commented out as staff probably do not want/need the JQuery tooltips
<?php if($apptpro_config->use_jquery_tooltips == "Yes"){ ?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/sv_tooltip.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/sv_tooltip.js"></script>
<?php } ?>-->

<?php if($apptpro_config->cart_enable == "Yes" || $apptpro_config->cart_enable == "Staff"){ ?>
    <script>
        var iframe = null;
        var cart_dialog = null;
        var cart_title = "<?php echo JText::_('RS1_VIEW_CART_SCRN_TITLE')?>"		
        var cart_close = "<?php echo JText::_('SV_CART_CLOSE')?>"		
    </script>
<?php } ?>

<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>
<script language="JavaScript">
	var non_booking_days = null;
	var bookoff_dates = null;	
	var myMinDate = null;
	var myMaxDate = null;
	if(isNaN(myMinDate)){
		myMinDate = 1;
	}	
	if(isNaN(myMaxDate) || myMaxDate == ""){	
		myMaxDate = null;
	}	

	jQuery(function() {
  		jQuery( "#display_grid_date" ).datepicker({
//			minDate: <?php echo $mindate;?>,		
			beforeShowDay: checkday, 
			minDate: myMinDate,		
			maxDate: myMaxDate,
			showOn: "button",
			showWeek: true,
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#grid_date",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});
	
	function doSubmit(pp){
	
		document.getElementById("errors").innerHTML = document.getElementById("wait_text").value
	
		// ajax validate form
		result = validateForm();
		//alert("|"+result+"|");

		if(result.indexOf('<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>')>-1){
			document.getElementById("ppsubmit").value = pp;
		    document.body.style.cursor = "wait"; 
			document.frmRequest.task.value = "process_booking_request";
			document.frmRequest.submit();
			return true;
		       
		} else {
			disable_enableSubmitButtons("enable");
			return false;
		}
		return false;
	}

	function checkSMS(){
		if(document.getElementById("use_sms").checked == true){
			document.getElementById("sms_reminders").value="Yes";
		} else {
			document.getElementById("sms_reminders").value="No";
		}	
	}

	function doCancel(){
		document.getElementById("task").value="cancel"
		document.frmRequest.submit();		
	}		
	

	function manualPayment(){
		calcTotal();	
	}
	
	
	function search_postback(theuser){
		selected_user = theuser.split("|");
		document.getElementById("users").value = selected_user[0];
		if(document.getElementById("sel_user") != null){
			document.getElementById("sel_user").innerHTML = selected_user[1];	
		}
		changeUser();
	}
	
	
</script>
<script language="javascript">
		window.onload = function() {
		   jQuery('#resources_slick').ddslick({
			   onSelected: function(data){
				   jQuery('#resources').val(data.selectedData.value);
				   if(need_changeResource == 1){
					   changeResource();
				   } else {
					   need_changeResource = 1;
				   }
			}   
		   }); 
		   jQuery('#category_id_slick').ddslick({
			   onSelected: function(data){jQuery('#category_id').val(data.selectedData.value);changeCategory();}           
		   }); 
			if(document.getElementById("resources")!=null){
				if(document.getElementById("resources").options.length==2){
					if(document.getElementById("resources_slick") != null){
						jQuery('#resources_slick').ddslick('select', {index: 1 });										
						changeResource();
					} else {
						document.getElementById("resources").options[1].selected=true;
						changeResource();
					}
				} else {
					changeResource();
				}
			}
		<?php if($single_category_mode){ ?>
				document.getElementById("category_id").options[1].selected=true;
				changeCategory();		
		<?php } ?>

			submit_section_show_hide("hide")			
				
		}	
</script>

<?php include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_js1.php"; // some common js ?>

<script>
jQuery(document).ready(function(){
});
</script>
  	<?php echo $rateArrayString; ?>            
    <?php echo $rate_unitArrayString; ?>            
    <?php echo $depositArrayString; ?>            
    <?php echo $deposit_unitArrayString; ?>            
    <?php echo $res_user_drag_durationArrayString; ?> 
    
<div id="search_div" title="<?php echo JText::_('RS1_INPUT_SCRN_NAME_SEARCH');?>"></div>	
    
<form name="frmRequest" id="frmRequest" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<?php if($showform){?>
<div id="sv_apptpro_request_gad_mobile">
  <table width="100%" align="center" >
	<?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "<tr><td colspan='8'><span class='sv_apptpro_errors'>".JText::_('RS1_INPUT_SCRN_LOGIN_REQUIRED')."</span></td></tr>";} ?> 
    <tr>
      <td><h3><?php echo JText::_('RS1_FRONTDESK_BOOKING_TITLE');?></h3></td>
    </tr>
       <td class="sv_apptpro_request_select_user_label"><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_SELECT_USER');?></div>
  	  <div class="controls">
		<?php if(sv_count_($user_rows) < 100){ ?>
		  <div style="display: table-cell; padding-left:0px;"><select name="users" id="users" class="sv_apptpro_request_dropdown" onchange="changeUser();" style="width:auto">
      		<option value="0"><?php echo JText::_('RS1_FRONTDESK_SCRN_NOT_REG');?></option>
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_rows ); $i++) {
			$user_row = $user_rows[$i];
			?>
                <option value="<?php echo $user_row->id; ?>"><?php echo $user_row->name." (".$user_row->username.")"; ?></option>
                <?php $k = 1 - $k; 
			} ?>
              </select></div>
          <?php } else { ?>
          		<div style="display: table-cell; padding-left:0px;"><label id="sel_user"></label><input type="hidden" id="users" name="users"/></div>
          <?php } ?>                
           <div style="display: table-cell; padding-left:5px;"><label id="user_fetch"  class="sv_apptpro_errors">&nbsp;</label></div>
           <!--<div style="display: table-cell; padding-left:5px;"><a href=<?php echo $search_link?>><?php echo JText::_('RS1_INPUT_SCRN_NAME_SEARCH');?></a></div> -->
           <div><input type="button" id="search_opener" value="<?php echo JText::_('RS1_INPUT_SCRN_NAME_SEARCH');?>"/></div> 
		</div>
      </td>
   
    <tr>
      <td>
      <div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_NAME');?></div>
      <div class="controls"><input name="name" type="text" id="name"  
      		size="40" maxlength="50" title="<?php echo JText::_('RS1_INPUT_SCRN_NAME_TOOLTIP');?>" value="<?php echo $name; ?>"
            <?php if($name != "" && $apptpro_config->name_read_only == "Yes"){echo " readonly='readonly'";}?>  />
            <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" /> <?php echo $required_symbol;?>
      </div>
      </td>
    </tr>
    <?php if($apptpro_config->requirePhone == "Hide"){?>
	    <input name="phone" type="hidden" id="phone" value="" />
    <?php } else { ?>   
    <tr>
      <td>
      <div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_PHONE');?></div>
      <div class="controls"><input name="phone" type="text" id="phone" value="<?php echo $phone ?>" 
           <?php if($apptpro_config->phone_read_only == "Yes" /*&& $apptpro_config->phone_cb_mapping != ""*/){echo " readonly='readonly' ";}?>
      		size="15" maxlength="20" title="<?php echo JText::_('RS1_INPUT_SCRN_PHONE_TOOLTIP');?>"/> <?php echo ($apptpro_config->requirePhone == "Yes"?$required_symbol:"")?>
      </div>
      </td>
    </tr>
    <?php } ?>
    <?php if(($apptpro_config->sms_to_resource_only == 'No') 
		&& ($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes")){?>
    <tr>
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_SMS_LABEL');?></div>
      <div class="controls"><input type="checkbox" name="use_sms" id="use_sms" onchange="checkSMS();" />&nbsp;
	  		<?php echo JText::_('RS1_INPUT_SCRN_SMS_CHK_LABEL');?>&nbsp;<br />
	      	<?php echo JText::_('RS1_INPUT_SCRN_SMS_PHONE');?>&nbsp;<input name="sms_phone" type="text" id="sms_phone" value="<?php echo $jinput->getString('sms_phone'); ?>"  
      		size="15" maxlength="20" title="<?php echo JText::_('RS1_INPUT_SCRN_SMS_PHONE_TOOLTIP');?>"
             />
             <?php if($apptpro_config->clickatell_show_code == "Yes"){ ?>
	            <select name="sms_dial_code" id="sms_dial_code" class="sv_apptpro_request_dropdown" title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_SMS_CODE_TOOLTIP'));?>">
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($dial_rows ); $i++) {
				$dial_row = $dial_rows[$i];
				?>
          <option value="<?php echo $dial_row->dial_code; ?>"  <?php if($apptpro_config->clickatell_dialing_code == $dial_row->dial_code){echo " selected='selected' ";} ?>><?php echo $dial_row->country." - ".$dial_row->dial_code ?></option>
              <?php $k = 1 - $k; 
				} ?>
      		</select>&nbsp;
   			 <?php } else { ?>
             <input type="hidden" name="sms_dial_code" id="sms_dial_code" value="<?php echo $apptpro_config->clickatell_dialing_code?>" />
             <?php } ?>
             <input type="hidden" name="sms_reminders" id="sms_reminders" value="No" />
      </div>
      </td>
    </tr>
    <?php }?>
    <?php if($apptpro_config->requireEmail == "Hide"){?>
	    <input name="email" type="hidden" id="email" value="" />
    <?php } else { ?>
    <tr>
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_EMAIL');?></div>
      <div class="controls"><input name="email" type="text" id="email" value="<?php echo $email ?>" 
      		 title="<?php echo JText::_('RS1_INPUT_SCRN_EMAIL_TOOLTIP');?>" size="40" maxlength="50"
              > <?php echo ($apptpro_config->requireEmail == "Yes"?$required_symbol:"")?>
      </div>
      </td>
    </tr>
	<?php } ?>
    <?php if(sv_count_($udf_rows > 0)){
		// (to be added at a later date) if logged in user, fetch udf values from last booking
		
        $k = 0;
        for($i=0; $i < sv_count_($udf_rows ); $i++) {
        	$udf_row = $udf_rows[$i];
			// if cb_mapping value specified, fetch the cb data
			if($user->guest == false and $udf_row->cb_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
				$udf_value = getCBdata($udf_row->cb_mapping, $user->id);
			} else if($user->guest == false and $udf_row->profile_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
				$udf_value = getProfiledata($udf_row->profile_mapping, $user->id);
			} else if($user->guest == false and $udf_row->js_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
				$udf_value = getJSdata($udf_row->js_mapping, $user->id);
			} else {
				$udf_value = $jinput->getString('user_field'.$i.'_value', '');
			}
				
        	?>
            <tr>
              <td>
              <div class="control-label"><label id="<?php echo 'user_field'.$i.'_label'; ?>" ><?php echo JText::_(stripslashes($udf_row->udf_label)) ?></label></div>
			  <div class="controls">
               <?php 
				if($udf_row->read_only == "Yes" && $udf_row->cb_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
				else if($udf_row->js_read_only == "Yes" && $udf_row->js_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
				else if($udf_row->profile_read_only == "Yes" && $udf_row->profile_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
				else {$readonly ="";}
				?>
                <?php if($udf_row->udf_type == 'Textbox'){ ?>
                    <input name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" type="text" value="<?php echo $udf_value; ?>" 
                    size="<?php echo $udf_row->udf_size ?>" maxlength="255" <?php echo $readonly?>
                     <?php echo ($udf_row->udf_placeholder_text != ""?" placeholder='".$udf_row->udf_placeholder_text."'":"")?> 
                      title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'Textarea'){ ?>
                    <textarea name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" 
                     <?php echo ($udf_row->udf_placeholder_text != ""?" placeholder='".$udf_row->udf_placeholder_text."'":"")?> 
					<?php echo $readonly?>
                    rows="<?php echo $udf_row->udf_rows ?>" cols="<?php echo $udf_row->udf_cols ?>" 
                      title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/><?php echo $udf_value; ?></textarea>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'Radio'){ 
						$aryButtons = explode(",", JText::sprintf("%s",stripslashes($udf_row->udf_radio_options)));
						foreach ($aryButtons as $button){ ?>
							<input name="user_field<?php echo $i?>_value" type="radio" id="user_field<?php echo $i?>_value" 
                            <?php  
								if(strpos($button, "(d)")>-1){
									echo " checked=\"checked\" ";
									$button = str_replace("(d)","", $button);
								} ?>
							value="<?php echo JText::_(stripslashes(trim($button))) ?>" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                            <?php echo JText::_(stripslashes(trim($button)))?><br />
						<?php } ?>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'List'){ 
						$aryOptions = explode(",", JText::sprintf("%s",stripslashes($udf_row->udf_radio_options))); ?>
						<select name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" class="sv_apptpro_request_dropdown"
                        title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_(stripslashes($udf_row->udf_tooltip))) ?>"> 
                        <?php 
						foreach ($aryOptions as $listitem){ ?>
				            <option value="<?php echo JText::_(str_replace("(d)","", $listitem)); ?>"
                            <?php  
								if(strpos($listitem, "(d)")>-1){
									echo " selected=true ";
									$listitem = str_replace("(d)","", $listitem);
								} ?>
                                ><?php echo JText::_(stripslashes($listitem)); ?></option>
						<?php } ?>              
                        </select>                 
                <?php } else if($udf_row->udf_type == 'Date'){ ?>
                	<script language="JavaScript">
						jQuery(function() {
							jQuery( "#user_field<?php echo $i?>_value" ).datepicker({
								showOn: "button",
			autoSize: true,
								firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
								changeMonth: true,
								changeYear: true,
								yearRange: "1920:2020",
								dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
								buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
								buttonImageOnly: true,
								buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>"
							});
						});
					</script>
                    <input type="text" readonly="readonly" id="user_field<?php echo $i?>_value" name="user_field<?php echo $i?>_value" 
                    	class="sv_date_box" size="10" maxlength="10" value="<?php echo $display_picker_date ?>">
                     	<input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'Content'){ ?>
                    <label> <?php echo JText::_($udf_row->udf_content) ?></label>
                    <input type="hidden" name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" value="<?php echo JText::_(htmlentities($udf_row->udf_content, ENT_QUOTES, "UTF-8"));?>">
                    <input type="hidden" name="user_field<?php echo $i?>_type" id="user_field<?php echo $i?>_type" value='Content'>
                <?php } else { ?>
                    <input name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" type="checkbox" value="<?php echo JText::_('RS1_INPUT_SCRN_CHECKED');?>" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } ?>    
                     <input type="hidden" name="user_field<?php echo $i?>_udf_id" id="user_field<?php echo $i?>_udf_id" value="<?php echo $udf_row->id_udfs ?>" />
                <?php if($udf_row->udf_help != "" && $udf_row->udf_help_as_icon == "Yes" ){      
//					echo $udf_help_icon." title='".JText::_(stripslashes($udf_row->udf_help))."'>";
			    	include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_udf_help.php";
				} ?>	
                </div>
                </td>
            </tr>
            <?php if($udf_row->udf_help_as_icon == "No" && $udf_row->udf_help != ""){ ?>
		    <tr>      		
	      		<td class="sv_apptpro_request_helptext"><?php echo JText::_(stripslashes($udf_row->udf_help)) ?></td>
            </tr>
			<?php } ?>
          <?php $k = 1 - $k; 
		} ?>
    <?php }?>
	<?php if(sv_count_($res_cats) > 0 ){ ?>
    <tr>
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES');?></div>
      <div class="controls"><select name="category_id" id="category_id" class="sv_apptpro_request_dropdown" onchange="changeCategory();"
        <?php echo ($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"");?>
      title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_TOOLTIP'));?>">
          <option value="0"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_PROMPT');?></option>
          <?php 
					$k = 0;
					for($i=0; $i < sv_count_($res_cats ); $i++) {
					$res_cat = $res_cats[$i];
					?>
          <option value="<?php echo $res_cat->id_categories; ?>" ><?php echo JText::_(stripslashes($res_cat->name)); ?></option>
          <?php $k = 1 - $k; 
					} ?>
        </select>
      <?php if($apptpro_config->enable_ddslick == "Yes"){?>
            <select id="category_id_slick" >
	          <option value="0"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_PROMPT');?></option>
          <?php 
                    $k = 0;
                    for($i=0; $i < sv_count_($res_cats ); $i++) {
                    $res_cat = $res_cats[$i];
                    ?>
	            <option value="<?php echo $res_cat->id_categories; ?>"
                data-imagesrc="<?php echo ($res_cat->ddslick_image_path!=""?getResourceImageURL($res_cat->ddslick_image_path):"")?>"
                    data-description="<?php echo $res_cat->ddslick_image_text?>">
                <?php echo JText::_(stripslashes($res_cat->name)); ?></option>
          <?php $k = 1 - $k; 
                    } ?>
            </select>
      <?php } ?>
        </div>
        <div align="right"></div></td>
    </tr>
    <?php if($sub_cat_count->count > 0 ){ // there are sub cats ?>
    <tr id="subcats_row" style="visibility:hidden; display:none">
    <td><div id="subcats_div"></div>
    </td>
    </tr>
	<?php } ?>
    <tr>
      <td>
      <div class="control-label"><label id="resources_label" style="visibility:hidden;"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE');?></label></div>
      <div class="controls">
      <div id="resources_div" style="visibility:hidden;">&nbsp;</div>
      </div>
      </td>
    </tr>
    <?php } else { ?>
    <tr>
      <td><div class="control-label"><label id="resources_label" style="visibility:hidden;"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE');?></label></div>
      <div class="controls"><select name="resources" id="resources" class="sv_apptpro_request_dropdown" onchange="changeResource()"
        <?php echo ($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"");?>
      title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_TOOLTIP'));?>">
          <?php 
					$k = 0;
					for($i=0; $i < sv_count_($res_rows ); $i++) {
					$res_row = $res_rows[$i];
					?>
          <option value="<?php echo $res_row->id_resources; ?>" <?php //if($resource == $res_row->id_resources ){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); echo ($res_row->cost==""?"":" - "); echo JText::_(stripslashes($res_row->cost)); ?></option>
          <?php $k = 1 - $k; 
					} ?>
        </select>
      <?php if($apptpro_config->enable_ddslick == "Yes"){?>
            <select id="resources_slick" >
          <?php 
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
	            <option value="<?php echo $res_row->id_resources; ?>"
                data-imagesrc="<?php echo ($res_row->ddslick_image_path!=""?getResourceImageURL($res_row->ddslick_image_path):"")?>"
                    data-description="<?php echo $res_row->ddslick_image_text?>">
                <?php echo JText::_(stripslashes($res_row->name)); echo ($res_row->cost==""?"":" - "); echo JText::_(stripslashes($res_row->cost)); ?></option>
          <?php $k = 1 - $k; 
                    } ?>
            </select>
      <?php } ?>
        </div></td>
    </tr>    
    <?php } ?>
    
    <tr id="services" style="visibility:hidden; display:none">
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_SERVICES');?></div>
    <div class="controls"><div id="services_div">&nbsp;</div></div></td>
    </tr>
    <tr id="resource_udfs" style="visibility:hidden; display:none"><td><div id="resource_udfs_div"></div></td></tr>
    <tr id="resource_seat_types" style="visibility:hidden; display:none"><td><div id="resource_seat_types_div"></div></td></tr>
    <tr id="resource_extras" style="visibility:hidden; display:none"><td><div id="resource_extras_div"></div></td></tr>

    <tr id="booking_detail" style="visibility:hidden; display:none">
      <td>
      <div id="booking_detail_div">
      <div class="control-label"><?php echo JText::_('RS1_GAD_SCRN_DETAIL');?></div>
      <div class="controls"><label class="sv_apptpro_errors" id="selected_resource_wait"></label>
      	<div>
      	<div><label class="sv_apptpro_selected_resource_mobile" id="selected_resource"> </label></div>
    	<div><label class="sv_apptpro_selected_resource_mobile" id="selected_date"> </label></div>
		<div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile" id="selected_starttime"> </label></div>
        <div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile"  style="padding:5px"><?php echo JText::_('RS1_TO');?></label></div>
        <div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile" id="selected_endtime"> </label></div>
        </div>
     </div>   
     </div>
    </td>
    </tr>
    <!-- *********************  GAD *******************************-->
    <tr>
        <td>        
          <table class="sv_gad_container_table" id="gad_container" style="display:none" width="100%">
            <tr>
              <td>
			  <div class="control-label"><?php echo JText::_('RS1_GAD_SCRN_DATE_MOBILE');?></div>
                <input readonly="readonly" name="grid_date" id="grid_date" type="hidden" 
                  class="sv_date_box" size="10" maxlength="10" value="<?php echo $grid_date ?>"/>
        
                <input type="text" readonly="readonly" id="display_grid_date" name="display_grid_date" class="sv_date_box" size="10" maxlength="10" 
                	value="<?php echo $display_grid_date ?>" onchange="changeDate();">
              <div style="float:right">  
              <img src="<?php echo getImageSrc("arrow_left30.png");?>" onclick="gridPrevious();" style="padding-right:10px" >
              <img src="<?php echo getImageSrc("arrow_right30.png");?>" onclick="gridNext();" >
<!--
              <input type="button" class="sv_grid_button" onclick="gridPrevious();" value="<<-">
              <input type="button" class="sv_grid_button" onclick="gridNext();" value="->>">
-->
              </div>
              </div>
              <?php if($apptpro_config->gad_grid_hide_startend == "Yes"){?>
 				<div style="float:right">             
              	  <input type="hidden" name="gridstarttime" id="gridstarttime" value="<?php echo $gridstarttime ?>"/>
	              <input type="hidden" name="gridendtime" id="gridendtime" value="<?php echo $gridendtime ?>"/>&nbsp;
                </div>  
              <?php } else { ?>
	              <hr />
              	  <div class="control-label"><?php echo JText::_('RS1_GAD_SCRN_GRID_START_MOBILE');?>
                  <select name="gridstarttime" id="gridstarttime" class="sv_apptpro_request_dropdown" onchange="changeGrid();" style="width:auto">
                    <?php 
                    for($x=0; $x<25; $x+=1){
                        if($x==12){
                            echo "<option value=".$x.":00 "; if($gridstarttime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_NOON')."</option>";  
                        } else if($x==24){
                            echo "<option value=".$x.":00 "; if($gridstarttime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_MIDNIGHT')."</option>";  
                        } else {
                            if($apptpro_config->timeFormat == "12"){
                                $AMPM = " AM";
                                $x1 = $x;
                                if($x>12){ 
                                    $AMPM = " PM";
                                    $x1 = $x-12;
                                }
                            } else {
                                $AMPM = "";
                                $x1 = $x;
                            }
                            echo "<option value=".$x.":00 "; if(trim($gridstarttime) == $x.":00") {echo " selected='selected' ";} echo "> ".$x1.":00".$AMPM." </option>";  
                        }
                    }
                    ?>
                    </select>
                    <?php echo JText::_('RS1_GAD_SCRN_GRID_END');?>
                    <select name="gridendtime" id="gridendtime" class="sv_apptpro_request_dropdown" onchange="changeGrid();" style="width:auto">
                    <?php 
                    for($x=0; $x<25; $x+=1){
                        if($x==12){
                            echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_NOON')."</option>";  
                        } else if($x==24){
                            echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_MIDNIGHT')."</option>";  
                        } else {
                            if($apptpro_config->timeFormat == "12"){
                                $AMPM = " AM";
                                $x1 = $x;
                                if($x>12){ 
                                    $AMPM = " PM";
                                    $x1 = $x-12;
                                }
                            } else {
                                $AMPM = "";
                                $x1 = $x;
                            }
                            echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo "> ".$x1.":00".$AMPM." </option>";  
                        }
                    }
                    ?>
                    </select> 
                    </div>
                    <hr />
			<?php } ?>
            </td>
            </tr>                        
            <tr>
              <td align="center" width="<?php echo $gridwidth?>"><div id="table_here" style="padding-bottom:20px"></div></td>
            </tr>
        </table>
        
        <input type="hidden" id="mode" name="mode" value="<?php echo $mode?>" />
        <?php if($gridwidth>-1){ ?>
	        <input type="hidden" id="gridwidth" name="gridwidth" value="<?php echo $gridwidth?>" />
        <?php } ?>
        <input type="hidden" id="grid_days" name="grid_days" value="<?php echo $griddays?>" />   
        <input type="hidden" id="namewidth" name="namewidth" value="<?php echo $namewidth?>" />        

        <input type="hidden" name="selected_resource_id" id="selected_resource_id" value="-1" />
        <input type="hidden" name="startdate" id="startdate" value="<?php echo $enddate ?>" />
        <input type="hidden" name="enddate" id="enddate" value="<?php echo $enddate ?>" />
        <input type="hidden" name="starttime" id="starttime" value="<?php echo $starttime ?>"/>
        <input type="hidden" name="endtime" id="endtime" value="<?php echo $endtime ?>"/>  
        <input type="hidden" name="endtime_original" id="endtime_original" value=""/>  
        <input type="hidden" name="sub_cat_count" id="sub_cat_count" value="<?php echo $sub_cat_count->count ?>"/>  
           
        </td>
    </tr>
    <!-- *********************  GAD *******************************-->
 <?php if($apptpro_config->enable_coupons == "Yes"){ ?>
     <tr class="submit_section">
        <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_COUPONS');?></div>
        <div class="controls"><input name="coupon_code" type="text" id="coupon_code" value="" size="20" maxlength="80" 
              title="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_TOOLTIP');?>" />
              <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_BUTTON');?>" onclick="getCoupon('fd')" />
              <div id="coupon_info"></div>
              <input type="hidden" id="coupon_value" />
              <input type="hidden" id="coupon_units" />  </div>            
        </td>
    </tr>
 <?php } ?>
 <?php if($apptpro_config->enable_gift_cert == "Yes"){ ?>
     <tr class="submit_section">
        <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT');?></div>
        <div class="controls"><input name="gift_cert" type="text" id="gift_cert" value="" size="20"  
              title="<?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_TOOLTIP');?>" />
              <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_BUTTON');?>" onclick="getGiftCert()" />
              <div id="gift_cert_info"></div>
              <input type="hidden" id="gift_cert_bal" /> </div>
        </td>
    </tr>
 <?php } ?>
 <?php if($pay_proc_enabled || $apptpro_config->non_pay_booking_button == "DAB" ||  $apptpro_config->non_pay_booking_button == "DO" ){ ?>
    <tr class="submit_section">
      <td style="height:auto" align="center">
      <div id="calcResults" style="visibility:hidden; display:none; height:auto;">
        <table border="1"  cellpadding="4" cellspacing="0" class="calcResults_outside" align="center" >
          <tr class="calcResults_header">
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE');?></td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label id="res_hours_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_UNITS');?></label></td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_TOTAL');?></td>
          </tr>
          <tr style="text-align:right">
            <td style="border-bottom:solid 1px; border-right:solid 1px; height:auto;"><div style="float:right">
            <div style="display: table-cell; padding-left:0px;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
            <div style="display: table-cell; padding-left:5px;"><label id="res_rate"></label></div></div>
            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label id="res_hours"></label></td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><div style="float:right">
            <div style="display: table-cell; padding-left:0px;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
            <div style="display: table-cell; padding-left:5px;"><label id="res_total"></label></div></div></td>
          </tr>
      <?php if ($extras_row_count->count > 0 ){?>
          <tr>
            <td style="border-bottom:solid 1px;">&nbsp;            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_EXTRAS_FEE');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><div style="display: table-cell; padding-left:5px; float:right"><label id="extras_fee"></label></div></td>
          </tr>
      <?php } ?>    
      <?php if ($apptpro_config->additional_fee != 0.00 ){?>
          <tr>
            <td style="border-bottom:solid 1px;">&nbsp;            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_RES_ADDITIONAL_FEE');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right"><label id="res_fee"></label></div></td>
          </tr>
      <?php } ?>    
      <?php if($apptpro_config->enable_coupons == "Yes" || $apptpro_config->enable_eb_discount == "Yes"){ ?>
          <tr>
            <td style="border-bottom:solid 1px;">&nbsp;            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_DISCOUNT');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right"><label id="discount"></label></div></td>
          </tr>
	  <?php } ?>
      <?php if($apptpro_config->enable_gift_cert == "Yes"){ ?>
          <tr style="text-align:right" id="gc_row">
            <td style="border-bottom:solid 1px;">&nbsp;            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_GC_CREDIT');?>&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right"><label id="gc_credit"></label> </div></td>
          </tr>
	  <?php } ?>
          <tr align="right">
            <td colspan="2" style="border-bottom:solid 1px;"><span id='current_credit'></span>&nbsp;<?php echo JText::_('RS1_INPUT_SCRN_USER_CREDIT');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px;">&nbsp;<label id="uc_credit"></label> <input type="hidden" name="applied_credit" id="applied_credit" /></td>
          </tr>
          <tr align="right">
            <td colspan="2" style="border-bottom:solid 1px;"><span id='current_credit'></span>&nbsp;<?php echo JText::_('RS1_INPUT_SCRN_PAYMENT_COLLECTED');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px;">&nbsp;<input type="text" id="manual_payment_collected" name="manual_payment_collected" size="6" value="0.00" style="text-align:right; width:auto" 
            title="<?php echo JText::_('RS1_INPUT_SCRN_PAYMENT_COLLECTED_HELP');?>" onchange="manualPayment();"/></td>
          </tr>
          <tr align="right">
            <td style="border-bottom:solid 1px;">&nbsp;
                <input type="hidden" id="additionalfee" value="<?php echo $apptpro_config->additional_fee ?>" />
            	<input type="hidden" id="feerate" value="<?php echo $apptpro_config->fee_rate ?>" />
            	<input type="hidden" id="rateunit" value="<?php echo $apptpro_config->fee_rate ?>" />
                <input type="hidden" id="grand_total" name="grand_total" value="<?php echo $grand_total ?>" />			
             </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_TOTAL');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
			<div style="display: table-cell; padding-left:5px;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div><div style="display: table-cell; padding-left:5px;"><label id="res_grand_total"></label></div></td>
          </tr>
          <tr align="right" id="deposit_only">
            <td style="border-bottom:solid 1px;">&nbsp;            </td>
            <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_DEPOSIT');?>:&nbsp;</td>
            <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="float:right"><div style="display: table-cell; padding-left:0px;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
            <div style="display: table-cell; padding-left:5px;"><label id="display_deposit_amount"></label></div></div>
            <input type="hidden" id="deposit_amount" name="deposit_amount" value="0.00" />			
			</td>
          </tr>
        </table>
      </div>      
      </td>
    </tr>
<?php } ?>    
<?php if($apptpro_config->cart_enable == "No" || $apptpro_config->cart_enable == "Public"){ 
	// If using the cart you cannot override status	?>
   <tr class="submit_section">
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_BOOK_STATUS');?></div>
      <div class="controls">
          <select id="book_as_request_status" name="book_as_request_status" class="sv_apptpro_requests_dropdown" style="font-size:12px">
<!--            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>-->
            <option value="new"  class="color_new" ><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NEW');?></option>
            <option value="accepted" class="color_accepted" selected ><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_ACCEPTED');?></option>
            <option value="pending" class="color_pending" ><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_PENDING');?></option>
          </select>
      </div>
      </td>
    </tr>
<?php } ?>
    <tr class="submit_section">
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_ADMINCOMMENT');?>:</div>
      <div class="controls"><textarea name="admin_comment" id="admin_comment" class="sv_apptpro_request_text" rows="2" cols="60" ></textarea>
      </div>
      </td>
    </tr>
<?php if($apptpro_config->cart_enable == "No" || $apptpro_config->cart_enable == "Public"){ 
	// If using the cart you cannot turn off confirmation emails ?>
    <tr  class="submit_section">
      <td><div class="control-label"><?php echo JText::_('RS1_INPUT_SCRN_EMAIL_CONF');?></div>
      <div class="controls">
          <Input type="checkbox" name="chk_email_confirmation" checked="checked" value="Yes" />&nbsp;<?php echo JText::_('RS1_INPUT_SCRN_EMAIL_CONF_HELP');?>
      </div>
      </td>
    </tr>
<?php } ?>
    <tr>
      <td><div id="errors" class="sv_apptpro_errors"><?php echo $err ?></div></td>
	</tr>
    <tr>
      <td><div class="controls"><input  name="cbCopyMe" type="hidden" value="yes"  />
<?php if($apptpro_config->cart_enable == "Yes" || $apptpro_config->cart_enable == "Staff"){ ?>
        <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_ADD_TO_CART');?>" id="btnAddToCart" onclick="addToCart(); return false;"
        <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
        <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_VIEW_CART');?>" onclick="viewCart(); return false;"
        <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
<?php } else { ?>
          <input type="submit" class="button"  name="btnSubmit" id="btnSubmit" onclick="return doSubmit(0);" 
            value="<?php echo JText::_('RS1_FRONTDESK_SCRN_SUBMIT');?>" /> 
<?php } ?>            
        <input type="button" class="button"  name="cancel" id="btncancel" onclick="return doCancel();" 
          value="<?php echo JText::_('RS1_FRONTDESK_SCRN_CANCEL');?>" /> 
	</div>
    </td>
    </tr>
  </table>
  </div>
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
	  <span style="font-size:9px; color:#999999">powered by <a href="http://www.AppointmentBookingPro.com" target="_blank">AppointmentBookingPro.com</a> v 4.0.5</span>
  <?php } ?>
  <input type='hidden' name="mobile" id="mobile" value="Yes">

  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" id="select_date_text" value="<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>" />
  <input type="hidden" id="beyond_end_of_day" value="<?php echo JText::_('RS1_INPUT_SCRN_BEYOND_EOD');?>" />
  <input type="hidden" id="udf_count" name="udf_count" value="<?php echo sv_count_($udf_rows);?>" />
  <input type="hidden" id="enable_paypal" value="<?php echo $apptpro_config->enable_paypal ?>" />
  <input type="hidden" id="authnet_enable" value="<?php echo $apptpro_config->authnet_enable ?>" />
  <input type="hidden" id="_2co_enable" value="<?php echo $apptpro_config->_2co_enable ?>" />
  <input type="hidden" id="non_pay_booking_button" value="<?php echo $apptpro_config->non_pay_booking_button ?>" />
  <input type="hidden" id="flat_rate_text" name="flat_rate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_RES_FLAT_RATE'); ?>" />			             
  <input type="hidden" id="non_flat_rate_text" name="non_flat_rate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_UNITS'); ?>" />			             
  <input type="hidden" id="ppsubmit" name="ppsubmit" value="-1" />			             
  <input type="hidden" id="screen_type" name="screen_type" value="fd_gad" />			             
  <input type="hidden" id="reg" name="reg" value="<?php echo ($user->guest?'No':'Yes')?>" />	
  <input type="hidden" id="adjusted_starttime" name="adjusted_starttime" value="" />			             
  <input type="hidden" id="timeFormat" value="<?php echo $apptpro_config->timeFormat ?>" />
  <input type="hidden" id="end_of_day" value="<?php echo $apptpro_config->def_gad_grid_end ?>" />
  <input type="hidden" name="redirect" id="redirect" value="" />
  <input type="hidden" name="fd" id="fd" value="Yes" />
  <input type="hidden" id="uc" value="<?php echo $user_credit ?>" />
  <input type="hidden" id="gad2" value="<?php echo $apptpro_config->use_gad2 ?>" />
  
  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" id="controller" name="controller" value="front_desk" />
	<input type="hidden" name="id" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="frompage" value="front_desk" />
  	<input type="hidden" name="frompage_item" id="frompage_item" value="<?php echo $itemId ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
    <input type="hidden" id="enable_overrides" value="<?php echo $apptpro_config->enable_overrides ?>" />
	<input type="hidden" name="validate_text" id="validate_text" value="<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>" />    
	<input type="hidden" name="mobile" id="mobile" value="Yes" />    
	<input type="hidden" name="agent" id="agent" value="<?php echo $this->agent; ?>" />    
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
	<input type="hidden" id="enable_payproc" value="<?php echo ($pay_proc_enabled?"Yes":"No")?>" />
    <input type="hidden" name="mobile" id="mobile" value="Yes" />    
	<input type="hidden" name="gap" id="gap" value="<?php echo $apptpro_config->gap; ?>" /> 
	<input type="hidden" name="res_spec_gap" id="res_spec_gap" value="0" /> 
  <?php echo ($apptpro_config->enable_eb_discount=="Yes"?getResourceEBDiscounts():"") ?>
  <?php echo getCategoryDurations(); ?>
	<input type="hidden" name="jit_submit" id="jit_submit" value="<?php echo $apptpro_config->jit_submit; ?>" /> 
	<input type="hidden" name="uc_used" id="uc_used" value="0" /> 
	<input type="hidden" name="gc_used" id="gc_used" value="0" /> 
	<input type="hidden" name="applied_credit" id="applied_credit" value="0" />  
	<input type="hidden" id="user_duration" value="0" />
<?php } ?> 
<?php echo JHtml::_('form.token'); ?>
</form>
<script>
jQuery.noConflict();
jQuery( document ).ready(function() {

	jQuery( "#search_div" ).dialog({ autoOpen: false,
	   autoOpen: false,
	   modal: true,
	   height: 350,
	   width: 300
	});
		
	function showDialog(){
	   jQuery("#search_div").html('<iframe id="modalIframeId" width="100%" height="100%" marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto" />').dialog("open");
	   jQuery("#modalIframeId").attr("src","<?php echo JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=user_search&frompage=front_desk');?>");
	   return false;
	}
	   
	jQuery( "#search_opener" ).click(function() {
		// using an iframe rather than jQuery.load so we can close in code when opeartor make selection
		showDialog();
	});
});

</script>
