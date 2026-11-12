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

	

//	setSessionStuff("service");
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
			logIt($e->getMessage(), "services_detail_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// get resource_scope assignments 
		$resource_scope = "";
		if (strlen($this->detail->resource_scope) > 0 ){
			$resource_scope_assignments = str_replace("||", ",", $this->detail->resource_scope);
			$resource_scope_assignments = str_replace("|", "", $resource_scope_assignments);
			//echo $resource_scope_assignments;
			//exit;
			$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE ".
				"id_resources IN (".$resource_scope_assignments.") ORDER BY name";
			try{
				$database->setQuery($sql);
				$resource_scope_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		}	
	
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "services_detail_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

	}	
	
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
	var cal = new CalendarPopup(<?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);

		
	function doCancel(){
		Joomla.submitform("srv_cancel");
	}		

	function doClose(){
		Joomla.submitform("srv_close");
	}		
	
	function doSave(){
		if(document.getElementById('selected_resources_id').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE_ERR');?>');
			return(false);
		}
		if(document.getElementById('name').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_NAME_ERR');?>');
			return(false);
		}
		Joomla.submitform("save_services_detail");
	}
	
	function doAddResourceScope(){
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

	function doRemoveResourceScope(){
		if(document.getElementById("selected_resources").selectedIndex == -1){
			alert("No resource selected for Removal");
			return false;
		}
		var res_to_go = document.getElementById("selected_resources").options[document.getElementById("selected_resources").selectedIndex].value;
		document.getElementById("selected_resources").remove(document.getElementById("selected_resources").selectedIndex);
		
		var selected_resources = document.getElementById("selected_resources_id").value;

		selected_resources = selected_resources.replace("|" + res_to_go + "|", "");
		document.getElementById("selected_resources_id").value = selected_resources;
	}
	
	</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_service_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_RESOURCE_SERVICE_TITLE');?></h3></td>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0">
   <tr>
      <td colspan="3" style="text-align:right"  height="40px"  class="fe_header_bar">
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
      <td colspan="2">
        <?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_INTRO');?><br /></td>
    </tr>
    <tr class="admin_detail_row0">
      <td width="25%"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_services ?></td>
    </tr>
   <tr class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_NAME');?></td>
      <td colspan="2"><input type="text" size="50" maxsize="250" name="name" id="name" class="sv_apptpro_request_text" value="<?php echo $this->detail->name; ?>" /></td>
    </tr>
    <tr class="admin_detail_row0">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_DESC');?> </td>
      <td><input type="text" size="50" maxsize="250" name="description" class="sv_apptpro_request_text" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
    </tr>
      <tr class="admin_detail_row1">
        <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RESOURCE');?></td>
        <td><table class="table table-striped" >
          <tr>
            <td width="33%"><select style="width:auto" name="resources" id="resources">
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                ?>
              <option value="<?php echo $res_row->id_resources; ?>"><?php echo $res_row->name;?></option>
              <?php $k = 1 - $k; 
                } ?>
            </select></td>
            <td width="34%" valign="top" align="center"><input type="button" name="btnAddResourceScope" id="btnAddResourceScope" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_ADD');?>" onclick="doAddResourceScope()" />
              <br />
              &nbsp;<br />
              <input type="button" name="btnRemoveResourceScope" id="btnRemoveresourceScope" size="10"  onclick="doRemoveResourceScope()" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_REMOVE');?>" /></td>
            <td width="33%"><div class="sv_select"><select name="selected_resources" id="selected_resources" multiple="multiple" size="4" >
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($resource_scope_rows ); $i++) {
                $resource_scope_row = $resource_scope_rows[$i];
                ?>
              <option value="<?php echo $resource_scope_row->id_resources; ?>"><?php echo $resource_scope_row->name; ?></option>
              <?php 
                    $resource_scope = $resource_scope."|".$resource_scope_row->id_resources."|";
                    $k = 1 - $k; 
                } ?>
            </select></div></td>
          </tr>
        </table></td>
        <td><?php echo JText::_('RS1_ADMIN_SCRN_SRV_RESOURCE_HELP');?>&nbsp;</td>
      </tr>
   <tr class="admin_detail_row0">
      <td valign="top" ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE');?></td>
      <td valign="top"><input type="text" size="8" maxsize="10" name="service_rate" value="<?php echo $this->detail->service_rate; ?>" />
        &nbsp;&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_UNIT');?><select name="service_rate_unit">
          <option value="Hour" <?php if($this->detail->service_rate_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_HOUR');?></option>
          <option value="Flat" <?php if($this->detail->service_rate_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_BOOKING');?></option>
        </select>        </td>
      <td width="60%"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_HELP');?></td>
    </tr>
	<tr class="admin_detail_row1">
      <td valign="top" ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION');?></td>
      <td valign="top"><input type="text" size="8" maxsize="10" name="service_duration" value="<?php echo $this->detail->service_duration; ?>" />
        &nbsp;&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_UNIT');?> <select name="service_duration_unit">
          <option value="Minute" <?php if($this->detail->service_duration_unit == "Minute"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_MINUTE');?></option>
          <option value="Hour" <?php if($this->detail->service_duration_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_HOUR');?></option>
      </select>        </td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_HELP');?></td>
    </tr>
	<tr class="admin_detail_row0">
      <td valign="top" ><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="service_eb_discount" value="<?php echo $this->detail->service_eb_discount; ?>" />
        <br/><select style="width:auto;" name="service_eb_discount_unit">
          <option value="Flat" <?php if($this->detail->service_eb_discount_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FLAT');?></option>
          <option value="Percent" <?php if($this->detail->service_eb_discount_unit == "Percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_PERCENT');?></option>
      </select>
	  <br/>
      <input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="service_eb_discount_lead" value="<?php echo $this->detail->service_eb_discount_lead; ?>" />
      &nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_DAYS');?>
      </td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_HELP');?></td>
    </tr>
	<tr  class="admin_detail_row1">
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_ORDER');?></td>
      <td><input type="text" size="5" maxsize="2" name="ordering" class="sv_apptpro_request_text" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
    </tr>
    <tr class="admin_detail_row0">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_STAFF_ONLY_HELP');?></td>
    </tr>
    <tr class="admin_detail_row1">
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_PUBLISHED');?></td>
        <td>
            <select name="published" class="sv_apptpro_request_text">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>
    <tr class="admin_detail_row0">
      <td colspan="2" >
      <p>&nbsp;</p></td>
    </tr>  
  </table>
  <input type="hidden" name="id_services" value="<?php echo $this->detail->id_services; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
  <input type="hidden" name="resource_scope" id="selected_resources_id" value="<?php echo $resource_scope; ?>" />

  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>    
<?php echo JHTML::_( 'form.token' ); ?>
  
</form>
<?php } ?>