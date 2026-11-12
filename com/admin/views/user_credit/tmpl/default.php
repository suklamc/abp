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

// get enable gc config setting
	$database = JFactory::getDBO(); 
	$sql = 'SELECT enable_gift_cert FROM #__sv_apptpro3_config';
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
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove') )
	 {
	  form.controller.value="user_credit_detail";
	 }
	return true;	
	//id="adminForm" onsubmit="myonsubmit();"
}

function set_enable_gift_cert(){
	jQuery.noConflict();

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		url: "index.php?option=com_rsappt_pro3&controller=ajax&task=ajax_set_gift_cert_enable&nv="+document.getElementById("enable_gift_cert").value,
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

<?php if($this->credit_type == "gc"){ ?>
	<?php echo JText::_('RS1_ADMIN_GC_LIST');?>
	<p><?php echo JText::_('RS1_ADMIN_GC_INTRO');?></p>
<?php } else { ?>
	<?php echo JText::_('RS1_ADMIN_USER_CREDIT_LIST');?>
	<p><?php echo JText::_('RS1_ADMIN_USER_CREDIT_INTRO');?></p>
<?php } ?>
	
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php if($this->credit_type == "gc"){ ?>
<hr/>
<?php echo JText::_('RS1_ADMIN_GIFT_CERT_ENABLE');?>: <select id="enable_gift_cert" name="enable_gift_cert" onchange="set_enable_gift_cert()">
	<option value="Yes" <?php if($apptpro_config->enable_gift_cert == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
    <option value="No" <?php if($apptpro_config->enable_gift_cert == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
</select>
<hr/>
<?php } ?>

<table class="table table-striped" >
	<thead>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="title" style="text-align:center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_user_credit', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
<?php if($this->credit_type == "gc"){ ?>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_GIFT_CERT'), 'gift_cert', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_NAME'), 'gift_cert_name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" style="text-align:right"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_GC_BALANCE'), 'balance', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
<?php } else { ?>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_ID'), 'user_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_NAME'), 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" style="text-align:right"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_USER_CREDIT_BALANCE'), 'balance', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
<?php } ?>
	  <th>&nbsp;</th>      
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=user_credit_detail&task=edit&cid[]='. $row->id_user_credit );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_user_credit');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_user_credit ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td style="text-align:center"><?php echo $row->id_user_credit; ?>&nbsp;</td>
<?php if($this->credit_type == "gc"){ ?>
      <td align="center"><a href=<?php echo $link."&credit_type=gc"; ?>><?php echo  $row->gift_cert; ?></a></td>
      <td align="center"><?php echo $row->gift_cert_name; ?>&nbsp;</td>
<?php } else { ?>
      <td align="center"><a href=<?php echo $link."&credit_type=uc"; ?>><?php echo  $row->user_id; ?></a></td>
      <td align="center"><?php echo $row->name." (".$row->username.")"; ?>&nbsp;</td>
<?php } ?>
      <td style="text-align:right"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<?php echo $row->balance; ?></span></td>
      <?php $k = 1 - $k; ?>
	  <td>&nbsp;</td>      
    </tr>
    <?php } 

?>
	<tfoot>
   	<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
  </table>
  <input type="hidden" name="controller" value="user_credit" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="credit_type" value="<?php echo $this->credit_type; ?>" />

  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
