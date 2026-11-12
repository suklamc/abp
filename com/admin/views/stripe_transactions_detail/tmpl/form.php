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

?>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<fieldset class="adminform">
  <table border="0" cellpadding="2" cellspacing="0" class="table-striped">
    <tr>
      <td colspan="2"><u><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_INTRO');?></u></td>
    </tr>
    <tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_ID');?></td>
      <td><?php echo $this->detail->stripe_txn_id; ?>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_STATUS');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="status" value="<?php echo $this->detail->status; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_REQID');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="request_id" value="<?php echo $this->detail->request_id; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_CART');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="cart" value="<?php echo $this->detail->cart; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_AMOUNT');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="amount" value="<?php echo number_format($this->detail->amount/100,2); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_CURR');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="currency" value="<?php echo stripslashes($this->detail->currency); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_DESC');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_SELLER_MSG');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="seller_message" value="<?php echo $this->detail->seller_message; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_CC_BRAND');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="card_brand" value="<?php echo $this->detail->card_brand; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_CC_EXP');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="card_exp" value="<?php echo sprintf("%02d", $this->detail->card_exp_month)."/".$this->detail->card_exp_year; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_CC_LAST4');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="card_last4" value="<?php echo $this->detail->card_last4; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STRIPE_TXN_DETAIL_STAMP');?></td>
      <td colspan="2"><input type="text" readonly="readonly" name="stamp" value="<?php echo $this->detail->stamp; ?>" /></td>
    </tr>
  </table>
</fieldset>
  <input type="hidden" name="id_stripe_transactions" value="<?php echo $this->detail->id_stripe_transactions; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="stripe_transactions_detail" />
  <input type="hidden" name="frompage" value="<?php echo $this->frompage; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
