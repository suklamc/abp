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
	if(!isset($isCart)){$isCart = "no";};
	// get google_wallet settings
	$sql = 'SELECT * FROM #__sv_apptpro3_google_wallet_settings;';
	try{
		$database->setQuery($sql);
		$google_wallet_settings = NULL;
		$google_wallet_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_button", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}
	$prefix = "google_wallet";
	  	if($google_wallet_settings->google_wallet_button_url != ""){ 
			$google_wallet_button_url = $google_wallet_settings->google_wallet_button_url;?>
	      		<input type="image" id="btnGWallet" align="top" src="<?php echo JURI::base( true )."/components/com_rsappt_pro3/payment_processors/google_wallet/".$google_wallet_settings->google_wallet_button_url?>" border="0" name="submit_gw" alt="submit this form" onclick="checkout_gw(); return false;"
                <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> style="border:none" >
      	<?php } else { ?>
      	<input type="button" class="button"  onclick="checkout_gw(); return false;" name="submit_gw" id="submit_gw" value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT_GOOGLE_WALLET');?>"
                <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo " disabled ";} ?> />
      	<?php } ?>

<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/payment_processors/google_wallet/google_wallet_checkout.css" rel="stylesheet">
<?php if($google_wallet_settings->server = "Test"){ ?>
	<script src="https://sandbox.google.com/checkout/inapp/lib/buy.js"></script>
<?php } else { ?>
	<script src="https://wallet.google.com/inapp/lib/buy.js"></script></script>
<?php } ?>    

