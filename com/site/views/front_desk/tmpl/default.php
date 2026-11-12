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


	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	$jinput = JFactory::getApplication()->input;

	$user = JFactory::getUser();
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$front_desk_view = $this->front_desk_view;
	$front_desk_resource_filter = $this->front_desk_resource_filter;
	$front_desk_category_filter = $this->front_desk_category_filter;
	$front_desk_status_filter = $this->front_desk_status_filter;
	$front_desk_payment_status_filter = $this->front_desk_payment_status_filter;
	$front_desk_user_search = $this->front_desk_user_search;

	$front_desk_cur_week_offset = $this->front_desk_cur_week_offset;
	$front_desk_cur_day = $this->front_desk_cur_day;
	$front_desk_cur_month = $this->front_desk_cur_month;
	$front_desk_cur_year = $this->front_desk_cur_year;

	//$mainframe = JFactory::getApplication();
	//$params = $mainframe->getParams('com_rsappt_pro3');

	$menu = JFactory::getApplication()->getMenu(); 
	$active = $menu->getActive(); 
	$menu_id = $active->id;
	$params = $menu->getParams($menu_id);

	$read_only = false;
	$resadmin_only = true;
	$month_view_only = false;
	if($params->get('fd_read_only') == 'Yes'){
		$read_only = true;
		// force month view only if screen is public
		$month_view_only = true;
		$front_desk_view = "month";
	}
	
	$resadmin_only = true;
	if($params->get('fd_res_admin_only') == 'No'){
		$resadmin_only = false;
	}

	$fd_login_required = true;
	if($params->get('fd_login_required') == 'No'){
		$fd_login_required = false;
	}

	$fd_allow_cust_hist = true;
	if($params->get('fd_allow_cust_hist') == 'No'){
		$fd_allow_cust_hist = false;
	}
	
	$fd_allow_show_seats = true;
	if($params->get('fd_allow_show_seats') == 'No'){
		$fd_allow_show_seats = false;
	}

	$fd_res_admin_only = true;
	if($params->get('fd_res_admin_only') == 'No'){
		$fd_res_admin_only = false;
	}

	$fd_use_page_title = true;
	if($params->get('fd_use_page_title') == 'No'){
		$fd_use_page_title = false;
	}

	$fd_allow_reminders = true;
	if($params->get('fd_allow_reminders') == 'No'){
		$fd_allow_reminders = false;
	}

	$fd_show_cats_filter = false;
	if($params->get('fd_show_cats_filter') == 'Yes'){
		$fd_show_cats_filter = true;
	}

	$fd_booking_staff_or_public = "Staff";
	if($params->get('fd_booking_staff_or_public') != null){
		$fd_booking_staff_or_public = $params->get('fd_booking_staff_or_public');
	}

	$fd_show_financials = true;
	if($params->get('fd_show_financials') == 'No'){
		$fd_show_financials = false;
	}

	$retore_settings = "";
	switch($front_desk_view){
		case "month":
			if($front_desk_cur_month != ""){
				$retore_settings = "'', '".$front_desk_cur_month."', '".$front_desk_cur_year."', ''";
			}		
			break;
		case "week":
			if($front_desk_cur_week_offset != ""){
				$retore_settings = "'', '', '', '".$front_desk_cur_week_offset."'";
			}
			break;
		case "day":
			if($front_desk_cur_day != ""){
				$retore_settings = "'".$front_desk_cur_day."', '', '', ''";
			}		
			break;
	}
	
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$single_category_mode = false;
	$single_category_id = "";
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

	$single_resource_mode = false;
	$single_resource_id = "";
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
		$fd_show_cats_filter = false; // turn off cat filter
		//echo "single resource mode (querystring), id=".$single_resource_id;
	}

	$header_text = $params->get('header_text', $jinput->getString('header_text',''));

	$showform= true;

	if($fd_login_required == false && $fd_res_admin_only == true){
		echo "<font color='red'>".JText::_('ERROR: Conflicting settings, you cannot require res-admin AND not require login.')."</font><br/>";
	}
	if(!$user->guest || $fd_login_required == false){
	
		$database = JFactory::getDBO();
		$andClause = "";
		
		// get catgories
		// cannot relate cateory to operator so shows all categories	
		if($single_category_mode){
			$andClause .= " AND id_categories = ". (int)$single_category_id;
		}	
		$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 '.$andClause;
		$sql .= " ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$cat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		$andClause = "";		
		// get resources
		if($single_resource_mode){
			$andClause .= " AND id_resources = ". (int)$single_resource_id;
		}	
		if($single_category_mode){
			$safe_search_string = '%|' . $database->escape( $single_category_id, true ) . '|%' ;
			$andClause .= " AND category_scope LIKE ".$database->quote( $safe_search_string, false );
		}
		$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE Published=1 ".$andClause;
		if($fd_res_admin_only){
			$sql .= " AND resource_admins LIKE '%|".$user->id."|%' ";
		}
		if($user->guest){
			// if not logged in, only show public resources
			$sql .= " AND access LIKE '%|1|%' ";
		}
		$sql .= " ORDER BY ordering;";
		//echo $sql;		
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// get statuses
		if($read_only){
			$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value IN('new', 'pending', 'accepted') ORDER BY ordering ";
		} else {
			$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value!='deleted' ORDER BY ordering ";
		}
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
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

		
		// purge stale paypal bookings
		if($apptpro_config->purge_stale_paypal == "Yes"){
			purgeStalePayPalBookings($apptpro_config->minutes_to_stale);
		}

	} else{
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	}

	if($fd_res_admin_only && $showform){
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
			$showform = false;
		}	
	}

	
	// add new booking link
	if($fd_booking_staff_or_public == 'Staff'){
		$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&view=front_desk').'&task=add_booking&frompage=front_desk&Itemid='.$itemid;
	} else {
		// only the staff booking screen is now supported
		//$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=booking_screen_gad&task=add_booking&frompage=front_desk&Itemid='.$itemid);
	}
	// link to customer history screen
	$link_history = JRoute::_( 'index.php?option=com_rsappt_pro3&view=front_desk').'&task=customer_history&frompage=front_desk&Itemid='.$itemid;	
	$pdflink = JRoute::_( 'index.php?option=com_rsappt_pro3&view=front_desk').'&task=printer&frompage=front_desk&Itemid='.$itemid.
	'&menu_id='.$menu_id.
	'&tmpl=component';
	
	$document = JFactory::getDocument();
	$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");

	
