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

if($isMobile != "yes"){ ?>
	
	<div id="sv_apptpro_view_checkout">
      <fieldset>
        <div>
          <label for="x_card_num"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_NUMBER');?></label>
          <input type="text" class="authnet_aim" size="15" name="x_card_num" id="x_card_num" value=""></input>
        </div>
        <div>
          <label for="x_exp_date"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_EXP');?></label>
          <input type="text" class="authnet_aim_exp" size="4" name="x_exp_date" id="x_exp_date" value=""></input>
        </div>
        <div>
          <label for="x_card_code"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_CCV');?></label>
          <input type="text" class="authnet_aim_ccv" size="4" name="x_card_code" id="x_card_code" value="" ></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label for="x_first_name"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_FIRST_NAME');?></label>
          <input type="text" class="authnet_aim" size="15" name="x_first_name" id="x_first_name" value="" ></input>
        </div>
        <div>
          <label for="x_last_name"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_LAST_NAME');?></label>
          <input type="text" class="authnet_aim" size="14" name="x_last_name" id="x_last_name" value=""></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label for="x_address"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_ADDRESS');?></label>
          <input type="text" class="authnet_aim" size="26" name="x_address" id="x_address" value=""></input>
        </div>
        <div>
          <label for="x_city"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CITY');?></label>
          <input type="text" class="authnet_aim" size="15" name="x_city" id="x_city" value=""></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label for="x_state"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_STATE');?></label>
          <input type="text" class="authnet_aim_state" size="4" name="x_state" id="x_state" value=""></input>
        </div>
        <div>
          <label for="x_zip"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_ZIP');?></label>
          <input type="text" class="authnet_aim_zip" size="9" name="x_zip" id="x_zip" value=""></input>
        </div>
        <div>
          <label for="x_country"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_COUNTRY');?></label>
          <input type="text" class="authnet_aim_country" size="22" name="x_country" id="x_country" value=""></input>
        </div>
      </fieldset>
      <div id="theButtons">
      <input type="submit" id="btnSubmit" value="<?php echo JText::_('RS1_INPUT_AUTHNET_AIM_SUBMIT');?>" >
      <input type="button" id="btnCancel" value="<?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CANCEL');?>" >
      </div>
	</div>

<?php } else { ?>
	<div id="sv_apptpro_view_checkout_mobile">
    	<table style="border-collapse:collapse">
        <tr><td>
        <div class="control-label">
          <label><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_NUMBER');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div class="controls">
          <input type="text" size="15" class="sv_aim_input" name="x_card_num" id="x_card_num" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div class="control-label">
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_EXP');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div class="controls">
          <input type="text" size="4" class="sv_aim_input" name="x_exp_date" id="x_exp_date" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div class="control-label">
          <label><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_CCV');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div class="controls">
          <input type="text" size="4" class="sv_aim_input" name="x_card_code" id="x_card_code" value="" ></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_FIRST_NAME');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="15" name="x_first_name" id="x_first_name" value="" ></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CC_LAST_NAME');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="14" name="x_last_name" id="x_last_name" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_ADDRESS');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="26" name="x_address" id="x_address" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CITY');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="15" name="x_city" id="x_city" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_STATE');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="4"  name="x_state" id="x_state" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_ZIP');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="9" name="x_zip" id="x_zip" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_AUTHNET_AIM_COUNTRY');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_aim_input" size="22" name="x_country" id="x_country" value=""></input>
        </div>
        </td></tr>
        </table>
      <div id="theButtons_mobile">
      <input type="submit" id="btnSubmit" value="<?php echo JText::_('RS1_INPUT_AUTHNET_AIM_SUBMIT');?>" >
      <input type="button" id="btnCancel" value="<?php echo JText::_('RS1_INPUT_AUTHNET_AIM_CANCEL');?>" >
      </div>

	</div>
<?php } ?>

    <script>
	jQuery(document).ready(function () {

		jQuery("#frmRequest").validate({
		  onfocusout: false,
		  rules: {
			x_card_num: {
			  required: true,
			  creditcard: true
			},
			x_exp_date: "required",
			x_card_code: "required",
			x_first_name: "required",
			x_last_name: "required",
			x_address: "required",
			x_city: "required",
			x_state: "required",
			x_zip: "required",
			x_country: "required"
		  },
		  messages: {
			x_card_num: "",
			x_exp_date: "",
			x_card_code: "",
			x_first_name: "",
			x_last_name: "",
			x_address: "",
			x_city: "",
			x_state: "",
			x_zip: "",
			x_country: ""
		  }
		});

		jQuery('#btnCancel').click(function() {
			jQuery( '#authnet_aim_form' ).hide();
			disable_enableSubmitButtons("enable");	
		});
		
		jQuery('#btnSubmit').click(function() {
				if(jQuery("#frmRequest").valid()){
					if(jQuery("#controller").val() != "cart"){
						document.getElementById("ppsubmit").value = "authnet_aim";
					    document.body.style.cursor = "wait"; 
						document.frmRequest.task.value = "process_booking_request";
						document.frmRequest.submit();
						return true;
					} else {
						localStorage["checkout_required"] = "yes";
						localStorage["checkout_sid"] = document.getElementById("sid").value;
						localStorage["checkout_dest"] = "authnet_aim";
						localStorage["checkout_cart_total"] = document.getElementById("display_total").innerHTML;
						localStorage["cart"] = "yes";	
						var toPass = jQuery('#x_card_num').val()+"|";
						toPass += jQuery('#x_exp_date').val()+"|";					
						toPass += jQuery('#x_card_code').val()+"|";					
						toPass += jQuery('#x_first_name').val()+"|";					
						toPass += jQuery('#x_last_name').val()+"|";					
						toPass += jQuery('#x_address').val()+"|";					
						toPass += jQuery('#x_city').val()+"|";					
						toPass += jQuery('#x_state').val()+"|";					
						toPass += jQuery('#x_zip').val()+"|";					
						toPass += jQuery('#x_country').val();					
					// JED automated check will fail anything with base 64 encoding :-(
					// change 164 to 64 in the following line to make authnet aim work
						localStorage["xfo"] = svBase64.encode(toPass);
						window.parent.cart_window_close();
						//window.parent.SqueezeBox.close();
					}
				}
			});
    });
    </script>
