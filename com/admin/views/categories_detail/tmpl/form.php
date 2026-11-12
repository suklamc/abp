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

// get parent categories
	$database = JFactory::getDBO(); 
	if($this->detail->id_categories == ""){
		$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE (parent_category IS NULL OR parent_category =\'\') order by ordering';
	} else {
		$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE id_categories != '.$this->detail->id_categories.' AND (parent_category IS NULL OR parent_category =\'\') order by ordering';
	}
	try{
		$database->setQuery($sql);
		$parent_cats = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_cat_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_mail WHERE published = 1 ORDER BY id_mail" );
		$mail_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_cat_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}


	// get group_scope assignments 
	$group_scope = "";
	if (strlen($this->detail->group_scope) > 0 ){
		$group_scope_assignments = str_replace("||", ",", $this->detail->group_scope);
		$group_scope_assignments = str_replace("|", "", $group_scope_assignments);
		//echo $group_scope_assignments;
		//exit;
		$sql = "SELECT id as groupid, title FROM #__usergroups WHERE ".
  			"id IN (".$group_scope_assignments.") ORDER BY title";
		try{
			$database->setQuery($sql);
			$access_group_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}	
		//print_r($group_scope_rows);	
	}	

	try{
		$database->setQuery("SELECT * FROM #__usergroups ORDER BY title" );
		$user_group_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_resources_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

?>

<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		if (pressbutton == 'save' || pressbutton == 'save2new'){
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



	function doAddUserGroup(){
		var groupid = document.getElementById("user_groups").value;
		var cur_user_groups = document.getElementById("category_groups_id").value;
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
		document.getElementById("category_groups_id").value = cur_user_groups;
	}

	function doRemoveUserGroup(){
		if(document.getElementById("access_groups").selectedIndex == -1){
			alert("<?php echo JText::_('RS1_NO_GROUP_SELECTED');?>");
			return false;
		}
		var user_to_go = document.getElementById("access_groups").options[document.getElementById("access_groups").selectedIndex].value;
		document.getElementById("access_groups").remove(document.getElementById("access_groups").selectedIndex);
		
		var cur_user_groups = document.getElementById("category_groups_id").value;

		cur_user_groups = cur_user_groups.replace("|" + user_to_go + "|", "");
		document.getElementById("category_groups_id").value = cur_user_groups;
	}

</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_INTRO');?>
  <table class="table table-striped" >
    <tr>
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_categories ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_NAME');?></td>
      <td colspan="3"><input type="text" size="40" maxsize="50" name="name" id="name" value="<?php echo stripslashes($this->detail->name); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_DESC');?></td>
      <td><input type="text" size="60" maxsize="80" name="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_PARENTS');?></td>
        <td>
            <select name="parent_category">
          	<option value=0><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_NOPARENT_PROMPT');?></option>
          <?php 
					$k = 0;
					for($i=0; $i < sv_count_($parent_cats ); $i++) {
					$parent_cat = $parent_cats[$i];
					?>
          	<option value="<?php echo $parent_cat->id_categories; ?>" <?php if($parent_cat->id_categories == $this->detail->parent_category ){echo " selected='selected' ";} ?>><?php echo stripslashes($parent_cat->name); ?></option>
          		<?php $k = 1 - $k; 
					} ?>
          	</select>        </td>
        <td><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_PARENTS_HELP');?>&nbsp;</td>
    </tr>
      <tr>
        <td><?php echo JText::_('RS1_ADMIN_SCRN_CAT_ACCESS');?></td>
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
        <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_CAT_ACCESS_HELP');?></td>
      </tr>

	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_DURATION');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="category_duration" value="<?php echo $this->detail->category_duration; ?>" />
        <div style="display: table-cell; padding-left:10px;"><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_DURATION_UNIT');?></div>
        <div style="display: table-cell; padding-left:10px;"><select style="width:auto;" name="category_duration_unit">
          <option value="Minute" <?php if($this->detail->category_duration_unit == "Minute"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_MINUTE');?></option>
          <option value="Hour" <?php if($this->detail->category_duration_unit == "Hour"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_DURATION_HOUR');?></option>
      </select></div></td>
      <td width="55%"><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_DURATION_HELP');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_MAIL_DETAIL_CATEGORY');?></td>
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
        <td><?php echo JText::_('RS1_ADMIN_CAT_IMAGE');?> </td>
        <td><input type="text" style="width:90%;" name="ddslick_image_path" value="<?php echo $this->detail->ddslick_image_path; ?>" />
        <?php echo ($this->detail->ddslick_image_path != ""?"<br/><img src=\"".getResourceImageURL($this->detail->ddslick_image_path)."\" style='max-height: 64px;'/>":"")?>		
        </td>
        <td><?php echo JText::_('RS1_ADMIN_CAT_IMAGE_HELP');?></td>
      <tr>
        <td><?php echo JText::_('RS1_ADMIN_CAT_IMAGE_TEXT');?></td>
        <td><input type="text" style="width:90%;" name="ddslick_image_text" value="<?php echo $this->detail->ddslick_image_text; ?>" />
          &nbsp;&nbsp;</td>
        <td><?php echo JText::_('RS1_ADMIN_CAT_IMAGE_TEXT_HELP');?></td>
      </tr>    
   	<tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_ORDER');?></td>
      <td><input class="sv_order_style" type="text"  size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
      <td>&nbsp;</td>
   	</tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_CAT_DETAIL_PUBLISHED');?></td>
        <td>
            <select name="published">
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
  <input type="hidden" name="id_categories" value="<?php echo $this->detail->id_categories; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="categories_detail" />
  <input type="hidden" name="group_scope" id="category_groups_id" value="<?php echo $access_groups_groups; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
