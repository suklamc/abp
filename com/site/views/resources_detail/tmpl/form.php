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



	setSessionStuff("resource");

	$jinput = JFactory::getApplication()->input;
	$showform= true;
	$listpage = $jinput->getString('listpage', 'list');

	$resource = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );
	$category_scope = "";
	
	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

		if($this->detail->id_resources == 0){
			// new add default res admin
			$this->detail->resource_admins = "|".$user->id."|";
		}
		
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// get categories 
		$sql = "SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 Order By ordering ";
//		// With the switch to category_scope for multiple cats per resource, we have lost the one-to-on relationship of resource to category.
//		// To get the catgories for resources that the operator is res-admin for, we need two steps now.
//		// First get all the category_scope valuse for resources that the operator is res-admin

//!! problem: (In the front-end only) As soon as operator has no resources in a category, the category disappraes so if they only had one 
// and accidently change it, the category will disappear. 
// Work around for now is show ALL categories. If you want different uncomment the code below.

//		$sql1 = "SELECT category_scope FROM #__sv_apptpro3_resources ".
//			" WHERE resource_admins LIKE '%|".$user->id."|%' AND category_scope != ''";
//		//echo $sql1;
//		$database->setQuery($sql1);
//		$cat_scopes = $database -> loadObjectList();
//		// create a single string with all
//		$master_cat_scope = "";
//		for($i=0; $i < sv_count_($cat_scopes ); $i++) {
//			$tmp = str_replace("||",",",$cat_scopes[$i]->category_scope);
//			$master_cat_scope .= str_replace("|","",$tmp);
//			if($i+1 < sv_count_($cat_scopes )){
//				$master_cat_scope .=",";
//			}
//		}	
//		//echo $master_cat_scope;
//		$sql = 'SELECT DISTINCT * FROM #__sv_apptpro3_categories '.
//			' WHERE id_categories IN('.$master_cat_scope.')' .
//		
//		$andClause.' order by #__sv_apptpro3_categories.ordering';
		try{
			$database->setQuery($sql);
			$cat_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// show groups the operator is in plus 'Public' and 'Registered'
		$sql = "SELECT DISTINCT #__usergroups.id, #__usergroups.title ".
			" FROM #__user_usergroup_map ".
			" RIGHT OUTER JOIN #__usergroups ON #__user_usergroup_map.group_id = #__usergroups.id ".
			" WHERE (#__usergroups.id = 1 OR #__usergroups.id = 2 OR #__user_usergroup_map.user_id = ".$user->id.") ".
			" ORDER BY Title";	
		try{
			$database->setQuery($sql);
			$user_group_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// get access groups
		// convert old settings to new
		if($this->detail->access == "" or $this->detail->access == "everyone" or $this->detail->access == "registered_only" or $this->detail->access == "public_only"){
			$this->detail->access = "|1|"; // Public
		}
		if (strlen($this->detail->access) > 0 ){
			$groups = str_replace("||", ",", $this->detail->access);
			$groups = str_replace("|", "", $groups);
			//echo $groups;
			//exit;
			$sql = "SELECT id as groupid, title as title FROM #__usergroups WHERE ".
				"id IN (".$groups.")";
			try{
				$database->setQuery($sql);
				$access_group_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "resources_detail_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
		}	

		// get category_scope assignments 
		if (strlen($this->detail->category_scope) > 0 ){
			$category_scope_assignments = str_replace("||", ",", $this->detail->category_scope);
			$category_scope_assignments = str_replace("|", "", $category_scope_assignments);
			//echo $category_scope_assignments;
			//exit;
			$sql = "SELECT id_categories, name FROM #__sv_apptpro3_categories WHERE ".
				"id_categories IN (".$category_scope_assignments.")";
			try{
				$database->setQuery($sql);
				$category_scope_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "resources_detail_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
		}	
		
		$display_picker_date = $this->detail->disable_dates_before;	
		if($display_picker_date != "Today" && $display_picker_date != "Tomorrow" && $display_picker_date != "XDays"){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date = date("Y-m-d", strtotime($this->detail->disable_dates_before));
					break;
				case "dd-mm-yy":
					$display_picker_date = date("d-m-Y", strtotime($this->detail->disable_dates_before));
					break;
				case "mm-dd-yy":
					$display_picker_date = date("m-d-Y", strtotime($this->detail->disable_dates_before));
					break;
				default:	
					$display_picker_date = date("Y-m-d", strtotime($this->detail->disable_dates_before));
					break;
			}
		}
		
		$display_picker_date2 = $this->detail->disable_dates_after;		
		if($display_picker_date2 != "Not Set" && $display_picker_date2 != "XDays"){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2 = date("Y-m-d", strtotime($this->detail->disable_dates_after));
					break;
				case "dd-mm-yy":
					$display_picker_date2 = date("d-m-Y", strtotime($this->detail->disable_dates_after));
					break;
				case "mm-dd-yy":
					$display_picker_date2 = date("m-d-Y", strtotime($this->detail->disable_dates_after));
					break;
				default:	
					$display_picker_date2 = date("Y-m-d", strtotime($this->detail->disable_dates_after));
					break;
			}
		}
	}	
	
?>
<?php if($showform){?>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
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
			altField: "#disable_dates_before",
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
			altField: "#disable_dates_after",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	function doOnload(){
		if(document.getElementById('res_id').innerHTML == ''){
			// new resource, setup some defaults..
			document.getElementById('disable_dates_before_tomorrow').checked=true;
			document.getElementById('min_lead_time').value="0";
			setTomorrow();
			document.getElementById('disable_dates_after_notset').checked=true;
			setNotSet();
			document.getElementById('timeslots').selectedIndex=1;
			//document.getElementById('prevent_dupe_bookings').selectedIndex=0;
			document.getElementById('max_seats').value='1';
			document.getElementById('display_order').value='1';
			document.getElementById('resource_admins').value="|"+document.getElementById('user').value+"|";
			document.getElementById('default_calendar_category').value="General";
		}
	}
		
	function doCancel(){
		Joomla.submitform("res_cancel");
	}		

	function doClose(){
		Joomla.submitform("res_close");
	}		
	
	function doSave(){
		if(document.getElementById('name').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_NAME_ERR');?>');
			return(false);
		}
		
		// if no groups selected force to Public.
		if(document.getElementById('resource_groups_id').value == ""){	
			document.getElementById('resource_groups_id').value = "|1|";
		}
		
		Joomla.submitform("save_res_detail");
	}
	
	function setHidden(which_day){
		if(document.getElementById('chk'+which_day).checked==true){
			document.getElementById('allow'+which_day).value = "Yes";
		} else {
			document.getElementById('allow'+which_day).value = "No";
		}
		// ensure at least one day is checked
		if(document.getElementById('chkSunday').checked==false 
			&& document.getElementById('chkMonday').checked==false
			&& document.getElementById('chkTuesday').checked==false
			&& document.getElementById('chkWednesday').checked==false
			&& document.getElementById('chkThursday').checked==false
			&& document.getElementById('chkFriday').checked==false
			&& document.getElementById('chkSaturday').checked==false){
			alert("You cannot un-check ALL days, you must allow bookings on at least one day.");
			document.getElementById('chk'+which_day).checked=true
		}			
		return true;
	}
 
	function set_disable_before_radios(){
		if(document.getElementById('disable_dates_before').value == "Tomorrow"){
			document.getElementById('disable_dates_before_tomorrow').checked = true;
		} else {
			document.getElementById('disable_dates_before_specific').checked = true;
		}
	}
	
	function set_disable_after_radios(){
		if(document.getElementById('disable_dates_after').value == "Not Set"){
			document.getElementById('disable_dates_after_notset').checked = true;
		} else {
			document.getElementById('disable_dates_after_specific').checked = true;
		}
	}

	function setTomorrow(){
		document.getElementById('disable_dates_before').value = "Tomorrow";
		document.getElementById('display_picker_date').value = "Tomorrow";
	}

	function setToday(){
		document.getElementById('disable_dates_before').value = "Today";
		document.getElementById('display_picker_date').value = "Today";
	}

	function setNotSet(){
		document.getElementById('disable_dates_after').value = "Not Set";
		document.getElementById('display_picker_date2').value = "Not Set";
	}

	function setAfterXDays(){
		document.getElementById('disable_dates_after').value = "XDays";
		document.getElementById('display_picker_date2').value = "XDays";
	}

	function setBeforeXDays(){
		document.getElementById('disable_dates_before').value = "XDays";
		document.getElementById('display_picker_date').value = "XDays";
	}

	function doAddUserGroup(){
		var groupid = document.getElementById("user_groups").value;
		var cur_user_groups = document.getElementById("resource_groups_id").value;
		var x = document.getElementById("access_groups");
		for (i=0;i<x.length;i++){
			if(x[i].value == groupid) {
				alert("<?php echo JText::_('RS1_ALREADY_SELECTED');?>");
				return false;
			}			
		}
	
		var opt = document.createElement("option");
        // Add an Option object to Drop Down/List Box
        document.getElementById("access_groups").options.add(opt); 
        opt.text = document.getElementById("user_groups").options[document.getElementById("user_groups").selectedIndex].text;
        opt.value = document.getElementById("user_groups").options[document.getElementById("user_groups").selectedIndex].value;
		cur_user_groups = cur_user_groups + "|" + groupid + "|";
		document.getElementById("resource_groups_id").value = cur_user_groups;
	}

	function doRemoveUserGroup(){
		if(document.getElementById("access_groups").selectedIndex == -1){
			alert("<?php echo JText::_('RS1_NO_GROUP_SELECTED');?>");
			return false;
		}
		var user_to_go = document.getElementById("access_groups").options[document.getElementById("access_groups").selectedIndex].value;
		document.getElementById("access_groups").remove(document.getElementById("access_groups").selectedIndex);
		
		var cur_user_groups = document.getElementById("resource_groups_id").value;

		cur_user_groups = cur_user_groups.replace("|" + user_to_go + "|", "");
		document.getElementById("resource_groups_id").value = cur_user_groups;
	}
	
	function doAddCategoryScope(){
		var catid = document.getElementById("categories").value;
		var selected_categories = document.getElementById("selected_categories_id").value;
		var x = document.getElementById("selected_categories");
		for (i=0;i<x.length;i++){
			if(x[i].value == catid) {
				alert("Already selected");
				return false;
			}			
		}
	
		var opt = document.createElement("option");
        // Add an Option object to Drop Down/List Box
        document.getElementById("selected_categories").options.add(opt); 
        opt.text = document.getElementById("categories").options[document.getElementById("categories").selectedIndex].text;
        opt.value = document.getElementById("categories").options[document.getElementById("categories").selectedIndex].value;
		selected_categories = selected_categories + "|" + catid + "|";
		document.getElementById("selected_categories_id").value = selected_categories;
	}

	function doRemoveCategoryScope(){
		if(document.getElementById("selected_categories").selectedIndex == -1){
			alert("No Category selected for Removal");
			return false;
		}
		var cat_to_go = document.getElementById("selected_categories").options[document.getElementById("selected_categories").selectedIndex].value;
		document.getElementById("selected_categories").remove(document.getElementById("selected_categories").selectedIndex);
		
		var selected_categories = document.getElementById("selected_categories_id").value;

		selected_categories = selected_categories.replace("|" + cat_to_go + "|", "");
		document.getElementById("selected_categories_id").value = selected_categories;
	}
	
	function setDatePicker(which_one){
		if(document.getElementById("date_picker_format")!=null){
			if(which_one == 1){	
				var tempdate;
				tempdate = Date.parse(document.getElementById("disable_dates_before").value);	
					
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
				tempdate = Date.parse(document.getElementById("disable_dates_after").value);	
					
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
<div id="sv_apptpro_fe_resource_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_RESOURCE_DETAIL_TITLE');?></h3></td>
    </tr>
</table>
<table  class="table table-striped" width="100%">
   <tr>
      <td colspan="3"  style="text-align:right" height="40px"  class="fe_header_bar">
      <?php if($this->lock_msg != ""){?>
	      <?php echo $this->lock_msg?>
    	  &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doClose();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?></a>&nbsp;&nbsp;</td>
      <?php } else { ?>
	      <a href="#" onclick="doSave();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?></a>
    	  &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doCancel();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
      <?php } ?>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ID');?></td>
      <td colspan="2"><span id="res_id"><?php echo $this->detail->id_resources; ?></span>&nbsp; </td>
    </tr>
    <?php if(sv_count_($cat_rows) > 0){ ?>
    <tr>
      <td><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES');?></td>

    <td><table width="95%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="33%" valign="top" ><select name="categories" id="categories" style="width:auto" >
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($cat_rows ); $i++) {
			$cat_row = $cat_rows[$i];
			?>
          <option value="<?php echo $cat_row->id_categories; ?>"><?php echo JText::_($cat_row->name); ?></option>
          <?php $k = 1 - $k; 
			} ?>
        </select></td>
        <td width="34%" valign="top" align="center"><input type="button" name="btnAddCategoryScope" id="btnAddCategoryScope" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_GROUP_ADD');?>" onclick="doAddCategoryScope()" />
          <br />
          &nbsp;<br />
          <input type="button" name="btnRemoveCategoryScope" id="btnRemoveCategoryScope" size="10"  onclick="doRemoveCategoryScope()" value="<?php echo JText::_('RS1_ADMIN_SCRN_GROUP_REMOVE');?>" /></td>
        <td width="33%"><div class="sv_select"><select name="selected_categories" id="selected_categories" size="4" >
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($category_scope_rows ); $i++) {
			$category_scope_row = $category_scope_rows[$i];
			?>
          <option value="<?php echo $category_scope_row->id_categories; ?>"><?php echo JText::_($category_scope_row->name); ?></option>
          <?php 
				$category_scope = $category_scope."|".$category_scope_row->id_categories."|";
				$k = 1 - $k; 
			} ?>
        </select></div></td>
      </tr>
    </table></td>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_CATEGORY_HELP');?></td>
    </tr>
    <?php } else { ?>
      <input type="hidden" name="category_id" value="<?php echo $this->detail->category_id; ?>" />
    <?php } ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_NAME');?></td>
      <td><input type="text" size="40" maxsize="50" name="name" id="name" class="sv_apptpro3_request_text" value="<?php echo JText::_(stripslashes($this->detail->name)); ?>" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DESC');?> </td>
      <td><input type="text" size="40" maxsize="80" name="description" class="sv_apptpro3_request_text" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DESC_HELP');?></td>
    </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_CONFIG_AUTO_ACCEPT');?>: </td>
    <td><select name="auto_accept">
        <option value="Yes" <?php if($this->detail->auto_accept == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
        <option value="No" <?php if($this->detail->auto_accept == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        <option value="Global" <?php if($this->detail->auto_accept == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_GLOBAL');?></option>
      </select>
      &nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_AUTO_ACCEPT_RES_HELP');?></td>
  </tr> 
<!--    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS');?></td>
      <td><select name="access" >
          <option value="everyone" <?php if($this->detail->access == "" or $this->detail->access == "everyone"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_EVERYONE');?></option>
          <option value="registered_only" <?php if($this->detail->access == "registered_only"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_REGISTERED');?></option>
          <option value="public_only" <?php if($this->detail->access == "public_only"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_PUBLIC');?></option>
	      </select>&nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_HELP');?>&nbsp;</td>
    </tr>-->
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS');?></td>
    <td><table width="95%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="33%" valign="top"><select name="user_groups" id="user_groups" style="width:auto">
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_group_rows ); $i++) {
			$user_group_row = $user_group_rows[$i];
			?>
          <option value="<?php echo $user_group_row->id; ?>"><?php echo $user_group_row->title; ?></option>
          <?php $k = 1 - $k; 
			} ?>
        </select></td>
        <td width="34%" valign="top" align="center"><input type="button" name="btnAddUserGroup" id="btnAddUserGroup" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_GROUP_ADD');?>" onclick="doAddUserGroup()" />
          <br />
          &nbsp;<br />
          <input type="button" name="btnRemoveUserGroup" id="btnRemoveUserGroup" size="10"  onclick="doRemoveUserGroup()" value="<?php echo JText::_('RS1_ADMIN_SCRN_GROUP_REMOVE');?>" /></td>
        <td width="33%"><div class="sv_select"><select name="access_groups" id="access_groups" size="4" >
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($access_group_rows ); $i++) {
			$access_group_row = $access_group_rows[$i];
			?>
          <option value="<?php echo $access_group_row->groupid; ?>"><?php echo $access_group_row->title; ?></option>
          <?php 
				$access_groups_groups = $access_groups_groups."|".$access_group_row->groupid."|";
				$k = 1 - $k; 
			} ?>
        </select></div></td>
      </tr>
    </table></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_HELP');?></td>
  </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_COST');?></td>
      <td><input type="text" size="20" maxsize="20" name="cost" class="sv_apptpro3_request_text" value="<?php echo $this->detail->cost; ?>" />
&nbsp;&nbsp;         </td>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_COST_HELP');?></td>
    </tr>
    <tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE');?></td>
      <td ><input type="text" size="8" maxsize="10" name="rate" value="<?php echo $this->detail->rate; ?>" />
        &nbsp;&nbsp;&nbsp;<br/><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_UNIT');?> <select name="rate_unit">
          <option value="Hour" <?php if($this->detail->rate_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_HOUR');?></option>
          <option value="Flat" <?php if($this->detail->rate_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_BOOKING');?></option>
        </select>        </td>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_HELP');?></td>
    </tr>
	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="resource_eb_discount" value="<?php echo $this->detail->resource_eb_discount; ?>" />
        <br/><select style="width:auto;" name="resource_eb_discount_unit">
          <option value="Flat" <?php if($this->detail->resource_eb_discount_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FLAT');?></option>
          <option value="Percent" <?php if($this->detail->resource_eb_discount_unit == "Percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_PERCENT');?></option>
      </select>
	  <br/>
      <input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="resource_eb_discount_lead" value="<?php echo $this->detail->resource_eb_discount_lead; ?>" />
      &nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_DAYS');?>
      </td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_HELP');?></td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_EMAILTO');?></td>
      <td valign="top"><input type="text" size="40" maxsize="200" class="sv_apptpro3_request_text" name="resource_email" value="<?php echo $this->detail->resource_email; ?>" />
        <br /></td>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_EMAILTO_HELP');?></td>
    </tr>
    <!-- if you want your users to be able to collect payments va their PayPal accounts, un-comment the next <tr> -->
<!--  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_PAYPAL');?></td>
    <td><input style="width:90%;" type="text" size="40" maxsize="50" name="paypal_account" id="paypal_account" value="<?php echo $this->detail->paypal_account; ?>" /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_PAYPAL_HELP');?>&nbsp;</td>
  </tr>
-->    
<!--    <tr  >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SEND_ICS');?> </td>
      <td><select name="send_ics" class="sv_apptpro3_request_text" >
          <option value="Yes" <?php if($this->detail->send_ics == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->send_ics == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SEND_ICS_HELP');?></td>
    </tr>  -->  
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_SMS_PHONE');?></td>
      <td valign="top"><input type="text" size="40" maxsize="200" name="sms_phone" class="sv_apptpro3_request_text" value="<?php echo $this->detail->sms_phone; ?>"/></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SMS_PHONE_HELP');?></td>
    </tr>
<!--    <tr  >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DUPES');?> </td>
      <td><select name="prevent_dupe_bookings" id="prevent_dupe_bookings" class="sv_apptpro3_request_text">
          <option value="Global" <?php if($this->detail->prevent_dupe_bookings == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_GLOBAL');?></option>
          <option value="Yes" <?php if($this->detail->prevent_dupe_bookings == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->prevent_dupe_bookings == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select>
      &nbsp;&nbsp; <?php echo JText::_('RS1_ADMIN_SCRN_RES_MAX_DUPES');?> 
      <input type="text" name="max_dupes" id="max_dupes" size="2" maxlength="4" align="right" class="sv_apptpro3_request_text" value="<?php echo $this->detail->max_dupes; ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DUPES_HELP');?>      </td>
    </tr>  -->  
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_MAX_SEATS');?></td>
      <td valign="top"><input type="text" size="2" maxsize="3" name="max_seats" id="max_seats" value="<?php echo $this->detail->max_seats; ?>"/></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_MAX_SEATS_HELP');?></td>
    </tr>
<!--    <tr >
      <td>Default Calendar Category:</td>
      <td><input type="text" size="25" maxsize="2" name="default_calendar_category" value="<?php echo $this->detail->default_calendar_category; ?>" />
        &nbsp;&nbsp;</td>
      <td>Enter a default calendar category for this resource. This is only applicable if you are using 'auto-accept' and a 3rd party calendar (JCalPro or JEvents)</td>  
    </tr>
    <tr>
      <td>Resource Administrators:</td>
      <td>
      <table width="95%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="33%"><select name="users" id="users">
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_rows ); $i++) {
			$user_row = $user_rows[$i];
			?>
                <option value="<?php echo $user_row->id; ?>"><?php echo $user_row->name; ?></option>
                <?php $k = 1 - $k; 
			} ?>
              </select></td>
            <td width="34%" valign="top" align="center"><input type="button" name="btnAddUser" id="btnAddUser" size="10" value="    Add >>   " onclick="doAddUser()" />
              <br />
              &nbsp;<br />
              <input type="button" name="btnRemoveUser" id="btnRemoveUser" size="10"  onclick="doRemoveUser()" value="<< Remove" /></td>
            <td width="33%">
            <select name="admins" id="admins" size="4" >
             <?php
			$k = 0;
			for($i=0; $i < sv_count_($admins_rows ); $i++) {
			$admins_row = $admins_rows[$i];
			?>
                <option value="<?php echo $admins_row->userid; ?>"><?php echo $admins_row->username; ?></option>
                <?php 
				$admin_users = $admin_users."|".$admins_row->userid."|";
				$k = 1 - $k; 
			} ?>
              </select>            </td>
          </tr>
        </table></td>
    <td valign="top">Select one or more users to be administrators for this resource.  When a resource administrator accesses the front-end control they see only  requests for their resources. A user can administer one or many resources and a resource can have one or many administrators.</td>
    </tr>-->
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_TIMESLOTS');?></td>
	  <td ><select name="timeslots" id="timeslots" class="sv_apptpro3_request_text">
        <option value="Global" <?php if($this->detail->timeslots == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_USEGLOBAL');?></option>
        <option value="Specific" <?php if($this->detail->timeslots == "Specific"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC');?></option>
      </select></td>
	  <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_TIMESLOTS_HELP');?></td>
    </tr>
	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_BOOKING_DAYS');?></td>
      <td  valign="bottom"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr align="center">
            <td><?php echo JText::_('RS1_ADMIN_SCRN_SUN');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_MON');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_TUE');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_WED');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_THU');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_FRI');?></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_SAT');?></td>
            <td>&nbsp;</td>
          </tr>
          <tr align="center">
            <td><input type="checkbox" name="chkSunday" id="chkSunday" onclick="setHidden('Sunday');"  <?php if($this->detail->allowSunday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkMonday" id="chkMonday" onclick="setHidden('Monday');" <?php if($this->detail->allowMonday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkTuesday" id="chkTuesday" onclick="setHidden('Tuesday');" <?php if($this->detail->allowTuesday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkWednesday" id="chkWednesday" onclick="setHidden('Wednesday');" <?php if($this->detail->allowWednesday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkThursday" id="chkThursday" onclick="setHidden('Thursday');" <?php if($this->detail->allowThursday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkFriday" id="chkFriday" onclick="setHidden('Friday');" <?php if($this->detail->allowFriday == "Yes"){echo "checked";} ?>/></td>
            <td><input type="checkbox" name="chkSaturday" id="chkSaturday" onclick="setHidden('Saturday');" <?php if($this->detail->allowSaturday == "Yes"){echo "checked";} ?>/></td>
            <td></td>
          </tr>
        </table>
        <input type="hidden" name="allowSunday" id="allowSunday" value="<?php echo $this->detail->allowSunday?>" />
        <input type="hidden" name="allowMonday" id="allowMonday" value="<?php echo $this->detail->allowMonday?>" />
        <input type="hidden" name="allowTuesday" id="allowTuesday" value="<?php echo $this->detail->allowTuesday?>" />
        <input type="hidden" name="allowWednesday" id="allowWednesday" value="<?php echo $this->detail->allowWednesday?>" />
        <input type="hidden" name="allowThursday" id="allowThursday" value="<?php echo $this->detail->allowThursday?>" />
        <input type="hidden" name="allowFriday" id="allowFriday" value="<?php echo $this->detail->allowFriday?>" />
        <input type="hidden" name="allowSaturday" id="allowSaturday" value="<?php echo $this->detail->allowSaturday?>" />      </td>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_BOOKING_DAYS_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_HIDE');?></td>
        <td >
            <select name="non_work_day_hide" class="sv_apptpro3_request_text">
            <option value="No" <?php if($this->detail->non_work_day_hide == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->non_work_day_hide == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>
        </td>
         <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_HIDE_HELP');?></td>
    </tr>  
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_MESSAGE');?></td>
      <td valign="top"><input type="text" size="40" maxsize="255" name="non_work_day_message" value="<?php echo $this->detail->non_work_day_message; ?>" />
        <br /></td>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_MESSAGE_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD');?></td>
	  <td ><input type="text" size="2" maxlength="2" name="min_lead_time" id="min_lead_time" class="sv_apptpro3_request_text" 
      value="<?php echo $this->detail->min_lead_time; ?>"/>&nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD_UNITS');?>         
      </td>
	  <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD_HELP');?>&nbsp;</td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_BEFORE');?></td>
	  <td ><input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_today" value="disable_dates_before_today"
      	<?php echo ($this->detail->disable_dates_before == "Today" ? "checked='checked'" : "");?> onclick="setToday();" /> 
	    <?php echo JText::_('RS1_ADMIN_SCRN_RES_TODAY');?>&nbsp;&nbsp; <br /> 
        <input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_tomorrow" value="disable_dates_before_tomorrow"
      	<?php echo ($this->detail->disable_dates_before == "Tomorrow" ? "checked='checked'" : "");?> onclick="setTomorrow();" /> 
	    <?php echo JText::_('RS1_ADMIN_SCRN_RES_TOMORROW');?>&nbsp;&nbsp;        <br />
        <input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_xdays" value="disable_dates_before_xdays"
      	<?php echo ($this->detail->disable_dates_before == "XDays" ? "checked='checked'" : "");?> onclick="setBeforeXDays();" />
          <input type="text" size="2" name="disable_dates_before_days" id="disable_dates_before_days" value="<?php echo $this->detail->disable_dates_before_days?>" 
          style="text-align:center; width:30px;"/>
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_DAYS_FROM_NOW');?>&nbsp;<br/>
        <input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_specific" value="disable_dates_before_specific" 
        <?php echo (($this->detail->disable_dates_before != "Tomorrow" AND $this->detail->disable_dates_before != "Today") ? "checked='checked'" : "");?> />
        <?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC_DATE');?>
        
           <input readonly="readonly" name="disable_dates_before" id="disable_dates_before" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->disable_dates_before; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date ?>" onchange="set_disable_before_radios(); setDatePicker(1);  return false;">          

      </td>
	  <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_BEFORE_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_AFTER');?></td>
	  <td ><input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_notset" value="disable_dates_after_notset" 
      <?php echo ($this->detail->disable_dates_after == "Not Set" ? "checked='checked'" : "");?> onclick="setNotSet();"/> 
	    <?php echo JText::_('RS1_ADMIN_SCRN_RES_NOT_SET');?>&nbsp;&nbsp;        <br />
		<input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_xdays" value="disable_dates_after_xdays"
	    <?php echo ($this->detail->disable_dates_after == "XDays" ? "checked='checked'" : "");?> onclick="setAfterXDays();" />
        <input type="text" size="2" name="disable_dates_after_days" id="disable_dates_after_days" value="<?php echo $this->detail->disable_dates_after_days?>" 
        style="text-align:center; width:30px;"/>
        <?php echo JText::_('RS1_ADMIN_SCRN_RES_DAYS_FROM_NOW');?>&nbsp;<br />
        <input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_specific" value="disable_dates_after_specific"
        <?php echo ($this->detail->disable_dates_after != "Not Set" ? "checked='checked'" : "");?> />
        <?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC_DATE');?> 
        
          <input readonly="readonly" name="disable_dates_after" id="disable_dates_after" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->disable_dates_after; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date2 ?>" onchange="set_disable_after_radios(); setDatePicker(2);  return false;">          
 
		</td>
	  <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_AFTER_HELP');?></td>
    </tr>
	<tr>
	  <td colspan="3"><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_DATES');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_GAP');?></td>
      <td><input type="text" size="5" maxsize="2" name="gap" style="width:30px; text-align: center" value="<?php echo $this->detail->gap; ?>" />
        &nbsp;&nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_GAP_HELP');?></td>
    </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_RES_IMAGE');?> </td>
    <td><input type="text" style="width:90%;" name="ddslick_image_path" value="<?php echo $this->detail->ddslick_image_path; ?>" />
	<?php echo ($this->detail->ddslick_image_path != ""?"<br/><img src=\"".getResourceImageURL($this->detail->ddslick_image_path)."\" style='max-height: 64px;'/>":"")?>		
    <div><?php echo JText::_('RS1_ADMIN_RES_IMAGE_SHOW_IN_GRID');?>
    <select name="show_image_in_grid" style="width:auto">
      <option value="No" <?php if($this->detail->show_image_in_grid == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
      <option value="Yes" <?php if($this->detail->show_image_in_grid == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    </select></div>
    </td>
    <td><?php echo JText::_('RS1_ADMIN_RES_IMAGE_HELP');?></td>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_RES_IMAGE_TEXT');?></td>
    <td><input type="text" style="width:90%;" name="ddslick_image_text" value="<?php echo $this->detail->ddslick_image_text; ?>" />
      &nbsp;&nbsp;</td>
    <td><?php echo JText::_('RS1_ADMIN_RES_IMAGE_TEXT_HELP');?></td>
  </tr>
  </tr>
	<tr >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_DISPLAY_ORDER');?></td>
      <td><input type="text" size="5" maxsize="2" name="ordering" id="ordering" class="sv_apptpro3_request_text" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_DISPLAY_ORDER_HELP');?></td>      
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISHED');?> </td>
        <td colspan="2">
            <select name="published" class="sv_apptpro3_request_text">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>  
  </table>
  <input type="hidden" name="resource_admins" id="resource_admins" value="<?php echo $this->detail->resource_admins; ?>" />
  <input type="hidden" name="default_calendar_category" value="<?php echo $this->detail->default_calendar_category; ?>" />


  <input type="hidden" name="id_resources" value="<?php echo $this->detail->id_resources; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="1" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <input type="hidden" name="access" id="resource_groups_id" value="<?php echo $access_groups_groups; ?>" />
  <input type="hidden" name="category_scope" id="selected_categories_id" value="<?php echo $category_scope; ?>" />
  <input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    

  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>    
<?php echo JHTML::_( 'form.token' ); ?>
  
</form>
<script>
	doOnload();
</script>
<?php } ?>