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

// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE timeslots != 'Global' ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_timeslots_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	if($this->detail->day_number == "all"){
		$day_number = $this->detail->day_number;
	} else {
		$day_number = $this->detail->day_number;
	}
	if($day_number == ""){
		$day_number = $this->current_day_number;
	}
	
	$resource = $this->detail->resource_id;
	if($resource == ""){
		$resource = $this->current_resource_id;
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
<script>
	function setstarttime(){
		document.getElementById("timeslot_starttime").value = document.getElementById("timeslot_starttime_hour").value + ":" + document.getElementById("timeslot_starttime_minute").value + ":00";
	}
	function setendtime(){
		document.getElementById("timeslot_endtime").value = document.getElementById("timeslot_endtime_hour").value + ":" + document.getElementById("timeslot_endtime_minute").value + ":00";
	}
</script>

<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="JavaScript">

$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		var ok = "yes";
		if (pressbutton == 'save' || pressbutton == 'save2new'){
			if(document.getElementById('day_number').selectedIndex == 0){
				alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_A_DAY');?>');
				ok = "no";
				return false;
			}
			if(ok == "yes"){
				if(document.getElementById('single_or_series').value == "series"){
					if(document.getElementById('range_start_time_hour').selectedIndex == document.getElementById('range_end_time_hour').selectedIndex
					&& document.getElementById('range_start_time_minute').selectedIndex == document.getElementById('range_end_time_minute').selectedIndex)				{
						alert('<?php echo JText::_('RS1_ADMIN_SCRN_START_EQ_END');?>');
						return false;
					}

					// duration a number
					if(isNaN(document.getElementById('slot_duration').value)){
						alert('<?php echo JText::_('RS1_ADMIN_SCRN_DURATION_BAD');?>');
						return false;
					}
					// duration 1-1440
					intDuration = parseInt(document.getElementById('slot_duration').value);
					if(intDuration < 5 || intDuration > 1440){
						alert('<?php echo JText::_('RS1_ADMIN_SCRN_DURATION_BAD');?>');
						return false;
					}
					//start + first slot not beyond end
					today = Date.today();
					range_start = today.add({ hours: parseInt(document.getElementById('range_start_time_hour').value), minutes: parseInt(document.getElementById('range_start_time_minute').value) });
					today2 = Date.today();
					range_end = today2.add({ hours: parseInt(document.getElementById('range_end_time_hour').value), minutes: parseInt(document.getElementById('range_end_time_minute').value) });
					
					start_plus_slot = range_start.add(intDuration).minutes(); 
					if(start_plus_slot > range_end){
						alert('<?php echo JText::_('RS1_ADMIN_SCRN_SLOT_TOO_BIG');?>');
						return false;
					}
					if (pressbutton == 'save2new'){
						Joomla.submitform("create_timeslot_series2new");
					} else {
						Joomla.submitform("create_timeslot_series");
					}
					
				} else {
					if(document.getElementById('timeslot_starttime_hour').selectedIndex == document.getElementById('timeslot_endtime_hour').selectedIndex
					&& document.getElementById('timeslot_starttime_minute').selectedIndex == document.getElementById('timeslot_endtime_minute').selectedIndex)				{
						alert('<?php echo JText::_('RS1_ADMIN_SCRN_START_EQ_END');?>');
						ok = "no";
						return false;
					}
					Joomla.submitform(pressbutton);
				}
			}
		} else {
			Joomla.submitform(pressbutton);
		}		

  });
});

