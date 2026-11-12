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
	jimport('joomla.filter.output');
	$jinput = JFactory::getApplication()->input;

	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$filter="";
	$session = JFactory::getSession();
	
	$filter = $this->filter_request_status;
	$resourceFilter = $this->filter_request_resource;
	$startdateFilter = $this->filter_startdate;
	
	$selected_resource_book_dates = "";

	if($session->get("current_tab") != "" ){
		$current_tab = $session->get("current_tab");
		$session->set("current_tab", "");
	} else {
		$current_tab = $jinput->getString( 'current_tab', '0' );
	}

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	
	$user = JFactory::getUser();
	//print_r($user->groups);
	//if($user->groups[13]!=""){
	//	echo "in";
	//}
	
	$ordering = ($this->lists['order'] == 'ordering');
	$showform = true;
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	 
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else{
		$showform = true;

		$database = JFactory::getDBO();
		
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
			$showform = false;
		}	

		// get user's groups
		$sql = "SELECT group_id FROM #__user_usergroup_map WHERE ".
			"user_id=".$user->id;	
		try{		
			$database->setQuery($sql);
			$my_groups = $database -> loadColumn();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		//print_r($my_groups);
		
		// get resources
		$sql = "SELECT * FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' and published=1 ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// get categories
		$sql = "SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 ORDER BY name";
		try{
			$database->setQuery($sql);
			$cat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// get statuses
		$sql = "SELECT * FROM #__sv_apptpro3_status ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$sql = "SELECT * FROM #__sv_apptpro3_payment_status ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$pay_statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// get payment processors
		$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1 AND processor_name != "Payage" ;';
		try{
			$database->setQuery($sql);
			$pay_procs = NULL;
			$pay_procs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$startdateFilter = $this->filter_startdate;
		$enddateFilter = $this->filter_enddate;

		$display_picker_date = $this->filter_startdate;	
		if($display_picker_date != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date = date("Y-m-d", strtotime($this->filter_startdate));
					break;
				case "dd-mm-yy":
					$display_picker_date = date("d-m-Y", strtotime($this->filter_startdate));
					break;
				case "mm-dd-yy":
					$display_picker_date = date("m-d-Y", strtotime($this->filter_startdate));
					break;
				default:	
					$display_picker_date = date("Y-m-d", strtotime($this->filter_startdate));
					break;
			}
		}
	
		$display_picker_date2 = $this->filter_enddate;	
		if($display_picker_date2 != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2 = date("Y-m-d", strtotime($this->filter_enddate));
					break;
				case "dd-mm-yy":
					$display_picker_date2 = date("d-m-Y", strtotime($this->filter_enddate));
					break;
				case "mm-dd-yy":
					$display_picker_date2 = date("m-d-Y", strtotime($this->filter_enddate));
					break;
				default:	
					$display_picker_date2 = date("Y-m-d", strtotime($this->filter_enddate));
					break;
			}
		}

		$pub = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'>";
		$unpub = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' border='0'>";
	
		$tab = 0;
		
		// was planning to use pdf but Joomla pdf is useless with tables
		$pdflink = JRoute::_( "index.php?option=com_rsappt_pro3&controller=advadmin&task=printer&frompage=advadmin&tmpl=component");	

	}	
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/jquery-1.12.4.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>

