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

	// require the html view class
	jimport( 'joomla.application.helper' );

	JHtml::_('jquery.framework');

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	$jinput = JFactory::getApplication()->input;

	$option = $jinput->getString( 'option', '' );
	
	$session = JSession::getInstance($handler=null, $options=null);
	if($session->get("status_filter") != "" ){
		$filter = $session->get("status_filter");
		$session->set("status_filter", "");
	} else {
		$filter = $jinput->getString( 'status_filter', '' );
	}
	
	if($session->get("startdateFilter") != "" ){
		$startdateFilter = $session->get("startdateFilter");
		$session->set("startdateFilter", "");
	} else {
		$startdateFilter = $jinput->getString( 'startdateFilter', date("Y-m-d"));
	}

	if($session->get("enddateFilter") != "" ){
		$enddateFilter = $session->get("enddateFilter");
		$session->set("enddateFilter", "");
	} else {
		$enddateFilter = $jinput->getString( 'enddateFilter', '' );
	}

	// Load configuration data
	//include( JPATH_SITE . "/administrator/components/com_rsappt_pro3/config.rsappt_pro.php" );
	//include( JPATH_SITE."/administrator/components/com_rsappt_pro3/config.rsappt_pro3.php" );
//	require_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	//global $my;  
	$user = JFactory::getUser();
	$showform = true;	 

	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if($session->get("filter_order") != "" ){
		$filter_order = $session->get("filter_order");
		$session->set("filter_order", "");
	} else {
		$filter_order = $jinput->getString( 'filter_order', 'startdatetime' );
	}
	$ordering = $filter_order;

	if($session->get("filter_order_Dir") != "" ){
		$filter_order_Dir = $session->get("filter_order_Dir");
		$session->set("filter_order_Dir", "");
	} else {
		$filter_order_Dir = $jinput->getString( 'filter_order_Dir', 'asc' );
	}
	$direction = $filter_order_Dir;

	 
	if(!$user->guest){

		$database = JFactory::getDBO();
		
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";		
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		// find requests
		$sql = "SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_admins, ".
			"#__sv_apptpro3_resources.name as resname, ".
			//"#__sv_apptpro3_services.name as ServiceName, ".
			"CONCAT(#__sv_apptpro3_requests.startdate,#__sv_apptpro3_requests.starttime) as startdatetime, ".
			" IF(CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) > Now(),'no','yes') as expired, ";
			if($apptpro_config->timeFormat == "12"){
				$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%l:%i %p') as display_starttime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%l:%i %p') as display_endtime ";
			} else {
				$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%k:%i') as display_starttime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%k:%i') as display_endtime ";
			}
			$sql = $sql." FROM #__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				"ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
				//" INNER JOIN #__sv_apptpro3_services ON #__sv_apptpro3_requests.service = #__sv_apptpro3_services.id_services ".
			"WHERE request_status!='deleted' AND ";
			if($filter != ""){
				$sql = $sql." request_status='".$database->escape($filter)."' AND ";
			}
			if($startdateFilter != ""){
				$sql = $sql." startdate>='".$database->escape($startdateFilter)."' AND ";
			}
			if($enddateFilter != ""){
				$sql = $sql." enddate<='".$database->escape($enddateFilter)."' AND ";
			}
			$sql = $sql."#__sv_apptpro3_requests.user_id = ".$user->id.
