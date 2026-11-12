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

	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$scope= "";
	
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
			logIt($e->getMessage(), "be_rate_over_detail_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_timeslots_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

	}	

	$display_start_pub_date = $this->detail->start_publishing;	
	if($display_start_pub_date != "" && $display_start_pub_date != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_start_pub_date = date("Y-m-d", strtotime($this->detail->start_publishing));
				break;
			case "dd-mm-yy":
				$display_start_pub_date = date("d-m-Y", strtotime($this->detail->start_publishing));
				break;
			case "mm-dd-yy":
				$display_start_pub_date = date("m-d-Y", strtotime($this->detail->start_publishing));
				break;
			default:	
				$display_start_pub_date = date("Y-m-d", strtotime($this->detail->start_publishing));
				break;
		}
	}

	$display_end_pub_date = $this->detail->end_publishing;	
	if($display_end_pub_date != "" && $display_end_pub_date != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_end_pub_date = date("Y-m-d", strtotime($this->detail->end_publishing));
				break;
			case "dd-mm-yy":
				$display_end_pub_date = date("d-m-Y", strtotime($this->detail->end_publishing));
				break;
			case "mm-dd-yy":
				$display_end_pub_date = date("m-d-Y", strtotime($this->detail->end_publishing));
				break;
			default:	
				$display_end_pub_date = date("Y-m-d", strtotime($this->detail->end_publishing));
				break;
		}
	}


	// get resources 
	$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE published = 1 and resource_admins LIKE '%|".$user->id."|%' ";
	try{
		$database->setQuery($sql);
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "coup_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		


?>
<?php if($showform){?>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.8.2/jquery-ui.js"></script>

<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>
<script language="JavaScript">
	jQuery(function() {
  		jQuery( "#display_start_pub_date" ).datepicker({
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
  		jQuery( "#display_end_pub_date" ).datepicker({
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

		
	function doCancel(){
		Joomla.submitform("cancel");
	}		

	function doClose(){
		Joomla.submitform("close");
	}		
	
	function doSave(){
		if(document.getElementById('entity_id').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE');?>');
			return(false);
		}
		if(isNaN(document.getElementById('rate_adjustment').value)){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENTS_NAN');?>');
			return(false);
		}

		Joomla.submitform("save_rate_adjustments_detail");
	}
	

	function setDatePicker(which){
		if(which == "start_publishing"){
			if(document.getElementById("date_picker_format")!=null){
				var tempdate;
				tempdate = Date.parse(document.getElementById("start_publishing").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_start_pub_date").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_start_pub_date").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_start_pub_date").value = tempdate.toString("yyyy-MM-dd");
				}		
			}
		} else {
			if(document.getElementById("date_picker_format")!=null){
				var tempdate;
				tempdate = Date.parse(document.getElementById("end_publishing").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_end_pub_date").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_end_pub_date").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_end_pub_date").value = tempdate.toString("yyyy-MM-dd");
				}		
			}
		}
	}	
	
	function setstarttime(){
		document.getElementById("timeRangeStart").value = document.getElementById("starttime_hour").value + ":" + document.getElementById("starttime_minute").value + ":00";
	}
	function setendtime(){
		document.getElementById("timeRangeEnd").value = document.getElementById("endtime_hour").value + ":" + document.getElementById("endtime_minute").value + ":00";
	}
	
	</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_rate_admin_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<table width="100%"  >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE')." - ".JText::_('RS1_ADMIN_SCRN_TAB_RATE_ADJUSTMENTS');?></h3></td>
    </tr>
   <tr>
      <td colspan="3" align="right" height="40px" class="fe_header_bar">
      <?php if($this->lock_msg != ""){?>
	      <?php echo $this->lock_msg?>
    	  &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doClose();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?></a>&nbsp;&nbsp;</td>
      <?php } else { ?>
	      <a href="#" onclick="doSave();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?></a>
    	  &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doCancel();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
      <?php } ?>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0" class="table table-striped">
    <tr>
      <td colspan="3">
        <p><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENTS_INTRO');?><br /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD');?>:</td>
      <td colspan="2"><?php echo $this->detail->id_rate_adjustments ?></td>
    </tr>
	<tr>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RES_NAME');?></td>
      <td>
      <select name="entity_id" id="entity_id" >
          <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>"  <?php if($this->detail->entity_id == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo stripslashes($res_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
      </select>
      </td>
      <td></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_BY');?></td>
      <td><select style="width:auto" name="by_day_time" id="by_day_time">
			<option value="DayOnly" <?php if($this->detail->by_day_time == "DayOnly"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_DAY_ONLY');?></option>
			<option value="TimeOnly"<?php if($this->detail->by_day_time == "TimeOnly"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TIME_ONLY');?></option>
			<option value="DayAndTime"<?php if($this->detail->by_day_time == "DayAndTime"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_DAY_TIME');?></option>
           </select>
      </td>      
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_BY_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_DAYS');?></td>
	  <td>
      <table>
      	<tr>
	      	<td><?php echo JText::_('RS1_ADMIN_SCRN_SUNDAY');?></td>
            <td><select name="adjustSunday" id="adjustSunday" >
	            <option value="Yes" <?php if($this->detail->adjustSunday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
	            <option value="No" <?php if($this->detail->adjustSunday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_MONDAY');?></td>
            <td><select name="adjustMonday" id="adjustMonday">
        	    <option value="Yes" <?php if($this->detail->adjustMonday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            	<option value="No" <?php if($this->detail->adjustMonday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_TUESDAY');?></td>
            <td><select name="adjustTuesday" id="adjustTuesday">
    	        <option value="Yes" <?php if($this->detail->adjustTuesday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
	            <option value="No" <?php if($this->detail->adjustTuesday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_WEDNESDAY');?></td>
            <td><select name="adjustWednesday" id="adjustWednesday">
        	    <option value="Yes" <?php if($this->detail->adjustWednesday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    	        <option value="No" <?php if($this->detail->adjustWednesday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_THURSDAY');?></td>
            <td><select name="adjustThursday" id="adjustThursday">
	            <option value="Yes" <?php if($this->detail->adjustThursday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    	        <option value="No" <?php if($this->detail->adjustThursday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_FRIDAY');?></td>
            <td><select name="adjustFriday" id="adjustFriday">
	            <option value="Yes" <?php if($this->detail->adjustFriday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
	            <option value="No" <?php if($this->detail->adjustFriday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_SATURDAY');?></td>
            <td><select name="adjustSaturday" id="adjustSaturday">
	            <option value="Yes" <?php if($this->detail->adjustSaturday == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
	            <option value="No" <?php if($this->detail->adjustSaturday == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select>
    		</td>
        </tr>
      </table>      
      </td>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_DAYS_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TIME');?></td>
	  <td>
      <table>
      	<tr>
	      	<td><?php echo JText::_('RS1_ADMIN_SCRN_STARTTIME');?></td>
            <td>
                <div style="display: table-cell;"><select style="width:auto;" name="starttime_hour" id="starttime_hour" onchange="setstarttime();" class="admin_dropdown">
                <?php 
                for($x=0; $x<24; $x+=1){
                    if($x<10){
                        $x = "0".$x;
                    }
                    echo "<option value=".$x; if(substr($this->detail->timeRangeStart,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                }
                ?>
                </select></div>
                <div style="display: table-cell; padding-left:5px; vertical-align:middle">:</div>
                <div style="display: table-cell; padding-left:5px;"><select style="width:auto;" name="starttime_minute" id="starttime_minute" onchange="setstarttime();" class="admin_dropdown" >
                <?php
                for($x=0; $x<59; $x+=1){
                    if($x<10){
                        $x = "0".$x;
                    }
                    echo "<option value=".$x; if(substr($this->detail->timeRangeStart,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                }
                ?>
                </select></div>        
                <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
                 <input type="hidden" name="timeRangeStart" id="timeRangeStart" value="<?php echo $this->detail->timeRangeStart ?>" />      
	        </td>
        </tr>
		<tr>
			<td><?php echo JText::_('RS1_ADMIN_SCRN_ENDTIME');?></td>
            <td>
                <div style="display: table-cell;"><select style="width:auto;" name="endtime_hour" id="endtime_hour" onchange="setendtime();" class="admin_dropdown">
                <?php 
                for($x=0; $x<24; $x+=1){
                    if($x<10){
                        $x = "0".$x;
                    }
                    echo "<option value=".$x; if(substr($this->detail->timeRangeEnd,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                }
                ?>
                </select></div>
                <div style="display: table-cell; padding-left:5px; vertical-align:middle">:</div>
                <div style="display: table-cell; padding-left:5px;"><select style="width:auto;" name="endtime_minute" id="endtime_minute" onchange="setendtime();" class="admin_dropdown" >
                <?php
                for($x=0; $x<59; $x+=1){
                    if($x<10){
                        $x = "0".$x;
                    }
                    echo "<option value=".$x; if(substr($this->detail->timeRangeEnd,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                }
                ?>
                </select></div>        
                <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
                 <input type="hidden" name="timeRangeEnd" id="timeRangeEnd" value="<?php echo $this->detail->timeRangeEnd ?>" />      
	        </td>
        </tr>
      </table>      
      </td>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_TIME_HELP');?></td>
    </tr>


	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE');?></td>
	  <td><input style="width:50px; text-align: center" type="text" size="8" maxsize="6" name="rate_adjustment" id="rate_adjustment" value="<?php echo $this->detail->rate_adjustment; ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT');?></td>
	  <td><select name="rate_adjustment_unit" id="rate_adjustment_unit">
            <option value="Percent" <?php if($this->detail->rate_adjustment_unit == "Percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_PERCENT');?></option>
            <option value="Flat" <?php if($this->detail->rate_adjustment_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_FLAT');?></option>
            </select>        </td>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBSTART_DATE');?></td>
 	  <td>
        <input readonly="readonly" name="start_publishing" id="start_publishing" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->start_publishing; ?>" />
    
        <input type="text" readonly="readonly" id="display_start_pub_date" name="display_start_pub_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_start_pub_date ?>" onchange="setDatePicker('start_publishing');">

	    &nbsp;<a href="#" onclick="document.getElementById('display_start_pub_date').value=''; document.getElementById('start_publishing').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBSTART_DATE_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBEND_DATE');?></td>
 	  <td>
        <input readonly="readonly" name="end_publishing" id="end_publishing" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->end_publishing; ?>" />
    
        <input type="text" readonly="readonly" id="display_end_pub_date" name="display_end_pub_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_end_pub_date ?>" onchange="setDatePicker('end_publishing');">
      
	    &nbsp;<a href="#" onclick="document.getElementById('display_end_pub_date').value=''; document.getElementById('end_publishing').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBEND_DATE_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD');?></td>
        <td colspan="2">
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>

  </table>
  <input type="hidden" name="id_rate_adjustments" id="id_rate_adjustments" value="<?php echo $this->detail->id_rate_adjustments; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
  <input type="hidden" name="scope" id="selected_resources_id" value="<?php echo $scope; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
	<input type="hidden" name="entity_type" value="resource" />
	<input type="hidden" name="group_id" value="1" />
    
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
