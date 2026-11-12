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
	  form.controller.value="errorlog_detail";
	 }
	return true;	
	//
}
</script>

<?php echo JText::_('RS1_ADMIN_ERRLOG_LIST');?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<div class="row">
    <div id="j-sidebar-container" class="span2 col-md-2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10 col-md-10">
      <table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
      <th width="5%" class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_errorlog', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_PAYPAL_TIMESTAMP_COL_HEAD'), 'stamp', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="15%" class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_ERROR_OBJECT_COL_HEAD'), 'err_object', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', 'Description', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	//$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=errorlog_detail&task=edit&cid[]='. $row->id_errorlog );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_errorlog');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_errorlog; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td align="center"><?php echo $row->id_errorlog; ?>&nbsp;</td>
      <td><?php echo $row->stamp; ?></td>
      <td align="left"><?php echo $row->err_object; ?>&nbsp;</td>
      <td align="left"><?php echo $row->description; ?>&nbsp;</td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="5"><?php echo $this->pagination->getListFooter(); ?><?php echo $this->pagination->getLimitBox(); ?></td>
    </tfoot>
  </table>
  </div>
</div>  
  <input type="hidden" name="controller" value="errorlog" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
