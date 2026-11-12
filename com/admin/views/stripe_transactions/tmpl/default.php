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

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_stripe_trans_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}

?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
</script>
<script>
	function cleardate(){
		document.getElementById("startdateFilter").value="";
		document.getElementById("enddateFilter").value="";
		Joomla.submitbutton('');
		return false;		
	}
</script>

<script language="javascript" type="text/javascript">
	function setReqID(id){
		document.getElementById("xid").value = id;
		Joomla.submitform('get_booking');
		return false;
	}
</script>

<script language="javascript" type="text/javascript">
function myonsubmit(){
	task = document.adminForm.task.value;
	var form = document.adminForm;
   if (task)    
	if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove') )
	 {
	  form.controller.value="stripe_transactions_detail";
	 }
	return true;	
	//
}
</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<?php echo JText::_('RS1_ADMIN_STRIPE_LIST');?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
  <table class="table table-striped" >
	<thead>
    <tr>
        <td colspan="11" ><div style="float:right">
            <?php echo JText::_('RS1_ADMIN_APPT_LIST_DATE_RANGE');?>
      <input class="sv_date_box" type="text" size="12" maxsize="12" name="startdateFilter" id="startdateFilter" readonly="readonly" style="background-color:#FFFFCC"
        value="<?php echo $this->filter_startdate; ?>" onchange="this.form.submit();"/>
        <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].startdateFilter,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
        <input class="sv_date_box" type="text" size="12" maxsize="12" name="enddateFilter" id="enddateFilter" readonly="readonly" style="background-color:#FFFFCC"
        	value="<?php echo $this->filter_enddate; ?>" onchange="this.form.submit();"/>
        <a href="#" id="anchor2" onclick="cal.select(document.forms['adminForm'].enddateFilter,'anchor2','yyyy-MM-dd'); return false;"
					 name="anchor2"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>        
        <a href="#" onclick="cleardate();"><?php echo JText::_('RS1_ADMIN_APPT_LIST_CLEAR_DATES');?></a>&nbsp;&nbsp;</div>
        </td>
	</tr>
    <tr>
      <th width="3%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_TXN_COL_HEAD'), 'stripe_txn_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_REQ_ID_COL_HEAD'), 'request_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_AMOUNT_COL_HEAD'), 'amount', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_SELL_MSG_COL_HEAD'), 'seller_message', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th class="title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_TIMESTAMP_COL_HEAD'), 'stamp', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
 	$published 	= JHtml::_('jgrid.published', $row->published, $i );
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=stripe_transactions_detail&task=edit&cid[]='. $row->id_stripe_transactions );
	$link_to_request = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=requests_detail&task=edit&cid[]='. $row->request_id."&frompage=Stripe" );
	$checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_stripe_transactions');
  ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_stripe_transactions; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
      <td align="center"><a href=<?php echo $link; ?>><?php echo $row->stripe_txn_id; ?></a></td>
      <td align="center"><a href=<?php echo $link_to_request; ?>><?php echo $row->request_id; ?></a>&nbsp;</td>
      <td align="right"><?php echo number_format($row->amount/100,2); ?>&nbsp;</td>
      <td align="center"><?php echo $row->seller_message; ?>&nbsp;</td>
      <td><?php echo $row->stamp; ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

	?>
  </table>
  <input type="hidden" name="controller" value="stripe_transactions" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="xid" id="xid" value="" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
