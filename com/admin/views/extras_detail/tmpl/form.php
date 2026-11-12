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

// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_extras_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// get resources 
	$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE published = 1 ORDER BY ordering";
	try{
		$database->setQuery($sql);
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_extras_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// get resource assignments 
	if (strlen($this->detail->resource_scope) > 0 ){
		$res_assignments = str_replace("||", ",", $this->detail->resource_scope);
		$res_assignments = str_replace("|", "", $res_assignments);
		//echo $admins;
		//exit;
		$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE ".
  			"id_resources IN (".$res_assignments.")";
		try{
			$database->setQuery($sql);
			$res_assignment_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_extras_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}	
	$scope = "";

?>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
	pressbutton = document.adminForm.task.value;  
	var ok_to_go = true;
	var min_q = parseInt(document.getElementById("min_quantity").value);
	var max_q = parseInt(document.getElementById("max_quantity").value);
	var def_q = parseInt(document.getElementById("default_quantity").value);
   	if (pressbutton == 'save' || pressbutton == 'save2new'){
		if(document.getElementById("extras_label").value == ""){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_LABEL_REQ');?>");
			ok_to_go = false;
		}
		if(min_q > max_q){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_MIN_MAX');?>");
			ok_to_go = false;
		}
		if(def_q < min_q){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_DEF_MIN_MAX');?>");
			ok_to_go = false;
		}
		if(def_q > max_q){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_DEF_MIN_MAX');?>");
			ok_to_go = false;
		}
	}
	if(ok_to_go){
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
  });
});


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

</script>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
	<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DETAIL_INTRO');?><br /><?php echo JText::_('RS1_ADMIN_EXTRAS_INTRO');?></p>
  <table class="table table-striped" >
    <tr>
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_ID');?>:</td>
      <td><?php echo $this->detail->id_extras ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_LABEL');?>:</td>
      <td><input type="text" size="20" maxsize="40" name="extras_label" id="extras_label" value="<?php echo stripslashes($this->detail->extras_label); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_LABEL_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_TOOLTIP');?>:</td>
      <td valign="top"><input style="width:90%" type="text" size="50" maxsize="255" name="extras_tooltip" id="extras_tooltip" value="<?php echo stripslashes($this->detail->extras_tooltip); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_TOOLTIP_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST');?>:</td>
      <td valign="top"><div style="display: table-cell; vertical-align:middle"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
      <div style="display: table-cell; padding-left:5px;"><input style="width:50px; text-align: center" type="text" size="5" maxsize="10" name="extras_cost" id="extras_cost" value="<?php echo stripslashes($this->detail->extras_cost); ?>" /></div>
      <div style="display: table-cell; padding-left:5px; vertical-align:middle"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_PER');?></div> 
      <div style="display: table-cell; padding-left:5px;"><select name="cost_unit" style="width:auto;">
          <option value="Hour" <?php if($this->detail->cost_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_HOUR');?></option>
          <option value="Flat" <?php if($this->detail->cost_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_BOOKING');?></option>
        </select></div></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION');?>:</td>
      <td valign="top"><div style="display: table-cell;"><input style="width:50px; text-align: center" type="text" size="5" maxsize="10" name="extras_duration" id="extras_duration" value="<?php echo stripslashes($this->detail->extras_duration); ?>" /></div>
      <div style="display: table-cell; padding-left:5px;"><select style="width:auto; min-width:100px" name="extras_duration_unit">
          <option value="Hour" <?php if($this->detail->extras_duration_unit == "Minute"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_MINUTE');?></option>
          <!--<option value="Flat" <?php if($this->detail->extras_duration_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_BOOKING');?></option>-->
        </select></div>
      <div style="display: table-cell; padding-left:5px; vertical-align:top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER');?></div>
      <div style="display: table-cell; padding-left:5px;"><select style="width:auto; min-width:100px;" name="extras_duration_effect">
          <option value="PerUnit" <?php if($this->detail->extras_duration_effect == "PerUnit"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER_UNIT');?></option>
          <option value="PerBooking" <?php if($this->detail->extras_duration_effect == "PerBooking"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER_BOOKING');?></option>
        </select></div>
      </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_HELP_TEXT');?>:</td>
      <td valign="top"><input style="width:90%" type="text" size="50" maxsize="255" name="extras_help" id="extras_help" value="<?php echo stripslashes($this->detail->extras_help); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_HELP_TEXT_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MIN_QUANTITY');?>:</td>
      <td valign="top"><input style="width:30px; text-align: center" type="text" size="2" maxsize="3" name="min_quantity" id="min_quantity" value="<?php echo stripslashes($this->detail->min_quantity); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MIN_QUANTITY_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MAX_QUANTITY');?>:</td>
      <td valign="top"><input style="width:30px; text-align: center" type="text" size="2" maxsize="3" name="max_quantity" id="max_quantity" value="<?php echo stripslashes($this->detail->max_quantity); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MAX_QUANTITY_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DEFAULT_QUANTITY');?>:</td>
      <td valign="top"><input  style="width:30px; text-align: center" type="text" size="2" maxsize="3" name="default_quantity" id="default_quantity" value="<?php echo stripslashes($this->detail->default_quantity); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DEFAULT_QUANTITY_HELP');?>&nbsp;</td>
    </tr>
	<tr>
	  <td colspan="3" ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_INTRO');?></td>
    </tr>
    <tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES');?>:</td>
      <td >
      <table width="95%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="33%"><select style="width:auto" name="resources" id="resources">
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_rows ); $i++) {
			$res_row = $res_rows[$i];
			?>
                <option value="<?php echo $res_row->id_resources; ?>"><?php echo stripslashes($res_row->name); ?></option>
                <?php $k = 1 - $k; 
			} ?>
              </select></td>
            <td valign="top" align="center"><input type="button" name="btnAddResource" id="btnAddResource" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_ADD');?>" onclick="doAddResource()" />
              <br />
              &nbsp;<br />
              <input type="button" name="btnRemoveResource" id="btnRemoveResource" size="10"  onclick="doRemoveResource()" value="<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_REMOVE');?>" /></td>
            <td >
            <div class="sv_select"><select name="selected_resources" id="selected_resources" size="4" >
             <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_assignment_rows ); $i++) {
			$res_assignment_row = $res_assignment_rows[$i];
			?>
                <option value="<?php echo $res_assignment_row->id_resources ?>"><?php echo $res_assignment_row->name; ?></option>
                <?php 
				$scope = $scope."|".$res_assignment_row->id_resources."|";
				$k = 1 - $k; 
			} ?>
              </select></div><?php echo JText::_('RS1_ADMIN_SCRN_EMPTY_ALL');?>            </td>
          </tr>
        </table></td>
    <td valign="top" width="50%"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_STAFF_ONLY_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DETAIL_ORDER');?></td>
      <td colspan="2"><input class="sv_order_style" type="text" size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_PUBLISHED');?></td>
        <td><select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" ><br />
       
        <p>&nbsp;</p></td>
    </tr>  
  </table>
</fieldset>
  <input type="hidden" name="id_extras" value="<?php echo $this->detail->id_extras; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="extras_detail" />
  <input type="hidden" name="resource_scope" id="selected_resources_id" value="<?php echo $scope; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
