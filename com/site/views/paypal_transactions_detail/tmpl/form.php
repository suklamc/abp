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

	$showform= true;
	$listpage = $jinput->getString('listpage', 'list');
	$fromtab =  $jinput->getString('fromtab', '');		
	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

	}	

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pp_trans__detail_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	
	
?>
<?php if($showform){?>

<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript">
		
	function doCancel(){		
		Joomla.submitform("pp_close");
	}		
	
	</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE')." ".JText::_('RS1_ADMIN_SCRN_PP_TXN_TITLE');?></h3></td>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0" class="table-striped">
   <tr>
      <td colspan="3" align="right" height="40px"  class="fe_header_bar">&nbsp;
      <a href="#" onclick="doCancel();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?></a>&nbsp;&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">
        <p><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_INTRO');?><br /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_ID');?></td>
      <td><?php echo $this->detail->txnid; ?>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_REQID');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="10" name="custom" class="sv_apptpro_request_text" value="<?php echo $this->detail->custom; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_FIRSTNAME');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="firstname" class="sv_apptpro_request_text" value="<?php echo stripslashes($this->detail->firstname); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_LASTNAME');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="lastname" class="sv_apptpro_request_text" value="<?php echo stripslashes($this->detail->lastname); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_EMAIL');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="50" maxsize="100" name="buyer_email" class="sv_apptpro_request_text" value="<?php echo $this->detail->buyer_email; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_STREET');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="street" class="sv_apptpro_request_text" value="<?php echo $this->detail->street; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_CITY');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="50" name="city" class="sv_apptpro_request_text" value="<?php echo $this->detail->city; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PROVSTATE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="4" maxsize="3" name="state" class="sv_apptpro_request_text" value="<?php echo $this->detail->state; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_POSTALZIP');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="11" name="zipcode" class="sv_apptpro_request_text" value="<?php echo $this->detail->zipcode; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PAYDATE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="30" maxsize="50" name="paymentdate" class="sv_apptpro_request_text" value="<?php echo $this->detail->paymentdate; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_AMOUNT');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="6" maxsize="6" name="mc_gross" class="sv_apptpro_request_text" value="<?php echo $this->detail->mc_gross; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_FEE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="6" maxsize="5" name="mc_fee" class="sv_apptpro_request_text" value="<?php echo $this->detail->mc_fee; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_TAX');?></td>
      <td colspan="2" align=""><input type="text" readonly="readonly" size="6" maxsize="10" name="tax" class="sv_apptpro_request_text" value="<?php echo $this->detail->tax; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PAY_STATUS');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="15" name="paymentstatus" class="sv_apptpro_request_text" value="<?php echo $this->detail->paymentstatus; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PENDING');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="10" name="pendingreason" class="sv_apptpro_request_text" value="<?php echo $this->detail->pendingreason; ?>" /></td>
    </tr>

    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_CURRENCY');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="5" maxsize="5" name="mc_currency" class="sv_apptpro_request_text" value="<?php echo $this->detail->mc_currency; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_REASON');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="10" name="reasoncode" class="sv_apptpro_request_text" value="<?php echo $this->detail->reasoncode; ?>" /></td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_PP_MEMO');?></td>
      <td colspan="2"><textarea rows="2" cols="30" readonly="readonly" ><?php echo stripslashes($this->detail->memo); ?></textarea></td>
    </tr>
  </table>
  <input type="hidden" name="id_paypal_transactions" value="<?php echo $this->detail->id_paypal_transactions; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="current_tab" id="current_tab" value="<?php echo $current_tab; ?>" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $fromtab ?>" />
  
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
