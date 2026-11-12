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


?>

<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove')||(task=='saveorder') )
	 {
	  form.controller.value="udfs_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}
</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php echo JText::_('RS1_ADMIN_UDF_LIST');?>
<table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_udfs', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_UDF_SCREEN_LABEL'), 'udf_label', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_UDF_TYPE'), 'udf_type', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_UDF_REQUIRED'), 'udf_required', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_UDF_USERHELP'), 'udf_help', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="5%" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ORDER_COL_HEAD'), 'ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    	<?php if ($saveOrder) :?>
			<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'saveorder'); ?>
		<?php endif; ?>
	   </th>
 	  <th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=udfs_detail&task=edit&cid[]='. $row->id_udfs );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_udfs');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_udfs; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td class="center"><?php echo $row->id_udfs; ?>&nbsp;</td>
      <td><a href=<?php echo $link; ?>><?php echo  stripslashes($row->udf_label); ?></a></td>
      <td align="center"><?php echo $row->udf_type; ?>&nbsp;</td>
      <td align="center"><?php echo ($row->udf_type == "Textbox" || $row->udf_type == "Textarea" || $row->udf_type == "Checkbox" || $row->udf_type == "Radio" ? $row->udf_required : "--"); ?>&nbsp;</td>
      <td align="left"><?php echo stripslashes($row->udf_help); ?>&nbsp;</td>
      <td align="center"> <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
            <input class="sv_order_style" type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> />
		</td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="udfs" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
