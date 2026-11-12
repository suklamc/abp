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
	// get stripe settings
	$sql = 'SELECT * FROM #__sv_apptpro3_stripe_settings;';
	try{
		$database->setQuery($sql);
		$stripe_settings = NULL;
		$stripe_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_button", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}
	
  	if($stripe_settings->stripe_button_image != ""){ 
		$stripe_button_url = $stripe_settings->stripe_button_image;?>
      		<input type="image" id="btnStripe"  align="top" src="<?php echo $stripe_button_url ?>" border="0" name="submit" alt="submit this form" 
            onclick="doStripe(); return false;"
               <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> style="border:none" >
     	<?php } else { ?>
     	<input type="button" class="button" onclick="doStripe(); return false;" name="submit5" id="btnStripe" value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT_STRIPE');?>"
        	title="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT_STRIPE_HELP');?>"
                <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo " disabled ";} ?> />
      	<?php } ?>

	<script src="https://checkout.stripe.com/checkout.js"></script>
    <input type="hidden" name="stripeToken" id="stripeToken"/>
	
	<script>
    function doStripe(){
		var pk = "";
	    // only call stripe if grand_total > $0
		if(parseFloat(jQuery("#grand_total").val()) == 0){
			doSubmit(0); // submit as no charge booking
			return fasle;
		}
		// check to see if there are resource specific keys

		if(document.getElementById("resources")!=null){
			if(document.getElementById("mode")===null){
				// non gad
				res_id = document.getElementById("resources").value;
			} else {
				if(document.getElementById("gad_mobile_simple")!=null){
					// gad is in use but mobile device is switched to simple
					res_id = document.getElementById("resources").value;
				} else {
					res_id = document.getElementById("selected_resource_id").value;
				}
			}
		}
		if(res_id != ""){
			pk = aryStripePublicKeys[res_id];
		}
		if(pk == ""){
			pk = "<?php echo $stripe_settings->stripe_pk ?>";
		}
		disable_enableSubmitButtons("disable");
		var handler = StripeCheckout.configure({
		  //key: '<?php echo $stripe_settings->stripe_pk ?>',
		  key: pk,
		  image: '<?php echo $stripe_settings->stripe_image ?>',
		  token: function(token) {
			  //alert(token.id);
			document.getElementById("stripeToken").value = token.id;
			if(document.getElementById("errors") != null){
				document.getElementById("errors").innerHTML = document.getElementById("wait_text").value		
			}
			if(jQuery("#view").val() == "cart"){
				disable_enableSubmitButtons("disable");
				doCheckout(5); 
				return false;
			} else {
				disable_enableSubmitButtons("disable");
				doSubmit(5);
			}
		  }
		});		
		if(jQuery("#view").val() == "cart"){
			// for cart we do not validate here, validation was done on adding to cart
			  // Open Checkout with further options:
				handler.open({
					name: '<?php echo $stripe_settings->stripe_company_name ?>',
					description: '<?php echo $stripe_settings->stripe_billing_description ?>',
					currency: '<?php echo $stripe_settings->stripe_currency ?>',
					amount: parseFloat(jQuery("#grand_total").val())*100
				});
		} else {
			document.getElementById("errors").innerHTML = document.getElementById("wait_text").value      
			var stripe_amount = 0.00;
			if(document.getElementById("deposit_amount") != null){
				stripe_amount = parseFloat(jQuery("#deposit_amount").val())*100;
			} else {
				stripe_amount = parseFloat(jQuery("#grand_total").val())*100;
			}
			result = validateForm();
			if(result.indexOf('<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>')>-1){
//				var handler = StripeCheckout.configure({
//				  //key: '<?php echo $stripe_settings->stripe_pk ?>',
//				  key: pk,
//				  image: '<?php echo $stripe_settings->stripe_image ?>',
//				  token: function(token) {
//					  //alert(token.id);
//					document.getElementById("stripeToken").value = token.id;
//					if(document.getElementById("errors") != null){
//						document.getElementById("errors").innerHTML = document.getElementById("wait_text").value		
//					}
//					if(jQuery("#view").val() == "cart"){
//						disable_enableSubmitButtons("disable");
//						doCheckout(5); 
//						return false;
//					} else {
//						disable_enableSubmitButtons("disable");
//						doSubmit(5);
//					}
//				  }
//				});

			  // Open Checkout with further options:
				handler.open({
	  				name: '<?php echo $stripe_settings->stripe_company_name ?>',
					description: '<?php echo $stripe_settings->stripe_billing_description ?>',
					receipt_email: jQuery("#email").val(),
					currency: '<?php echo $stripe_settings->stripe_currency ?>',
					amount: stripe_amount,
					closed: function () {
						if(jQuery("#stripeToken").val() == ""){
							document.getElementById("errors").innerHTML = ""		
							disable_enableSubmitButtons("enable");
							return false;
						}
                	}
				});
			} else {
				disable_enableSubmitButtons("enable");
				return false;
			}
			return false;
		}
    }
    </script>
      
    <script>  

	// Close Checkout on page navigation:
	window.addEventListener('popstate', function() {
	  handler.close();
	});	
    </script>
