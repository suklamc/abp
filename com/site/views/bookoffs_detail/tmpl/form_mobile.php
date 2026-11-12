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


	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	$showform= true;
	$jinput = JFactory::getApplication()->input;
	$listpage = $jinput->getString('listpage', 'list');

	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

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
			logIt($e->getMessage(), "bo_detail_tmpl_form", "", "");
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
			logIt($e->getMessage(), "bo_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$display_picker_date = $this->detail->off_date;	
		if($display_picker_date != "" && $display_picker_date != "0000-00-00"){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date = date("Y-m-d", strtotime($this->detail->off_date));
					break;
				case "dd-mm-yy":
					$display_picker_date = date("d-m-Y", strtotime($this->detail->off_date));
					break;
				case "mm-dd-yy":
					$display_picker_date = date("m-d-Y", strtotime($this->detail->off_date));
					break;
				default:	
					$display_picker_date = date("Y-m-d", strtotime($this->detail->off_date));
					break;
			}
		}

		$daily_note = "";
		$rolling_days = array("1","1","1","1","1","1","1");
		if($this->detail->rolling_bookoff != "No"){
			$daily_note = JText::_('RS1_ADMIN_SCRN_BO_DAILY_DATE_NOTE');
			$rolling_days = explode(",", $this->detail->rolling_bookoff);
		}

	}	
	$sv_help_icon = "<img alt=\"\" src='".getImageSrc("help_udf2.png")."' class='sv_help_icon' style=\"float:right;\" ";
	
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
  		jQuery( "#display_picker_date" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#off_date",
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
			altField: "#off_date2",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	window.onload = function() {
	  changeDaily();
	};
<?php
	// To limit the New Book-offs date picker so operators cannot make book-offs too far in advance, uncomment the lines below.
	// Shown here limiting to 90 days, chaneg the 90 as required.
	
	//$day = strtotime("now");
	//$day = $day + (86400*90);
	//echo "cal.addDisabledDates('".strftime("%Y-%m-%d", $day)."', null);"; 
?>
		
	function doCancel(){
		Joomla.submitform("bo_cancel");
	}		

	function doClose(){
		Joomla.submitform("bo_close");
	}		
	
	function doSave(){
		if(document.getElementById('resource_id').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE_ERR');?>');
			return(false);
		}
		if(document.getElementById('off_date').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_OFF_DATE_ERR');?>');
			return(false);
		}
		if(document.getElementById("off_date2")!=null && document.getElementById("off_date2").value != ""){				
			document.getElementById("task").value = "create_bookoff_series"; // needed for IE9 strangeness
			Joomla.submitform("create_bookoff_series");				
			return true;				
		}

		document.getElementById("task").value = "save_bookoffs_detail"; // needed for IE9 strangeness
		if(document.getElementById('rolling_bookoff_select').value != "No"){
			document.getElementById('full_day').disabled = false; // if changing daily_bookoff disabled full_day we need to re-enable it so its No value gets saved.
		}
		Joomla.submitform("save_bookoffs_detail");
	}
	
	function setbookoffstarttime(){
		document.getElementById("bookoff_starttime").value = document.getElementById("bookoff_starttime_hour").value + ":" + document.getElementById("bookoff_starttime_minute").value + ":00";
	}
	function setbookoffendtime(){
		document.getElementById("bookoff_endtime").value = document.getElementById("bookoff_endtime_hour").value + ":" + document.getElementById("bookoff_endtime_minute").value + ":00";
	}
	
	function setDatePicker(which_one){
		if(document.getElementById("date_picker_format")!=null){
			if(which_one == 1){	
				var tempdate;
				tempdate = Date.parse(document.getElementById("off_date").value);	
					
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
				tempdate = Date.parse(document.getElementById("off_date2").value);	
					
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

	function changeDaily(){
		if(document.getElementById('rolling_bookoff_select').value === "No"){
			document.getElementById('rolling_bookoff').value = "No";
			document.getElementById('full_day').disabled = false;
			document.getElementById('anchor1').disabled = false;
			document.getElementById('daily_bookoff_help').innerHTML = "";
			if(document.getElementById("bo_days")!=null){
				document.getElementById("bo_days").style.visibility = "visible";
				document.getElementById("bo_days").style.display = "";
			}
			document.getElementById("rolling_days_table").style.visibility = "hidden";
			document.getElementById("rolling_days_table").style.display = "none";
			
		} else {
			document.getElementById('full_day').value = "No";
			document.getElementById('full_day').disabled = true;
			document.getElementById('daily_bookoff_help').innerHTML = "<?php echo JText::_('RS1_ADMIN_SCRN_BO_DAILY_DATE_NOTE');?>";
			document.getElementById('off_date').value = Date.today().toString("yyyy-MM-dd")
			document.getElementById("rolling_days_table").style.visibility = "visible";
			document.getElementById("rolling_days_table").style.display = "";
			
			if(document.getElementById("bo_days")!=null){
				document.getElementById("bo_days").style.visibility = "hidden";
				document.getElementById("bo_days").style.display = "none";
				document.getElementById('off_date2').value = Date.today().toString("yyyy-MM-dd")
			}
			// build string from checkboxes
			var day_filter = "";
			if(document.getElementById('chkRollingSunday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingMonday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingTuesday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingWednesday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingThursday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingFriday').checked==true){
				day_filter += "1,";
			} else {
				day_filter += "0,";
			}
			if(document.getElementById('chkRollingSaturday').checked==true){
				day_filter += "1";
			} else {
				day_filter += "0";
			}
			document.getElementById('rolling_bookoff').value = day_filter;
		}
	}
	
	</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_bookoff_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<h3><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_BOOKOFFS_TITLE_MOBILE');?></h3>
  <table class="table table-striped" width="100%" >
    <tr>
      <td class="fe_header_bar">
      <div class="controls sv_yellow_bar" align="center">
      <?php if($this->lock_msg != ""){?>
	      <?php echo $this->lock_msg?>
    	  <input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
      <?php } else { ?>
 		<input type="button" id="saveLink" onclick="doSave();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?>">
		<input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
      <?php } ?>
      </div>
      </td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_DETAIL_INTRO');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_DETAIL_ID');?>&nbsp;<?php echo $this->detail->id_bookoffs ?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BO_RESOURCE');?>
      <div class="controls">
      <?php if($this->detail->resource_id == ""){ ?>
	      <select name="resource_id" id="resource_id" class="sv_apptpro3_request_text">
          <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
        	  <option value="<?php echo $res_row->id_resources; ?>"  <?php if($this->filter_bookoffs_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
              <?php $k = 1 - $k; 
				} ?>
    	  </select>
      <?php } else { ?>
      			<input type="hidden" name="resource_id" id="resource_id" value=<?php echo $this->detail->resource_id;?> />
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
					$res_row = $res_rows[$i];
					if($this->detail->resource_id == $res_row->id_resources){
        	  			echo JText::_(stripslashes($res_row->name));
              		}
					$k = 1 - $k; 
				}     		
      		} 
			?>    
      </div>
      </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_DAILY');?><?php echo $sv_help_icon." onclick='alert(\"".strip_tags(JText::_('RS1_ADMIN_SCRN_BO_DAILY_HELP'))."\")'>";?></div>
      <div class="controls">
      	<select name="rolling_bookoff_select" id="rolling_bookoff_select" onchange="changeDaily();">
          <option value="Yes" <?php if($this->detail->rolling_bookoff != "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->rolling_bookoff == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
          </select>  
            <div id="rolling_days_table" style="visibility:hidden">    
                <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                      <tr align="left">
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_SUN');?></td>
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_MON');?></td>
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_TUE');?></td>
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_WED');?></td>
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_THU');?></td>
                        <td width="15%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_FRI');?></td>
                        <td width="10%" class="center"><?php echo JText::_('RS1_ADMIN_SCRN_SAT');?></td>
                      </tr>
                      <tr>
                        <td class="center"><input type="checkbox" name="chkRollingSunday" id="chkRollingSunday" <?php echo ($rolling_days[0]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingMonday" id="chkRollingMonday" <?php echo ($rolling_days[1]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingTuesday" id="chkRollingTuesday" <?php echo ($rolling_days[2]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingWednesday" id="chkRollingWednesday" <?php echo ($rolling_days[3]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingThursday" id="chkRollingThursday" <?php echo ($rolling_days[4]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingFriday" id="chkRollingFriday" <?php echo ($rolling_days[5]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                        <td class="center"><input type="checkbox" name="chkRollingSaturday" id="chkRollingSaturday" <?php echo ($rolling_days[6]=="1"?"checked":"")?> onchange="changeDaily();"/></td>
                  </tr>
             </table>
            </div>
           <input type="hidden" id="rolling_bookoff" name="rolling_bookoff" value="<?php echo $this->detail->rolling_bookoff;?>"/>
        </div>    
      </td>
    </tr>	    
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_DATE');?><?php echo $sv_help_icon." onclick='alert(\"".strip_tags(JText::_('RS1_ADMIN_SCRN_BO_DATE_HELP'))."\")'>";?></div>
      <div class="controls">
        <input readonly="readonly" name="off_date" id="off_date" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->off_date; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date ?>" onchange="setDatePicker(1);">
           
		   <?php if($this->detail->id_bookoffs == ""){ ?>
            <br /><?php echo JText::_('RS1_ADMIN_SCRN_BO_DATE_TO');?>&nbsp;
            <input readonly="readonly" name="off_date2" id="off_date2" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="" />
        
            <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
                value="" onchange="setDatePicker(2);">
            	<div><label id="daily_bookoff_help"><?php echo $daily_note;?></label></div>
	           <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr align="left">
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_SUN');?></td>
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_MON');?></td>
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_TUE');?></td>
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_WED');?></td>
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_THU');?></td>
                    <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_FRI');?></td>
                    <td width="10%"><?php echo JText::_('RS1_ADMIN_SCRN_SAT');?></td>
                  </tr>
                  <tr>
                    <td><input type="checkbox" name="chkSunday" id="chkSunday" checked /></td>
                    <td><input type="checkbox" name="chkMonday" id="chkMonday" checked /></td>
                    <td><input type="checkbox" name="chkTuesday" id="chkTuesday" checked /></td>
                    <td><input type="checkbox" name="chkWednesday" id="chkWednesday" checked /></td>
                    <td><input type="checkbox" name="chkThursday" id="chkThursday" checked /></td>
                    <td><input type="checkbox" name="chkFriday" id="chkFriday" checked /></td>
                    <td><input type="checkbox" name="chkSaturday" id="chkSaturday" checked /></td>
              </tr>
              </table>   
              <?php echo JText::_('RS1_ADMIN_SCRN_BO_DATE_DAYS_HELP');?>         
           <?php } else { ?>
           <label id="daily_bookoff_help"><?php echo $daily_note;?></label>
            <?php } ?>
		</div>
        </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_FULLDAY');?><?php echo $sv_help_icon." onclick='alert(\"".strip_tags(JText::_('RS1_ADMIN_SCRN_BO_RANGE_HELP'))."\")'>";?></div>
      <div class="controls">
      	<select name="full_day" id="full_day" class="sv_apptpro3_request_text">
           <option value="Yes" <?php if($this->detail->full_day == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
           <option value="No" <?php if($this->detail->full_day == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
         </select>
       <div>
       </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_RANGE_START');?></div>
      <div class="controls">
      	<select name="bookoff_starttime_hour" id="bookoff_starttime_hour" style="width:auto;" onchange="setbookoffstarttime();" class="sv_apptpro3_requests_dropdown" >
		<?php
		for($x=0; $x<24; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->bookoff_starttime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select> : 
		<select name="bookoff_starttime_minute" id="bookoff_starttime_minute" style="width:auto;" onchange="setbookoffstarttime();" class="sv_apptpro3_requests_dropdown" >
		<?php
		for($x=0; $x<59; $x+=5){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->bookoff_starttime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select>
		<?php echo JText::_('RS1_ADMIN_SCRN_HHMM');?>
        <input type="hidden" name="bookoff_starttime" id="bookoff_starttime" value="<?php echo $this->detail->bookoff_starttime ?>" />
      <div>
      </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_RANGE_END');?> </div>
      <div class="controls">
        <select name="bookoff_endtime_hour" id="bookoff_endtime_hour" style="width:auto;" onchange="setbookoffendtime();" class="sv_apptpro3_requests_dropdown" >
		<?php
		for($x=0; $x<24; $x+=1){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->bookoff_endtime,0,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select> : 
		<select name="bookoff_endtime_minute" id="bookoff_endtime_minute" style="width:auto;" onchange="setbookoffendtime();" class="sv_apptpro3_requests_dropdown" >
		<?php
		for($x=0; $x<59; $x+=5){
			if($x<10){
				$x = "0".$x;
			}
			echo "<option value=".$x; if(substr($this->detail->bookoff_endtime,3,2) == $x) {echo " selected='selected' ";} echo ">".$x." </option>";  
		}
		?>
        </select>
		<?php echo JText::_('RS1_ADMIN_SCRN_HHMM');?>
        <input type="hidden" name="bookoff_endtime" id="bookoff_endtime" value="<?php echo $this->detail->bookoff_endtime ?>" />
      </div>
      </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BO_DESC');?><?php echo $sv_help_icon." onclick='alert(\"".strip_tags(JText::_('RS1_ADMIN_SCRN_BO_DESC_HELP'))."\")'>";?></div>
      <div class="controls">
      	<input type="text" size="40" maxsize="80" name="description" class="sv_apptpro3_request_text" value="<?php echo stripslashes($this->detail->description); ?>" />
      </div>
      </td>
    </tr>
    <tr>
      <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_DETAIL_PUBLISHED');?></div>
      <div class="controls">
        <select name="published" class="sv_apptpro3_request_text">
        <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
        </select>
      </div>
      </td>
    </tr>

  </table>
  <input type="hidden" name="id_bookoffs" value="<?php echo $this->detail->id_bookoffs; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
  <input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
  <input type="hidden" name="mobile" id="mobile" value="Yes" />    
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 <br/> Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
