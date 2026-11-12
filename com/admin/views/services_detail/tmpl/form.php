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
	$cur_res = $jinput->getString( 'resource_id' );
	// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_services_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
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
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE published = 1 ORDER BY ordering" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}		


	// get category_scope assignments 
	$category_scope = "";
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
			logIt($e->getMessage(), "be_services_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}	
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 ORDER BY ordering" );
		$cat_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}		

?>
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		var ok = "yes";
		if (pressbutton == 'save' || pressbutton == 'save2new'){
	//		if(document.getElementById("resource_id").selectedIndex == 0){
	//			alert("Please select a Resource.");
	//			ok = "no";
	//		}
			if(document.getElementById("name").value == ""){
				alert("Please enter a Service Name");
				ok = "no";
				return false;
			}
			if(ok == "yes"){
				Joomla.submitform(pressbutton);
			}
		} else {
			Joomla.submitform(pressbutton);
		}		
  });
});

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

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<fieldset class="adminform">
<?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_INTRO');?>
  <table class="table table-striped" >
    <tr>
      <td width="15%" ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_services ?> </td>
      <td width="50%">&nbsp;</td>
    </tr>      
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_NAME');?></td>
      <td colspan="2"><input type="text" size="60" maxsize="250" name="name" id="name" value="<?php echo $this->detail->name; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_DESC');?></td>
      <td colspan="2"><input type="text" size="60" maxsize="250" name="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
    </tr>
	<tr>
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
            </select></div><?php echo JText::_('RS1_ADMIN_SCRN_EMPTY_ALL');?></td>
          </tr>
        </table></td>
        <td><?php echo JText::_('RS1_ADMIN_SCRN_SRV_RESOURCE_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE');?></td>
      <td ><div ><input style="width:50px; text-align: center" type="text" size="8" maxsize="10" name="service_rate" value="<?php echo $this->detail->service_rate; ?>" /></div>
        <div style="display: table-cell; padding-left:10px;"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_UNIT');?></div>
        <div style="display: table-cell; padding-left:10px;"><select style="width:auto;" name="service_rate_unit">
          <option value="Hour" <?php if($this->detail->service_rate_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_HOUR');?></option>
          <option value="Flat" <?php if($this->detail->service_rate_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_BOOKING');?></option>
        </select></div></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_HELP');?></td>
    </tr>
	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="service_duration" value="<?php echo $this->detail->service_duration; ?>" />
        <div style="display: table-cell; padding-left:10px;"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_RATE_UNIT');?></div>
        <div style="display: table-cell; padding-left:10px;"><select style="width:auto;" name="service_duration_unit">
          <option value="Minute" <?php if($this->detail->service_duration_unit == "Minute"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_MINUTE');?></option>
          <option value="Hour" <?php if($this->detail->service_duration_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_HOUR');?></option>
      </select></div></td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_HELP');?></td>
    </tr>
	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="service_eb_discount" value="<?php echo $this->detail->service_eb_discount; ?>" />
        <br/><select style="width:auto;" name="service_eb_discount_unit">
          <option value="Flat" <?php if($this->detail->service_eb_discount_unit == "Flat"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_FLAT');?></option>
          <option value="Percent" <?php if($this->detail->service_eb_discount_unit == "Percent"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_PERCENT');?></option>
      </select>
	  <br/>
      <input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="service_eb_discount_lead" value="<?php echo $this->detail->service_eb_discount_lead; ?>" />
      &nbsp;<?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_DAYS');?>
      </div>
      </td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_EB_DISCOUNT_HELP');?></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_STAFF_ONLY');?></td>
        <td>
            <select name="staff_only">
            <option value="No" <?php if($this->detail->staff_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="Yes" <?php if($this->detail->staff_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
         <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_STAFF_ONLY_HELP');?></td>
    </tr>
      <tr>
        <td><?php echo JText::_('RS1_ADMIN_SRV_IMAGE');?> </td>
        <td><input type="text" style="width:90%;" name="ddslick_image_path" value="<?php echo $this->detail->ddslick_image_path; ?>" />
        <?php echo ($this->detail->ddslick_image_path != ""?"<br/><img src=\"".getResourceImageURL($this->detail->ddslick_image_path)."\" style='max-height: 64px;'/>":"")?>		
        </td>
        <td><?php echo JText::_('RS1_ADMIN_SRV_IMAGE_HELP');?></td>
      <tr>
        <td><?php echo JText::_('RS1_ADMIN_SRV_IMAGE_TEXT');?></td>
        <td><input type="text" style="width:90%;" name="ddslick_image_text" value="<?php echo $this->detail->ddslick_image_text; ?>" />
          &nbsp;&nbsp;</td>
        <td><?php echo JText::_('RS1_ADMIN_SRV_IMAGE_TEXT_HELP');?></td>
      </tr>
      <tr>
        <td><?php echo JText::_('RS1_ADMIN_SCRN_SRV_CATEGORY');?></td>
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
        <td><?php echo JText::_('RS1_ADMIN_SCRN_SRV_CATEGORY_HELP');?>&nbsp;</td>
      </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_ORDER');?></td>
      <td colspan="2"><input style="width:30px; text-align: center" type="text" size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DETAIL_PUBLISHED');?></td>
        <td colspan="2">
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>
    <tr>
      <td colspan="2" >
      <p>&nbsp;</p></td>
    </tr>  
  </table>

</fieldset>
  <input type="hidden" name="id_services" value="<?php echo $this->detail->id_services; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="services_detail" />
  <input type="hidden" name="category_scope" id="selected_categories_id" value="<?php echo $category_scope; ?>" />
  <input type="hidden" name="resource_scope" id="selected_resources_id" value="<?php echo $resource_scope; ?>" />

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