<script language="JavaScript">
	jQuery(function() {
  		jQuery( "#display_picker_date" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#startdateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#enddateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	function cleardate(){
		document.getElementById("startdateFilter").value="";
		document.getElementById("enddateFilter").value="";
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;		
	}

	function selectStartDate(){
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;		
	}

	function selectEndDate(){
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;		
	}
	
	function changeRequestResourceFilter(){
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;				
	}

	function changeRequestStatusFilter(){
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;				
	}

	function changePaymentStatusFilter(){
		document.getElementById("current_tab").value="0";
		Joomla.submitbutton('');
		return false;				
	}

	function sendReminders(which){
		if(!check_somthing_is_checked("cid_req[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return;
		}
		if(which=="Email"){
			document.getElementById("current_tab").value="0";
			Joomla.submitbutton('reminders');
		} else if(which=="ThankYou"){
			document.getElementById("current_tab").value="0";
			Joomla.submitbutton('thankyou');
		} else {
			document.getElementById("current_tab").value="0";
			Joomla.submitbutton('reminders_sms');
		}	
		return false;		
	}
	
	function doInvoicing(){
		if(!check_somthing_is_checked("cid_req[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return false;
		}
		document.getElementById("task").value="create_invoice";		
		document.getElementById("adminForm").action = "<?php echo JURI::base( false );?>index.php?option=com_rsappt_pro3&view=admin_invoice";				
		document.adminForm.submit();			
	}
	
	function doPublish(id){
		if(id != undefined){			
			document.getElementById('res_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("resources_tab").value;
		Joomla.submitbutton('publish_resource');
		return false;		
	}

	function doUnPublish(id){
		if(id != undefined){			
			document.getElementById('res_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value = document.getElementById("resources_tab").value;
		Joomla.submitbutton('unpublish_resource');
		return false;		
	}

	function selectResource(tab){
		document.getElementById("current_tab").value=tab;
		Joomla.submitbutton('');
		return false;			
	}	

	function selectCategory(tab){
		document.getElementById("current_tab").value=tab;
		Joomla.submitbutton('');
		return false;			
	}	
	
	function goResCopy(id){
		if(id != undefined){			
			document.getElementById('res_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("resources_tab").value;
		Joomla.submitbutton('copy_resource');
		return false;		
	}


	function doResRemove(){
		var answer = confirm(" <?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_DELETE_RESOURCE');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("resources_tab").value;
			Joomla.submitbutton('remove_resource');
			return false;		
		}
	}

	function doSrvPublish(id){
		if(document.getElementById('service_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('srv_cb'+id).checked = true;
			}
//			document.getElementById("redirect").value="publish_service";
			document.getElementById("current_tab").value=document.getElementById("services_tab").value;
			Joomla.submitbutton('publish_service');
			return false;		
		}
	}

	function doSrvUnPublish(id){
		if(document.getElementById('service_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('srv_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("services_tab").value;
			Joomla.submitbutton('unpublish_service');
			return false;		
		}
	}

	function doSrvRemove(){
		if(document.getElementById('service_resourceFilter').selectedIndex != 0){
			var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_DELETE_SERVICE');?>")
			if (answer){
				document.getElementById("current_tab").value=document.getElementById("services_tab").value;
				Joomla.submitbutton('remove_service');
				return false;		
			}
		}
	}

	function goSrvCopy(id){
		if(document.getElementById('service_resourceFilter').selectedIndex != 0){
			document.getElementById("id").value=id;
			document.getElementById("current_tab").value=document.getElementById("services_tab").value;
			Joomla.submitbutton('copy_services');
			return false;		
		}
	}

	function doBOPublish(id){
		if(document.getElementById('bookoffs_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('bo_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("bookoffs_tab").value;
			Joomla.submitbutton('publish_bookoff');
			return false;		
		}
	}

	function doBOUnPublish(id){
		if(document.getElementById('bookoffs_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('bo_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("bookoffs_tab").value;
			Joomla.submitbutton('unpublish_bookoff');
			return false;		
		}
	}

	function doBORemove(){
		if(document.getElementById('bookoffs_resourceFilter').selectedIndex != 0){
			var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_DELETE_BOOKOFF');?>")
			if (answer){
				document.getElementById("current_tab").value=document.getElementById("bookoffs_tab").value;
				Joomla.submitbutton('remove_bookoff');
				return false;		
			}
		}
	}

	function goBOCopy(id){
		if(document.getElementById('bookoffs_resourceFilter').selectedIndex != 0){
			document.getElementById("id").value=id;
			document.getElementById("current_tab").value=document.getElementById("bookoffs_tab").value;
			Joomla.submitbutton('copy_bookoffs');
			return false;		
		}
	}
	
	function doBDPublish(id){
		if(document.getElementById('book_dates_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('bd_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("book_dates_tab").value;
			Joomla.submitbutton('publish_book_date');
			return false;		
		}
	}

	function doBDUnPublish(id){
		if(document.getElementById('book_dates_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('bd_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("book_dates_tab").value;
			Joomla.submitbutton('unpublish_book_date');
			return false;		
		}
	}

	function doBDRemove(){
		if(document.getElementById('book_dates_resourceFilter').selectedIndex != 0){
			var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_DELETE_BOOK_DATE');?>")
			if (answer){
				document.getElementById("current_tab").value=document.getElementById("book_dates_tab").value;
				Joomla.submitbutton('remove_book_date');
				return false;		
			}
		}
	}


	function selectDay(){
			document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
			Joomla.submitbutton('');
			return false;		
	}

	function doTSPublish(id){
		if(document.getElementById('timeslots_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('ts_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
			Joomla.submitbutton('publish_timeslot');
			return false;		
		}
	}

	function doTSUnPublish(id){
		if(document.getElementById('timeslots_resourceFilter').selectedIndex != 0){
			if(id != undefined){			
				document.getElementById('ts_cb'+id).checked = true;
			}
			document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
			Joomla.submitbutton('unpublish_timeslot');
			return false;		
		}
	}

	function doTSRemove(){
		if(document.getElementById('timeslots_resourceFilter').selectedIndex != 0){
			var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_DELETE_TIMESLOT');?>")
			if (answer){
				document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
				Joomla.submitbutton('remove_timeslot');
				return false;		
			}
		}
	}

	function doImportGlobal(){
		if(document.getElementById('timeslots_resourceFilter').selectedIndex == 0){
			alert("<?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_FOR_IMPORT_PROMPT');?>");
			return false
		}
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_IMPORT_GLOBAL');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
			Joomla.submitbutton('do_global_import_timeslots');
			return false;		
		}
	}


	function goTSCopy(id){
		if(document.getElementById('timeslots_resourceFilter').selectedIndex != 0){
			document.getElementById("id").value=id;
			document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value;
			Joomla.submitbutton('copy_timeslots');
			return false;		
		}
	}

	
	function selectPPStartDate(){
		document.getElementById("current_tab").value=document.getElementById("paypal_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function selectPPEndDate(){
		document.getElementById("current_tab").value=document.getElementById("paypal_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function ppcleardate(){
		document.getElementById("ppstartdateFilter").value="";
		document.getElementById("ppenddateFilter").value="";
		document.getElementById("current_tab").value=document.getElementById("paypal_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
	
	function selectANStartDate(){
		document.getElementById("current_tab").value=document.getElementById("authnet_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function selectANEndDate(){
		document.getElementById("current_tab").value=document.getElementById("authnet_tab").value;
		Joomla.submitbutton('');
		return false;		
	}

	function selectANAIMStartDate(){
		document.getElementById("current_tab").value=document.getElementById("authnet_aim_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function selectANAIMEndDate(){
		document.getElementById("current_tab").value=document.getElementById("authnet_aim_tab").value;
		Joomla.submitbutton('');
		return false;		
	}

	function selectGOOGStartDate(){
		document.getElementById("current_tab").value=document.getElementById("google_wallet_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function selectGOOGEndDate(){
		document.getElementById("current_tab").value=document.getElementById("google_wallet_tab").value;
		Joomla.submitbutton('');
		return false;		
	}


	function select2COStartDate(){
		document.getElementById("current_tab").value=document.getElementById("_2co_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function select2COEndDate(){
		document.getElementById("current_tab").value=document.getElementById("_2co_tab").value;
		Joomla.submitbutton('');
		return false;		
	}

	function selectSTRIPEStartDate(){
		document.getElementById("current_tab").value=document.getElementById("stripe_tab").value;
		Joomla.submitbutton('');
		return false;		
	}
		
	function selectSTRIPEEndDate(){
		document.getElementById("current_tab").value=document.getElementById("stripe_tab").value;
		Joomla.submitbutton('');
		return false;		
	}

	
	function doCoupPublish(id){
		if(id != undefined){			
			document.getElementById('coup_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("coupons_tab").value;
		Joomla.submitbutton('publish_coupon');
		return false;		
	}

	function doCoupUnPublish(id){
		if(id != undefined){			
			document.getElementById('coup_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("coupons_tab").value;
		Joomla.submitbutton('unpublish_coupon');
		return false;		
	}

	
	function doCoupRemove(){
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_TOOLBAR_COUPON_DEL_CONF');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("coupons_tab").value;
			Joomla.submitbutton('remove_coupon');
			return false;		
		}
	}

	function goCoupCopy(id){
		document.getElementById("id").value=id;
		document.getElementById("current_tab").value=document.getElementById("coupons_tab").value;
		Joomla.submitbutton('copy_coupons');
		return false;		
	}

	function doExtPublish(id){
		if(id != undefined){			
			document.getElementById('ext_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("extras_tab").value;
		Joomla.submitbutton('publish_extra');
		return false;		
	}

	function doExtUnPublish(id){
		if(id != undefined){			
			document.getElementById('ext_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("extras_tab").value;
		Joomla.submitbutton('unpublish_extra');
		return false;		
	}

	
	function doExtRemove(){
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_TOOLBAR_EXTRA_DEL_CONF');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("extras_tab").value;
			Joomla.submitbutton('remove_extra');
			return false;		
		}
	}

	function doRAPublish(id){
		if(id != undefined){			
			document.getElementById('ra_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("rate_adjustments_tab").value;
		Joomla.submitbutton('publish_rate_adjustment');
		return false;		
	}

	function doRAUnPublish(id){
		if(id != undefined){			
			document.getElementById('ra_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("rate_adjustments_tab").value;
		Joomla.submitbutton('unpublish_rate_adjustment');
		return false;		
	}
	
	function doRARemove(){
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_DEL_CONF');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("rate_adjustments_tab").value;
			Joomla.submitbutton('remove_rate_adjustment');
			return false;		
		}
	}

	function doSAPublish(id){
		if(id != undefined){			
			document.getElementById('sa_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("seat_adjustments_tab").value;
		Joomla.submitbutton('publish_seat_adjustment');
		return false;		
	}

	function doSAUnPublish(id){
		if(id != undefined){			
			document.getElementById('sa_cb'+id).checked = true;
		}
		document.getElementById("current_tab").value=document.getElementById("seat_adjustments_tab").value;
		Joomla.submitbutton('unpublish_seat_adjustment');
		return false;		
	}
	
	function doSARemove(){
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_SEAT_ADJUSTMENT_DEL_CONF');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("seat_adjustments_tab").value;
			Joomla.submitbutton('remove_seat_adjustment');
			return false;		
		}
	}

	function doUCRemove(){
		var answer = confirm("<?php echo JText::_('RS1_ADMIN_SCRN_USER_CREDIT_DEL_CONF');?>")
		if (answer){
			document.getElementById("current_tab").value=document.getElementById("user_credit_tab").value;
			Joomla.submitbutton('remove_user_credit');
			return false;		
		}
	}


	function doSearch(){
		Joomla.submitbutton('');
		return false;
	}

	function exportCSV(){
		if(!check_somthing_is_checked("cid_req[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return;
		}
		document.getElementById("task").value="export_csv";
		document.adminForm.submit();
		document.getElementById("task").value="";
	}
	
	function setTab(tab_num){
		//document.getElementById("current_tab").value = tab_num;
	}

  </script>

<script>
  	//var $ = jQuery.noConflict();

  jQuery(document).ready(function() {
   jQuery("#tabs").tabs();
	
	var the_tabs = jQuery('#tabs').tabs();
	the_tabs.tabs('select', <?php echo $current_tab; ?>);

	jQuery('#tabs').bind('tabsselect', function(event, ui) {
		document.getElementById("current_tab").value = ui.index;
		// Objects available in the function context:
		ui.tab     // anchor element of the selected (clicked) tab
		ui.panel   // element, that contains the selected/clicked tab contents
		ui.index   // zero-based index of the selected (clicked) tab
	
	});	

  });
  </script>
<script>
	var iframe = null;
	var jq_dialog = null;
	var jq_dialog_title = "";		
	var jq_dialog_close = "<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE')?>";		
 </script>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
 <!-- <div id="sv_apptpro_fe_adv_admin"> --> 
  <?php if($showform){?>
  <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE');?></h3>
  <div id="tabs">
    <ul id="adv_admin_tabs" class="tabs">
      <li><a href="#panel1" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOKING'); ?></a></li>
      <?php if($apptpro_config->adv_admin_show_resources == "Yes" || in_array($apptpro_config->adv_admin_show_resources, $my_groups)){?>
      <li onclick="setTab(0)"><a href="#panel2" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_RESOURCES'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_services == "Yes" || in_array($apptpro_config->adv_admin_show_services, $my_groups)){?>
      <li onclick="setTab(1)"><a href="#panel3" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_SERVICES'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_timeslots == "Yes" || in_array($apptpro_config->adv_admin_show_timeslots, $my_groups)){?>
      <li onclick="setTab(2)"><a href="#panel4" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_TIMESLOTS'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_bookoffs == "Yes" || in_array($apptpro_config->adv_admin_show_bookoffs, $my_groups)){?>
      <li onclick="setTab(3)"><a href="#panel5" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOKOFFS'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_book_dates == "Yes" || in_array($apptpro_config->adv_admin_show_book_dates, $my_groups)){?>
      <li onclick="setTab(12)"><a href="#panel12" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOK_DATES'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_coupons == "Yes" || in_array($apptpro_config->adv_admin_show_coupons, $my_groups)){?>
      <li onclick="setTab(7)"><a href="#panel7" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_COUPONS'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_extras == "Yes" || in_array($apptpro_config->adv_admin_show_extras, $my_groups)){?>
      <li onclick="setTab(8)"><a href="#panel8" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_EXTRAS'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_rate_adj == "Yes" || in_array($apptpro_config->adv_admin_show_rate_adj, $my_groups)){?>
      <li onclick="setTab(9)"><a href="#panel9" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_RATE_ADJ'); ?></a></li>
      <?php } ?>
      <?php if($apptpro_config->adv_admin_show_seat_adj == "Yes" || in_array($apptpro_config->adv_admin_show_seat_adj, $my_groups)){?>
      <li onclick="setTab(10)"><a href="#panel10" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_SEAT_ADJ'); ?></a></li>
      <?php } ?>
      <?php if(in_array($apptpro_config->adv_admin_show_credits, $my_groups)){?>
      <li onclick="setTab(11)"><a href="#panel11" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_CREDITS'); ?></a></li>
      <?php } ?>
      <?php 
			$i=20;
			foreach($pay_procs as $pay_proc){ 
				$who_to_show = getPayProcinFE($pay_proc->prefix);
				if($who_to_show == "Yes" || in_array($who_to_show, $my_groups)){
					?>
      <li onclick="setTab(<?php echo $i?>)"><a href="#panel<?php echo $i?>" data-toggle="tab"><?php echo JText::_($pay_proc->display_name); ?></a></li>
      <?php
					$i++;	
				}				
			}
		?>
    </ul>
    <div id="panel1" class="tab-pane">
      <table  style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOKING');?></th>
          <th style="text-align:right">&nbsp;<a href="#" onclick="exportCSV();return(false);" title="<?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV_HELP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV');?></a> &nbsp;|&nbsp;&nbsp;<a href="#" onclick="sendReminders('Email');return(false);" title="<?php echo JText::_('RS1_ADMIN_SCRN_REMINDERS_TOOLTIP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS');?></a>
            <?php if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){?>
            &nbsp;|&nbsp;&nbsp;<a href="#" onclick="sendReminders('SMS');return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS_SMS');?></a>
            <?php } ?>
            &nbsp;|&nbsp;<a href="javascript:sendReminders('ThankYou');" title="<?php echo JText::_('RS1_ADMIN_SCRN_THANKYOU_TOOLTIP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_THANKYOU');?></a> &nbsp;|&nbsp;&nbsp;<a href="<?php echo $pdflink; ?>" target="_blank" title="<?php echo JText::_('RS1_ADMIN_PRINT_TIP');?>"><?php echo JText::_('RS1_ADMIN_PRINT');?></a>
            <?php 	// look to see if invoiceing plugin is installed..
				if(JPluginHelper::isEnabled('abpro_plugins', 'abpro_invoicing')){ ?>
            &nbsp;|&nbsp;<a href="#" onclick="doInvoicing();return false;" title="<?php echo JText::_('RS1_ADMIN_INVOICE_TIP');?>"><?php echo JText::_('RS1_ADMIN_TOOLBAR_APPOINTMENTS_INVOICE'); ?></a>
            <?php	}?>
          </th>
        </tr>
      </table>
      <table width="100%">
        <thead>
          <tr class="fe_admin_header">
            <td ><div id="search_value" style="display: table-cell; padding-left:1px;">
                <input type="text" id="user_search" name="user_search" title="<?php echo JText::_('RS1_ADMIN_APPT_LIST_SEARCH_HELP');?>" 
                value="<?php echo $this->filter_user_search ?>" onchange="doSearch();" />
              </div>
              <div id="search_icon" style="display: table-cell; padding-left:1px;"><a href="#" onclick="doSearch();" title="<?php echo JText::_('RS1_ADMIN_APPT_LIST_SEARCH_HELP');?>"><img src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/search.png"border="0" /></a></div>
              </td>
            <td style="font-size:11px; text-align:right"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER');?>&nbsp;
              <input readonly="readonly" name="startdateFilter" id="startdateFilter" type="hidden" 
                  class="sv_date_box" size="10" maxlength="10" value="<?php echo $startdateFilter ?>" />
              <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
                    value="<?php echo $display_picker_date ?>" onchange="selectStartDate(); return false;">
              &nbsp;
              <input readonly="readonly" name="enddateFilter" id="enddateFilter" type="hidden" 
                  class="sv_date_box" size="10" maxlength="10" value="<?php echo $enddateFilter ?>" />
              <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
                    value="<?php echo $display_picker_date2 ?>" onchange="selectEndDate(); return false;">
              <a href="#" onclick="cleardate(); return false;"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER_CLEAR');?></a></td>
          </tr>
          <tr>
            <td colspan="2" style="text-align:right"><select name="request_resourceFilter" id="request_resourceFilter" onchange="changeRequestResourceFilter();" style="font-size:11px; width:auto;"  >
                <option value="0" <?php if($this->filter_request_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_RESOURCE_NONE');?></option>
                <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
                <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_request_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                <?php $k = 1 - $k; 
                    } ?>
              </select>
              &nbsp;&nbsp;
              <select name="request_status" onchange="changeRequestStatusFilter();" style="font-size:11px; width:auto;">
                <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
                <?php foreach($statuses as $status_row){ ?>
                <option value="<?php echo $status_row->internal_value ?>" <?php if($this->filter_request_status == $status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($status_row->status);?></option>
                <?php } ?>
              </select>
              <select name="payment_status" onchange="changePaymentStatusFilter();" style="font-size:11px; width:auto;">
                <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_PAYMENT_STATUS_NONE');?></option>
                <?php foreach($pay_statuses as $pay_status_row){ ?>
                <option value="<?php echo $pay_status_row->internal_value ?>" <?php if($this->filter_payment_status == $pay_status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($pay_status_row->status);?></option>
                <?php } ?>
              </select></td>
          </tr>
        </thead>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" width="100%">
        <tr class="fe_admin_header" style="font-size:smaller">
          <th class="svtitle" align="center"><input type="checkbox" name="toggle" value="" onclick="checkAll2(<?php echo sv_count_($this->items); ?>, 'appt_cb');" /></th>
          <th align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_REQ_ID_COL_HEAD'), 'id_requests', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'), 'name', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_EMAIL_COL_HEAD'), 'email', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD'), 'ResourceName',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_FROM_COL_HEAD'), 'startdatetime',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD'), 'ServiceName',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD'), 'request_status',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
          <th align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PAYMENT_COL_HEAD'), 'payment_status',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
        </tr>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items ); $i++) {
        $row = $this->items[$i];
        	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=edit&cid='. $row->id_requests.'&frompage=advadmin&tab=0');

       ?>
        <tr class="<?php echo "row$k"; ?>">
          <td align="center"><input type="checkbox" id="appt_cb<?php echo $i;?>" name="cid_req[]" value="<?php echo $row->id_requests; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td align="center"><?php echo $row->id_requests; ?></td>
          <td><a href=<?php echo $link; ?>><u><?php echo stripslashes($row->name); ?></u></a></td>
          <td align="left"><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a></td>
          <td align="left"><?php echo JText::_(stripslashes($row->ResourceName)); ?>&nbsp;</td>
          <td align="left"><?php echo $row->display_startdate; ?>&nbsp;<?php echo $row->display_starttime; ?></td>
          <td align="left"><?php echo JText::_(stripslashes($row->ServiceName)); ?></td>
          <?php if($apptpro_config->status_quick_change == "No"){ ?>
          <td align="center"><?php echo translated_status($row->request_status); ?></td>
          <?php } else {?>
          <td align=\"center\"><select id="booking_status_<?php echo $row->id_requests?>" name="booking_status_<?php echo $row->id_requests?>" style="width:auto" 
				onfocus="this.oldvalue = this.value;" onchange="quick_status_change('<?php echo $row->id_requests?>',this);">
              <?php foreach($statuses as $status_row){ ?>
              <option value="<?php echo $status_row->internal_value?>" 
						<?php echo ($row->request_status == $status_row->internal_value ? " selected='selected' ":"");?>
						><?php echo JText::_($status_row->status)?></option>
              <?php } ?>
            </select></td>
          <?php } ?>
          <td align="center"><?php echo translated_status($row->payment_status).($row->invoice_number != ""?"<br/>(".$row->invoice_number.")":"") ?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
    
    ?>
      </table>
      <input type="hidden" name="id" id="id" value="" />
      <input type="hidden" name="option" value="<?php echo $option; ?>" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="reminders" id="reminders" value="" />
      <input type="hidden" name="filter" id="filter" value="" />
      <input type="hidden" name="resourceFilter" id="resourceFilter" value="" />
      <input type="hidden" name="req_filter_order" value="<?php echo $this->lists['order_req']; ?>" />
      <input type="hidden" name="req_filter_order_Dir" value="<?php echo $this->lists['order_Dir_req']; ?>" />
      <input type="hidden" name="requests_tab" value ="0" />
      
      <!--</div>--> <!--End of tab 1-->
      <?php

	if($apptpro_config->adv_admin_show_resources == "Yes" || in_array($apptpro_config->adv_admin_show_resources, $my_groups)){
		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel2' class='tab-pane'>";
		
	//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_RESOURCES'), 'panel2');	 

		
	$link_new_res = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=res_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_RESOURCES');?></th>
          <th style="text-align:right"> <a href="javascript:doPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:goResCopy();"><?php echo JText::_('RS1_ADMIN_SCRN_COPY');?></a>&nbsp;| <a href="javascript:doResRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_res; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th>&nbsp;</th>
            <th colspan="8" style="text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES');?>&nbsp;
              <select name="resource_categoryFilter" id="resource_categoryFilter"
           onchange="selectCategory(<?php echo $tab ?>);" style="font-size:11px; width:auto;">
                <option value="0" <?php if($this->filter_resource_category == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_PROMPT');?></option>
                <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($cat_rows ); $i++) {
                    $cat_row = $cat_rows[$i];
                    ?>
                <option value="<?php echo $cat_row->id_categories; ?>" <?php if($this->filter_resource_category == $cat_row->id_categories){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($cat_row->name)); ?></option>
                <?php $k = 1 - $k; 
                    } ?>
              </select></th>
          </tr>
          <tr class="fe_admin_header">
            <th class="svtitle"  width="3%"><input type="checkbox" name="toggle2" value="" onclick="checkAll2(<?php echo sv_count_($this->items_res); ?>, 'res_cb',2);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_resources', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'), 'name', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_DAYS_COL_HEAD');?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_TIMESLOTS_COL_HEAD'), 'timeslots', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_CATEGORY_COL_HEAD_NEW'), 'cat_name', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ORDER_COL_HEAD'), 'ordering', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
          </tr>
        </thead>
        <?php
		$k = 0;
		for($i=0; $i < sv_count_($this->items_res ); $i++) {
		$res_row = $this->items_res[$i];
		if($res_row->published==1){
			$published 	= "<a href='javascript:doUnPublish(".$i.")'>".$pub."</a>";
		} else {
			$published 	= "<a href='#' OnClick='javascript:doPublish(".$i.");return false;'>".$unpub."</a>";
		}	
		$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=res_detail&cid='. $res_row->id_resources.'&frompage=advadmin&tab='.$tab);
		
	   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td align="center"><input type="checkbox" id="res_cb<?php echo $i;?>" name="cid_res[]" value="<?php echo $res_row->id_resources; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td align="center"><?php echo $res_row->id_resources; ?>&nbsp;</td>
          <td><a href="<?php echo $link; ?>"><u><?php echo JText::_(stripslashes($res_row->name)); ?></u></a></td>
          <td align="left"><?php echo JText::_(stripslashes($res_row->description)); ?>&nbsp;</td>
          <td align="center"><?php 
			echo ($res_row->allowSunday=="Yes" ? JText::_('RS1_ADMIN_SCRN_SUN').' ' : '');
			echo ($res_row->allowMonday=="Yes" ? JText::_('RS1_ADMIN_SCRN_MON').' ' : '');
			echo ($res_row->allowTuesday=="Yes" ? JText::_('RS1_ADMIN_SCRN_TUE').' ' : '');
			echo ($res_row->allowWednesday=="Yes" ?JText::_('RS1_ADMIN_SCRN_WED').' ' : '');
			echo ($res_row->allowThursday=="Yes" ?JText::_('RS1_ADMIN_SCRN_THU').' ' : '');
			echo ($res_row->allowFriday=="Yes" ?JText::_('RS1_ADMIN_SCRN_FRI').' ' : '');
			echo ($res_row->allowSaturday=="Yes" ?JText::_('RS1_ADMIN_SCRN_SAT').' ' : '');
			 ?></td>
          <td align="center"><?php echo $res_row->timeslots; ?>&nbsp;</td>
          <td align="center"><?php echo str_replace("||",",",$res_row->category_scope); ?>&nbsp;</td>
          <td align="center"><?php echo $res_row->ordering; ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
	
	?>
      </table>
      <input type="hidden" name="res_filter_order" value="<?php echo $this->lists['order_res']; ?>" />
      <input type="hidden" name="res_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_res'] ?>" />
      <input type="hidden" name="resources_tab" id="resources_tab" value ="<?php echo $tab ?>" />
      <?php
	}
	
	if($apptpro_config->adv_admin_show_services == "Yes" || in_array($apptpro_config->adv_admin_show_services, $my_groups)){
		
		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel3' class='tab-pane'>";
		//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_SERVICES'), 'panel3');	 

		$link_new_srv = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=services_detail&cid=0&frompage=advadmin&tab='.$tab);
	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_SERVICES');?> <br />
          </th>
          <th style="text-align:right"> <a href="javascript:doSrvPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doSrvUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:goSrvCopy();"><?php echo JText::_('RS1_ADMIN_SCRN_COPY');?></a>&nbsp;| <a href="javascript:doSrvRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_srv; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead >
          <tr class="fe_admin_header">
            <th>&nbsp;</th>
            <th colspan="7" style="text-align:right"><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE');?>&nbsp;
              <select name="service_resourceFilter" id="service_resourceFilter"
           onchange="selectResource(<?php echo $tab ?>);" style="font-size:11px; width:auto;">
                <option value="0" <?php if($this->filter_service_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
                <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
                <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_service_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                <?php $k = 1 - $k; 
                    } ?>
              </select></th>
          </tr>
          <tr class="fe_admin_header">
            <th class="svtitle" width="3%"><input type="checkbox" name="toggle3" value="" onclick="checkAll2(<?php echo sv_count_($this->items_srv); ?>, 'srv_cb',3);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_services', $this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
            <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD'), 'name',$this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
            <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_COL_HEAD'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_SERVICE_STAFF_ONLY'), 'staff_only', $this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ORDER_COL_HEAD'), 'ordering', $this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_srv'], $this->lists['order_srv'], "srv_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items_srv ); $i++) {
        $srv_row = $this->items_srv[$i];
		if($srv_row->published==1){
			$published 	= "<a href='javascript:doSrvUnPublish(".$i.")'>".$pub."</a>";
		} else {
			$published 	= "<a href='#' OnClick='javascript:doSrvPublish(".$i.");return false;'>".$unpub."</a>";
		}	
		$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=services_detail&cid='. $srv_row->id_services.'&frompage=advadmin&tab='.$tab);
       ?>
        <tr class="<?php echo "row$k"; ?>">
          <td align="center"><input type="checkbox" id="srv_cb<?php echo $i;?>" name="cid_srv[]" value="<?php echo $srv_row->id_services; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td align="center"><?php echo $srv_row->id_services; ?></u>&nbsp;</td>
          <td align="left"><a href="<?php echo $link; ?>"><?php echo JText::_(stripslashes($srv_row->name)); ?></a></td>
          <td align="left"><?php echo JText::_(stripslashes($srv_row->description)); ?>&nbsp;</td>
          <td align="center"><?php echo str_replace("||",",",$srv_row->resource_scope); ?>&nbsp;</td>
          <td align="center"><?php echo $srv_row->staff_only;?>&nbsp;</td>
          <td align="center"><?php echo $srv_row->ordering; ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
    
    ?>
      </table>
      <input type="hidden" name="srv_filter_order" value="<?php echo $this->lists['order_srv']; ?>" />
      <input type="hidden" name="srv_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_srv'] ?>" />
      <input type="hidden" name="services_tab" id="services_tab" value ="<?php echo $tab ?>" />
      <?php
	}

	if($apptpro_config->adv_admin_show_timeslots == "Yes" || in_array($apptpro_config->adv_admin_show_timeslots, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel4' class='tab-pane'>";
		//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_TIMESLOTS'), 'panel4');	 
		
		$daynames = array(0=>'Sunday', 1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday');

		$link_new_ts = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=timeslots_detail&cid=0&frompage=advadmin&tab='.$tab);
		
	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_TIMESLOTS');?> <br />
          </th>
          <th style="text-align:right"> <a href="javascript:doTSPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doTSUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:goTSCopy();"><?php echo JText::_('RS1_ADMIN_SCRN_COPY');?></a>&nbsp;| <a href="javascript:doImportGlobal();"><?php echo JText::_('RS1_ADMIN_SCRN_IMPORT_GLOBAL');?></a>&nbsp;| <a href="javascript:doTSRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_ts; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th> <table align="right">
                <tr>
                  <td style="text-align:right"><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE');?></td>
                  <td style="text-align:right"><select name="timeslots_resourceFilter" id="timeslots_resourceFilter" onchange="selectResource(<?php echo $tab ?>);" style="font-size:11px; width:auto" >
                      <option value="0" <?php if($this->filter_timeslots_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
                      <?php
                        $k = 0;
                        for($i=0; $i < sv_count_($res_rows ); $i++) {
                        $res_row = $res_rows[$i];
                        ?>
                      <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_timeslots_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                      <?php $k = 1 - $k; 
                        } ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;&nbsp;</td>
                  <td style="text-align:right"><?php echo JText::_('RS1_ADMIN_SCRN_TS_DAY');?></td>
                  <td><select name="day_numberFilter" id="day_numberFilter" onchange="selectDay();" style="font-size:11px; width:auto" >
                      <option value="all" <?php if($this->filter_day_number == "all"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_TS_SHOWALL');?></option>
                      <option value="0"<?php if($this->filter_day_number == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SUNDAY');?></option>
                      <option value="1"<?php if($this->filter_day_number == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_MONDAY');?></option>
                      <option value="2"<?php if($this->filter_day_number == "2"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_TUESDAY');?></option>
                      <option value="3"<?php if($this->filter_day_number == "3"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_WEDNESDAY');?></option>
                      <option value="4"<?php if($this->filter_day_number == "4"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_THURSDAY');?></option>
                      <option value="5"<?php if($this->filter_day_number == "5"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FRIDAY');?></option>
                      <option value="6"<?php if($this->filter_day_number == "6"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SATURDAY');?></option>
                    </select>
                    &nbsp;&nbsp; </td>
                </tr>
              </table>
            </th>
          </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle5" value="" onclick="checkAll2(<?php echo sv_count_($this->items_ts); ?>, 'ts_cb',5);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_timeslots', $this->lists['order_Dir_ts'], $this->lists['order_ts'], "ts_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_COL_HEAD');?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DAY_COL_HEAD'), 'day_number', $this->lists['order_Dir_ts'], $this->lists['order_ts'], "ts_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_STAFF'), 'staff_only', $this->lists['order_Dir_res'], $this->lists['order_res'], 'res_'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_START_COL_HEAD'), 'timeslot_starttime', $this->lists['order_Dir_ts'], $this->lists['order_ts'], "ts_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_END_COL_HEAD'), 'timeslot_endtime', $this->lists['order_Dir_ts'], $this->lists['order_ts'], "ts_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_ts'], $this->lists['order_ts'], "ts_" ); ?></th>
          </tr>
        </thead>
        <?php
            $k = 0;
            for($i=0; $i < sv_count_($this->items_ts ); $i++) {
            $ts_row = $this->items_ts[$i];
            if($ts_row->published==1){
                $published 	= "<a href='javascript:doTSUnPublish(".$i.")'>".$pub."</a>";
            } else {
                $published 	= "<a href='#' OnClick='javascript:doTSPublish(".$i.");return false;'>".$unpub."</a>";
            }	
            $link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=timeslots_detail&cid='. $ts_row->id_timeslots.'&frompage=advadmin&tab='.$tab);
    
           ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="ts_cb<?php echo $i;?>" name="cid_ts[]" value="<?php echo $ts_row->id_timeslots; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td width="5%" align="center"><a href="<?php echo $link; ?>"><u><?php echo stripslashes($ts_row->id_timeslots); ?></u></a></td>
          <!--          <td width="5%" align="center"><a href="#edit_timeslot" onclick="hideMainMenu(); return listItemTask('cb<?php echo $i;?>','edit_timeslot')"><?php echo $ts_row->id; ?></a></td>-->
          <td align="center"><?php echo ($ts_row->name == ""?"Global": JText::_(stripslashes($ts_row->name))); ?>&nbsp;</td>
          <td align="center"><?php echo JText::_($daynames[$ts_row->day_number]); ?>&nbsp;</td>
          <td align="center"><?php echo $ts_row->staff_only;?></td>
          <td align="center"><?php echo $ts_row->timeslot_starttime; ?>&nbsp;</td>
          <td align="center"><?php echo $ts_row->timeslot_endtime; ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
        
        ?>
      </table>
      <br />
      <span style="font-size:11px;">
      <input type="hidden" name="ts_filter_order" value="<?php echo $this->lists['order_ts']; ?>" />
      <input type="hidden" name="ts_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_ts'] ?>" />
      <input type="hidden" name="timeslots_tab" id="timeslots_tab" value ="<?php echo $tab ?>" />
      <?php echo JText::_('RS1_ADMIN_SCRN_TS_RESOURCE_NOTE');?></span>
      <?php
		
	}
	
	if($apptpro_config->adv_admin_show_bookoffs == "Yes" || in_array($apptpro_config->adv_admin_show_bookoffs, $my_groups)){
		
		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel5' class='tab-pane'>";
		//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_BOOKOFFS'), 'panel5');	 

		$link_new_bo = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=bookoffs_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOKOFFS');?><br /></th>
          <th style="text-align:right"> <a href="javascript:doBOPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doBOUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:goBOCopy();"><?php echo JText::_('RS1_ADMIN_SCRN_COPY');?></a>&nbsp;| <a href="javascript:doBORemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_bo; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th align="left" >&nbsp;</th>
            <th style="text-align:right" colspan="8"><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE');?>&nbsp;
              <select name="bookoffs_resourceFilter" id="bookoffs_resourceFilter" 
              onchange="selectResource(<?php echo $tab ?>);" style="font-size:11px; width:auto" >
                <option value="0" <?php if($this->filter_bookoffs_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
                <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
                <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_bookoffs_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                <?php $k = 1 - $k; 
                    } ?>
              </select>
              &nbsp;</th>
          </tr>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle4" value="" onclick="checkAll2(<?php echo sv_count_($this->items_bo); ?>, 'bo_cb',4);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_bookoffs', $this->lists['order_Dir_bo'], $this->lists['order_bo'], "bo_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_COL_HEAD');?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DATEOFF_COL_HEAD'), 'off_date', $this->lists['order_Dir_bo'], $this->lists['order_bo'], "bo_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_FULDAY_COL_HEAD'); ?></th>
            <th class="svtitle" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_RANGE_COL_HEAD'); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir_bo'], $this->lists['order_bo'], "bo_" ); ?></th>
            <th width="15%" class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_bo'], $this->lists['order_bo'], "bo_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items_bo ); $i++) {
        $boff_row = $this->items_bo[$i];
		if($boff_row->published==1){
			$published 	= "<a href='javascript:doBOUnPublish(".$i.")'>".$pub."</a>";
		} else {
			$published 	= "<a href='#' OnClick='javascript:doBOPublish(".$i.");return false;'>".$unpub."</a>";
		}	
		$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=bookoffs_detail&cid='. $boff_row->id_bookoffs.'&frompage=advadmin&tab='.$tab);
       ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="bo_cb<?php echo $i;?>" name="cid_bo[]" value="<?php echo $boff_row->id_bookoffs; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td width="5%" align="center"><?php echo stripslashes($boff_row->id_bookoffs); ?></td>
          <td width="20%" align="center"><a href="<?php echo $link; ?>"><u><?php echo ($boff_row->name == ""?"Global": JText::_(stripslashes($boff_row->name))); ?></u></a>&nbsp;</td>
          <td width="20%" align="center"><?php echo JText::_($boff_row->off_date_display); ?>&nbsp;</td>
          <td width="10%" align="center"><?php echo $boff_row->full_day; ?>&nbsp;</td>
          <td width="10%" align="center"><?php echo $boff_row->hours; ?>&nbsp;</td>
          <td width="20%" align="center"><?php echo JText::_(stripslashes($boff_row->description)); ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
    
    ?>
      </table>
      <input type="hidden" name="bo_filter_order" value="<?php echo $this->lists['order_bo']; ?>" />
      <input type="hidden" name="bo_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_bo'] ?>" />
      <input type="hidden" name="bookoffs_tab" id="bookoffs_tab" value ="<?php echo $tab ?>" />
      <?php    
		
	}

	if($apptpro_config->adv_admin_show_book_dates == "Yes" || in_array($apptpro_config->adv_admin_show_book_dates, $my_groups)){
		
		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel12' class='tab-pane'>";

		$link_new_bd = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=book_dates_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
       <p class="row0"><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_INTRO');?></p>       
       <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_BOOK_DATES');?><br /></th>
          <th style="text-align:right"> <a href="javascript:doBDPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doBDUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:do_book_date_purge();"><?php echo JText::_('RS1_ADMIN_SCRN_PURGE');?></a>&nbsp;| <a href="javascript:doBDRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_bd; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>     
	 <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th align="left" >&nbsp;</th>
            <th style="text-align:right" colspan="8">
			<?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE');?>&nbsp;
              <select name="book_dates_resourceFilter" id="book_dates_resourceFilter" 
              onchange="selectResource(<?php echo $tab ?>);" style="font-size:11px; width:auto" >
                <option value="0" <?php if($this->filter_book_dates_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
                <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
                <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_book_dates_resource == $res_row->id_resources){echo " selected='selected' "; $selected_resource_book_dates = $res_row->date_specific_booking;} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                <?php $k = 1 - $k; 
                    } ?>
              </select>
              &nbsp;
              <?php if($this->filter_book_dates_resource > "0"){ ?>
              <br/>
				<?php echo JText::_('RS1_ADMIN_RATE_BOOK_DATES_ENABLE');?>: <select id="enable_book_dates" name="enable_book_dates" onchange="set_enable_book_dates(); return false;" style="width:100px;">
                    <option value="Yes" <?php if($selected_resource_book_dates == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($selected_resource_book_dates != "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                </select>&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_RATE_BOOK_DATES_ENABLE_HELP');?>
              <?php } ?>  
              </th>
          </tr>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle12" value="" onclick="checkAll2(<?php echo sv_count_($this->items_bd); ?>, 'bd_cb',12);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_book_dates', $this->lists['order_Dir_bd'], $this->lists['order_bd'], "bd_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_BOOKDATE_COL_HEAD'), 'book_date', $this->lists['order_Dir_bd'], $this->lists['order_bd'], "bd_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir_bd'], $this->lists['order_bd'], "bd_" ); ?></th>
            <th width="15%" class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_bd'], $this->lists['order_bd'], "bd_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items_bd ); $i++) {
        $bdate_row = $this->items_bd[$i];
		if($bdate_row->published==1){
			$published 	= "<a href='javascript:doBDUnPublish(".$i.")'>".$pub."</a>";
		} else {
			$published 	= "<a href='#' OnClick='javascript:doBDPublish(".$i.");return false;'>".$unpub."</a>";
		}	
		$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=book_dates_detail&cid='. $bdate_row->id_book_dates.'&frompage=advadmin&tab='.$tab);
       ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="bd_cb<?php echo $i;?>" name="cid_bd[]" value="<?php echo $bdate_row->id_book_dates; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td width="5%" align="center"><?php echo stripslashes($bdate_row->id_book_dates); ?></td>
          <td align="center"><a href="<?php echo $link; ?>"><u><?php echo $bdate_row->book_date_display; ?></u></a>&nbsp;</td>
          <td align="center"><?php echo JText::_(stripslashes($bdate_row->description)); ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
    
    ?>
      </table>     
      <input type="hidden" name="bd_filter_order" value="<?php echo $this->lists['order_bd']; ?>" />
      <input type="hidden" name="bd_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_bd'] ?>" />
      <input type="hidden" name="book_dates_tab" id="book_dates_tab" value ="<?php echo $tab ?>" />
      <?php    
		
	}

		if($apptpro_config->adv_admin_show_coupons == "Yes" || in_array($apptpro_config->adv_admin_show_coupons, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel7' class='tab-pane'>";
		//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_COUPONS'), 'panel7');	 

		// get operator's resources
        $user = JFactory::getUser();
        $sql = "SELECT CONCAT(\"|\",id_resources,\"|\") as wrapped_id FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%' ";
		try{
			$database->setQuery($sql);
	        $my_resources = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$link_new_coup = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=coupons_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_COUPONS');?></th>
          <th style="text-align:right"> <a href="javascript:doCoupPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doCoupUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:goCoupCopy();"><?php echo JText::_('RS1_ADMIN_SCRN_COPY');?></a>&nbsp;| <a href="javascript:doCoupRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_coup; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr>
            <td colspan="10"><div style="float:right">
                <div style="display: table-cell; padding-left:1px;">
                  <input type="text" id="coupon_search" name="coupon_search" style="font-size:11px;" size="15" title="<?php echo JText::_('RS1_ADMIN_COUPON_LIST_SEARCH_HELP');?>" 
                value="<?php echo $this->filter_coupon_search ?>" onchange="doSearch();" />
                </div>
                <div style="display: table-cell; padding-left:5px;"> <a href="#" onclick="doSearch();" title="<?php echo JText::_('RS1_ADMIN_COUPON_LIST_SEARCH');?>"> <img height="16" hspace="2" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/search.png" width="16" border="0" /></a> </div>
              </div></td>
          </tr>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle7" value="" onclick="checkAll2(<?php echo sv_count_($this->items_coup); ?>, 'coup_cb',7);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_coupons', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_DESC'), 'description', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_CODE'), 'coupon_code',$this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_VALUE'), 'discount', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_EXPIRY'), 'expiry_date', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_MAX_USER_USE_COL'), 'max_user_use', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_MAX_TOTAL_USE_COL'), 'max_total_use', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_CUR_TOTAL_USE_COUNT'), 'current_count', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_"); ?></th>
            <th width="15%" class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_coup'], $this->lists['order_coup'], "coup_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
		
        for($i=0; $i < sv_count_($this->items_coup ); $i++) {
			$coup_row = $this->items_coup[$i];
			// only show this row if the coupon is assigned to one of the operator's resources
			$show_row = false;
			foreach ($my_resources as $my_resource) {
				if(strpos($coup_row->scope, $my_resource->wrapped_id) > -1){
					$show_row = true;
				}	
			}
			if($show_row == true){
				if($coup_row->published==1){
					$published 	= "<a href='javascript:doCoupUnPublish(".$i.")'>".$pub."</a>";
				} else {
					$published 	= "<a href='#' OnClick='javascript:doCoupPublish(".$i.");return false;'>".$unpub."</a>";
				}	
				$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=coupons_detail&cid='. $coup_row->id_coupons.'&frompage=advadmin&tab='.$tab);

			   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="coup_cb<?php echo $i;?>" name="cid_coup[]" value="<?php echo $coup_row->id_coupons; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td width="5%" align="center"><?php echo stripslashes($coup_row->id_coupons); ?></td>
          <td align="left"><?php echo $coup_row->description; ?>&nbsp;</td>
          <td align="center"><a href="<?php echo $link; ?>"><u><?php echo $coup_row->coupon_code; ?></u></a>&nbsp;</td>
          <td align="center"><?php echo $coup_row->discount; ?>/<?php echo $coup_row->discount_unit; ?>&nbsp;</td>
          <td align="center"><?php echo $coup_row->expiry; ?>&nbsp;</td>
          <td class="center"><?php echo $coup_row->max_user_use; ?>&nbsp;</td>
          <td class="center"><?php echo $coup_row->max_total_use; ?>&nbsp;</td>
          <td class="center"><?php echo $coup_row->current_count; ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php 
			}  // if show_row
		} // for
    
    ?>
      </table>
      <br />
      <p><span style="font-size:smaller"><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_NOTE2');?></span></p>
      <input type="hidden" name="coup_filter_order" value="<?php echo  $this->lists['order_coup']; ?>" />
      <input type="hidden" name="coup_filter_order_Dir" value ="<?php echo  $this->lists['order_Dir_coup'];?>" />
      <input type="hidden" name="coupons_tab" id="coupons_tab" value ="<?php echo $tab ?>" />
      <?php    
		
	}
		if($apptpro_config->adv_admin_show_extras == "Yes" || in_array($apptpro_config->adv_admin_show_extras, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel8' class='tab-pane'>";
		//echo JHtml::_('tabs.panel', JText::_('RS1_ADMIN_SCRN_TAB_EXTRAS'), 'panel8');	 

		// get operator's resources
        $user = JFactory::getUser();
        $sql = "SELECT CONCAT(\"|\",id_resources,\"|\") as wrapped_id FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%' ";
		try{
			$database->setQuery($sql);
	        $my_resources = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		

		$link_new_ext = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=extras_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_EXTRAS');?></th>
          <th  style="text-align:right"> <a href="javascript:doExtPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doExtUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:doExtRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_ext; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle8" value="" onclick="checkAll2(<?php echo sv_count_($this->items_ext); ?>, 'ext_cb',8);" /></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_extras', $this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_" ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_EXTRAS_LABEL'), 'extras_label', $this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_EXTRAS_COST'), 'extras_cost',$this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_EXTRAS_UNITS'), 'cost_unit', $this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_"  ); ?></th>
            <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_EXTRAS_ORDERING'), 'ordering', $this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_"  ); ?></th>
            <th width="15%" class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_ext'], $this->lists['order_ext'], "ext_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
		
        for($i=0; $i < sv_count_($this->items_ext ); $i++) {
			$ext_row = $this->items_ext[$i];
			// only show this row if the coupon is assigned to one of the operator's resources
			$show_row = false;
			foreach ($my_resources as $my_resource) {
				if(strpos($ext_row->resource_scope, $my_resource->wrapped_id) > -1){
					$show_row = true;
				}	
			}
			if($show_row == true){
				if($ext_row->published==1){
					$published 	= "<a href='javascript:doExtUnPublish(".$i.")'>".$pub."</a>";
				} else {
					$published 	= "<a href='#' OnClick='javascript:doExtPublish(".$i.");return false;'>".$unpub."</a>";
				}	
				$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=extras_detail&cid='. $ext_row->id_extras.'&frompage=advadmin&tab='.$tab);

			   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="ext_cb<?php echo $i;?>" name="cid_ext[]" value="<?php echo $ext_row->id_extras; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td width="5%" align="center"><?php echo stripslashes($ext_row->id_extras); ?></td>
          <td align="left"><a href="<?php echo $link; ?>"><u><?php echo $ext_row->extras_label; ?></u></a>&nbsp;</td>
          <td align="center"><?php echo $ext_row->extras_cost; ?>&nbsp;</td>
          <td align="center"><?php 
                  if ($ext_row->cost_unit =='Hour') {
                            echo JTEXT::_('RS1_ADMIN_SCRN_EXTRAS_COST_HOUR');
                  } elseif ($ext_row->cost_unit =='Flat') {
                            echo JTEXT::_('RS1_ADMIN_SCRN_EXTRAS_COST_BOOKING'); 
                  }?>
          &nbsp;</td>
          <td align="center"><?php echo $ext_row->ordering; ?>&nbsp;</td>
          <td align="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php 
			}  // if show_row
		} // for
    
    ?>
      </table>
      <br />
      <p><span style="font-size:smaller"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_NOTE2');?></span></p>
      <input type="hidden" name="ext_filter_order" value="<?php echo  $this->lists['order_ext']; ?>" />
      <input type="hidden" name="ext_filter_order_Dir" value ="<?php echo  $this->lists['order_Dir_ext']; ?>" />
      <input type="hidden" name="extras_tab" id="extras_tab" value ="<?php echo $tab ?>" />
      <?php 
    } // end of show extras
		
		if($apptpro_config->adv_admin_show_rate_adj == "Yes" || in_array($apptpro_config->adv_admin_show_rate_adj, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel9' class='tab-pane'>";

		// get operator's resources
        $user = JFactory::getUser();
        $sql = "SELECT CONCAT(\"|\",id_resources,\"|\") as wrapped_id FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%' ";
		try{
			$database->setQuery($sql);
	        $my_resources = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		
		$link_new_ra = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=rate_adjustments_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_RATE_ADJUSTMENTS');?></th>
          <th  style="text-align:right"> <a href="javascript:doRAPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doRAUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:doRARemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_ra; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle9" value="" onclick="checkAll2(<?php echo sv_count_($this->items_ra); ?>, 'ra_cb',9);" /></th>
            <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_rate_adjustments', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?></th>
            <th class="center" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RES_NAME'), 'res_name', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?></th>
            <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_BY'), 'by_day_time', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?> </th>
            <!--      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_SUN'), 'adjustSunday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_MON'), 'adjustMonday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TUE'), 'adjustTuesday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_WED'), 'adjustWednesday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_THU'), 'adjustThursday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_FRI'), 'adjustFriday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_SAT'), 'adjustSaturday', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TIMESTART'), 'timeRangeStart', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TIMEEND'), 'timeRangeEnd', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?>	   </th>
-->
            <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE'), 'rate_adjustment', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?> </th>
            <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT'), 'rate_adjustment_unit', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?> </th>
            <th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_ra'], $this->lists['order_ra'], "ra_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
		
        for($i=0; $i < sv_count_($this->items_ra ); $i++) {
			$ra_row = $this->items_ra[$i];
			// only show this row if the coupon is assigned to one of the operator's resources
			$show_row = false;
			foreach ($my_resources as $my_resource) {
				if(strpos($my_resource->wrapped_id, $ra_row->entity_id ) > -1){
					$show_row = true;
				}	
			}
			if($show_row == true){
				if($ra_row->published==1){
					$published 	= "<a href='javascript:doRAUnPublish(".$i.")'>".$pub."</a>";
				} else {
					$published 	= "<a href='#' OnClick='javascript:doRAPublish(".$i.");return false;'>".$unpub."</a>";
				}	
				$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=rate_adjustments_detail&cid='. $ra_row->id_rate_adjustments.'&frompage=advadmin&tab='.$tab);

			   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="ra_cb<?php echo $i;?>" name="cid_ra[]" value="<?php echo $ra_row->id_rate_adjustments; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td class="center"><?php echo $ra_row->id_rate_adjustments; ?></td>
          <td class="center"><a href=<?php echo $link; ?>><u><?php echo $ra_row->res_name; ?></u></a></td>
          <td class="center"><?php echo translated_status($ra_row->by_day_time); ?></td>
          <!--                  <td class="center" style="border-left:thin solid #CCC"><?php echo ($ra_row->adjustSunday=="Yes"?$ra_row->adjustSunday:""); ?></td>
                  <td class="center"><?php echo ($ra_row->adjustMonday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center"><?php echo ($ra_row->adjustTuesday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center"><?php echo ($ra_row->adjustWednesday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center"><?php echo ($ra_row->adjustThursday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center"><?php echo ($ra_row->adjustFriday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center" style="border-right:thin solid #CCC"><?php echo ($ra_row->adjustSaturday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
                  <td class="center"><?php echo substr($ra_row->timeRangeStart,0,5); ?></td>
                  <td class="center" style="border-right:thin solid #CCC"><?php echo substr($ra_row->timeRangeEnd,0,5); ?></td>
-->
          <td class="center"><?php echo $ra_row->rate_adjustment; ?></td>
          <td class="center"><?php echo ($ra_row->rate_adjustment_unit=="Flat"?JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_FLAT'):JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_PERCENT')); ?></td>
          <td class="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php 
			}  // if show_row
		} // for
    
    ?>
      </table>
      <br />
      <p><span style="font-size:smaller"><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENTS_INTRO');?></span></p>
      <input type="hidden" name="ra_filter_order" value="<?php echo  $this->lists['order_ra']; ?>" />
      <input type="hidden" name="ra_filter_order_Dir" value ="<?php echo  $this->lists['order_Dir_ra']; ?>" />
      <input type="hidden" name="rate_adjustments_tab" id="rate_adjustments_tab" value ="<?php echo $tab ?>" />
      <?php 
    } // end of show rate_adjustments

	if($apptpro_config->adv_admin_show_seat_adj == "Yes" || in_array($apptpro_config->adv_admin_show_seat_adj, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel10' class='tab-pane'>";
		
		// get operator's resources
        $user = JFactory::getUser();
        $sql = "SELECT CONCAT(\"|\",id_resources,\"|\") as wrapped_id FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%' ";
		try{
			$database->setQuery($sql);
	        $my_resources = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		

		$link_new_sa = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=seat_adjustments_detail&cid=0&frompage=advadmin&tab='.$tab);

	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_SEAT_ADJUSTMENTS');?></th>
          <th  style="text-align:right"> <a href="javascript:doSAPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISH');?></a>&nbsp;| <a href="javascript:doSAUnPublish();"><?php echo JText::_('RS1_ADMIN_SCRN_UNPUBLISH');?></a>&nbsp;| <a href="javascript:doSARemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_sa; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
        <thead>
          <tr class="fe_admin_header">
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle10" value="" onclick="checkAll2(<?php echo sv_count_($this->items_sa); ?>, 'sa_cb',10);" /></th>
            <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_seat_adjustments', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?></th>
            <th class="center" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_RES_NAME'), 'res_name', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?></th>
            <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_BY'), 'by_day_time', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?> </th>
            <!--      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_SUN'), 'adjustSunday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_MON'), 'adjustMonday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TUE'), 'adjustTuesday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_WED'), 'adjustWednesday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_THU'), 'adjustThursday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_FRI'), 'adjustFriday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_SAT'), 'adjustSaturday', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TIMESTART'), 'timeRangeStart', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEATE_ADJUSTMENT_TIMEEND'), 'timeRangeEnd', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?>	   </th>
-->
            <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT'), 'seat_adjustment', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?> </th>
            <th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir_sa'], $this->lists['order_sa'], "sa_" ); ?></th>
          </tr>
        </thead>
        <?php
        $k = 0;
		
        for($i=0; $i < sv_count_($this->items_sa ); $i++) {
			$sa_row = $this->items_sa[$i];
			// only show this row if the coupon is assigned to one of the operator's resources
			$show_row = false;
			foreach ($my_resources as $my_resource) {
				if(strpos($my_resource->wrapped_id, $sa_row->id_resources ) > -1){
					$show_row = true;
				}	
			}
			if($show_row == true){
				if($sa_row->published==1){
					$published 	= "<a href='javascript:doSAUnPublish(".$i.")'>".$pub."</a>";
				} else {
					$published 	= "<a href='#' OnClick='javascript:doSAPublish(".$i.");return false;'>".$unpub."</a>";
				}	
				$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=seat_adjustments_detail&cid='. $sa_row->id_seat_adjustments.'&frompage=advadmin&tab='.$tab);

			   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="sa_cb<?php echo $i;?>" name="cid_sa[]" value="<?php echo $sa_row->id_seat_adjustments; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td class="center"><?php echo $sa_row->id_seat_adjustments; ?></td>
          <td class="center"><a href=<?php echo $link; ?>><u><?php echo $sa_row->res_name; ?></u></a></td>
          <td class="center"><?php echo translated_status($sa_row->by_day_time); ?></td>
          <td class="center"><?php echo $sa_row->seat_adjustment; ?></td>
          <td class="center"><?php echo $published;?></td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php 
			}  // if show_row
		} // for
    
    ?>
      </table>
      <br />
      <p><span style="font-size:smaller"><?php echo JText::_('RS1_ADMIN_SEAT_ADJUSTMENTS_INTRO');?></span></p>
      <input type="hidden" name="sa_filter_order" value="<?php echo  $this->lists['order_sa']; ?>" />
      <input type="hidden" name="sa_filter_order_Dir" value ="<?php echo  $this->lists['order_Dir_sa']; ?>" />
      <input type="hidden" name="seat_adjustments_tab" id="seat_adjustments_tab" value ="<?php echo $tab ?>" />
      <?php 
    } // end of show seat_adjustments

	if(in_array($apptpro_config->adv_admin_show_credits, $my_groups)){

		echo "</div>"; // end of previous tab
		$tab = $tab + 1;
	    echo "<div id='panel11' class='tab-pane'>";

		$link_new_uc = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=user_credit_detail&cid=0&frompage=advadmin&tab='.$tab);
		
	?>
      <table style="border-bottom:1px solid #666666;" width="100%">
        <tr>
          <th align="left"><?php echo JText::_('RS1_ADMIN_SCRN_TAB_USER_CREDIT');?></th>
          <th style="text-align:right"> <a href="javascript:doUCRemove();"><?php echo JText::_('RS1_ADMIN_SCRN_REMOVE');?></a>&nbsp;| <a href="<?php echo $link_new_uc; ?>"><?php echo JText::_('RS1_ADMIN_SCRN_NEW');?></a> </th>
        </tr>
      </table>
      <table class="table table-striped" >
        <thead>
          <tr>
            <th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle11" value="" onclick="checkAll2(<?php echo sv_count_($this->items_uc); ?>, 'uc_cb',11);" /></th>
            <th class="title" style="text-align:center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_user_credit', $this->lists['order_Dir_uc'], $this->lists['order_uc'], "uc_" ); ?></th>
            <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_ID'), 'user_id', $this->lists['order_Dir_uc'], $this->lists['order_uc'], "uc_" ); ?></th>
            <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_NAME'), 'name', $this->lists['order_Dir_uc'], $this->lists['order_uc'], "uc_" ); ?></th>
            <th class="title" style="text-align:right"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_BALANCE'), 'balance', $this->lists['order_Dir_uc'], $this->lists['order_uc'], "uc_" ); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items_uc ); $i++) {
		$uc_row = $this->items_uc[$i];
		$published 	= JHTML::_('grid.published', $uc_row, $i );
		$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=user_credit_detail&cid='. $uc_row->id_user_credit.'&frompage=advadmin&tab='.$tab );
		$checked 	= JHTML::_('grid.checkedout', $uc_row, $i, 'id_user_credit');
   ?>
        <tr class="<?php echo "row$k"; ?>">
          <td width="5%" align="center"><input type="checkbox" id="uc_cb<?php echo $i;?>" name="cid_uc[]" value="<?php echo $uc_row->id_user_credit; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
          <td style="text-align:center"><?php echo $uc_row->id_user_credit; ?>&nbsp;</td>
          <td align="center"><a href=<?php echo $link."&credit_type=uc"; ?>><u><?php echo  $uc_row->user_id; ?></u></a></td>
          <td align="center"><?php echo $uc_row->name." (".$uc_row->username.")"; ?>&nbsp;</td>
          <td style="text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $uc_row->balance; ?></span></td>
          <?php $k = 1 - $k; ?>
          <td>&nbsp;</td>
        </tr>
        <?php } 

?>
        <tfoot>
        <td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
            </tfoot>
      </table>
      <br />
      <p><span style="font-size:smaller"><?php echo JText::_('RS1_ADMIN_USER_CREDIT_INTRO');?></span></p>
      <input type="hidden" name="uc_filter_order" value="<?php echo  $this->lists['order_uc']; ?>" />
      <input type="hidden" name="uc_filter_order_Dir" value ="<?php echo  $this->lists['order_Dir_uc']; ?>" />
      <input type="hidden" name="user_credit_tab" id="user_credit_tab" value ="<?php echo $tab ?>" />
      <?php 
    } // end of show user credits


		$i2=20;
		foreach($pay_procs as $pay_proc){ 
			$who_to_show = getPayProcinFE($pay_proc->prefix);
			if($who_to_show == "Yes" || in_array($who_to_show, $my_groups)){			
				echo "</div>"; // end of previous tab
				$tab = $tab + 1;
				echo "<div id='panel".$i2."' class='tab-pane'>";
		    	include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR.$pay_proc->prefix.DIRECTORY_SEPARATOR.$pay_proc->prefix."_fe_trans.php";
				$i2++;	
			}				
		}

		echo "</div>"; // end of previous tab
		//echo JHtml::_('tabs.end');
	?>
    <!--End of tabs-->
 	 </div>
    <?php 
	} // end of if showform
	?>
    <br />
    <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
    <?php } ?>
    <input type="hidden" name="option" value="<?php echo $option; ?>" />
    <input type="hidden" name="controller" id="controller" value="admin" />
    <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="current_tab" id="current_tab" value="<?php echo $current_tab; ?>" />
    <input type="hidden" name="frompage" value="advadmin" />
    <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  </div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
