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



	$jinput = JFactory::getApplication()->input;

	$showform= true;
	$listpage = $jinput->getString('listpage', 'list');
//	if($listpage == 'list'){
//		$savepage = 'srv_save';
//	} else {
//		$savepage = 'srv_save_adv_admin';
//	}
//	$current_tab = $jinput->getString('current_tab', '');

	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

//	$current_tab = $jinput->getString('current_tab', '');
//	$resource = $jinput->getString('resource_id_FilterTS', '');
//	// see below $day_number = $jinput->getString('day_numberFilter', '');

	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

		// get resources
		$sql = "SELECT * FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' and published=1 ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "timeslots_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
				
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "timeslots_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

	}	

	if($jinput->getString('day_numberFilter') == "all"){
		$day_number = $this->detail->day_number;
	} else {
		$day_number = $jinput->getString('day_numberFilter');
	}
	
	$display_picker_date = $this->detail->start_publishing;	
	if($display_picker_date != "" && $display_picker_date != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date = date("Y-m-d", strtotime($this->detail->start_publishing));
				break;
			case "dd-mm-yy":
				$display_picker_date = date("d-m-Y", strtotime($this->detail->start_publishing));
				break;
			case "mm-dd-yy":
				$display_picker_date = date("m-d-Y", strtotime($this->detail->start_publishing));
				break;
			default:	
				$display_picker_date = date("Y-m-d", strtotime($this->detail->start_publishing));
				break;
		}
	}

	$display_picker_date2 = $this->detail->end_publishing;	
	if($display_picker_date2 != "" && $display_picker_date2 != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date2 = date("Y-m-d", strtotime($this->detail->end_publishing));
				break;
			case "dd-mm-yy":
				$display_picker_date2 = date("d-m-Y", strtotime($this->detail->end_publishing));
				break;
			case "mm-dd-yy":
				$display_picker_date2 = date("m-d-Y", strtotime($this->detail->end_publishing));
				break;
			default:	
				$display_picker_date2 = date("Y-m-d", strtotime($this->detail->end_publishing));
				break;
		}
	}
	