//			" AND CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) >= NOW() ".
		" ORDER BY ".$database->escape($ordering).' '.$database->escape($direction);
		try{
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// check for credit activity
		if($user->id != ""){
			$sql = "SELECT #__sv_apptpro3_user_credit_activity.*, #__users.name as operator, #__sv_apptpro3_requests.startdate, ";
			if($apptpro_config->timeFormat == "12"){
				$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%l:%i %p') as display_starttime, ";
			} else {
				$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ";				
			}
			$sql .= "#__sv_apptpro3_resources.description as resource ".
				"FROM #__sv_apptpro3_user_credit_activity ".
				"  LEFT OUTER JOIN #__users ON #__sv_apptpro3_user_credit_activity.operator_id = #__users.id ".
				"  LEFT OUTER JOIN #__sv_apptpro3_requests ON #__sv_apptpro3_user_credit_activity.request_id = #__sv_apptpro3_requests.id_requests ".
				"  LEFT OUTER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
				"WHERE #__sv_apptpro3_user_credit_activity.user_id = ".$user->id." ORDER BY id desc LIMIT 20";
			try{
				$database->setQuery($sql);
				$activity_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
		}
		
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

		// get statuses
		$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value!='deleted' ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$display_picker_date = $startdateFilter;	
		if($display_picker_date != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date = date("Y-m-d", strtotime($startdateFilter));
					break;
				case "dd-mm-yy":
					$display_picker_date = date("d-m-Y", strtotime($startdateFilter));
					break;
				case "mm-dd-yy":
					$display_picker_date = date("m-d-Y", strtotime($startdateFilter));
					break;
				default:	
					$display_picker_date = date("Y-m-d", strtotime($startdateFilter));
					break;
			}
		}
	
		$display_picker_date2 = $enddateFilter;	
		if($display_picker_date2 != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2 = date("Y-m-d", strtotime($enddateFilter));
					break;
				case "dd-mm-yy":
					$display_picker_date2 = date("d-m-Y", strtotime($enddateFilter));
					break;
				case "mm-dd-yy":
					$display_picker_date2 = date("m-d-Y", strtotime($enddateFilter));
					break;
				default:	
					$display_picker_date2 = date("Y-m-d", strtotime($enddateFilter));
					break;
			}
		}

		$sv_help_icon = "<img alt=\"\" src='".getImageSrc("help_udf2.png")."' class='sv_help_icon' style=\"float:right;\" ";


	} else{
		echo "<font color='red'>".JText::_('RS1_MYBOOKINGS_SCRN_NO_LOGIN')."</font>";
		$showform= false;	 
	}
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.8.2/jquery-ui.js"></script>

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

	function doFilter(){
		document.adminForm.submit();
	}
	
	function call_doCancel(cancel_id, row){
		document.getElementById("cancellation_id").value = cancel_id;
		if(doCancel()){
			document.getElementById(row).innerHTML = "";
		}
	}

	function call_doDelete(delete_id, row){
		document.getElementById("cancellation_id").value = delete_id;
		if(doDelete()){
			document.getElementById(row).innerHTML = "";
		}
	}

	function cleardate(){
		document.getElementById("startdateFilter").value="";
		document.getElementById("enddateFilter").value="";
		Joomla.submitbutton('');
		return false;		
	}
	
	function dateCheck(){
		if(document.getElementById("startdateFilter").value != "" && document.getElementById("enddateFilter").value != "" ){
			if(Date.parse(document.getElementById("startdateFilter").value) > Date.parse(document.getElementById("enddateFilter").value)){
				alert(document.getElementById("date_check_text").value);
				document.getElementById("enddateFilter").value = "";
				return false;
			} 
		}
		Joomla.submitbutton('');
		return false;	
	}

