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

$database = JFactory::getDBO();

	// get resources 
	$sql = "SELECT id_resources, name FROM #__sv_apptpro3_resources WHERE published = 1 ORDER BY ordering";
	try{
		$database->setQuery($sql);
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_rate_over_detail_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// get groups
	try{
		$database->setQuery("SELECT * FROM #__usergroups ORDER BY title" );
		$user_group_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_rate_over_detail_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		


?>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
	pressbutton = document.adminForm.task.value;  
   	if (pressbutton == 'save'){
		if(document.getElementById("entity_type") != null){
			if(document.getElementById("entity_type").value == ""){
				alert('<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_TYPE_REQ');?>');
				return false;
			}
			if(document.getElementById("entity_id").options.length == 0){
				document.getElementById("entity_type").selectedIndex = 0;
				alert('<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_TYPE_REQ');?>');
				return false;
			}
		}
		if(document.getElementById("rate_override").value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_RATE_REQ');?>');
			return false;
		} else if(isNaN(document.getElementById("rate_override").value)){
			alert('<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_RATE_NUM');?>');
			return false;			
		}
	}		
	Joomla.submitform(pressbutton);
  });
});


function changeOverrideType(){
	document.getElementById("entity_id").options.length = 0;
	if(document.getElementById("entity_type").value != ""){
		jQuery.noConflict();
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			url: window.location.href+"&task=ajax_get_override_entity_ids&etype="+document.getElementById("entity_type").value,
			success: function(data) {
				//alert(data);
				var select = document.getElementById("entity_id"); 
				var temp = data.split(",");
				for (var i = 0; i < temp.length; i++) {
					var temp2 = temp[i].split(":");
					var el = document.createElement("option");
					el.textContent = temp2[1];
					el.value = temp2[0];
					select.appendChild(el);
				}
			},
			error: function(data) {
				alert(data.msg);
			}					
		 });
	}
}
</script>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
	<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_DETAIL_INTRO');?><hr/>
  <table class="table table-striped" >
    <tr >
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_rate_overrides ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr >
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE');?></td>
      <?php if($this->detail->id_rate_overrides == 0){?>
    	  <td colspan="2"> <select name="entity_type" id="entity_type" onchange="changeOverrideType(); return false;">
              <option value="" selected="selected" ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_SELECT');?></option>
              <option value="resource" ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_RESOURCE');?></option>
              <option value="service" ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_SERVICE');?></option>
              <option value="extra" ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_EXTRA');?></option>
              <option value="seat" ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_SEAT');?></option>
            </select></td>
      <?php } else { ?>
	      <td colspan="2"><?php echo translated_status($this->detail->entity_type);?></td>
      <?php } ?>
		        
    </tr>
	<tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_NAME');?></td>
      <td>
      <?php if($this->detail->id_rate_overrides == 0){?>
    	  <select name="entity_id" id="entity_id"></select>
      <?php } else { 
		  if($this->detail->entity_type == "resource"){
			  echo $this->detail->res_name;
		  } else if($this->detail->entity_type == "service"){
			  echo $this->detail->srv_name;
		  } else if($this->detail->entity_type == "extra"){
			  echo $this->detail->extra_name;
		  } else if($this->detail->entity_type == "seat"){
			  echo $this->detail->seat_name;
		  }
	  } ?>
      </td>
      <td></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_GROUP');?></td>
      <td><select style="width:auto" name="group_id" id="group_id">
            <?php
			$k = 0;
			for($i=0; $i < sv_count_($user_group_rows ); $i++) {
			$user_group_row = $user_group_rows[$i];
			?>
                <option value="<?php echo $user_group_row->id; ?>"<?php if($this->detail->group_id == $user_group_row->id){echo " selected='selected' ";} ?>><?php echo stripslashes($user_group_row->title); ?></option>
                <?php $k = 1 - $k; 
			} ?>
           </select>
      </td>      
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_GROUP_HELP');?></td>
    </tr>
	<tr>
	  <td><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_RATE');?></td>
	  <td><input style="width:50px; text-align: center" type="text" size="8" maxsize="6" name="rate_override" id="rate_override" value="<?php echo $this->detail->rate_override; ?>" /></td>
      <td></td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_PUBLISHED');?></td>
        <td colspan="2">
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>
  </table>
  <hr />
        <?php echo JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_NOTE');?>
        <br/>
        <br/>
        <?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_RATE_PRECEDENCE');?>
        

</fieldset>
  <input type="hidden" name="id_rate_overrides" value="<?php echo $this->detail->id_rate_overrides; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="rate_overrides_detail" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