?>

<?php if($showform){?>
<link href="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script>
	var iframe = null;
	var jq_dialog = null;
	var jq_dialog_title = "";		
	var jq_dialog_close = "<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE')?>";		
</script>
<script language="JavaScript" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/script.js"></script>
<script src="//code.jquery.com/ui/1.8.2/jquery-ui.js"></script>

<script language="javascript">
	window.onload = function() {
		buildFrontDeskView( <?php echo $retore_settings ?>);	
		var an_link = document.getElementById("add_new_link");
          an_link.onclick = function() {
			if(document.getElementById("front_desk_view").value == "day"){
				//alert(document.getElementById("cur_day").value);
				var new_href = document.getElementById("add_new_link").getAttribute("href");
				if(new_href.indexOf("?")>-1){
					new_href += "&mystartdate="+document.getElementById("cur_day").value;
				} else {
					new_href += "?mystartdate="+document.getElementById("cur_day").value;
				}
				document.getElementById("add_new_link").setAttribute("href", new_href); 
			}
            return true;		
		  }
	} 	

	function goManifest(resid, startdate, starttime, endtime){
//		document.getElementById("redirect").value="manifest";
		document.getElementById("resid").value=resid;
		document.getElementById("startdate").value=startdate;
		document.getElementById("starttime").value=starttime;
		document.getElementById("endtime").value=endtime;
		Joomla.submitbutton('display_manifest');
		return false;		
	}

	function toggleTotals(){
		if(document.getElementById("cur_day") != null){
			buildFrontDeskView( document.getElementById("cur_day").value);
		}
	}
	
	function goDayView(day){
		document.getElementById("front_desk_view").selectedIndex=0;
		buildFrontDeskView(day);
	}

	function sendReminders(which){
		if(!check_somthing_is_checked("cid[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return;
		}
		if(which=="Email"){
			Joomla.submitbutton('reminders');
		} else if(which=="ThankYou"){
			Joomla.submitbutton('thankyou');
		} else {
			Joomla.submitbutton('reminders_sms');
		}	
		return false;		
	}
	
	function doSearch(){
/*		if(document.getElementById("user_search").value==""){
			alert("<?php echo JText::_('RS1_FRONTDESK_SCRN_SEARCH_HELP');?>");
			return false;
		}*/
		buildFrontDeskView();
	}

	function exportCSV(){
		if(!check_somthing_is_checked("cid[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return;
		}
		document.getElementById("task").value="export_csv";
		document.adminForm.submit();
		document.getElementById("task").value="";
	}

	function doInvoicing(){
		if(!check_somthing_is_checked("cid[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return false;
		}
		document.getElementById("task").value="create_invoice";		
		document.getElementById("adminForm").action = "<?php echo JURI::base( false );?>index.php?option=com_rsappt_pro3&view=admin_invoice";	
		document.adminForm.submit();			
	}

	
</script>
<form name="adminForm" id="adminForm" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<div id="sv_apptpro_front_desk">
<div id="sv_apptpro_front_desk_top">
    <table width="100%" >
        <tr>
          <td align="left" colspan="2"> <h3>
          <?php if($fd_use_page_title){
		  	echo JText::_($params->get('page_title'));
		  } else {
			echo JText::_('RS1_FRONTDESK_SCRN_TITLE');
		  }?>
            </h3></td>
          <td style="text-align:right"><?php echo $user->name ?></td>
        </tr>
        <tr>
			<td colspan="3" style="vertical-align:top; text-align:center"><div id="sv_header"><label><?php echo JText::_($header_text); ?></label></div></td>
        </tr>      
          <?php if($fd_allow_reminders){ ?>
            <tr id="reminder_links">
			<td colspan="3" align="right">
          	<a href="#" onclick="exportCSV();return(false);" title="<?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV_HELP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV');?></a>&nbsp;|&nbsp;
			<a href="javascript:sendReminders('Email');"title="<?php echo JText::_('RS1_ADMIN_SCRN_REMINDERS_TOOLTIP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS');?></a>
            <?php if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){?>&nbsp;|&nbsp;
				<a href="javascript:sendReminders('SMS');"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS_SMS');?></a>&nbsp;			
            <?php } ?>    
			&nbsp;|&nbsp;<a href="javascript:sendReminders('ThankYou');" title="<?php echo JText::_('RS1_ADMIN_SCRN_THANKYOU_TOOLTIP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_THANKYOU');?></a>
            |&nbsp;<a href='<?php echo $pdflink;?>' target='_blank' title="<?php echo JText::_('RS1_ADMIN_PRINT_TIP');?>"><?php echo JText::_('RS1_ADMIN_PRINT');?></a>
		<?php 	// look to see if invoiceing plugin is installed..
				if(JPluginHelper::isEnabled('abpro_plugins', 'abpro_invoicing')){ ?>
			        &nbsp;|&nbsp;<a href="#" onclick="doInvoicing();return false;" title="<?php echo JText::_('RS1_ADMIN_INVOICE_TIP');?>"><?php echo JText::_('RS1_ADMIN_TOOLBAR_APPOINTMENTS_INVOICE'); ?></a>
        <?php	}?>            
        	</td>
            </tr>
          <?php } ?>
		<tr>
         <td align="left">&nbsp;&nbsp;<select id="front_desk_view" name="front_desk_view" onchange="buildFrontDeskView()" style="font-size:11px; width:auto">
        <?php if(!$month_view_only){?>
            <option value="day" <?php if($front_desk_view == "day"){ echo " selected ";}?>><?php echo JText::_('RS1_FRONTDESK_SCRN_VIEW_DAY');?></option>
            <option value="week" <?php if($front_desk_view == "week"){ echo " selected ";}?>><?php echo JText::_('RS1_FRONTDESK_SCRN_VIEW_WEEK');?></option>
        <?php } ?>    
            <option value="month" <?php if($front_desk_view == "month"){ echo " selected ";}?>><?php echo JText::_('RS1_FRONTDESK_SCRN_VIEW_MONTH');?></option>
            </select> </td>
          <td align="right"></td>          
          <td style="text-align:right"><input type="text" id="user_search" name="user_search" size="20" style="width:150px" 
          	title="<?php echo JText::_('RS1_FRONTDESK_SCRN_SEARCH_HELP');?>" value="" /> 
            <input type="button" onclick="doSearch();" class="sv_apptpro3_request_text" value="<?php echo JText::_('RS1_FRONTDESK_SCRN_SEARCH');?>" /></td>
        </tr>
        <tr>
          <?php if($fd_booking_staff_or_public == 'None'){ ?>
	          <td colspan="2">&nbsp;&nbsp;
          <?php } else {?>
	          <td colspan="2">&nbsp;&nbsp;<a href="<?php echo $link ?>" id="add_new_link"><?php echo JText::_('RS1_FRONTDESK_SCRN_ADDNEW');?></a>
          <?php } ?>
        
          <?php if($fd_allow_cust_hist){ ?>
          &nbsp;|&nbsp;<a href="<?php echo $link_history ?>"><?php echo JText::_('RS1_FRONTDESK_SCRN_HISTORY');?></a>
          <?php } ?>
          </td>
          </tr>
          <tr>
          <td colspan="3" style="text-align:right;">             
          <?php if($fd_allow_show_seats){ ?>  
				<span id="chkSeatTotals"><input type="checkbox" id="showSeatTotals" name="showSeatTotals" onclick="toggleTotals(); " <?php echo $jinput->getString("showSeatTotals","") ?>/> <?php echo JText::_('RS1_FRONTDESK_SCRN_SHOW_SEAT_TOTALS');?>&nbsp;</span>
          <?php } ?>        

          <?php if($fd_show_cats_filter){ ?>          
            <select name="category_filter" id="category_filter" onchange="buildFrontDeskView();" style="font-size:11px; width:auto" >
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_CATEGORY_NONE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($cat_rows ); $i++) {
				$cat_row = $cat_rows[$i];
				?>
              <option value="<?php echo $cat_row->id_categories; ?>" <?php if($front_desk_category_filter == $cat_row->id_categories){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($cat_row->name)); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select>            &nbsp;&nbsp;
          <?php } ?>        

            <select name="resource_filter" id="resource_filter" onchange="buildFrontDeskView();" style="font-size:11px; width:auto" >
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_RESOURCE_NONE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
              <option value="<?php echo $res_row->id_resources; ?>" <?php if($front_desk_resource_filter == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select>            &nbsp;&nbsp;
          <select id="status_filter" name="status_filter" onchange="buildFrontDeskView()" style="font-size:11px; width:auto;">
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
			<?php foreach($statuses as $status_row){ ?>
                <option value="<?php echo $status_row->internal_value ?>" <?php if($front_desk_status_filter == $status_row->internal_value){echo " selected='selected' ";} ?> class="color_<?php echo $status_row->internal_value ?>" ><?php echo JText::_($status_row->status);?></option>        
            <?php } ?>
            </select>&nbsp;&nbsp;      
	     <?php if($fd_show_financials){?>
            <select id="payment_status_filter" onchange="buildFrontDeskView()" style="font-size:11px; width:auto;">
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_PAYMENT_STATUS_NONE');?></option>
            <?php foreach($pay_statuses as $pay_status_row){ ?>
                <option value="<?php echo $pay_status_row->internal_value ?>" <?php if($front_desk_payment_status_filter == $pay_status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($pay_status_row->status);?></option>        
            <?php } ?>
            </select>
         <?php } ?>
           </td>
        </tr> 
    </table>
</div>
<div id="calview_here">&nbsp;</div>

<input type="hidden" name="uid" id="uid" value="<?php echo $user->id; ?>">
<input type="hidden" name="redirect" id="redirect" value="" />
<input type="hidden" name="listpage" id="listpage" value="front_desk" />
<input type="hidden" name="startdate" id="startdate" value="" />
<input type="hidden" name="starttime" id="starttime" value="" />
<input type="hidden" name="endtime" id="endtime" value="" />
<input type="hidden" name="resid" id="resid" value="" />

  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" name="controller" value="front_desk" />
	<input type="hidden" name="id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="task" id='task' value="" />
	<input type="hidden" name="frompage" value="front_desk" />
  	<input type="hidden" name="frompage_item" id="frompage_item" value="<?php echo $itemid ?>" />

  	<input type="hidden" name="menu_id" id="menu_id" value="<?php echo $menu_id ?>"/>
  	<input type="hidden" name="wait_text" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT'); ?>"/>

  	<input type="hidden" name="single_cat_mode" id="single_cat_mode" value="<?php echo ($single_category_mode?"Yes":"No") ?>"/>
  	<input type="hidden" name="single_cat_value" id="single_cat_value" value="<?php echo $single_category_id?>"/>
  	<input type="hidden" name="single_res_mode" id="single_res_mode" value="<?php echo ($single_resource_mode?"Yes":"No") ?>"/>
  	<input type="hidden" name="single_res_value" id="single_res_value" value="<?php echo $single_resource_id?>"/>
  <input type="hidden" name="req_filter_order" />
  <input type="hidden" name="req_filter_order_Dir" />

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?> 
</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php } ?>

