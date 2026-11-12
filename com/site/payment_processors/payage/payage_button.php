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
?>

<?php 
	if(!isset($isMobile)){$isMobile = "no";};
	if(!isset($isCart)){$isCart = "no";};
	// get payage settings
	$sql = "SELECT * FROM #__sv_apptpro3_payage_settings;";
	try{
		$database->setQuery($sql);
		$payage_settings = NULL;
		$payage_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_button", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}
	
	// get enabled payage buttons
	$sql = "SELECT * FROM #__payage_accounts ".
		" WHERE account_group = ".$payage_settings->payage_group. 
		" AND published = 1 ".
		" AND account_currency = '".$payage_settings->payage_currency."'";
	try{
		$database->setQuery($sql);
		$payage_buttons = NULL;
		$payage_buttons = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_button", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	foreach($payage_buttons as $payage_button){
	// different Payage gateways require different info up front.
		switch($payage_button->gateway_shortname){
			case "Mollie":?>
				<input type="image" id="btnMollie" src="<?php echo $payage_button->button_image;?>" name="submit" alt="submit this form" 
				onclick="<?php echo $submit_function ?>('payage~<?php echo $payage_button->id;?>'); return false;"
				   <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> >
			<?php break; 
			case "PayPlug":?>
				<input type="image" id="btnPayPlug" src="<?php echo $payage_button->button_image;?>" name="submit" alt="submit this form" 
				onclick="<?php echo $submit_function ?>('payage~<?php echo $payage_button->id;?>'); return false;"
				   <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> >
			<?php break; 
			case "Barclaycard":?>
				<input type="image" id="btnBarclaycard" src="<?php echo $payage_button->button_image;?>" name="submit" alt="submit this form" 
				onclick="<?php echo $submit_function ?>('payage~<?php echo $payage_button->id;?>'); return false;"
				   <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> >
			<?php break; 
			case "Skrill":?>
				<input type="image" id="btnSkrill" src="<?php echo $payage_button->button_image;?>" name="submit" alt="submit this form" 
				onclick="<?php echo $submit_function ?>('payage~<?php echo $payage_button->id;?>'); return false;"
				   <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> >
			<?php break; 
			case "SagePay":?>
		   		<input type="image" id="btnSagePay" src="<?php echo $payage_button->button_image;?>" name="submit" alt="submit this form" 
		        onclick="checkout_sagepay(<?php echo $payage_button->id;?>); return false;"
		           <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> >
                    <link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/payment_processors/payage/sagepay_checkout.css" rel="stylesheet">
                    <div id="sagepay_form" class="svSagePay_Container<?php echo ($isMobile=='yes'?"_mobile":"")?>">
                    <?php
                        include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR."payage".DIRECTORY_SEPARATOR."sagepay_checkout_form.php";
                    ?>
                    </div>
                    <input type="hidden" id="sagepay_payage_account_id" value="<?php echo $payage_button->id;?>"  />
					<script>
                    function checkout_sagepay(account_id){
						var payage_account_id = account_id;
                        <?php if($isCart != "yes"){?>
                        result = validateForm();
                        if(result.indexOf('<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>')>-1){
                            jQuery('#errors').html("");
                        <?php } ?>	
                            if(jQuery('#grand_total').val() != ""){
                                 if(parseFloat(jQuery('#grand_total').val()) > 0){			 
                                    jQuery('#sagepay_form').show();
                                 }
                            }
                        <?php if($isCart != "yes"){?>	
                        } else {
                            disable_enableSubmitButtons("enable");	
                        }
                        return false;	
                        <?php } ?>	                       
                    }                    
                    </script>
                   
			<?php break; 
                   }?>
	<?php } ?>
	
