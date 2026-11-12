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


	if($x_response_code == 2){
		$errsql = "insert into #__sv_apptpro3_errorlog (description) values('Authorise.net: This transaction has been declined. ".$x_invoice_num."')";
		try{
			$database->setQuery($errsql);
			$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "authnet_resp", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}
		// set booking to timeout so it can be reused
		$sql = "update #__sv_apptpro3_requests set request_status = \"timeout\", ".
			"admin_comment = 'Authorise.net: This transaction has been declined' ".
			"where id_requests = ".$x_invoice_num;
		try{
			$database->setQuery($sql);
			$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "authnet_resp", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}
		$mailer=null;
		$mailer = JFactory::getMailer();
		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
		$mailer->setSubject("This transaction has been declined");
		$mailer->setBody("Authorise.net: This transaction has been declined. ABPro request #".$x_invoice_num);
		if($mailer->send() != true){
			logIt("Error sending email");
		}
	}
?>

<HTML>
  <HEAD>
    <title>Payment Receipt</title>

    <link href="<?php echo JURI::base()."components/com_rsappt_pro3/payment_processors/authnet/authnet_receipt.css"?>" rel="stylesheet">
    <link href="<?php echo JURI::base()."components/com_rsappt_pro3/sv_apptpro.css"?>" rel="stylesheet">

  </HEAD>
  <BODY>
 
<?php
	if($x_response_code == 2){
	?>
    <table class="sv_receipt_table" width="500"  align="center" >
      <tr>
        <td align="center"><DIV id="divClickAway"><A HREF="<?php echo JURI::base().'index.php?option=com_rsappt_pro3&view='.$frompage?>">Return to  site</A></DIV></td>	
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td align="center"><h3>Transaction declined by credit card company.</h3></td>
      </tr>
      <tr>
        <td align="center">Appointment has not been booked.</td>
      </tr>
     </table> 
    <?php  
	} else {
?>		
<table class="sv_receipt_table" width="500"  align="center" >
  <tr>
    <td colspan="2" align="center"><DIV id="divClickAway"><A HREF="<?php echo JURI::base().'index.php?option=com_rsappt_pro3&view='.$frompage.'&task=authnet_return&req_id='.$x_invoice_num?>">Return to  site</A></DIV></td>	
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><h3>Thank you for your order</h3></td>
  </tr>
  <tr>
    <td colspan="2"><hr>You may print this receipt page for your records.
	</td>
  </tr>
    <td colspan="2"><hr><h4>Booking Information</h4></td>
  <tr>
    <td>Name:</td>
    <td><?php echo $x_first_name." ".$x_last_name ?></td>
  </tr>
  <tr>
    <td>Appointment:</td>
    <td><?php echo $x_description ?></td>
  </tr>
  <tr>
    <td>Booking ID:</td>
    <td><?php echo $x_invoice_num ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  </tr>
    <td colspan="2"><hr><h4>Payment Information</h4></td>
  <tr>
  <tr>
    <td>Name:</td>
    <td><?php echo $x_first_name  ?>&nbsp;<?php echo $x_last_name  ?></td>
  </tr>
  <tr>
    <td>Method:</td>
    <td><?php echo $x_card_type?>&nbsp;<?php echo $x_account_number?></td>
  </tr>
  <tr>
  <tr>
    <td>Authorization Code:</td>
    <td><?php echo $x_auth_code ?></td>
  </tr>
  <tr>
    <td>Amount:</td>
    <td><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?><?php echo $x_amount ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<?php } ?>
 
  </BODY>
</HTML>
<?php exit; ?>

