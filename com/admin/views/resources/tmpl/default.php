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
//$ordering = ($this->lists['order'] == 'ordering');
//$saveOrder = $ordering == 'a.ordering';
// unable to get save of ordering to work in J4
$saveOrder = "";

?>

<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove')||(task=='saveorder'))
	 {
	  form.controller.value="resources_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}

</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<div class="row">
    <div id="j-sidebar-container" class="span2 col-md-2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10 col-md-10">
	<table class="table table-striped" >
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_RES_LIST');?> <br /></th>
            <th>
            <table align="right" cellspadding="2" >
            <tr>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RES_CATEGORY');?></td>
            <td><select name="category_id" onchange="this.form.submit();" style="background-color:#FFFFCC" >
              <option value="0" <?php if($this->filter_category == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_APPT_LIST_SELECT_CAT');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($cat_rows ); $i++) {
				$cat_row = $cat_rows[$i];
				?>
              <option value="<?php echo $cat_row->id_categories; ?>" <?php if($this->filter_category == $cat_row->id_categories){echo " selected='selected' ";} ?>><?php echo stripslashes($cat_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
           </table>
         </th> 
      </tr>
  </table>
    <table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_resources', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'), 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="center"><?php echo JText::_('RS1_ADMIN_SCRN_DAYS_COL_HEAD') ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_TIMESLOTS_COL_HEAD'), 'timeslots', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_COL_HEAD_NEW'); ?></th>
      <th width="5%" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ORDER_COL_HEAD'), 'ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			<?php  if ($saveOrder) :?>
				<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'saveorder'); ?>
			<?php endif; ?>
      </th>
 	  <th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for ($i=0, $n=sv_count_($this->items ); $i < $n; $i++){
	$row =$this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=resources_detail&task=edit&cid[]='. $row->id_resources );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_resources');

   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_resources; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td align="center"><?php echo $row->id_resources; ?>&nbsp;</td>
      <td><a href=<?php echo $link; ?>><?php echo  stripslashes($row->name); ?></a></td>
      <td align="left"><?php echo stripslashes($row->description); ?>&nbsp;</td>
      <td class="center">  
   	  <?php 
		echo ($row->allowSunday=="Yes" ? JText::_('RS1_SUN').' ' : '');
		echo ($row->allowMonday=="Yes" ? JText::_('RS1_MON').' ' : '');
		echo ($row->allowTuesday=="Yes" ? JText::_('RS1_TUE').' ' : '');
		echo ($row->allowWednesday=="Yes" ? JText::_('RS1_WED').' ' : '');
		echo ($row->allowThursday=="Yes" ? JText::_('RS1_THU').' ' : '');
		echo ($row->allowFriday=="Yes" ? JText::_('RS1_FRI').' ' : '');
		echo ($row->allowSaturday=="Yes" ? JText::_('RS1_SAT').' ' : '');
		 ?></td>
      <td align="center"><?php echo $row->timeslots; ?>&nbsp;</td>
      <td class="center"><?php echo str_replace("||",",",$row->category_scope); ?>&nbsp;</td>
      <td class="center"><?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
            <input class="sv_order_style" type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> />
      </td>
      <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="9"><?php echo $this->pagination->getListFooter(); ?><?php echo $this->pagination->getLimitBox(); ?></td>
    </tfoot>
  </table>
  </div>
</div>  
  <input type="hidden" name="controller" value="resources" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