?>
<?php if($showform){?>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>
<script language="JavaScript">
	jQuery(function() {
  		jQuery( "#display_picker_date" ).datepicker({
			showOn: "button",
			autoSize: true,
			changeMonth: true,
			changeYear: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#start_publishing",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2" ).datepicker({
			showOn: "button",
			autoSize: true,
			changeMonth: true,
			changeYear: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#end_publishing",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	function setstarttime(){
		document.getElementById("timeslot_starttime").value = document.getElementById("timeslot_starttime_hour").value + ":" + document.getElementById("timeslot_starttime_minute").value + ":00";
	}
	function setendtime(){
		document.getElementById("timeslot_endtime").value = document.getElementById("timeslot_endtime_hour").value + ":" + document.getElementById("timeslot_endtime_minute").value + ":00";
	}
		
	function doCancel(){
		Joomla.submitform("ts_cancel");
	}		

	function doClose(){
		Joomla.submitform("ts_close");
	}		
	
	function doSave(){
		if(document.getElementById('resource_id').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE_ERR');?>');
			return(false);
		}
		if(document.getElementById('day_number').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_A_DAY');?>');
			return(false);
		}
		if(document.getElementById('timeslot_starttime_hour').selectedIndex == document.getElementById('timeslot_endtime_hour').selectedIndex
		&& document.getElementById('timeslot_starttime_minute').selectedIndex == document.getElementById('timeslot_endtime_minute').selectedIndex)				{
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_START_EQ_END');?>');
			return(false);
		}
		Joomla.submitform("save_timeslots_detail");
	}
	
	function setDatePicker(which_one){
		if(document.getElementById("date_picker_format")!=null){
			if(which_one == 1){	
				var tempdate;
				tempdate = Date.parse(document.getElementById("start_publishing").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date").value = tempdate.toString("yyyy-MM-dd");
				}		
			} else {
				var tempdate;
				tempdate = Date.parse(document.getElementById("end_publishing").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date2").value = tempdate.toString("yyyy-MM-dd");
				}
			}
		}
	}	
	

	
	</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_timeslot_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_RESOURCE_TIMESLOT_TITLE');?></h3></td>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0">
   <tr>
      <td colspan="3" style="text-align:right" height="40px" class="fe_header_bar">
     <?php if($this->lock_msg != ""){?>
	      <?php echo $this->lock_msg?>
    	  &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doClose();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?></a>&nbsp;&nbsp;
      <?php } else { ?>
      <a href="#" onclick="doSave();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?></a>
      &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doCancel();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;
      <?php } ?>
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <p><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_INTRO');?><br /></td>
    </tr>
    <tr class="admin_detail_row0">
      <td width="20%"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_ID');?></td>
      <td colspan="2"><?php echo $this->detail->id_timeslots ?></td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_RESOURCE');?></td>
      <td colspan="2"><select name="resource_id" id="resource_id" class="sv_apptpro3_request_text" >
          <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>"  <?php if($this->detail->resource_id == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
              <?php $k = 1 - $k; 
				} ?>
      </select></td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_DAY');?></td>
      <td colspan="2"><select name="day_number" id="day_number" class="sv_apptpro3_request_text">
          <option value="-1" ><?php echo JText::_('RS1_ADMIN_SCRN_SELECT_A_DAY');?></option>
          <option value="0" <?php if($this->detail->day_number == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SUNDAY');?></option>
          <option value="1" <?php if($this->detail->day_number == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_MONDAY');?></option>
          <option value="2" <?php if($this->detail->day_number == "2"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_TUESDAY');?></option>
          <option value="3" <?php if($this->detail->day_number == "3"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_WEDNESDAY');?></option>
          <option value="4" <?php if($this->detail->day_number == "4"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_THURSDAY');?></option>
          <option value="5" <?php if($this->detail->day_number == "5"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FRIDAY');?></option>
          <option value="6" <?php if($this->detail->day_number == "6"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SATURDAY');?></option>
        </select></td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_START');?></td>
      <td colspan="2"><select name="timeslot_starttime_hour" id="timeslot_starttime_hour" style="width:auto;" onchange="setstarttime();" class="sv_apptpro3_request_text" <?php if($this->detail->hoursLimit == '24Hour'){ echo ' disabled ' ;} ?>>
		<?php
		for($x=0; $x<24; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select> : 
		<select name="timeslot_starttime_minute" id="timeslot_starttime_minute" style="width:auto;" onchange="setstarttime();" class="sv_apptpro3_request_text" <?php if($this->detail->hoursLimit == '24Hour'){ echo ' disabled ' ;} ?>>
		<?php
		for($x=0; $x<59; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select>        
         <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?>
        <input type="hidden" name="timeslot_starttime" id="timeslot_starttime" value="<?php echo $this->detail->timeslot_starttime ?>" />      </td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_END');?></td>
      <td colspan="2"><select name="timeslot_endtime_hour" id="timeslot_endtime_hour" style="width:auto;" onchange="setendtime();" class="sv_apptpro3_request_text" <?php if($this->detail->hoursLimit == '24Hour'){ echo ' disabled ' ;} ?>>
		<?php
		for($x=0; $x<24; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select>  : 
		<select name="timeslot_endtime_minute" id="timeslot_endtime_minute" style="width:auto;" onchange="setendtime();" class="sv_apptpro3_request_text" <?php if($this->detail->hoursLimit == '24Hour'){ echo ' disabled ' ;} ?>>
		<?php
		for($x=0; $x<59; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select>        
         <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?>
        <input type="hidden" name="timeslot_endtime" id="timeslot_endtime" value="<?php echo $this->detail->timeslot_endtime ?>" />      </td>
    </tr>
    <tr class="admin_detail_row1">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_DESC');?></td>
      <td valign="top" colspan="2"><input type="text" size="30" maxsize="50" name="timeslot_description" value="<?php echo $this->detail->timeslot_description; ?>" />&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBSTART_DATE');?></td>
      <td>
      
        <input readonly="readonly" name="start_publishing" id="start_publishing" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->start_publishing; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date ?>" onchange="setDatePicker(1);">
       	&nbsp;<a href="#" onclick="document.getElementById('display_picker_date').value=''; document.getElementById('start_publishing').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
     
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBSTART_DATE_HELP');?></td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBEND_DATE');?></td>
      <td>
            
        <input readonly="readonly" name="end_publishing" id="end_publishing" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->end_publishing; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date2 ?>" onchange="setDatePicker(2);">
      
      	&nbsp;<a href="#" onclick="document.getElementById('display_picker_date2').value=''; document.getElementById('end_publishing').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBEND_DATE_HELP');?></td>
    </tr>
    <tr class="admin_detail_row0">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_STAFF_ONLY_HELP');?></td>
    </tr>
    <tr class="admin_detail_row1">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_PUBLISHED');?></td>
        <td colspan="2">
            <select name="published" class="sv_apptpro3_request_text">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>
    <tr class="admin_detail_row1">
      <td colspan="3" ><br />
        <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_NOTES');?>
        </td>
    </tr>  
  </table>
  <input type="hidden" name="id_timeslots" value="<?php echo $this->detail->id_timeslots; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    


  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
