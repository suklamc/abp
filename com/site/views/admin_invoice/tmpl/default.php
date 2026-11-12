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

defined('_JEXEC') or die('Restricted access');


	$jinput = JFactory::getApplication()->input;
	$frompage = $jinput->getString( 'frompage', '' ); 
	$cid = null;
	if($frompage == "front_desk"){
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
	} else {
		$cid	= $jinput->get( 'cid_req', array(0), 'post', 'array' );
	}
	
	$frompage_item = $jinput->getInt( 'frompage_item', '' ); 

	//print_r($cid);
	
	$database = JFactory::getDBO(); 
	$user = JFactory::getUser();

	// get ccInvoice Contacts
	$sql = "SELECT id, name, email FROM #__ccinvoices_contacts ".
	"ORDER BY ordering;";
	try{
		$database->setQuery($sql);
		$cci_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_invoice", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get users 
	$sql = "SELECT id, name, username, email FROM #__users WHERE block = 0 ";
	try{
		$database->setQuery($sql);
		$users_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_invoice", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}

	// get bookings
	$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.name AS '.
		'ResourceName, #__sv_apptpro3_services.name AS ServiceName, '.
		'#__sv_apptpro3_categories.name AS CategoryName, '.
		"CONCAT(#__sv_apptpro3_requests.startdate,' ',#__sv_apptpro3_requests.starttime) as startdatetime, ".
		"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
		"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ".
		'#__sv_apptpro3_paypal_transactions.id_paypal_transactions AS id_transaction '.
		'FROM ('.
		'#__sv_apptpro3_requests LEFT JOIN '.
		'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
		'#__sv_apptpro3_resources.id_resources LEFT JOIN '.
		'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
		'#__sv_apptpro3_services.id_services LEFT JOIN '.
		'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
		'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
		'#__sv_apptpro3_paypal_transactions ON '.
		'#__sv_apptpro3_paypal_transactions.custom = '.
		'#__sv_apptpro3_requests.id_requests) '.
		' WHERE id_requests IN('.implode(",", $cid).')'.
		' AND request_status NOT IN("canceled", "deleted", "new", "timeout")'.
		' AND payment_status = "pending"';
	try{
		$database->setQuery($sql);
		$booking_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_invoice", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}
	
	// get config stuff
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_invoice", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	
?>

<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">


<script language="javascript">
	function contact_pick(){
		var contact_info;
		if(document.getElementById('cci_contact').checked == true){
			var sel_item = jQuery("#cci_selected_contact").val()
			contact_info = sel_item.split("|");			
			set_contact_info(contact_info[0], contact_info[1], contact_info[2], contact_info[3]);
		}
		if(document.getElementById('joomla_contact').checked == true){
			var sel_item = jQuery("#joomla_selected_contact").val()
			contact_info = sel_item.split("|");			
			set_contact_info(contact_info[0], contact_info[1], contact_info[2], contact_info[3]);
		}
		if(document.getElementById('other_contact').checked == true){
			set_contact_info("", "", "", "");
		}
	}
	
	function set_contact_info(contact_id, contact_name, contact_email, source){
		document.getElementById('sent_to_name').value = contact_name;
		document.getElementById('sent_to_email').value = contact_email;
		document.getElementById('sent_to_id').value = contact_id;
		document.getElementById('id_source').value = source;
	}
	
	function change_cci_contact(){
		document.getElementById('cci_contact').checked = true;
		contact_pick();
	}

	function change_joomla_contact(){
		document.getElementById('joomla_contact').checked = true;
		contact_pick();
	}

	function doCancel(){
		
		Joomla.submitform("cancel");
	}		

	function doCreateInvoice(pressbutton){
		if (pressbutton == 'create_and_send' || pressbutton == 'create_only'){			
			if(document.getElementById("bookings_to_invoice").value == "0"){				
				alert("<?php echo JText::_('RS1_ADMIN_SCRN_INVOICE_NO_BOOKINGS')?>");
				return true;
			}
			if(document.getElementById("sent_to_name").value == ""){				
				alert("<?php echo JText::_('RS1_ADMIN_SCRN_INVOICE_NAME_REQ')?>");
				return true;
			}
			if(document.getElementById("sent_to_email").value == ""){
				alert("<?php echo JText::_('RS1_ADMIN_SCRN_INVOICE_EMAIL_REQ')?>");
				return true;
			}
		}
		Joomla.submitform(pressbutton);
	}		

</script>


<form action="<?php echo JRoute::_($this->admin_invoice_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<table class="table table-striped" >
    <tr>
      <td colspan="3"  style="text-align:right" height="40px" class="fe_header_bar">
      	<!--<a href="#" onclick="doCreateInvoice('create_and_send');return(false);"><?php echo JText::_('RS1_ADMIN_TOOLBAR_ADMIN_INVOICE_CREATE_AND_SEND');?></a>
      	&nbsp;|&nbsp;&nbsp;--><a href="#" onclick="doCreateInvoice('create_only');return(false);"><?php echo JText::_('RS1_ADMIN_TOOLBAR_ADMIN_INVOICE_CREATE_ONLY');?></a>
      	&nbsp;|&nbsp;&nbsp;<a href="#" id="closeLink" onclick="doCancel();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3">
        <p><?php echo JText::_('RS1_ADMIN_SCRN_INVOICE_INTRO');?><br /></td>
    </tr>
	<tr>
		<td><input type="radio" name="who_to" id="cci_contact" onclick="contact_pick()"/>&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_CCI_CONTACT');?></td>
		<td>
        <select id="cci_selected_contact" name="cci_selected_contact" onchange="change_cci_contact()">
		<!--<option value="-1"><?php echo JText::_('RS1_ADMIN_SCRN_SEL_CONTACT');?></option>-->
		  <?php
            $k = 0;
            for($i=0; $i < sv_count_($cci_rows ); $i++) {
            $cci_row = $cci_rows[$i];
            ?>
          <option value="<?php echo $cci_row->id."|".$cci_row->name."|".$cci_row->email."|cci"; ?>" ?><?php echo JText::_(stripslashes($cci_row->name)); ?></option>
          <?php $k = 1 - $k; 
            } ?>
        
        </select>
        </td>
		<td width="50%"><?php echo JText::_('RS1_ADMIN_CCI_CONTACT_HELP');?>&nbsp;</td>
	</tr>        
	<tr>
      <td align="left"><input type="radio" name="who_to" id="joomla_contact" onclick="contact_pick()"/> <?php echo JText::_('RS1_ADMIN_JOOMLA_CONTACT');?></td>
      <td valign="top" align="left">
		<select name="joomla_selected_contact"  id="joomla_selected_contact" onchange="change_joomla_contact()">
          <!--<option value="-1"><?php echo JText::_('RS1_ADMIN_SCRN_SEL_USER');?></option>-->
          <?php
			$k = 0;
			for($i=0; $i < sv_count_($users_rows ); $i++) {
				$users_row = $users_rows[$i];
				?>
					<option value="<?php echo $users_row->id."|".$users_row->name."|".$users_row->email,"|joomla"; ?>"><?php echo stripslashes($users_row->name)." (".$users_row->username.")"; ?></option>
					<?php $k = 1 - $k; 
				} ?>
        </select>            
      </td>
		<td><?php echo JText::_('RS1_ADMIN_JOOMLA_CONTACT_HELP');?>&nbsp;</td>
    </tr>
	<tr>
		<td><input type="radio"  name="who_to" id="other_contact" checked="checked" onclick="contact_pick()"/>&nbsp;&nbsp;<?php echo JText::_('RS1_ADMIN_OTHER_CONTACT');?></td>
		<td colspan="2"><?php echo JText::_('RS1_ADMIN_OTHER_CONTACT_HELP');?></td>
	</tr>
    </table>
<hr />
	<table class="table table-striped">
    	<tr>
        	<td colspan="2">  <?php echo JText::_('RS1_ADMIN_SCRN_INVOICE_TO');?></td>
    	<tr>
	    	<td>Name: </td><td><input type="text" name="sent_to_name" id="sent_to_name" /></td>            
		</tr>        
    	<tr>
	    	<td>Email: </td><td><input type="text" name="sent_to_email" id="sent_to_email" />
            <input type="hidden" name="sent_to_id" id="sent_to_id" /><input type="hidden" name="id_source" id="id_source" /></td>            
		</tr>        
	</table>
<hr />
<?php echo JText::_('RS1_ADMIN_SCRN_BOOKING_FOR_INVOICE');?>    
	<table class="table table-striped" align="center">
	<thead>
    <tr>
      <th width="5%" class="small" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'); ?></th>
      <th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'); ?></th>
      <?php if($apptpro_config->admin_show_email=="Yes"){ ?>
      <th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_EMAIL_COL_HEAD'); ?></th>
      <?php } ?>
      <?php if($apptpro_config->admin_show_category=="Yes"){ ?>
      <th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_COL_HEAD'); ?><br /></th>
      <?php } ?>
      <?php if($apptpro_config->admin_show_resource=="Yes"){ ?>
      <th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_COL_HEAD'); ?><br /></th>
      <?php } ?>
      <?php if($apptpro_config->admin_show_service=="Yes"){ ?>
      <!--<th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD'); ?><br /></th>-->
      <?php } ?>
      <th class="small" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_FROM_COL_HEAD'); ?></th>
      <?php if($apptpro_config->admin_show_seats=="Yes"){ ?>
      <th class="small" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_SEATS_COL_HEAD'); ?></th>
      <?php } ?>
      <th class="small" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD');?></th>
      <?php if($apptpro_config->admin_show_pay_stat=="Yes"){ ?>
      <th class="small" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_PAYMENT_COL_HEAD');?></th>
      <?php } ?>
      <th class="small" style="text-align:center"><?php echo JText::_('RS1_ADMIN_SCRN_DUE_COL_HEAD');?></th>
    </tr>
    </thead>
	<?php
	$k = 0;
	for($i=0; $i < sv_count_($booking_rows ); $i++) {
	$row = $booking_rows[$i];

   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td class="small" align="center"><?php echo $row->id_requests; ?>&nbsp;</td>
      <td class="small" ><?php echo stripslashes($row->name); ?></td>
      <?php if($apptpro_config->admin_show_email=="Yes"){ ?>
      <td class="small" align="left"><?php echo $row->email; ?>&nbsp;</td>
      <?php } ?>      
      <?php if($apptpro_config->admin_show_category=="Yes"){ ?>
      <td class="small" align="left"><?php echo stripslashes($row->CategoryName); ?>&nbsp;</td>
      <?php } ?>      
      <?php if($apptpro_config->admin_show_resource=="Yes"){ ?>
      <td class="small" align="left"><?php echo stripslashes($row->ResourceName); ?>&nbsp;</td>
      <?php } ?>      
      <?php if($apptpro_config->admin_show_service=="Yes"){ ?>
      <!--<td class="small" align="left"><?php echo stripslashes($row->ServiceName); ?>&nbsp;</td>-->
      <?php } ?>
      <td class="small" align="left"><?php echo $row->display_startdate;?>&nbsp;<?php echo $row->display_starttime; ?></td>
      <?php if($apptpro_config->admin_show_seats=="Yes"){ ?>
      <td class="small" align="center"><?php echo $row->booked_seats; ?></td>
      <?php } ?>
      <td class="small" align="center"><?php echo translated_status($row->request_status); ?></td>
      <?php if($apptpro_config->admin_show_pay_stat=="Yes"){ ?>
      <td class="small" align="center"><?php echo translated_status($row->payment_status); ?></td>
      <?php } ?>
      <td class="small" style="text-align:center"><?php echo $row->booking_due; ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
  </table>    
  
</fieldset>
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="admin_invoice" />
  <input type="hidden" name="cid" value="<?php echo implode(",", $cid)?>" />
  <input type="hidden" name="frompage" value="<?php echo $frompage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $frompage_item ?>" />
  <input type="hidden" name="fromtab" value="0" />
  <input type="hidden" id="bookings_to_invoice" value="<?php echo sv_count_($booking_rows ) ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
<?php echo JHtml::_('form.token'); ?>

</form>
