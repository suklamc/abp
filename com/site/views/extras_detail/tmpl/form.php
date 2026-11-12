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

		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "extras_detail_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

	}	

	// get resources 
	$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%' AND published = 1";
	try{
		$database->setQuery($sql);
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "extras_detail_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get resource assignments 
	if (strlen($this->detail->resource_scope) > 0 ){
		$res_assignments = str_replace("||", ",", $this->detail->resource_scope);
		$res_assignments = str_replace("|", "", $res_assignments);
		//echo $admins;
		//exit;
		$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE ".
  			"id_resources IN (".$database->escape($res_assignments).")";
		try{	
			$database->setQuery($sql);
			$res_assignment_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "extras_detail_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}	

?>
<?php if($showform){?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="javascript">

	function doCancel(){
		Joomla.submitform("extra_cancel");
	}		

	function doClose(){
		Joomla.submitform("extra_close");
	}		
	
	function doSave(){
		if(document.getElementById("extras_label").value == ""){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_LABEL_REQ');?>");
			return(false);
		}
		if(document.getElementById("selected_resources_id").value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RES_REQ');?>');
			return(false);
		}

		Joomla.submitform("save_extras_detail");
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

</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<div id="sv_apptpro_fe_extras_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<fieldset class="sv_adminForm">
<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_EXTRAS_TITLE');?></h3></td>
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
        <p><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DETAIL_INTRO');?><br /><?php echo JText::_('RS1_ADMIN_EXTRAS_INTRO');?><br />
        </p>      </td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_ID');?>:</td>
      <td><?php echo $this->detail->id_extras ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_LABEL');?>:</td>
      <td><input type="text" size="20" maxsize="40" name="extras_label" id="extras_label" value="<?php echo stripslashes($this->detail->extras_label); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_LABEL_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_TOOLTIP');?>:</td>
      <td valign="top"><input type="text" size="50" maxsize="255" name="extras_tooltip" id="extras_tooltip" value="<?php echo stripslashes($this->detail->extras_tooltip); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_TOOLTIP_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST');?>:</td>
      <td valign="top"><div style="display: table-cell; padding-left:0px;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
      <div style="display: table-cell; padding-left:5px;"><input type="text"size="5" maxsize="10" name="extras_cost" id="extras_cost" value="<?php echo stripslashes($this->detail->extras_cost); ?>" /></div>
      <div><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_PER');?></div>
      <div style="display: table-cell; padding-left:5px;"><select name="cost_unit">
          <option value="Hour" <?php if($this->detail->cost_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_HOUR');?></option>
          <option value="Flat" <?php if($this->detail->cost_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_BOOKING');?></option>
        </select></div></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION');?>:</td>
      <td valign="top"><div style="display: table-cell; padding-left:5px;"><input type="text"  style="width:30px; text-align: center" size="5" maxsize="10" name="extras_duration" id="extras_duration" value="<?php echo stripslashes($this->detail->extras_duration); ?>" /></div>
      <div style="display: table-cell; padding-left:5px;"><select name="extras_duration_unit" style="width:auto"; >
          <option value="Hour" <?php if($this->detail->extras_duration_unit == "Minute"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_MINUTE');?></option>
          <!--<option value="Flat" <?php if($this->detail->extras_duration_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_COST_BOOKING');?></option>-->
        </select></div>
      <div><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER');?></div>
      <div style="display: table-cell; padding-left:5px;"><select name="extras_duration_effect">
          <option value="PerUnit" <?php if($this->detail->extras_duration_effect == "PerUnit"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER_UNIT');?></option>
          <option value="PerBooking" <?php if($this->detail->extras_duration_effect == "PerBooking"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_PER_BOOKING');?></option>
        </select></div>
      </td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DURATION_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_HELP_TEXT');?>:</td>
      <td valign="top"><input type="text" size="50" maxsize="255" name="extras_help" id="extras_help" value="<?php echo stripslashes($this->detail->extras_help); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_HELP_TEXT_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MAX_QUANTITY');?>:</td>
      <td valign="top"><input type="text" size="2" maxsize="3" name="max_quantity" id="max_quantity" value="<?php echo stripslashes($this->detail->max_quantity); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_MAX_QUANTITY_HELP');?>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DEFAULT_QUANTITY');?>:</td>
      <td valign="top"><input type="text" size="2" maxsize="3" name="default_quantity" id="default_quantity" value="<?php echo stripslashes($this->detail->default_quantity); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DEFAULT_QUANTITY_HELP');?>&nbsp;</td>
    </tr>
	<tr class="admin_detail_row0">
	  <td colspan="3" ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_INTRO');?></td>
    </tr>
    <tr class="admin_detail_row0">
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES');?>:</td>
      <td >
      <table width="95%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="33%"><select name="resources" id="resources" style="width:auto">
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_rows ); $i++) {
			$res_row = $res_rows[$i];
			?>
                <option value="<?php echo $res_row->id_resources; ?>"><?php echo stripslashes($res_row->name); ?></option>
                <?php $k = 1 - $k; 
			} ?>
              </select></td>
            <td width="34%" valign="top" align="center"><input type="button" name="btnAddResource" id="btnAddResource" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_ADD');?>" onclick="doAddResource()" />
              <br />
              &nbsp;<br />
              <input type="button" name="btnRemoveResource" id="btnRemoveResource" size="10"  onclick="doRemoveResource()" value="<?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_REMOVE');?>" /></td>
            <td width="33%">
            <div class="sv_select"><select name="selected_resources" id="selected_resources" size="4" >
             <?php
			$k = 0;
			for($i=0; $i < sv_count_($res_assignment_rows ); $i++) {
			$res_assignment_row = $res_assignment_rows[$i];
			?>
                <option value="<?php echo $res_assignment_row->id_resources; ?>"><?php echo $res_assignment_row->name; ?></option>
                <?php 
				$scope = $scope."|".$res_assignment_row->id_resources."|";
				$k = 1 - $k; 
			} ?>
              </select></div><br /><?php echo JText::_('RS1_ADMIN_SCRN_EMPTY_ALL');?>            </td>
          </tr>
        </table></td>
    <td valign="top" width="50%"><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_RESOURCES_HELP');?></td>
    </tr>
    <tr class="admin_detail_row1">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_STAFF_ONLY_HELP');?></td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_DETAIL_ORDER');?></td>
      <td colspan="2"><input type="text" size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
    </tr>
    <tr class="admin_detail_row1">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_EXTRAS_PUBLISHED');?></td>
        <td><select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
        <td>&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
      <td colspan="3" ><br />
       
        <p>&nbsp;</p></td>
    </tr>  
  </table>
</fieldset>
  <input type="hidden" name="id_extras" value="<?php echo $this->detail->id_extras; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
  <input type="hidden" name="resource_scope" id="selected_resources_id" value="<?php echo $scope; ?>" />

  <br /><p><?php echo JText::_('RS1_ADMIN_SCRN_EXTRA_NOTE2');?></p>
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
