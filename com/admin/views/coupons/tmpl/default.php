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


?>

<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove') )
	 {
	  form.controller.value="coupons_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}
</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php echo JText::_('RS1_ADMIN_COUPON_LIST');?>
<table class="table table-striped" >
	<thead>
     <tr>
        <td colspan="11" style="text-align:right"><input type="text" id="coupon_search" name="coupon_search" size="20" title="<?php echo JText::_('RS1_ADMIN_COUPON_LIST_SEARCH_HELP');?>" 
        value="<?php echo $this->filter_coupon_search ?>" />&nbsp;<input type="button" style="width:auto" onclick="this.form.submit();" value="<?php echo JText::_('RS1_ADMIN_COUPON_LIST_SEARCH');?>" />&nbsp;&nbsp;
		</td>
    </tr>    
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_coupons', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_DESC'), 'description', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_CODE'), 'coupon_code', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_VALUE'), 'discount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_TYPE'), 'discount_unit', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_MAX_USER_USE_COL'), 'max_user_use', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_MAX_TOTAL_USE_COL'), 'max_total_use', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_COUPON_CUR_TOTAL_USE_COUNT'), 'current_count', $this->lists['order_Dir'], $this->lists['order']); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_COUPON_EXPIRY'), 'expiry_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
 	  <th class="center" width="5%" nowrap="nowrap"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order']); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=coupons_detail&task=edit&cid[]='. $row->id_coupons );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_coupons');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_coupons; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td class="center"><a href=<?php echo $link; ?>><?php echo  $row->id_coupons; ?></a></td>
      <td class="left"><?php echo stripslashes($row->description); ?>&nbsp;</td>
      <td class="center"><?php echo $row->coupon_code; ?>&nbsp;</td>
      <td class="center"><?php echo $row->discount; ?>&nbsp;</td>
      <td class="center"><?php echo $row->discount_unit; ?>&nbsp;</td>
      <td class="center"><?php echo $row->max_user_use; ?>&nbsp;</td>
      <td class="center"><?php echo $row->max_total_use; ?>&nbsp;</td>
      <td class="center"><?php echo $row->current_count; ?>&nbsp;</td>
      <td class="center"><?php echo $row->expiry; ?>&nbsp;</td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="coupons" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
