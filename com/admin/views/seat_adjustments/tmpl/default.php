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
	  form.controller.value="seat_adjustments_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}

	
</script>

<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php echo JText::_('RS1_ADMIN_SEAT_ADJUSTMENTS_INTRO');?><hr/>
<table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_seat_adjustments', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_RES_NAME'), 'res_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_BY'), 'by_day_time', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_SUN'), 'adjustSunday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_MON'), 'adjustMonday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TUE'), 'adjustTuesday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_WED'), 'adjustWednesday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_THU'), 'adjustThursday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_FRI'), 'adjustFriday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_SAT'), 'adjustSaturday', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TIMESTART'), 'timeRangeStart', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
      <th class="center" width="10%"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TIMEEND'), 'timeRangeEnd', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>

      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_RATE'), 'seat_adjustment', $this->lists['order_Dir'], $this->lists['order'] ); ?>	   </th>
 	  <th nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=seat_adjustments_detail&task=edit&cid[]='. $row->id_seat_adjustments );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_seat_adjustments');
   ?>
    <tr>
      <td class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_seat_adjustments; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td class="center"><a href=<?php echo $link; ?>><?php echo $row->id_seat_adjustments; ?></a></td>
      <td class="center"><?php echo $row->res_name; ?></td>
      <td class="center"><?php echo translated_status($row->by_day_time); ?></td>
      <td class="center" style="border-left:thin solid #CCC"><?php echo ($row->adjustSunday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo ($row->adjustMonday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo ($row->adjustTuesday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo ($row->adjustWednesday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo ($row->adjustThursday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo ($row->adjustFriday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center" style="border-right:thin solid #CCC"><?php echo ($row->adjustSaturday=="Yes"?JText::_('RS1_ADMIN_SCRN_YES'):""); ?></td>
      <td class="center"><?php echo substr($row->timeRangeStart,0,5); ?></td>
      <td class="center" style="border-right:thin solid #CCC"><?php echo substr($row->timeRangeEnd,0,5); ?></td>

      <td style="text-align:center; border-right:thin solid #CCC"><?php echo $row->seat_adjustment; ?></td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="16"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="seat_adjustments" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
