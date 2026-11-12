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

defined('_JEXEC') or die('Restricted access');



//	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
//  	setSessionStuff("request");

	$showform= true;

	$mainframe = JFactory::getApplication();
	$jinput = JFactory::getApplication()->input;
	$itemid = $jinput->getInt( 'Itemid', '' ); // menu id of Front Desk
	$menu = $mainframe->getMenu();
	$params = $menu->getParams($itemid);

	$fd_show_contact_info = true;
	if($params->get('fd_show_contact_info') == 'No'){
		$fd_show_contact_info = false;
	}
	$fd_allow_show_seats = true;
	if($params->get('fd_allow_show_seats') == 'No'){
		$fd_allow_show_seats = false;
	}
	$fd_show_udfs = true;
	if($params->get('fd_show_udfs') == 'No'){
		$fd_show_udfs = false;
	}
	$fd_show_extras = true;
	if($params->get('fd_show_extras') == 'No'){
		$fd_show_extras = false;
	}
	$fd_show_financials = true;
	if($params->get('fd_show_financials') == 'No'){
		$fd_show_financials = false;
	}
	$fd_login_required = true;
	if($params->get('fd_login_required') == 'No'){
		$fd_login_required = false;
	}
	
	$listpage = $jinput->getString('listpage', 'list');
	
	if($listpage == 'list'){
		$savepage = 'save';
	} else if($listpage == "front_desk"){
		setSessionStuff("front_desk");
		$savepage = 'save_front_desk';
	} else {
		$savepage = 'save_adv_admin';
	}

	$session = JSession::getInstance($handler=null, $options=null);
	$session->set("status_filter", $jinput->getString('filter', ''));
	$session->set("request_resourceFilter", $jinput->getString('resourceFilter', ''));

	$request = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$user = JFactory::getUser();
	if($user->guest && $fd_login_required == true){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {
		$database = JFactory::getDBO(); 
		// get request details
		$user = JFactory::getUser();
		
		if($this->detail->id_requests==""){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_ACCESS')."</font>";
			$showform = false;
		}
		
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

		// get udfs
		$database = JFactory::getDBO(); 
		//$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 ORDER BY ordering';
		$sql = "SELECT ".
		"#__sv_apptpro3_udfs.udf_label, #__sv_apptpro3_udfs.udf_type, ".
		"#__sv_apptpro3_udfvalues.udf_value, #__sv_apptpro3_udfvalues.id as value_id, ".
		"#__sv_apptpro3_udfvalues.request_id ".
		"FROM ".
		"#__sv_apptpro3_udfvalues INNER JOIN ".
		"#__sv_apptpro3_udfs ON #__sv_apptpro3_udfvalues.udf_id = ".
		"#__sv_apptpro3_udfs.id_udfs ".
		"WHERE ".
		"#__sv_apptpro3_udfvalues.request_id = ".$this->detail->id_requests. " ".
		"ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$udf_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// get extras data
		$database = JFactory::getDBO(); 
		$sql = "SELECT extras_id, extras_label, extras_qty, extras_tooltip, max_quantity FROM ".
		" #__sv_apptpro3_extras_data INNER JOIN #__sv_apptpro3_extras ".
		"   ON #__sv_apptpro3_extras_data.extras_id = #__sv_apptpro3_extras.id_extras ".
		" WHERE #__sv_apptpro3_extras_data.request_id = ".$this->detail->id_requests. " ".
		" ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$extras_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// get data for dropdownlist
		
		// get seat types
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_seat_types WHERE published=1 '.
		' AND (scope = "" OR scope LIKE "%|'.$this->detail->resource.'|%") ORDER BY ordering';
		try{
			$database->setQuery($sql);
			$seat_type_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// get seat values
		$sql = "SELECT seat_type_id, seat_type_label, seat_type_qty FROM ".
		" #__sv_apptpro3_seat_counts INNER JOIN #__sv_apptpro3_seat_types ".
		"   ON #__sv_apptpro3_seat_counts.seat_type_id = #__sv_apptpro3_seat_types.id_seat_types ".
		" WHERE #__sv_apptpro3_seat_counts.request_id = ".$this->detail->id_requests. " ".
		" ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$seat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		//global $database;
		$sql = "(SELECT 0 as id, '".JText::_('RS1_INPUT_SCRN_RESOURCE_PROMPT')."' as name, '".
		JText::_('RS1_INPUT_SCRN_RESOURCE_PROMPT')."' as description, ".
		"0 as ordering, '' as cost) ".
		"UNION (SELECT id_resources,name,description,ordering,CONCAT(' - ', cost) as cost ".
		"FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' )".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$sql = "SELECT #__sv_apptpro3_services.* ".
//			"FROM #__sv_apptpro3_services LEFT JOIN #__sv_apptpro3_resources ".
//			"ON #__sv_apptpro3_services.resource_id = #__sv_apptpro3_resources.id_resources ".
//			"WHERE #__sv_apptpro3_services.published = 1 AND #__sv_apptpro3_resources.published = 1 ";
//			"AND #__sv_apptpro3_services.resource_id = ".$this->detail->resource." ORDER BY name ";	

			// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
			"FROM #__sv_apptpro3_services ".
			"WHERE #__sv_apptpro3_services.published = 1 " ;
			$safe_search_string = '%|' . $database->escape( $this->detail->resource, true ) . '|%' ;
			$sql .= " AND (#__sv_apptpro3_services.resource_scope = '' OR #__sv_apptpro3_services.resource_scope LIKE ".$database->quote( $safe_search_string, false ).")"; 
			$sql .= " ORDER BY name ";
		try{	
			$database->setQuery( $sql );
			$srv_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		if($apptpro_config->which_calendar == "JEvents"){
			$sql = "SELECT id,title FROM #__categories WHERE section = 'com_events'";
		} else if($apptpro_config->which_calendar == "EventList"){
			$sql = "SELECT id, catname as title FROM #__eventlist_categories";
		}	
		try{
			$database->setQuery($sql);
			$cal_cat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		$noCats = "";
		if(sv_count_($cal_cat_rows)<1){
			$noCats = JText::_('RS1_ADMIN_SCRN_NO_CATS');
		}
		
//	// default calendar (JCalPro 2 only)
//	if($apptpro_config->which_calendar == "JCalPro2"){
//		$sql = "SELECT cal_id as id, cal_name as title FROM #__jcalpro2_calendars";
//		try{
//			$database->setQuery($sql);
//			$cal_cal_rows = $database -> loadObjectList();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
//			echo JText::_('RS1_SQL_ERROR');
//			return false;
//		}		
//	}	
	
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		//echo "user_field1_label = ".$apptpro_config->user_field1_label;
		//exit;

	
		// get statuses
		$sql = "SELECT * FROM #__sv_apptpro3_status ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_detail_tmpl_default_ro", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}
	$rowclass = 0;
?>
<?php if($showform){?>

<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript">
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
	//cal.setDisabledWeekDays(0,6);

	
	function getTomorrow(){
		var tomorrow = new Date();
		tomorrow.setDate(tomorrow.getDate()+1);
		var tomstr = '' + tomorrow.getFullYear() + "-" + (tomorrow.getMonth()+1) + "-" +tomorrow.getDate();
		//alert(tomstr);
		return(tomstr);
	}
		
	function doClose(){
		Joomla.submitform("req_close");
	}		
	
	function doSave(){
		if(document.getElementById('name').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_NAME_ERR');?>');
			return(false);
		}
		Joomla.submitform("save");		
	}

	function calcSeatTotal(){
		if(document.getElementById("seat_type_count") != null && document.getElementById("seat_type_count").value > 0 ){
			var seat_count = 0; 
			rate = 0.00;
			for(i=0; i<parseInt(document.getElementById("seat_type_count").value); i++){
				seat_name_cost = "seat_type_cost_"+i;
				seat_name = "seat_"+i;
				group_seat_name = "seat_group_"+i;
				seat_count += parseInt(document.getElementById(seat_name).value);
			}
			document.getElementById("booked_seats_div").innerHTML = seat_count;
			document.getElementById("booked_seats").value = seat_count;
		}
	}
	
	function setstarttime(){
		document.getElementById("starttime").value = document.getElementById("starttime_hour").value + ":" + document.getElementById("starttime_minute").value + ":00";
	}
	
	function setendtime(){
		document.getElementById("endtime").value = document.getElementById("endtime_hour").value + ":" + document.getElementById("endtime_minute").value + ":00";
	}

	function changeStartdate(){
		document.getElementById("enddate").value = document.getElementById("startdate").value;
	}
	
	
    </script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_detail_read_only">
<table >
    <tr>
      <td align="left"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_BOOKING_DETAIL_TITLE_READONLY');?></h3></td>
    </tr>
</table>
  <table border="0" cellpadding="4" cellspacing="2" width="100%">
    <tr>
      <td colspan="3"  style="text-align:right" height="40px" class="fe_header_bar">
    	  <a href="#" id="closeLink" onclick="window.history.back();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?></a>&nbsp;&nbsp;</td>
    </tr>
<!--    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_REQ_ID_COL_HEAD');?>: </td>
      <td ><?php echo $this->detail->id_requests; ?></td>
    </tr>
-->
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_NAME');?>: </td>
      <td><?php echo stripslashes($this->detail->name); ?></td>
    </tr>
    <?php if($fd_show_contact_info){?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PHONE');?>:</td>
      <td><?php echo $this->detail->phone; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EMAIL');?>: </td>
      <td><?php echo $this->detail->email; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_USE_SMS_COL_HEAD');?>:</td>
      <td><?php echo $this->detail->sms_reminders ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SMS_PHONE_COL_HEAD');?>:</td>
      <td><?php echo $this->detail->sms_phone; ?></td>
    </tr>
	<?php } ?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_COL_HEAD');?>:</td>
      <td><?php echo JText::_($this->detail->category_name); ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE');?>:</td>
      <td><?php echo $this->detail->resource_name; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD');?>: </td>
      <td><?php echo $this->detail->service_name; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STARTDATE');?>: </td>
      <td><?php echo $this->detail->displaystartdate; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STARTTIME');?>:</td>
      <td><?php echo $this->detail->displaystarttime; ?></td>        
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_ENDTIME');?>:</td>
	  <td><?php echo $this->detail->displayendtime; ?></td>              
    </tr>
    </tr>
	<?php if($fd_allow_show_seats){ ?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOKED_SEATS');?>:</td>
      <td><?php echo $this->detail->booked_seats; ?></td>
    </tr>
	<?php 
	$si = 0; 
	if(sv_count_($seat_type_rows)>0){ ?>
		<tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
		  <td valign="top" ></td>
		  <td >
                <table border="0" cellpadding="2" cellspacing="1" >
	<?php foreach($seat_type_rows as $seat_type_row){ 
			$thiscount = 0;
	        for($i=0; $i < sv_count_($seat_rows ); $i++) {
    	    	if($seat_type_row->id_seat_types == $seat_rows[$i]->seat_type_id){
					$thiscount = $seat_rows[$i]->seat_type_qty;
				}
			}  ?>

			<tr>
			  <td><?php echo JText::_($seat_type_row->seat_type_label)?>:</td>
			  <td colspan="3" valign="top"><?php echo $thiscount?></td>
			</tr>
			<?php $si += 1; 
		} ?>
        </table></td></tr>
	<?php } ?>    
	<?php } ?>    
	<?php 
	$ei = 0; 
	if($fd_show_extras){
	if(sv_count_($extras_rows)>0){ ?>
        <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
          <td colspan="2" ><?php echo JText::_('RS1_INPUT_SCRN_EXTRAS_LABEL');?>:</td>
        </tr>
		<tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
		  <td valign="top" ></td>
		  <td>
                <table border="0" cellpadding="2" cellspacing="1" >
	<?php foreach($extras_rows as $extras_row){ ?>
			<tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
			  <td><?php echo JText::_($extras_row->extras_label)?>:</td>
			  <td colspan="3" valign="top"><?php echo $extras_row->extras_qty ?>
				&nbsp;
			  </td>
			</tr>
			<?php $ei += 1; 
		} ?>
        </table></td></tr>
	<?php } ?>    
	<?php } ?>    
	<?php if($fd_show_udfs){ ?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td colspan="2" valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_UDF');?>:</td>
    </tr>
    <?php if(sv_count_($udf_rows > 0)){?>
		<tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
		  <td valign="top" class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>"></td>
		  <td >
                <table border="0" cellpadding="2" cellspacing="1" >
        <?php 
		$k = 0;
        for($i=0; $i < sv_count_($udf_rows ); $i++) {
        	$udf_row = $udf_rows[$i];
        	?>
                  <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
                    <td ><?php echo JText::_(stripslashes($udf_row->udf_label))?>:</td>
            <?php if($udf_row->udf_type == 'Content'){?>
	                    <td valign="top"><?php echo substr(strip_tags($udf_row->udf_value), 0, 50);?>...
		    <?php } else { ?>
                    	<td valign="top"><?php echo stripslashes($udf_row->udf_value)?>
		    <?php } ?>
                    </td>
<!--                    <td valign="top"><?php echo stripslashes($udf_row->udf_type)?></td>
-->                  </tr>
          <?php $k = 1 - $k; 
		} ?>
                </table>
          </td>
        </tr>
    <?php }?>
	<?php } ?>    
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td style=" border-top:#999999 solid 1px"><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS');?>: </td>
      <td style=" border-top:#999999 solid 1px"><?php echo $this->detail->request_status;?></td>
    </tr>
   <?php if($fd_show_financials ){ ?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PAY_STATUS');?>:</td>
      <td><?php echo $this->detail->payment_status;?></td>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_BOOKING_TOTAL');?> :</td>
      <td ><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $this->detail->booking_total; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOKING_DUE');?> :</td>
      <td><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $this->detail->booking_due; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_BOOKING_DEPOSIT');?> :</td>
      <td ><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $this->detail->booking_deposit; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_MAUNAL_PAYMENT_COLLECTED');?> :</td>
      <td valign="top"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $this->detail->manual_payment_collected; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_USED');?> :</td>
      <td><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $this->detail->credit_used; ?></td>
    </tr>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_CODE');?> :</td>
      <td><?php echo $this->detail->coupon_code; ?></td>
    </tr>
	<?php } ?>
    <tr class="detail_row<?php echo $rowclass; $rowclass = 1 - $rowclass;?>">
      <td valign="top" ><?php echo JText::_('RS1_ADMIN_SCRN_ADMINCOMMENT');?>:</td>
      <td ><?php echo stripslashes($this->detail->admin_comment); ?></td>
    </tr>
  </table>
  <br /> 

  <?php if($apptpro_config->hide_logo == 'No'){ ?>
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
 </div>
 <?php echo JHTML::_( 'form.token' ); ?>

 </form>

<?php } ?>