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
	$front_desk_user_search = $this->front_desk_user_search;

	$front_desk_cur_week_offset = $this->front_desk_cur_week_offset;
	$front_desk_cur_day = $this->front_desk_cur_day;
	$front_desk_cur_month = $this->front_desk_cur_month;
	$front_desk_cur_year = $this->front_desk_cur_year;

	$menu = JFactory::getApplication()->getMenu(); 
	$menu_id = $jinput->getString( 'menu_id', '' ); // passed from normal view on 'print'
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
		logIt($e->getMessage(), "front_desk_tmpl_default_prt", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$showform= true;

	if($fd_login_required == false && $fd_res_admin_only == true){
		echo "<font color='red'>".JText::_('ERROR: Conflicting settings, you cannot require res-admin AND not require login.')."</font><br/>";
	}
	if(!$user->guest || $fd_login_required == false){
	
		$database = JFactory::getDBO();
		// get resources
		$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE Published=1 ";
		if($fd_res_admin_only){
			$sql .= " AND resource_admins LIKE '%|".$user->id."|%' ";
		}
		if($user->guest){
			// if not logged in, only show public resources
			$sql .= " AND access LIKE '%|1|%' ";
		}
		$sql .= " ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// get catgories
		// cannot relate cateory to operator so shows all categories	
		$sql = "SELECT * FROM #__sv_apptpro3_categories WHERE Published=1 ";
		$sql .= " ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$cat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk_tmpl_default_prt", "", "");
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
			logIt($e->getMessage(), "front_desk_tmpl_default_prt", "", "");
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
			logIt($e->getMessage(), "front_desk_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
			$showform = false;
		}	
	}

	
	// date for report
	require_once( JPATH_CONFIGURATION.DIRECTORY_SEPARATOR.'configuration.php' );
	$CONFIG = new JConfig();
	$timezone_identifier = $CONFIG->offset;
	date_default_timezone_set($timezone_identifier);
	
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

	$display_date = strftime(php_date_string_to_sql($apptpro_config->long_date_format, "PHP"));
?>

<?php if($showform){?>
<link href="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/script.js"></script>


<script language="javascript">
	window.onload = function() {
		buildFrontDeskView( <?php echo $retore_settings ?>);	
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
		if(which=="Email"){
			Joomla.submitbutton('reminders');
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
		document.getElementById("task").value="export_csv";
		document.adminForm.submit();
		document.getElementById("task").value="";
	}

	
</script>
<form name="adminForm" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<div id="sv_apptpro_front_desk">
<div id="sv_apptpro_front_desk_top">
    <table width="100%">
        <tr>
          <td align="left" colspan="2"> <h3>
          <?php if($fd_use_page_title){
		  	echo JText::_($params->get('page_title'));
		  } else {
			echo JText::_('RS1_FRONTDESK_SCRN_TITLE');
		  }?>
            </h3></td>
          <td style="text-align:right"><?php echo $display_date ?></td>
        </tr>
        <tr style="visibility:hidden; display:none"><td colspan="3">
          <?php if($fd_allow_reminders){ ?><div id="reminder_links" style="visibility:hidden; display:none; text-align:right">
          	<a href="#" onclick="exportCSV();return(false);" title="<?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV_HELP');?>"><?php echo JText::_('RS1_ADMIN_SCRN_EXPORT_CSV');?></a>&nbsp;|&nbsp;
			<a href="javascript:sendReminders('Email');"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS');?></a>
            <?php if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){?>&nbsp;|&nbsp;
				<a href="javascript:sendReminders('SMS');"><?php echo JText::_('RS1_ADMIN_SCRN_SEND_REMINDERS_SMS');?></a>&nbsp;			
            <?php } ?>    
            |&nbsp;<a href='<?php echo $pdflink;?>' target='_blank' title="<?php echo JText::_('RS1_ADMIN_PRINT_TIP');?>"><?php echo JText::_('RS1_ADMIN_PRINT');?></a>
        	</div>
          <?php } ?>
            </td>
        <tr style="visibility:hidden; display:none">
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
        <tr style="visibility:hidden; display:none">
          <?php if($fd_booking_staff_or_public == 'None'){ ?>
	          <td colspan="2">&nbsp;&nbsp;
          <?php } else {?>
	          <td colspan="2">&nbsp;&nbsp;<a href="<?php echo $link ?>"><?php echo JText::_('RS1_FRONTDESK_SCRN_ADDNEW');?></a>
          <?php } ?>
        
          <?php if($fd_allow_cust_hist){ ?>
          &nbsp;|&nbsp;<a href="<?php echo $link_history ?>"><?php echo JText::_('RS1_FRONTDESK_SCRN_HISTORY');?></a>
          <?php } ?>
          </td>
          <td style="text-align:right">
          <?php if($fd_allow_show_seats){ ?>          
				<input type="checkbox" id="showSeatTotals" name="showSeatTotals" onclick="toggleTotals();"/><?php echo JText::_('RS1_FRONTDESK_SCRN_SHOW_SEAT_TOTALS');?>&nbsp;&nbsp;&nbsp;&nbsp;
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
          <select id="status_filter" name="status_filter" onchange="buildFrontDeskView()" style="font-size:11px; width:auto">
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
			<?php foreach($statuses as $status_row){ ?>
                <option value="<?php echo $status_row->internal_value ?>" <?php if($front_desk_status_filter == $status_row->internal_value){echo " selected='selected' ";} ?> class="color_<?php echo $status_row->internal_value ?>" ><?php echo JText::_($status_row->status);?></option>        
            <?php } ?>
            </select>&nbsp;&nbsp;         
           </td>
        </tr> 
    </table>
</div>
<div id="calview_here">&nbsp;</div>

<input type="hidden" name="id" id="id" value="<?php echo $row->id; ?>">
<input type="hidden" name="uid" id="uid" value="<?php echo $user->id; ?>">
<input type="hidden" id="script_path" name="script_path" value="<?php echo $SCRIPTPATH?>" />
<input type="hidden" name="redirect" id="redirect" value="" />
<input type="hidden" name="listpage" id="listpage" value="front_desk" />
<input type="hidden" name="startdate" id="startdate" value="" />
<input type="hidden" name="starttime" id="starttime" value="" />
<input type="hidden" name="endtime" id="endtime" value="" />
<input type="hidden" name="resid" id="resid" value="" />

  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" name="controller" value="front_desk" />
	<input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
	<input type="hidden" name="task" id='task' value="" />
	<input type="hidden" name="frompage" value="front_desk" />
  	<input type="hidden" name="frompage_item" id="frompage_item" value="<?php echo $itemid ?>" />

  	<input type="hidden" name="menu_id" id="menu_id" value="<?php echo $menu_id ?>"/>
<input type="hidden" name="printer_view" id="printer_view" value="Yes" />

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?> 
</div>
	<?php echo JHtml::_('form.token'); ?>
    
</form>
<hr />
<p align="center">
  <input type="button"  onClick="window.print()"  value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_PRINT'); ?>"/>&nbsp;
  <input type="button"  onClick="window.close()"  value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE'); ?>"/>
  </p>
<?php } ?>