//	Joomla.submitbutton = function (pressbutton) {
//		var ok = "yes";
//		if (pressbutton == 'save' || pressbutton == 'save2new'){
//			if(document.getElementById('day_number').selectedIndex == 0){
//				alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_A_DAY');?>');
//				ok = "no";
//			}
//			if(ok == "yes"){
//				if(document.getElementById('single_or_series').value == "series"){
//					if(document.getElementById('range_start_time_hour').selectedIndex == document.getElementById('range_end_time_hour').selectedIndex
//					&& document.getElementById('range_start_time_minute').selectedIndex == document.getElementById('range_end_time_minute').selectedIndex)				{
//						alert('<?php echo JText::_('RS1_ADMIN_SCRN_START_EQ_END');?>');
//						return false;
//					}
//
//					// duration a number
//					if(isNaN(document.getElementById('slot_duration').value)){
//						alert('<?php echo JText::_('RS1_ADMIN_SCRN_DURATION_BAD');?>');
//						return false;
//					}
//					// duration 1-1440
//					intDuration = parseInt(document.getElementById('slot_duration').value);
//					if(intDuration < 5 || intDuration > 1440){
//						alert('<?php echo JText::_('RS1_ADMIN_SCRN_DURATION_BAD');?>');
//						return false;
//					}
//					//start + first slot not beyond end
//					today = Date.today();
//					range_start = today.add({ hours: parseInt(document.getElementById('range_start_time_hour').value), minutes: parseInt(document.getElementById('range_start_time_minute').value) });
//					today2 = Date.today();
//					range_end = today2.add({ hours: parseInt(document.getElementById('range_end_time_hour').value), minutes: parseInt(document.getElementById('range_end_time_minute').value) });
//					
//					start_plus_slot = range_start.add(intDuration).minutes(); 
//					if(start_plus_slot > range_end){
//						alert('<?php echo JText::_('RS1_ADMIN_SCRN_SLOT_TOO_BIG');?>');
//						return false;
//					}
//					if (pressbutton == 'save2new'){
//						Joomla.submitform("create_timeslot_series2new");
//					} else {
//						Joomla.submitform("create_timeslot_series");
//					}
//					
//				} else {
//					if(document.getElementById('timeslot_starttime_hour').selectedIndex == document.getElementById('timeslot_endtime_hour').selectedIndex
//					&& document.getElementById('timeslot_starttime_minute').selectedIndex == document.getElementById('timeslot_endtime_minute').selectedIndex)				{
//						alert('<?php echo JText::_('RS1_ADMIN_SCRN_START_EQ_END');?>');
//						ok = "no";
//					}
//					Joomla.submitform(pressbutton);
//				}
//			}
//		} else {
//			Joomla.submitform(pressbutton);
//		}		
//	}
	
	function single_series(){
		if(document.getElementById('Single_Slot').checked == true){
			document.getElementById('single_or_series').value = "single";
			}
		if(document.getElementById('Slot_Set').checked == true){
			document.getElementById('single_or_series').value = "series";
			}
	}
</script>