<script>
	var new_id = "";
	var cc = "";
	<?php if($isCart != "yes"){ ?>
		function checkout_gw(){
			// not a cart booking 		
			result = validateForm();
			if(result.indexOf('<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>')>-1){
				jQuery('#errors').html("");
				if(jQuery('#grand_total').val() != ""){
					 if(jQuery('#grand_total').val() != "0.00"){	
						lockAndPay();
						return false;
					 }
				}
				return false;	
			} else {
				disable_enableSubmitButtons("enable");
			}
			return false;
		}
	
	<?php } else { ?>
		function checkout_gw(){
			if(jQuery('#grand_total').val() != ""){
				 if(parseFloat(jQuery('#grand_total').val()) > 0){	
					// this is a cart booking 
					cartPay();
					return false;
				 }
			}
		}
	<?php } ?>
	
	<?php if($isCart != "yes"){ ?>
	function lockAndPay(){
		// add a pending booking so teh slot is locked for this user then go to payment.
		// get all input elements
		pagedata = decodeURIComponent("&"+jQuery(document.frmRequest).find('select, textarea, input:not([name=option], [name=controller], [name=task])').serialize());
		
		if(document.getElementById("selected_resource_id") != null){
			// gad and wiz
			pagedata += "&resource="+document.getElementById("selected_resource_id").value;
		} else {
			// simple
			pagedata += "&resource="+document.getElementById("resources").value;
		}
		pagedata += "&ppsubmit=4b"; // google wallet 
	
		// add timestamp so IE caching will not block the server call in the case of rebooking the same slot	
		pagedata += "&x="+ new Date();
		
		//var pagedata = encodeURIComponent(pagedata);
		//alert(pagedata); 	
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			url: presetIndex()+"?option=com_rsappt_pro3&controller=booking_screen_gad&task=process_booking_request"+pagedata,
			async: false,
			data: pagedata,
			success: function(data) {
				temp = data.msg.split("|");
				new_id = temp[0];
				cc = temp[1];
				// now we can continue with payment
				// first make token
				amount_due = jQuery('#grand_total').val();
				if(jQuery('#deposit_amount').val() != ""){
					 if(jQuery('#deposit_amount').val() != "0.00"){	
						 amount_due = jQuery('#deposit_amount').val();
					 }
				}
				var calldata;
				calldata = "&gw_name=<?php echo $google_wallet_settings->google_wallet_item_name; ?>";
				calldata +=	"&gw_description=<?php echo $google_wallet_settings->google_wallet_item_description; ?>";
				calldata += "&gw_price="+amount_due;
				calldata += "&gw_req_id="+new_id;
	  			var url = presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=get_gw_token";
				jQuery.ajax({               
					type: "GET",
					dataType: 'json',
					url: url,
					async: false,
					data: calldata,
					success: function(data) {
						// next step
						//alert(data);
					  	google.payments.inapp.buy({
							'jwt'     : data,
							'success' : successHandler,
							'failure' : failureHandler
						});						
					},
					error: function (xhr, ajaxOptions, thrownError) {
						alert("Error on server call to get token:\n"+xhr.status + " - " + thrownError);
						releaseOnFailure(new_id);
					}
				 });
				
			},
			error: function(data) {
				alert(data.responseText);
			}					
		 });
		
	}

	function releaseOnFailure(id_to_delete){
		// if payment failed, cancel the pending booking
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			url: presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=gw_fail",
			data: "&req_id="+id_to_delete,
			success: function(data) {
				// pending booking cancelled
				//alert("pending canceled");
				disable_enableSubmitButtons("enable");				
			},
			error: function(data) {
				alert(data.responseText);
			}					
		 });		
	}
	
	//Success handler
	var successHandler = function(purchaseAction){
		//alert("Purchase completed successfully.");
		document.body.style.cursor = "wait";
		var calldata;
		calldata = "&req_id="+new_id;
		calldata +=	"&gw_name="+purchaseAction.request.name;
		calldata += "&gw_description="+purchaseAction.request.description;
		calldata += "&gw_order_id="+purchaseAction.response.orderId;
		calldata += "&gw_price="+purchaseAction.request.price;		
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			async: false,
			url: presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=gw_wrapit",
			data: calldata,
			success: function(data) {
				// go to confirmation
				document.body.style.cursor = "wait";
				document.location = "index.php?option=com_rsappt_pro3&view="+jQuery('#frompage').val()+"&Itemid="+jQuery('#frompage_item').val()+"&task=show_confirmation&req_id="+new_id+"&cc="+cc;
			},
			error: function(data) {
				alert(data.responseText);
			}					
		 });		
	}
	
	//Failure handler
	var failureHandler = function(purchaseActionError){
		alert("Purchase did not complete: "+purchaseActionError.response.errorType);
		releaseOnFailure(new_id);
	}	


	<?php } // end of if not cart?>	
	
	<?php if($isCart === "yes"){ ?>
	function cartPay(){
		document.body.style.cursor = "wait";	
		disable_cart_buttons();	
		var calldata;
		calldata = "&gw_name=<?php echo $google_wallet_settings->google_wallet_item_name; ?>";
		calldata +=	"&gw_description=<?php echo $google_wallet_settings->google_wallet_item_description; ?>";
		calldata += "&gw_price="+<?php echo $total?>;
		calldata += "&gw_req_id=cart";
		var url = presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=get_gw_token";
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			url: url,
			async: false,
			data: calldata,
			success: function(data) {
				// next step
				//alert(data);
				google.payments.inapp.buy({
					'jwt'     : data,
					'success' : successHandlerCart,
					'failure' : failureHandlerCart
				});						
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert("Error on server call to get token:\n"+xhr.status + " - " + thrownError);
				document.body.style.cursor = "default";		
				releaseOnFailure(new_id);
			}
		 });

	}
	
	//Success handler
	var successHandlerCart = function(purchaseAction){
		//alert("Purchase completed successfully.");
		document.body.style.cursor = "wait";
		var calldata;
		calldata = "&req_id=cart";
		calldata +=	"&gw_name="+purchaseAction.request.name;
		calldata += "&gw_description="+purchaseAction.request.description;
		calldata += "&gw_order_id="+purchaseAction.response.orderId;
		calldata += "&gw_price="+purchaseAction.request.price;		
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			async: true,
			url: presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=gw_wrapit",
			data: calldata,
			success: function(data) {
				// cannot go to confirmation from a popup so close then go there
				localStorage["gw_confirm"] = "yes";
				window.parent.cart_window_close();
				//window.parent.SqueezeBox.close();
			},
			error: function(data) {
				document.body.style.cursor = "default";		
				alert(data.responseText);
			}					
		 });		
	}
	
	//Failure handler
	var failureHandlerCart = function(purchaseActionError){
		alert("Purchase did not complete: "+purchaseActionError.response.errorType);
		enable_cart_buttons();	
	}	

	
	<?php } ?>	


</script>
