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

	$daynames = array(0=>JText::_('RS1_SUNDAY'), 1=>JText::_('RS1_MONDAY'), 2=>JText::_('RS1_TUESDAY'), 3=>JText::_('RS1_WEDNESDAY'), 4=>JText::_('RS1_THURSDAY'), 5=>JText::_('RS1_FRIDAY'), 6=>JText::_('RS1_SATURDAY'));
	
	// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE timeslots != 'Global' ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_timeslots_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	
?>
<style type="text/css">
<!--
.icon-48-timeslots { background-image: url(./components/com_rsappt_pro3/images/timeslots.png); }
-->
}
</style>
<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove') )
	 {
	  form.controller.value="timeslots_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}
</script>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();" >
    <table class="table table-striped" >
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_TIMESLOT_LIST');?> <br /></th>
            <th>
            <table class="adminheading" align="right" cellspadding="2">
            <tr>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_COL_HEAD');?>*:</td>
            <td><select name="resource_id" onchange="this.form.submit();" style="background-color:#FFFFCC" >
              <option value="0" <?php if($this->filter_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SRV_LIST_SELECT_RES');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
              <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo stripslashes($res_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>
                <?php echo JText::_('RS1_ADMIN_SCRN_DAY_COL_HEAD');?>:            </td>
            <td>
                <select name="day_number" onchange="this.form.submit();" style="background-color:#FFFFCC" >
                  <option value="all" <?php if($this->filter_day_number == "all"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_SHOW_ALL')?></option>
                  <option value="0"<?php if($this->filter_day_number == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_SUNDAY')?></option>
                  <option value="1"<?php if($this->filter_day_number == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_MONDAY')?></option>
                  <option value="2"<?php if($this->filter_day_number == "2"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_TUESDAY')?></option>
                  <option value="3"<?php if($this->filter_day_number == "3"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_WEDNESDAY')?></option>
                  <option value="4"<?php if($this->filter_day_number == "4"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_THURSDAY')?></option>
                  <option value="5"<?php if($this->filter_day_number == "5"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_FRIDAY')?></option>
                  <option value="6"<?php if($this->filter_day_number == "6"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_SATURDAY')?></option>
                </select>&nbsp;<?php echo $this->pagination->getLimitBox(); ?>&nbsp;</td>
            </tr>
           </table>
         </th> 
      </tr>
    </table>
  <table class="table table-striped" >
   <thead>
    <tr>
      <th width="5%" align="center"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_timeslots', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD'), 'resource_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DAY_COL_HEAD'), 'day_number', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_START_COL_HEAD'), 'timeslot_starttime', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_END_COL_HEAD'), 'timeslot_endtime', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
 	  <th class="center" align="center"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_STAFF'), 'staff_only', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUB_START_COL_HEAD'), 'start_publishing', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUB_END_COL_HEAD'), 'end_publishing', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
 	  <th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
  </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=timeslots_detail&task=edit&cid[]='. $row->id_timeslots );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_timeslots');
   ?>
    <tr class="<?php echo "row$k";?>">
      <td width="5%" class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_timeslots; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td width="5%" class="center"><a href=<?php echo $link; ?>><?php echo $row->id_timeslots; ?></a></td>
      <td width="15%" class="center"><?php echo ($row->name == ""?"Global": stripslashes($row->name)); ?>&nbsp;</td>
      <td width="15%" class="center"><?php echo $daynames[$row->day_number]; ?>&nbsp;</td>
      <td width="15%" class="center"><?php echo $row->timeslot_starttime; ?>&nbsp;</td>
      <td width="15%" class="center"><?php echo $row->timeslot_endtime; ?>&nbsp;</td>
      <td class="center"><?php echo $row->staff_only; ?>&nbsp;</td>
      <td align="center"><?php echo $row->start_publishing; ?>&nbsp;</td>
      <td align="center"><?php echo $row->end_publishing; ?>&nbsp;</td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>	<tfoot>
    	<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
<?php echo JText::_('RS1_ADMIN_TIMESLOT_LIST_FOOTER');?>

  <input type="hidden" name="controller" value="timeslots" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