</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<?php if($showform){?>
<div id="sv_apptpro_mybookings">
  <table class="table table-striped" width="100%" >
    <tr>
      <td><h3><?php echo JText::_('RS1_MYBOOKINGS_SCRN_TITLE');?></h3></td>
    </tr>
    <tr>
      <td>
        <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER');?></div>
		<div class="controls">
            <input readonly="readonly" name="startdateFilter" id="startdateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $startdateFilter ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date ?>" onchange="dateCheck(); return false;">          
        <br />
           <input readonly="readonly" name="enddateFilter" id="enddateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $enddateFilter ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date2 ?>" onchange="dateCheck(); return false;">
                         
        <a href="#" onclick="cleardate(); return false;"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER_CLEAR');?></a>&nbsp;&nbsp;
        </div>
       </td>
     </tr>
     <tr>
       <td>   
        <div class="controls">
        <select name="status_filter" onchange="doFilter()" style="font-size:11px; width:auto">
        <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
        <?php foreach($statuses as $status_row){ ?>
            <option value="<?php echo $status_row->internal_value ?>" <?php if($filter == $status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($status_row->status);?></option>        
        <?php } ?>
        </select>
      </div>  
      </td>
    </tr>
    <tr>
      <td align="left"><div id="cancel_results"></div></td>
    </tr>
  </table>
  <table width="100%">
    <tr class="adminheading"  bgcolor="#F4F4F4">
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_MYBOOKINGS_SCRN_RESID_COL_HEAD'), 'resname', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_MYBOOKINGS_SCRN_DATE_COL_HEAD'), 'startdatetime', $direction, $ordering); ?></th>
      <!--<th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_MYBOOKINGS_SCRN_FROM_COL_HEAD'), 'starttime', $direction, $ordering); ?></th>-->
      <!--<th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_MYBOOKINGS_SCRN_UNTIL_COL_HEAD'), 'endtime', $direction, $ordering); ?></th>-->
      <!--<th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_MYBOOKINGS_SCRN_SEATS_HEAD'), 'booked_seats', $direction, $ordering); ?></th>-->
      <?php //if($apptpro_config->allow_cancellation == "Yes" OR $apptpro_config->allow_cancellation == "BEO" ){?>
      <th class="sv_title" align="left">&nbsp;</th>
	  <?php //} ?>
      <th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD'), 'request_status', $direction, $ordering); ?></th>
    </tr>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($rows ); $i++) {
	$row = $rows[$i];
   ?>
    <tr class="<?php echo "row$k"."_mobile"; ?>">
      <td align="left"><?php echo JText::_(stripslashes($row->resname)); ?>&nbsp;</td>
      <td align="left"><?php echo $row->display_startdate."<br/>".$row->display_starttime; ?></td>
      <!--<td align="left"><?php echo $row->display_starttime; ?> </td-->
      <!--<td align="left"><?php echo $row->display_endtime; ?> </td>-->
      <!--<td align="center"><?php echo $row->booked_seats; ?> </td>-->
      <td align="left"><div id="row<?php echo $i ?>">
      <?php if($row->expired == "yes"){ ?>
      		<?php // remove delete ability from My Bookings. If user is allowed to 'delete' it overrites any status change made by admin. ?>
	          <!--<a href="#" onclick="call_doDelete('<?php echo $row->cancellation_id; ?>', 'row<?php echo $i?>'); return false;" ><?php echo JText::_('RS1_INPUT_SCRN_DELETE_BUTTON');?></a>--> </td>
      <?php } elseif($apptpro_config->allow_cancellation == "Yes" OR $apptpro_config->allow_cancellation == "BEO" ){
				if($row->request_status != 'canceled'){?>
				  <a href="#" onclick="call_doCancel('<?php echo $row->cancellation_id; ?>', 'row<?php echo $i?>' ); return false;" ><?php echo JText::_('RS1_INPUT_SCRN_CANCEL_BUTTON');?></a> </td>
				<?php } else { ?> 
				  &nbsp; </td>
				<?php } ?>
	  <?php } else { ?>
		  &nbsp; </td>
	  <?php } ?>
      
      <td align="center"><?php echo translated_status($row->request_status); ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
  </table>

<?php if(sv_count_($activity_rows)>0){ ?>

<br /><br /><br />
<hr />
  <?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_ACTIVITY_INTRO');?><br />
  <table width="100%" >
	<thead>
    <tr>
<!--      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_COMMENT_COL_HEAD'); ?></th>-->
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BOOKING_COL_HEAD'); ?></th>
<!--      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_INCREASE_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_DECREASE_COL_HEAD'); ?></th>-->
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BALANCE_COL_HEAD'); ?></th>
<!--      <th width="5%" class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_OPERATOR_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_TIMESTAMP_COL_HEAD'); ?></th>
--> 
	
	</tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($activity_rows ); $i++) {
	$activity_row = $activity_rows[$i];
	$info_string = JText::_('RS1_ADMIN_CREDIT_ACTIVITY_COMMENT_COL_HEAD').':\t'.$activity_row->comment.'\n'.
	  JText::_('RS1_ADMIN_CREDIT_ACTIVITY_INCREASE_COL_HEAD').':\t '.$activity_row->increase.'\n'.
	  JText::_('RS1_ADMIN_CREDIT_ACTIVITY_DECREASE_COL_HEAD').':\t '.$activity_row->decrease;
   ?>
    <tr class="<?php echo "row$k"."_mobile"; ?>">
      <!--<td align="left"><?php echo stripslashes($activity_row->comment); ?>&nbsp;</td>-->
      <?php if($activity_row->request_id != ""){ ?>
      <td align="left"><?php echo $activity_row->display_startdate." / ".$activity_row->display_starttime; ?>&nbsp;- <?php echo JText::_(stripslashes($activity_row->resource)); ?></td>
      <?php } else { ?>
      <td align="center">&nbsp;</td>
      <?php } ?>
<!--      <td align="center"><?php echo $activity_row->increase; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->decrease; ?>&nbsp;</td>-->
      <td align="center"><?php echo $activity_row->balance; ?>&nbsp;
      <img src="<?php echo getImageSrc("help_udf2.png");?>" class="sv_help_icon" style=\"float:right;\" onclick="alert('<?php echo $info_string;?>');" />
	  </td>
<!--      <td align="center"><?php echo $activity_row->operator; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->stamp; ?></td>-->
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } ?>
  </table>
<?php } ?>

  <p></p><p class="row0"><?php 
  if($apptpro_config->allow_cancellation == "Yes" OR $apptpro_config->allow_cancellation == "BEO" ){
  	//echo JText::_('RS1_MYBOOKINGS_SCRN_CANCEL_HOWTO');
	}?></p>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="view" id="view" value="mybookings" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="cancellation_id" id="cancellation_id"  />
  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" name="filter_order" value="<?php echo $ordering ?>" />
  <input type="hidden" name="filter_order_Dir" value ="<?php echo $direction ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <input type="hidden" id="date_check_text" value="<?php echo JText::_('RS1_MYBOOKINGS_DATE_CHECK');?>" />
    <input type="hidden" name="mobile" id="mobile" value="Yes" />    
    
  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 <br/>Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
  
</div>
	<?php 
	} // end of if showform
	?>

</form>
