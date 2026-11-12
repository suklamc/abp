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

JHtml::_('jquery.framework');

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'ordering');
$selected_resource_book_dates = "";
	
	// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE Published = 1 ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_book_dates_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	
?>

<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove') )
	 {
	  form.controller.value="book_dates_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}

function set_enable_book_dates(){
	jQuery.noConflict();

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		url: "index.php?option=com_rsappt_pro3&controller=ajax&task=ajax_set_book_dates_enable&nv="+document.getElementById("enable_book_dates").value
			+"&res="+document.getElementById("resource_id").value,
		success: function(data) {
			alert(data);
		},
		error: function(data) {
			alert(data);
		}					
	 });	
}
</script>


<style type="text/css">
<!--
.icon-48-book_dates { background-image: url(./components/com_rsappt_pro3/images/book_dates.png); }
-->
}
</style>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
    <table class="table table-striped" >
        <tr>
          <th align="left" ><?php echo JText::_('RS1_ADMIN_BOOK_DATES_LIST');?> <br /></th>
            <th>
            <table class="adminheading" align="right" cellspadding="2">
            <tr>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD');?>:</td>
            <td><select name="resource_id" id="resource_id" onchange="this.form.submit();" style="background-color:#FFFFCC" >
              <option value="0" <?php if($this->filter_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SRV_LIST_SELECT_RES');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
              <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_resource == $res_row->id_resources){echo " selected='selected' "; $selected_resource_book_dates = $res_row->date_specific_booking;} ?>><?php echo stripslashes($res_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
           </table>
         </th> 
      </tr>
    </table>
    <?php echo JText::_('RS1_ADMIN_BOOK_DATES_LIST_INTRO');?>

	<?php if($this->filter_resource >0){ ?>

		<?php echo JText::_('RS1_ADMIN_RATE_BOOK_DATES_ENABLE');?>: <select id="enable_book_dates" name="enable_book_dates" onchange="set_enable_book_dates()" style="width:100px;">
            <option value="Yes" <?php if($selected_resource_book_dates == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            <option value="No" <?php if($selected_resource_book_dates == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        </select>&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_RATE_BOOK_DATES_ENABLE_HELP');?>
        <hr/>
    <?php } ?>
    
    <table class="table table-striped" >
   <thead>
    <tr>
      <th width="5%" class="center"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_book_dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="center"><?php echo JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD'); ?></th>
      <th class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_BOOKDATE_COL_HEAD'), 'book_date', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_DESCRIPTION_COL_HEAD'), 'description', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
 	  <th class="center" width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
  </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=book_dates_detail&task=edit&cid[]='. $row->id_book_dates );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_book_dates');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td width="5%" class="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_book_dates; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td width="5%" class="center"><a href=<?php echo $link; ?>><?php echo $row->id_book_dates; ?></a></td>
      <td width="20%" class="center"><?php echo ($row->name == ""?"Global": stripslashes($row->name)); ?>&nbsp;</td>
      <td width="20%" class="center"><?php echo $row->book_date_display; ?>&nbsp;</td>
      <td width="20%" class="left"><?php echo stripslashes($row->description); ?>&nbsp;</td>
	  <td class="center"><?php echo $published;?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>	<tfoot>
    	<td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="book_dates" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
