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

$database = JFactory::getDBO();

	// get resources 
	$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE published = 1 ORDER BY name";
	try{
		$database->setQuery($sql);
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_rate_over_detail_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// get groups
	try{
		$database->setQuery("SELECT * FROM #__usergroups ORDER BY title" );
		$user_group_rows = $database -> loadObjectList();
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
	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}	


?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white; z-index:999999"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
//	cal.addDisabledDates(null,formatDate(Date.parse('yesterday'),"yyyy-MM-dd")); 
	cal.showYearNavigation();
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
</script>

<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
	pressbutton = document.adminForm.task.value;  
   	if (pressbutton == 'save'){
		if(document.getElementById("entity_id").selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE');?>');
			return false;
		}
		if(document.getElementById("rate_adjustment").value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_REQ');?>');
			return false;
		} else if(isNaN(document.getElementById("rate_adjustment").value)){
			alert('<?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_NUM');?>');
			return false;			
		}
	}		
	Joomla.submitform(pressbutton);
  });
});

	function setstarttime(){
		document.getElementById("timeRangeStart").value = document.getElementById("starttime_hour").value + ":" + document.getElementById("starttime_minute").value + ":00";
	}
	function setendtime(){
		document.getElementById("timeRangeEnd").value = document.getElementById("endtime_hour").value + ":" + document.getElementById("endtime_minute").value + ":00";
	}

</script>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<div id="sv_be_admin">
	<?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENTS_INTRO');?><br/>
<hr/>    
  <table class="table table-striped" >
    <tr >
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD');?></td>
      <td><?php echo $this->detail->id_rate_adjustments ?></td>
      <td width="50%">&nbsp;</td>
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
            <option value="Per_Seat" <?php if($this->detail->rate_adjustment_unit == "Per_Seat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_PERSEAT');?></option>
            </select>        </td>
      <td><?php echo JText::_('RS1_ADMIN_RATE_ADJUSTMENT_RATE_UNIT_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBSTART_DATE');?></td>
      <td><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="start_publishing" id="start_publishing" value="<?php echo $this->detail->start_publishing; ?>" />
		        <a href="#" id="anchor3785" onclick="cal.select(document.forms['adminForm'].start_publishing,'anchor3785','yyyy-MM-dd'); return false;"
					 name="anchor3785"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBSTART_DATE_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_ADJUSTMENT_PUBEND_DATE');?></td>
      <td><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="end_publishing" id="end_publishing" value="<?php echo $this->detail->end_publishing; ?>" />
		        <a href="#" id="anchor3786" onclick="cal.select(document.forms['adminForm'].end_publishing,'anchor3786','yyyy-MM-dd'); return false;"
					 name="anchor3786"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
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

</fieldset>
  <input type="hidden" name="id_rate_adjustments" value="<?php echo $this->detail->id_rate_adjustments; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="entity_type" value="resource" />
  <input type="hidden" name="group_id" value="1" />
  <input type="hidden" name="controller" value="rate_adjustments_detail" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</div>
</form>
