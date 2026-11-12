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


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'ordering');
$saveOrder = $ordering == 'a.ordering';
// unable to get save of ordering to work in J4
$saveOrder = "";

// get enable rate config setting
	$database = JFactory::getDBO(); 
	$sql = 'SELECT enable_overrides FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_rate_over_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	

?>
<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove')||(task=='saveorder') )
	 {
	  form.controller.value="rate_overrides_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}

function set_enable_overrides(){
	jQuery.noConflict();

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		url: "index.php?option=com_rsappt_pro3&controller=ajax&task=ajax_set_rate_override_enable&nv="+document.getElementById("enable_overrides").value,
		success: function(data) {
			alert(data);
		},
		error: function(data) {
			alert(data);
		}					
	 });	
}
	
</script>

<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_INTRO');?>
<hr/>
<?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_ENABLE');?>: <select id="enable_overrides" name="enable_overrides" onchange="set_enable_overrides()">
	<option value="Yes" <?php if($apptpro_config->enable_overrides == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    <option value="No" <?php if($apptpro_config->enable_overrides == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
</select><?php echo JText::_('RS1_ADMIN_RATE_OVERRIDES_ENABLE_HELP');?>
<hr/>
<table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_rate_overrides', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_ENTITY_TYPE'), 'entity_type', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_ENTITY_ID'), 'entity_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_RES_NAME'), 'res_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_SRV_NAME'), 'srv_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_EXT_NAME'), 'extra_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_SEAT_NAME'), 'seat_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_GROUP'), 'user_group', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="5%" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_RATE_OVERRIDE_RATE'), 'rate_override', $this->lists['order_Dir'], $this->lists['order'] ); ?>
	   </th>
 	  <th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=rate_overrides_detail&task=edit&cid[]='. $row->id_rate_overrides );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_rate_overrides');
   ?>
    <tr>
      <td class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_rate_overrides; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td class="center"><a href=<?php echo $link; ?>><?php echo $row->id_rate_overrides; ?></a></td>
      <td><?php echo translated_status($row->entity_type); ?></td>
      <td align="center"><?php echo $row->entity_id; ?></td>
      <td><?php echo $row->res_name; ?></td>
      <td><?php echo $row->srv_name; ?></td>
      <td><?php echo $row->extra_name; ?></td>
      <td><?php echo $row->seat_name; ?></td>
      <td align="center"><?php echo $row->user_group; ?></td>
      <td align="left"><?php echo $row->rate_override; ?></td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="rate_overrides" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