<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<div id="sv_be_admin">
<?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_INTRO');?>
  <table class="table table-striped" >
    <tr>
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_timeslots ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_RESOURCE');?></td>
      <td ><select name="resource_id" >
          <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_GLOBAL');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>"  <?php if($resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo stripslashes($res_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
      </select>
      &nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_RESOURCE_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_DAY');?></td>
      <td colspan="3"><select name="day_number" id="day_number">
          <option value="-1" ><?php echo JText::_('RS1_ADMIN_SCRN_SELECT_A_DAY');?></option>
          <option value="0" <?php if($day_number == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SUNDAY');?></option>
          <option value="1" <?php if($day_number == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_MONDAY');?></option>
          <option value="2" <?php if($day_number == "2"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_TUESDAY');?></option>
          <option value="3" <?php if($day_number == "3"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_WEDNESDAY');?></option>
          <option value="4" <?php if($day_number == "4"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_THURSDAY');?></option>
          <option value="5" <?php if($day_number == "5"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FRIDAY');?></option>
          <option value="6" <?php if($day_number == "6"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SATURDAY');?></option>
        </select></td>
    </tr>
 <?php if($this->detail->id_timeslots == ""){ ?>    
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SINGLE_OR_SERIES');?></td>
      <td colspan="2">
      	<table>
        	<tr> 
            	<td><input name="rbslots" type="radio" id="Single_Slot" onclick="single_series()" checked="checked"/>
				<?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SINGLE');?></td>
                <td>
                <table>
                    <tr>
                      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_START');?> </td>
                      <td><div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_starttime_hour" id="timeslot_starttime_hour" onchange="setstarttime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<24; $x+=1){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div>
                        <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_starttime_minute" id="timeslot_starttime_minute" onchange="setstarttime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<59; $x+=5){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div>       
                         <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
                        <input type="hidden" name="timeslot_starttime" id="timeslot_starttime" value="<?php echo $this->detail->timeslot_starttime ?>" /> 
                        </td>                                             
                    </tr>
                    <tr>
                      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_END');?></td>
                      <td colspan="3">
                      <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_endtime_hour" id="timeslot_endtime_hour" onchange="setendtime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<24; $x+=1){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div> 
                        <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_endtime_minute" id="timeslot_endtime_minute" onchange="setendtime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<59; $x+=5){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div>
                         <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
                        <input type="hidden" name="timeslot_endtime" id="timeslot_endtime" value="<?php echo $this->detail->timeslot_endtime ?>" />      </td>
                    </tr>
                </table>
                </td>
			    <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SINGLE_HELP');?></td>                
            </tr>
        	<tr>
            	<td><input name="rbslots" type="radio" id="Slot_Set" onclick="single_series()" />
				<?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SET');?></td>
                <td>
                <table>
                    <tr>
                      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SERIES_START');?> </td>
                      <td><div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="range_start_time_hour" id="range_start_time_hour" onchange="setstarttime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<24; $x+=1){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div>
                        <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="range_start_time_minute" id="range_start_time_minute" onchange="setstarttime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<59; $x+=5){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div>       
                        </td>                                             
                    </tr>
                    <tr>
                      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SERIES_END');?></td>
                      <td>
                      <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="range_end_time_hour" id="range_end_time_hour" onchange="setendtime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<24; $x+=1){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div> 
                        <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="range_end_time_minute" id="range_end_time_minute" onchange="setendtime();" class="sv_ts_request_dropdown" >
                        <?php
                        for($x=0; $x<59; $x+=5){
                            if($x<10){
                                $x = "0".$x;
                            }
                            echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
                        }
                        ?>
                        </select></div>
                    </tr>
                    <tr>
                      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SERIES_DURATION');?></td>
					  <td><input type="text" name="slot_duration" id="slot_duration" maxlength="4" style="width:60px; text-align:center" 
                      value="60" /><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SERIES_DURATION_HELP');?> </td> 
                    </tr>
                </table>
                </td>
			    <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_SERIES_HELP');?></td>                
            </tr>
        </table>    
	  </td>
    </tr>  
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DELETE_B4_ADD');?></td>
        <td>
            <select name="delete_b4_create">
            <option value="No"><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes"><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DELETE_B4_ADD_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_UNPUB_B4_ADD');?></td>
        <td>
            <select name="unpublish_b4_create">
            <option value="No"><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes"><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_UNPUB_B4_ADD_HELP');?></td>
    </tr>
    
 <?php } else { ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_START');?> </td>
      <td><div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_starttime_hour" id="timeslot_starttime_hour" onchange="setstarttime();" class="sv_ts_request_dropdown" >
		<?php
		for($x=0; $x<24; $x+=1){
/*			if($x==12){
				echo "<option value=".$x.":00:00"; if($this->detail->timeslot_starttime == $x.":00:00") {echo " selected='selected' ";} echo ">Noon</option>";  
				echo "<option value=".$x.":15:00"; if($this->detail->timeslot_starttime == $x.":15:00") {echo " selected='selected' ";} echo ">".$x.":15 </option>";  
				echo "<option value=".$x.":30:00"; if($this->detail->timeslot_starttime == $x.":30:00") {echo " selected='selected' ";} echo ">".$x.":30 </option>";  
				echo "<option value=".$x.":45:00"; if($this->detail->timeslot_starttime == $x.":45:00") {echo " selected='selected' ";} echo ">".$x.":45 </option>";  
			} else if($x==24){
				echo "<option value=".$x.":00:00"; if($this->detail->timeslot_starttime == $x.":00:00") {echo " selected='selected' ";} echo ">Midnight</option>";  
			} else {
				if($x<10){
					$x = "0".$x;
				}
				echo "<option value=".$x.":00:00"; if($this->detail->timeslot_starttime == $x.":00:00") {echo " selected='selected' ";} echo ">".$x.":00 </option>";  
				echo "<option value=".$x.":15:00"; if($this->detail->timeslot_starttime == $x.":25:00") {echo " selected='selected' ";} echo ">".$x.":15 </option>";  
				echo "<option value=".$x.":30:00"; if($this->detail->timeslot_starttime == $x.":30:00") {echo " selected='selected' ";} echo ">".$x.":30 </option>";  
				echo "<option value=".$x.":45:00"; if($this->detail->timeslot_starttime == $x.":45:00") {echo " selected='selected' ";} echo ">".$x.":45 </option>";  
			}
*/
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div>
		<div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_starttime_minute" id="timeslot_starttime_minute" onchange="setstarttime();" class="sv_ts_request_dropdown" >
		<?php
		for($x=0; $x<59; $x+=5){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_starttime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select></div>       
         <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
        <input type="hidden" name="timeslot_starttime" id="timeslot_starttime" value="<?php echo $this->detail->timeslot_starttime ?>" />      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_END');?></td>
      <td colspan="3">
      <div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_endtime_hour" id="timeslot_endtime_hour" onchange="setendtime();" class="sv_ts_request_dropdown" >
		<?php
		for($x=0; $x<24; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select></div><div style="display: table-cell; padding-left:10px; vertical-align:middle">:</div> 
		<div style="display: table-cell; padding-left:10px;"><select style="width:auto; min-width:50px" name="timeslot_endtime_minute" id="timeslot_endtime_minute" onchange="setendtime();" class="sv_ts_request_dropdown" >
		<?php
		for($x=0; $x<59; $x+=5){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->timeslot_endtime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select></div>
         <div style="display: table-cell; padding-left:10px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_HINT');?></div>
        <input type="hidden" name="timeslot_endtime" id="timeslot_endtime" value="<?php echo $this->detail->timeslot_endtime ?>" />      </td>
    </tr>
    
 <?php } ?>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_DESC');?></td>
      <td valign="top"><input type="text" size="30" maxsize="50" name="timeslot_description" value="<?php echo $this->detail->timeslot_description; ?>" />&nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_DESC_HELP');?></td>
    </tr>
    
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBSTART_DATE');?></td>
      <td><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="start_publishing" id="start_publishing" value="<?php echo $this->detail->start_publishing; ?>" />
		        <a href="#" id="anchor3785" onclick="cal.select(document.forms['adminForm'].start_publishing,'anchor3785','yyyy-MM-dd'); return false;"
					 name="anchor3785"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBSTART_DATE_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBEND_DATE');?></td>
      <td><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="end_publishing" id="end_publishing" value="<?php echo $this->detail->end_publishing; ?>" />
		        <a href="#" id="anchor3786" onclick="cal.select(document.forms['adminForm'].end_publishing,'anchor3786','yyyy-MM-dd'); return false;"
					 name="anchor3786"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TS_PUBEND_DATE_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_STAFF_ONLY_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_PUBLISHED');?></td>
        <td>
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" ><br /><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_NOTES');?></td>
    </tr>  
  </table>

</fieldset>
  <input type="hidden" name="id_timeslots" value="<?php echo $this->detail->id_timeslots; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="timeslots_detail" />
  <input type="hidden" name="single_or_series" id="single_or_series" value="single" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</div>
</form>
