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


	$showform= true;
	$jinput = JFactory::getApplication()->input;
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

		if($this->detail->expiry_date == null){
			//$this->detail->expiry_date = date('Y-m-d',strtotime("+1 month"));
			$this->detail->expiry_date = "";
		}
				
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "coup_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

	}	

	$display_picker_date = $this->detail->expiry_date;	
	$display_picker_date2 = $this->detail->valid_range_start;	
	$display_picker_date3 = $this->detail->valid_range_end;	
	if($display_picker_date != "" && $display_picker_date != "0000-00-00 00:00:00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date = date("Y-m-d", strtotime($this->detail->expiry_date));
				break;
			case "dd-mm-yy":
				$display_picker_date = date("d-m-Y", strtotime($this->detail->expiry_date));
				break;
			case "mm-dd-yy":
				$display_picker_date = date("m-d-Y", strtotime($this->detail->expiry_date));
				break;
			default:	
				$display_picker_date = date("Y-m-d", strtotime($this->detail->expiry_date));
				break;
		}
	}
	if($display_picker_date2 != "" && $display_picker_date2 != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date2 = date("Y-m-d", strtotime($this->detail->valid_range_start));
				break;
			case "dd-mm-yy":
				$display_picker_date2 = date("d-m-Y", strtotime($this->detail->valid_range_start));
				break;
			case "mm-dd-yy":
				$display_picker_date2 = date("m-d-Y", strtotime($this->detail->valid_range_start));
				break;
			default:	
				$display_picker_date2 = date("Y-m-d", strtotime($this->detail->valid_range_start));
				break;
		}
	}
	if($display_picker_date3 != "" && $display_picker_date3 != "0000-00-00"){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date3 = date("Y-m-d", strtotime($this->detail->valid_range_end));
				break;
			case "dd-mm-yy":
				$display_picker_date3 = date("d-m-Y", strtotime($this->detail->valid_range_end));
				break;
			case "mm-dd-yy":
				$display_picker_date3 = date("m-d-Y", strtotime($this->detail->valid_range_end));
				break;
			default:	
				$display_picker_date3 = date("Y-m-d", strtotime($this->detail->valid_range_end));
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
	// get resource assignments 
	if (strlen($this->detail->scope) > 0 ){
		$res_assignments = str_replace("||", ",", $this->detail->scope);
		$res_assignments = str_replace("|", "", $res_assignments);
		//echo $admins;
		//exit;
		$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE ".
  			"id_resources IN (".$database->escape($res_assignments).")";
		try{
			$database->setQuery($sql);
			$res_assignment_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "coup_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}	

	// get current use
	$cur_use= 0;
	$sql = "SELECT count(*) FROM #__sv_apptpro3_requests WHERE coupon_code = '".$this->detail->coupon_code."' ".
		"  AND ( request_status = 'accepted' OR request_status = 'attended' OR request_status = 'completed')";
	try{
		$database->setQuery($sql);
		$cur_use = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_coup_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
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
			altField: "#expiry_date",
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
			altField: "#valid_range_start",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date3" ).datepicker({
			showOn: "button",
			autoSize: true,
			changeMonth: true,
			changeYear: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#valid_range_end",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

		
	function doCancel(){
		Joomla.submitform("coup_cancel");
	}		

	function doClose(){
		Joomla.submitform("coup_close");
	}		
	
	function doSave(){
		if(document.getElementById('description').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DESC_REQ');?>');
			return(false);
		}
		if(document.getElementById('coupon_code').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_CODE_REQ');?>');
			return(false);
		}
		if(document.getElementById("discount").value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_VALUE_REQ');?>');
			return(false);
		}
		if(document.getElementById("selected_resources_id").value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RES_REQ');?>');
			return(false);
		}
//		if(document.getElementById("expiry_date").value == ""){
//			alert('<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_EXPIRY_REQ');?>');
//			return(false);
//		}
		if(document.getElementById('selected_resources_id').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE_ERR');?>');
			return(false);
		}

		Joomla.submitform("save_coupon_detail");
	}
	
	function doAddResource(){
		var resid = document.getElementById("resources").value;
		var selected_resources = document.getElementById("selected_resources_id").value;
		var x = document.getElementById("selected_resources");
		for (i=0;i<x.length;i++){
			if(x[i].value == resid) {
				alert("Already selected");
				return false;
			}			
		}
	
		var opt = document.createElement("option");
        // Add an Option object to Drop Down/List Box
        document.getElementById("selected_resources").options.add(opt); 
        opt.text = document.getElementById("resources").options[document.getElementById("resources").selectedIndex].text;
        opt.value = document.getElementById("resources").options[document.getElementById("resources").selectedIndex].value;
		selected_resources = selected_resources + "|" + resid + "|";
		document.getElementById("selected_resources_id").value = selected_resources;
	}

	function doRemoveResource(){
		if(document.getElementById("selected_resources").selectedIndex == -1){
			alert("No Resource selected for Removal");
			return false;
		}
		var res_to_go = document.getElementById("selected_resources").options[document.getElementById("selected_resources").selectedIndex].value;
		document.getElementById("selected_resources").remove(document.getElementById("selected_resources").selectedIndex);
		
		var selected_resource = document.getElementById("selected_resources_id").value;

		selected_resource = selected_resource.replace("|" + res_to_go + "|", "");
		document.getElementById("selected_resources_id").value = selected_resource;
	}

	function setDatePicker(which_one){
		if(document.getElementById("date_picker_format")!=null){
			if(which_one == 1){	
				var tempdate;
				tempdate = Date.parse(document.getElementById("expiry_date").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date").value = tempdate.toString("yyyy-MM-dd");
				}	
			} else if(which_one == 2){
				var tempdate;
				tempdate = Date.parse(document.getElementById("valid_range_start").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date2").value = tempdate.toString("yyyy-MM-dd");
				}
			} else {
				var tempdate;
				tempdate = Date.parse(document.getElementById("valid_range_end").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date3").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date3").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date3").value = tempdate.toString("yyyy-MM-dd");
				}
			}
		}




		if(document.getElementById("date_picker_format")!=null){
			var tempdate;
			tempdate = Date.parse(document.getElementById("expiry_date").value);	
				
			if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
				document.getElementById("display_picker_date").value = tempdate.toString("dd-MM-yyyy");
			}
			if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
				document.getElementById("display_picker_date").value = tempdate.toString("MM-dd-yyyy");
			}
			if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
				document.getElementById("display_picker_date").value = tempdate.toString("yyyy-MM-dd");
			}		
		}
	}	
	
	
	</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_coupon_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_RESOURCE_COUPONS_TITLE');?></h3></td>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0">
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
    <tr>
      <td colspan="3">
        <p><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_INTRO');?><br /></td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_ID');?>:</td>
      <td colspan="2"><?php echo $this->detail->id_coupons ?></td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_DESC');?>:</td>
      <td><input type="text" size="40" maxsize="80" name="description" id="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_DESC_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_CODE');?>:</td>
      <td><input type="text" size="20" maxsize="80" name="coupon_code" id="coupon_code" value="<?php echo stripslashes($this->detail->coupon_code); ?>" /></td>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DETAIL_CODE_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_DISCOUNT');?>:</td>
      <td colspan="2"><input type="text" size="20" maxsize="80" name="discount" id="discount" value="<?php echo stripslashes($this->detail->discount); ?>" /></td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_UNIT');?>:</td>
        <td colspan="2"><select name="discount_unit" id="discount_unit">
            <option value="percent" <?php if($this->detail->discount_unit == "percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_PERCENT');?></option>
            <option value="fixed" <?php if($this->detail->discount_unit == "fixed"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_FIXED');?></option>
            </select>
        </td>
    </tr>
    <tr class="admin_detail_row0">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_EXPIRY_DATE');?>:</td>
      <td valign="top">
        <input readonly="readonly" name="expiry_date" id="expiry_date" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->expiry_date; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date ?>" onchange="setDatePicker(1);">
      
	    &nbsp;<a href="#" onclick="document.getElementById('display_picker_date').value=''; document.getElementById('expiry_date').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_EXPIRY_DATE_HELP');?>&nbsp;</td>
	  </td>
    </tr> 
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_VALID_RANGE_START');?></td>
      <td>
        <input readonly="readonly" name="valid_range_start" id="valid_range_start" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->valid_range_start; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date2 ?>" onchange="setDatePicker(2);">

		&nbsp;<a href="#" onclick="document.getElementById('valid_range_start').value=''; document.getElementById('valid_range_start').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>
  	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_VALID_RANGE_START_HELP');?>&nbsp;</td>
    </tr>
    <tr  class="admin_detail_rowo">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_VALID_RANGE_END');?></td>
      <td>
        <input readonly="readonly" name="valid_range_end" id="valid_range_end" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->valid_range_end; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date3" name="display_picker_date3" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date3 ?>" onchange="setDatePicker(3);">
      
		&nbsp;<a href="#" onclick="document.getElementById('valid_range_end').value=''; document.getElementById('valid_range_end').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>
  	  </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_VALID_RANGE_END_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_MAX_USER_USE');?>:</td>
      <td><input type="text" size="2" maxsize="3" name="max_user_use" id="max_user_use" value="<?php echo ($this->detail->max_user_use==''?"0":$this->detail->max_user_use) ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_MAX_USER_USE_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_MAX_TOTAL_USE');?>:</td>
      <td><input type="text" size="2" maxsize="3" name="max_total_use" id="max_total_use" value="<?php echo ($this->detail->max_total_use==''?"0":$this->detail->max_total_use) ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_MAX_TOTAL_USE_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_CUR_USE_COUNT');?>:</td>
      <td><label style="width:30px; text-align: center" ><?php echo $cur_use ?></label></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_CUR_USE_COUNT_HELP');?>&nbsp;</td>
    </tr>
	<tr class="admin_detail_row0">
	  <td colspan="3" ><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RESOURCES_INTRO');?></td>
    </tr>
    <tr class="admin_detail_row0">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RESOURCES');?></td>
      <td >
      <table width="95%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="33%"><select name="resources" id="resources" style="width:auto;">
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_rows ); $i++) {
			$res_row = $res_rows[$i];
			?>
                <option value="<?php echo $res_row->id_resources; ?>"><?php echo JText::_(stripslashes($res_row->name)); ?></option>
                <?php $k = 1 - $k; 
			} ?>
              </select></td>
            <td width="34%" valign="top" align="center"><input type="button" name="btnAddResource" id="btnAddResource" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RESOURCES_ADD');?>" onclick="doAddResource()" style="font-size:smaller" />
              <br />
              &nbsp;<br />
              <input type="button" name="btnRemoveResource" id="btnRemoveResource" size="10"  onclick="doRemoveResource()" value="<?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RESOURCES_REMOVE');?>" style="font-size:smaller"/></td>
            <td width="33%">
            <div class="sv_select"><select name="selected_resources" id="selected_resources" size="4" >
             <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_assignment_rows ); $i++) {
			$res_assignment_row = $res_assignment_rows[$i];
			?>
                <option value="<?php echo $res_assignment_row->id_resources; ?>"><?php echo JText::_($res_assignment_row->name); ?></option>
                <?php 
				$scope = $scope."|".$res_assignment_row->id_resources."|";
				$k = 1 - $k; 
			} ?>
              </select></div><br /></td>
          </tr>
        </table></td>
    <td valign="top" width="50%"><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_RESOURCES_HELP');?></td>
    </tr>
    <tr class="admin_detail_row1">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_PUBLISHED');?></td>
        <td colspan="2"><select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>
        </td>
    </tr>

  </table>
  <br /><p><?php echo JText::_('RS1_ADMIN_SCRN_COUPON_NOTE2');?></p>
  <input type="hidden" name="id_coupons" value="<?php echo $this->detail->id_coupons; ?>" />
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

  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
