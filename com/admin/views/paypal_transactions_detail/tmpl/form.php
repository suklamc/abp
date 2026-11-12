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
      <td colspan="2"><u><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_INTRO');?></u></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_ID');?></td>
      <td><?php echo $this->detail->txnid; ?>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_REQID');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="10" name="custom" value="<?php echo $this->detail->custom; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_FIRSTNAME');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="firstname" value="<?php echo stripslashes($this->detail->firstname); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_LASTNAME');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="lastname" value="<?php echo stripslashes($this->detail->lastname); ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_EMAIL');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="50" maxsize="100" name="buyer_email" value="<?php echo $this->detail->buyer_email; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_STREET');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="100" name="street" value="<?php echo $this->detail->street; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_CITY');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="50" name="city" value="<?php echo $this->detail->city; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PROVSTATE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="40" name="state" value="<?php echo $this->detail->state; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_POSTALZIP');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="11" name="zipcode" value="<?php echo $this->detail->zipcode; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PAYDATE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="30" maxsize="50" name="paymentdate" value="<?php echo $this->detail->paymentdate; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_AMOUNT');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="6" maxsize="6" name="mc_gross" value="<?php echo $this->detail->mc_gross; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_FEE');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="6" maxsize="5" name="mc_fee" value="<?php echo $this->detail->mc_fee; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_TAX');?></td>
      <td colspan="2" align=""><input type="text" readonly="readonly" size="6" maxsize="10" name="tax" value="<?php echo $this->detail->tax; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PAY_STATUS');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="15" name="paymentstatus" value="<?php echo $this->detail->paymentstatus; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_PENDING');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="20" maxsize="10" name="pendingreason" value="<?php echo $this->detail->pendingreason; ?>" /></td>
    </tr>

    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_CURRENCY');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="5" maxsize="5" name="mc_currency" value="<?php echo $this->detail->mc_currency; ?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PP_TXN_DETAIL_REASON');?></td>
      <td colspan="2"><input type="text" readonly="readonly" size="10" maxsize="10" name="reasoncode" value="<?php echo $this->detail->reasoncode; ?>" /></td>
    </tr>
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_PP_MEMO');?></td>
      <td colspan="2"><textarea rows="2" cols="30" readonly="readonly" ><?php echo stripslashes($this->detail->memo); ?></textarea></td>
    </tr>
  </table>
</fieldset>
  <input type="hidden" name="id_paypal_transactions" value="<?php echo $this->detail->id_paypal_transactions; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="paypal_transactions_detail" />
  <input type="hidden" name="frompage" value="<?php echo $this->frompage; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
