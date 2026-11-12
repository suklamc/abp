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

JHtml::_('jquery.framework');

	// get data for dropdowns
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__users ORDER BY name" );	
		$user_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	try{
		$database->setQuery("SELECT * FROM #__usergroups ORDER BY title" );
		$user_group_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 ORDER BY name" );
		$cat_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}		
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_mail WHERE published = 1 ORDER BY id_mail" );
		$mail_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}
	
	// get resource admins
	if (strlen($this->detail->resource_admins) > 0 ){
		$admins = str_replace("||", ",", $this->detail->resource_admins);
		$admins = str_replace("|", "", $admins);
		//echo $admins;
		//exit;
		$sql = "SELECT id as userid, name as username FROM #__users WHERE ".
  			"id IN (".$admins.")";
		try{
			$database->setQuery($sql);
			$admins_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
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
			logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}	

	// get category_scope assignments 
	$category_scope = "";
	if (strlen($this->detail->category_scope) > 0 ){
		$category_scope_assignments = str_replace("||", ",", $this->detail->category_scope);
		$category_scope_assignments = str_replace("|", "", $category_scope_assignments);
		//echo $category_scope_assignments;
		//exit;
		$sql = "SELECT id_categories, name FROM #__sv_apptpro3_categories WHERE ".
  			"id_categories IN (".$category_scope_assignments.") ORDER BY name";
		try{
			$database->setQuery($sql);
			$category_scope_rows = $database -> loadObjectList();
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
		$apptpro3_config = NULL;
		$apptpro3_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	$div_cal = "";
	if($apptpro3_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}
	
	$admin_users = "";

	// get MailChimp lists
	$email_marketing_info = NULL;
	$sql = 'SELECT * FROM #__sv_apptpro3_email_marketing WHERE id_email_marketing = 1';
	try{
		$database->setQuery($sql);
		$email_marketing_info = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$aryLists = null;
	if($email_marketing_info->mailchimp_api_key != ""){
		include_once( JPATH_SITE."/components/com_rsappt_pro3/inc/MailChimp.php" );
		$MailChimp = new \Drewm\MailChimp($email_marketing_info->mailchimp_api_key);
		$params = array(
      		'sort_field' => 'name',
            'sort_dir' => 'asc');				
		$aryLists = $MailChimp->call('lists/list', $params);
		//print_r($aryLists);		
	} else {
		$aryLists = array("total"=>1, "data"=>array(array("id"=>"-1","name"=>"None Loaded")));
	}


	// get AcyMailing lists	
	$acyLists = null;
	if(file_exists(JPATH_ADMINISTRATOR . '/components/com_acymailing/acymailing.php') && JComponentHelper::isEnabled('com_acymailing', true)){
		if(include_once(rtrim(JPATH_ADMINISTRATOR,DS).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
			$listClass = acymailing_get('class.list');
			$acyLists = $listClass->getLists();	
			//print_r($acyLists);
		 }
	}
	
?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>

<script language="JavaScript">
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro3_config->popup_week_start_day ?>);
</script>
<script language="javascript">
	function doAddUser(){
		var userid = document.getElementById("users").value;
		var admin_users = document.getElementById("resource_admins_id").value;
		var x = document.getElementById("admins");
		for (i=0;i<x.length;i++){
			if(x[i].value == userid) {
				alert("<?php echo JText::_('RS1_ALREADY_SELECTED');?>");
				return false;
			}			
		}
	
		var opt = document.createElement("option");
        // Add an Option object to Drop Down/List Box
        document.getElementById("admins").options.add(opt); 
        opt.text = document.getElementById("users").options[document.getElementById("users").selectedIndex].text;
        opt.value = document.getElementById("users").options[document.getElementById("users").selectedIndex].value;
		admin_users = admin_users + "|" + userid + "|";
		document.getElementById("resource_admins_id").value = admin_users;
	}

	function doRemoveUser(){
		if(document.getElementById("admins").selectedIndex == -1){
			alert("<?php echo JText::_('RS1_NO_ADMIN_SELECTED');?>");
			return false;
		}
		var user_to_go = document.getElementById("admins").options[document.getElementById("admins").selectedIndex].value;
		document.getElementById("admins").remove(document.getElementById("admins").selectedIndex);
		
		var admin_users = document.getElementById("resource_admins_id").value;

		admin_users = admin_users.replace("|" + user_to_go + "|", "");
		document.getElementById("resource_admins_id").value = admin_users;
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
	}

	function setToday(){
		document.getElementById('disable_dates_before').value = "Today";
	}

	function setNotSet(){
		document.getElementById('disable_dates_after').value = "Not Set";
	}
	
	function setAfterXDays(){
		document.getElementById('disable_dates_after').value = "XDays";
	}

	function setBeforeXDays(){
		document.getElementById('disable_dates_before').value = "XDays";
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
	

</script>
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>

<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		if (pressbutton == 'save'|| pressbutton == 'save2new'){
			if(document.getElementById("name").value == ""){
				alert("Name is required");
				return false;
			} else {
				Joomla.submitform(pressbutton);
			}
		} else {
			Joomla.submitform(pressbutton);
		}		
  });
});

</script>

<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<div id="sv_be_admin">
<?php echo JText::_('RS1_ADMIN_SCRN_RES_INTRO');?>
<table class="table table-striped" >
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ID');?></td>
    <td colspan="2"><?php echo $this->detail->id_resources; ?>&nbsp;</td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_NAME');?></td>
    <td><input type="text" size="40" maxsize="50" name="name" id="name" value="<?php echo stripslashes($this->detail->name); ?>" /></td>
    <td width="50%">&nbsp;</td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DESC');?></td>
    <td><input type="text" size="60" maxsize="80" name="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DESC_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_CONFIG_AUTO_ACCEPT');?> </td>
    <td><select name="auto_accept">
        <option value="Yes" <?php if($this->detail->auto_accept == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
        <option value="No" <?php if($this->detail->auto_accept == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        <option value="Global" <?php if($this->detail->auto_accept == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_GLOBAL');?></option>
      </select>
      &nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_AUTO_ACCEPT_RES_HELP');?></td>
  </tr> 
<!--  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS');?></td>
    <td><select name="access" >
      <option value="everyone" <?php if($this->detail->access == "" or $this->detail->access == "everyone"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_EVERYONE');?></option>
      <option value="registered_only" <?php if($this->detail->access == "registered_only"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_REGISTERED');?></option>
      <option value="public_only" <?php if($this->detail->access == "public_only"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_PUBLIC');?></option>
    </select>
      &nbsp;</td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS_HELP');?>&nbsp;</td>
  </tr>-->
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ACCESS');?></td>
    <td><table width="95%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="33%"><select style="width:auto" name="user_groups" id="user_groups">
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_group_rows ); $i++) {
			$user_group_row = $user_group_rows[$i];
			?>
          <option value="<?php echo $user_group_row->id; ?>"><?php echo $user_group_row->title; ?></option>
          <?php $k = 1 - $k; 
			} ?>
        </select></td>
        <td width="34%" valign="top" align="center"><input type="button" name="btnAddUserGroup" id="btnAddUserGroup" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_ADD');?>" onclick="doAddUserGroup()" />
          <br />
          &nbsp;<br />
          <input type="button" name="btnRemoveUserGroup" id="btnRemoveUserGroup" size="10"  onclick="doRemoveUserGroup()" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_REMOVE');?>" /></td>
        <td width="33%"><div class="sv_select"><select name="access_groups" id="access_groups" size="4" multiple="multiple" >
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
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_CATEGORY');?></td>
    <td><table class="table table-striped" >
      <tr>
        <td width="33%"><select style="width:auto" name="categories" id="categories">
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($cat_rows ); $i++) {
				$cat_row = $cat_rows[$i];
			?>
          <option value="<?php echo $cat_row->id_categories; ?>"><?php echo $cat_row->name;?></option>
          <?php $k = 1 - $k; 
			} ?>
        </select></td>
        <td width="34%" valign="top" align="center"><input type="button" name="btnAddCategoryScope" id="btnAddCategoryScope" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_ADD');?>" onclick="doAddCategoryScope()" />
          <br />
          &nbsp;<br />
          <input type="button" name="btnRemoveCategoryScope" id="btnRemoveCategoryScope" size="10"  onclick="doRemoveCategoryScope()" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_REMOVE');?>" /></td>
        <td width="33%"><div class="sv_select"><select name="selected_categories" id="selected_categories" multiple="multiple" size="4" >
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($category_scope_rows ); $i++) {
			$category_scope_row = $category_scope_rows[$i];
			?>
          <option value="<?php echo $category_scope_row->id_categories; ?>"><?php echo $category_scope_row->name; ?></option>
          <?php 
				$category_scope = $category_scope."|".$category_scope_row->id_categories."|";
				$k = 1 - $k; 
			} ?>
        </select></div></td>
      </tr>
    </table></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_CATEGORY_HELP');?>&nbsp;</td>
  </tr>
  <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_MAIL_DETAIL_RESOURCE');?></td>
      <td ><select name="mail_id" >
          <?php
				$k = 0;
				for($i=0; $i < sv_count_($mail_rows ); $i++) {
				$mail_row = $mail_rows[$i];
				?>
          <option value="<?php echo $mail_row->id_mail; ?>"  <?php if($this->detail->mail_id == $mail_row->id_mail){echo " selected='selected' ";} ?>><?php echo stripslashes($mail_row->mail_label); ?></option>
              <?php $k = 1 - $k; 
				} ?>
      </select>
      &nbsp;</td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_MAIL_DETAIL_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_COST');?></td>
    <td><input type="text" size="20" maxsize="20" name="cost" value="<?php echo $this->detail->cost; ?>" />
      &nbsp;&nbsp;</td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_COST_HELP');?></td>
  </tr>
  <tr>
    <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE');?></td>
    <td ><div style="display: table-cell" ><input style="width:50px; text-align: center" type="text" size="8" maxsize="10" name="rate" value="<?php echo $this->detail->rate; ?>" /></div>
      <div style="display: table-cell; padding-left:10px;"><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_UNIT');?></div>
      <div style="display:table-cell; padding-left:10px;"><select name="rate_unit" style="width:auto">
        <option value="Hour" <?php if($this->detail->rate_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_HOUR');?></option>
        <option value="Flat" <?php if($this->detail->rate_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_BOOKING');?></option>
      </select><div></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_RATE_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_FREE');?></td>
    <td><select name="free_booking">
      <option value="No" <?php if($this->detail->free_booking == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
      <option value="Yes" <?php if($this->detail->free_booking == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    </select></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_FREE_HELP');?></td>
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
    <td ><?php echo JText::_('RS1_ADMIN_SCRN_DEPOSIT');?></td>
    <td ><div style="display: table-cell"><input style="width:50px; text-align: center" type="text" size="8" maxsize="10" name="deposit_amount" value="<?php echo $this->detail->deposit_amount; ?>" /></div>
      <div style="display: table-cell; padding-left:10px;"><?php echo JText::_('RS1_ADMIN_SCRN_DEPOSIT_UNIT');?></div>
      <div style="display: table-cell; padding-left:10px;"><select name="deposit_unit" style="width:auto">
        <option value="Flat" <?php if($this->detail->deposit_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_DEPOSIT_FLAT');?></option>
        <option value="Percent" <?php if($this->detail->deposit_unit == "Percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_DEPOSIT_PERCENT');?></option>
      </select></div></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_DEPOSIT_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_PAYPAL');?></td>
    <td><input style="width:90%;" type="text" size="40" maxsize="50" name="paypal_account" id="paypal_account" value="<?php echo $this->detail->paypal_account; ?>" /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_PAYPAL_HELP');?>&nbsp;</td>
  </tr>
<!--  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_STRIPE');?></td>
    <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_PK');?>:&nbsp;&nbsp;<input type="text" style="width:250px;" name="res_stripe_pk" id="res_stripe_pk" value="<?php echo $this->detail->res_stripe_pk; ?>" /><br />
	    <?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_SK');?>:&nbsp;&nbsp;<input type="text" style="width:250px;" name="res_stripe_sk" id="res_stripe_sk" value="<?php echo $this->detail->res_stripe_sk; ?>" /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_STRIPE_HELP');?>&nbsp;</td>
  </tr>
-->  <tr>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_EMAILTO');?></td>
    <td valign="top"><input style="width:90%;" type="text" size="60" maxsize="200" name="resource_email" value="<?php echo $this->detail->resource_email; ?>" />
      <br /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_EMAILTO_HELP');?></td>
  </tr>
  <!--    <tr >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SEND_ICS');?> </td>
      <td><select name="send_ics">
          <option value="Yes" <?php if($this->detail->send_ics == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->send_ics == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SEND_ICS_HELP');?></td>
    </tr>    
-->
  <tr>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_SMS_PHONE');?></td>
    <td valign="top"><input type="text" size="60" maxsize="200" name="sms_phone" value="<?php echo $this->detail->sms_phone; ?>"/></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_SMS_PHONE_HELP');?></td>
  </tr>
  <!--    <tr >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DUPES');?> </td>
      <td><select name="prevent_dupe_bookings">
          <option value="Global" <?php if($this->detail->prevent_dupe_bookings == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_DUPES_GLOBAL');?></option>
          <option value="Yes" <?php if($this->detail->prevent_dupe_bookings == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->prevent_dupe_bookings == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select>
      &nbsp;&nbsp; <?php echo JText::_('RS1_ADMIN_SCRN_RES_MAX_DUPES');?> 
      <input type="text" name="max_dupes" id="max_dupes" size="2" maxlength="4" align="right" value="<?php echo $this->detail->max_dupes; ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DUPES_HELP');?></td>
    </tr>    
-->
  <tr>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_MAX_SEATS');?></td>
    <td valign="top"><input style="width:30px; text-align: center" type="text" size="2" maxsize="3" name="max_seats" value="<?php echo $this->detail->max_seats; ?>"/></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_MAX_SEATS_HELP');?></td>
  </tr>
  <tr>
    <td colspan = "3">
    	<table width="100%">
    	  <tr><td colspan="4"><?php echo JText::_('RS1_ADMIN_SCRN_GOOGLE_CAL_SETUP');?></td><tr>
          <tr>
          	<td>&nbsp;</td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_CLIENT_ID');?></td>
            <td width="45%"><input style="width:90%;" type="text" size="50" maxsize="255" name="google_client_id" value="<?php echo $this->detail->google_client_id ?>" /></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_CLIENT_ID_HELP');?></td>
          </tr>
          <tr >
          	<td>&nbsp;</td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_NAME');?></td>
            <td><input style="width:90%;" type="text" size="50" maxsize="255" name="google_app_name" value="<?php echo $this->detail->google_app_name; ?>" /></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_NAME_HELP');?></td>
          </tr>
          <tr >
          	<td>&nbsp;</td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_EMAIL');?></td>
            <td><input style="width:90%;" type="text" size="50" maxsize="255" name="google_app_email_address" value="<?php echo $this->detail->google_app_email_address; ?>" /></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_EMAIL_HELP');?></td>
          </tr>
          <tr >
          	<td>&nbsp;</td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_P12');?></td>
            <td><input style="width:90%;" type="text" size="50" maxsize="255" name="google_p12_key_filename" value="<?php echo $this->detail->google_p12_key_filename; ?>" /></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_APP_P12_HELP');?></td>
          </tr>
          <tr >
          	<td>&nbsp;</td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_CAL_ID');?></td>
            <td><input style="width:90%;" type="text" size="50" maxsize="255" name="google_default_calendar_name" value="<?php echo $this->detail->google_default_calendar_name; ?>" /></td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_GOOGLE_CAL_ID_HELP');?></td>
          </tr>
        </table>
    </td>
  </tr>

  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS');?></td>
    <td><table width="95%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="33%"><select style="width:auto; word-wrap:normal" name="users" id="users">
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_rows ); $i++) {
			$user_row = $user_rows[$i];
			?>
          <option value="<?php echo $user_row->id; ?>"><?php echo $user_row->name; ?></option>
          <?php $k = 1 - $k; 
			} ?>
        </select></td>
        <td width="34%" valign="top" align="center"><input type="button" name="btnAddUser" id="btnAddUser" size="10" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_ADD');?>" onclick="doAddUser()" />
          <br />
          &nbsp;<br />
          <input type="button" name="btnRemoveUser" id="btnRemoveUser" size="10"  onclick="doRemoveUser()" value="<?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_REMOVE');?>" /></td>
        <td width="33%"><div class="sv_select"><select name="admins" id="admins" size="4" >
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
        </select></div></td>
      </tr>
    </table></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_ADMINS_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_TIMESLOTS');?></td>
    <td ><select name="timeslots">
      <option value="Global" <?php if($this->detail->timeslots == "Global"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_USEGLOBAL');?></option>
      <option value="Specific" <?php if($this->detail->timeslots == "Specific"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC');?></option>
    </select></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_TIMESLOTS_HELP');?></td>
  </tr>
  <tr>
    <td width="17%"><?php echo JText::_('RS1_ADMIN_SCRN_RES_BOOKING_DAYS');?></td>
    <td width="38%" valign="bottom"><table  border="0" cellspacing="0" cellpadding="0">
      <tr align="left">
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_SUN');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_MON');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_TUE');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_WED');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_THU');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_FRI');?></td>
        <td class="center"><?php echo JText::_('RS1_ADMIN_SCRN_SAT');?></td>
        <td>&nbsp;</td>
      </tr>
      <tr align="center">
        <td class="center"><input type="checkbox" name="chkSunday" id="chkSunday" onclick="setHidden('Sunday');"  <?php if($this->detail->allowSunday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkMonday" id="chkMonday" onclick="setHidden('Monday');" <?php if($this->detail->allowMonday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkTuesday" id="chkTuesday" onclick="setHidden('Tuesday');" <?php if($this->detail->allowTuesday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkWednesday" id="chkWednesday" onclick="setHidden('Wednesday');" <?php if($this->detail->allowWednesday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkThursday" id="chkThursday" onclick="setHidden('Thursday');" <?php if($this->detail->allowThursday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkFriday" id="chkFriday" onclick="setHidden('Friday');" <?php if($this->detail->allowFriday == "Yes"){echo "checked";} ?>/></td>
        <td class="center"><input type="checkbox" name="chkSaturday" id="chkSaturday" onclick="setHidden('Saturday');" <?php if($this->detail->allowSaturday == "Yes"){echo "checked";} ?>/></td>
        <td></td>
      </tr>
    </table>
      <input type="hidden" name="allowSunday" id="allowSunday" value="<?php echo $this->detail->allowSunday?>" />
      <input type="hidden" name="allowMonday" id="allowMonday" value="<?php echo $this->detail->allowMonday?>" />
      <input type="hidden" name="allowTuesday" id="allowTuesday" value="<?php echo $this->detail->allowTuesday?>" />
      <input type="hidden" name="allowWednesday" id="allowWednesday" value="<?php echo $this->detail->allowWednesday?>" />
      <input type="hidden" name="allowThursday" id="allowThursday" value="<?php echo $this->detail->allowThursday?>" />
      <input type="hidden" name="allowFriday" id="allowFriday" value="<?php echo $this->detail->allowFriday?>" />
      <input type="hidden" name="allowSaturday" id="allowSaturday" value="<?php echo $this->detail->allowSaturday?>" /></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_BOOKING_DAYS_HELP');?></td>
  </tr>
  <tr>
    <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_HIDE');?></td>
    <td ><select name="non_work_day_hide">
      <option value="No" <?php if($this->detail->non_work_day_hide == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
      <option value="Yes" <?php if($this->detail->non_work_day_hide == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    </select></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_HIDE_HELP');?></td>
  </tr>
  <tr>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_MESSAGE');?></td>
    <td valign="top"><input style="width:90%;" type="text" size="60" maxsize="255" name="non_work_day_message" value="<?php echo $this->detail->non_work_day_message; ?>" />
      <br /></td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_NON_WORK_MESSAGE_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD');?></td>
    <td ><input style="width:30px; text-align: center" type="text" size="3" maxlength="3" name="min_lead_time" id="min_lead_time" class="sv_apptpro3_request_text" 
      value="<?php echo $this->detail->min_lead_time; ?>"/>
      &nbsp; <?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD_UNITS');?></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_MIN_LEAD_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_BEFORE');?></td>
    <td><table border="0" width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td><input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_today" value="disable_dates_before_today"
      	<?php echo ($this->detail->disable_dates_before == "Today" ? "checked='checked'" : "");?> onclick="setToday();" />
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_TODAY');?>&nbsp;</td>
      </tr>
      <tr>
        <td><input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_tomorrow" value="disable_dates_before_tomorrow"
      	<?php echo ($this->detail->disable_dates_before == "Tomorrow" ? "checked='checked'" : "");?> onclick="setTomorrow();" />
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_TOMORROW');?>&nbsp;</td>
      </tr>
      <tr>
        <td><input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_xdays" value="disable_dates_before_xdays"
      	<?php echo ($this->detail->disable_dates_before == "XDays" ? "checked='checked'" : "");?> onclick="setBeforeXDays();" />
          <input style="width:30px; text-align: center"  type="text" size="2" name="disable_dates_before_days" id="disable_dates_before_days" value="<?php echo $this->detail->disable_dates_before_days?>" />
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_DAYS_FROM_NOW');?>&nbsp;</td>
      </tr>
      <tr>
        <td><input type="radio" name="rdo_disable_dates_before" id="disable_dates_before_specific" value="disable_dates_before_specific" 
        <?php echo (($this->detail->disable_dates_before != "Tomorrow" AND $this->detail->disable_dates_before != "Today" AND $this->detail->disable_dates_before != "XDays")? "checked='checked'" : "");?>/>
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC_DATE');?>
          <input type="text" class="sv_date_box" name="disable_dates_before" id="disable_dates_before" size="10" readonly="readonly" value="<?php echo $this->detail->disable_dates_before; ?>" 
      	onchange="set_disable_before_radios();" />
          <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].disable_dates_before,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0" /></a>&nbsp;</td>
      </tr>
    </table></td>
    <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_BEFORE_HELP');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_DISABLE_AFTER');?></td>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_notset" value="disable_dates_after_notset" 
      		<?php echo ($this->detail->disable_dates_after == "Not Set" ? "checked='checked'" : "");?> onclick="setNotSet();"/>
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_NOT_SET');?>&nbsp;</td>
      </tr>
      <tr>
        <td><input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_xdays" value="disable_dates_after_xdays"
	      	<?php echo ($this->detail->disable_dates_after == "XDays" ? "checked='checked'" : "");?> onclick="setAfterXDays();" />
          <input type="text" style="width:30px; text-align: center" size="2" name="disable_dates_after_days" id="disable_dates_after_days" value="<?php echo $this->detail->disable_dates_after_days?>" />
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_DAYS_FROM_NOW');?>&nbsp;</td>
      </tr>
      <tr>
        <td><input type="radio" name="rdo_disable_dates_after" id="disable_dates_after_specific" value="disable_dates_after_specific"
			<?php echo (($this->detail->disable_dates_after != "Not Set" && $this->detail->disable_dates_after != "XDays") ? "checked='checked'" : "");?> />
          <?php echo JText::_('RS1_ADMIN_SCRN_RES_SPEC_DATE');?>
          <input type="text" class="sv_date_box" name="disable_dates_after" id="disable_dates_after"size="10" readonly="readonly"  
                value="<?php echo $this->detail->disable_dates_after; ?>"  onchange="set_disable_after_radios();"/>
          <a href="#" id="anchor2" onclick="cal.select(document.forms['adminForm'].disable_dates_after,'anchor2','yyyy-MM-dd'); return false;"
					 name="anchor2"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0" /></a>&nbsp;</td>
      </tr>
    </table>
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
  <tr >
    <td><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_LIST');?> </td>
    <td><select name="mailchimp_list_id">
    	<option value="-1" <?php if($this->detail->mailchimp_list_id == "-1"){echo " selected='selected' ";}?> ><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_USE_GLOBAL');?></option>        
    	<option value="-2" <?php if($this->detail->mailchimp_list_id == "-2"){echo " selected='selected' ";}?> ><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_NONE');?></option>
    <?php 
    foreach($aryLists["data"] as $List){ ?>			
        <option value="<?php echo $List["id"];?>"<?php if($this->detail->mailchimp_list_id == $List["id"]){echo " selected='selected' ";} ?>><?php echo $List["name"];?></option>
    <?php } ?>          
      </select></td>   
    <td><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_LIST_HELP');?></td>
  </tr>
  <tr >
    <td><?php echo JText::_('RS1_ADMIN_RES_ACYMAILING_LIST');?> </td>
    <td><select name="acymailing_list_id">
    	<option value="-1" <?php if($this->detail->acymailing_list_id == "-1"){echo " selected='selected' ";}?> ><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_USE_GLOBAL');?></option>        
    	<option value="-2" <?php if($this->detail->acymailing_list_id == "-2"){echo " selected='selected' ";}?> ><?php echo JText::_('RS1_ADMIN_RES_MAILCHIMP_NONE');?></option>
    <?php 
		foreach($acyLists as $List){ ?>			
			<option value="<?php echo $List->listid;?>"<?php if($this->detail->acymailing_list_id == $List->listid){echo " selected='selected' ";} ?>><?php echo $List->name;?></option>
    <?php } ?>          
      </select></td>   
    <td><?php echo JText::_('RS1_ADMIN_RES_ACYMAILING_LIST_HELP');?></td>
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
    <tr >
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_USER_DURATION_ENABLE');?>:</td>
      <td><select name="res_user_drag_duration_enable">
          <option value="Yes" <?php if($this->detail->res_user_drag_duration_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <option value="No" <?php if($this->detail->res_user_drag_duration_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select></td>
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_USER_DURATION_ENABLE_HELP');?></td>
    </tr>
    <tr >
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_USER_DURATION_SNAP');?>:</td>
      <td><select name="res_user_drag_duration_snap">
          <option value="5" <?php if($this->detail->res_user_drag_duration_snap == 5){echo " selected='selected' ";} ?>>5</option>
          <option value="10" <?php if($this->detail->res_user_drag_duration_snap == 10){echo " selected='selected' ";} ?>>10</option>
          <option value="15" <?php if($this->detail->res_user_drag_duration_snap == 15){echo " selected='selected' ";} ?>>15</option>
          <option value="20" <?php if($this->detail->res_user_drag_duration_snap == 20){echo " selected='selected' ";} ?>>20</option>
          <option value="30" <?php if($this->detail->res_user_drag_duration_snap == 30){echo " selected='selected' ";} ?>>30</option>
          <option value="60" <?php if($this->detail->res_user_drag_duration_snap == 60){echo " selected='selected' ";} ?>>60</option>
        </select></td>
      <td><?php echo JText::_('RS1_ADMIN_CONFIG_USER_DURATION_SNAP_HELP');?></td>
    </tr>
  </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_DISPLAY_ORDER');?></td>
    <td><input class="sv_order_style" type="text" size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
      &nbsp;&nbsp;</td>
    <td><?php echo JText::_('RS1_ADMIN_SCRN_DISPLAY_ORDER_HELP');?></td>
  </tr>
  <tr>
    <td ><?php echo JText::_('RS1_ADMIN_SCRN_RES_PUBLISHED');?></td>
    <td colspan="2"><select name="published">
      <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
      <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    </select></td>
  </tr>
</table>
</fieldset>
  <input type="hidden" name="id_resources" value="<?php echo $this->detail->id_resources; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="resources_detail" />
  <input type="hidden" name="resource_admins" id="resource_admins_id" value="<?php echo $admin_users; ?>" />
  <input type="hidden" name="access" id="resource_groups_id" value="<?php echo $access_groups_groups; ?>" />
  <input type="hidden" name="category_scope" id="selected_categories_id" value="<?php echo $category_scope; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro3_config->cal_position_method; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</div>
</form>
