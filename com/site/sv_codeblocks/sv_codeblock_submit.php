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

	$show_non_pay = true;
	$non_pay_group = $apptpro_config->non_pay_btn_group;

	if($non_pay_group != "" && $pay_proc_enabled == true ){
		// NOT set to 'Everyone'
		// if not logged in, hide
		if($user->guest){
			$show_non_pay = false;
		} else {
			// logged in so we can loo at the user's groups
			// get user's groups
			$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE ".
				"user_id=".$user->id." AND group_id=".$non_pay_group;	
			try{		
				$database->setQuery($sql);
				$my_groups = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			if($my_groups == 0){
				$show_non_pay = false;
			}
		}
	}
	//echo $show_non_pay;
?>
<?php	$block_selected_slot_codepath = JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_selected_slot.php"; ?> 

<div id="sv_codeblock_submit">
  <p>
    <?php include $block_selected_slot_codepath;  ?>
  </p>
  <table align="center" width="100%">
    <?php if($apptpro_config->enable_coupons == "Yes"){ ?>
    <tr class="submit_section">
      <td style="vertical-align:top"><?php echo JText::_('RS1_INPUT_SCRN_COUPONS');?></td>
      <td colspan="3"><input name="coupon_code" type="text" id="coupon_code" value="" size="20" maxlength="80" 
                          placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_COUPON_PLACEHOLDER');?>'             
                          title="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_TOOLTIP');?>" />
        <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_BUTTON');?>" onclick="getCoupon()" />
        <div id="coupon_info"></div>
        <input type="hidden" id="coupon_value" />
        <input type="hidden" id="coupon_units" /></td>
    </tr>
    <?php } ?>
    <?php if($apptpro_config->enable_gift_cert == "Yes"){ ?>
    <tr class="submit_section">
      <td style="vertical-align:top"><?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT');?></td>
      <td colspan="3"><input name="gift_cert" type="text" id="gift_cert" value="" size="20"  
                          placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_PLACEHOLDER');?>'             
                          title="<?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_TOOLTIP');?>" />
        <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_GIFT_CERT_BUTTON');?>" onclick="getGiftCert()" />
        <div id="gift_cert_info"></div>
        <input type="hidden" id="gift_cert_bal" /></td>
    </tr>
    <?php } ?>
    <?php if($pay_proc_enabled || $apptpro_config->non_pay_booking_button == "DAB" ||  $apptpro_config->non_pay_booking_button == "DO" || $show_non_pay){ ?>
    <tr class="submit_section">
      <td class="sv_apptpro_request_label" colspan="4" style="height:auto; vertical-align:top"><div id="calcResults" style="visibility:hidden; display:none; height:auto;">
          <table style="border:1px solid black; width:300px; margin:auto" class="calcResults_outside">
            <tr class="calcResults_header">
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label class="sv_apptpro_request_label" id="res_rate_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label id="res_hours_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_UNITS');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label class="sv_apptpro_request_label" id="res_rate_total_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_TOTAL');?></label></td>
            </tr>
            <tr style="text-align:right">
              <td style="border-bottom:solid 1px; border-right:solid 1px; height:auto;"><div style="float:right">
                  <div style="display: table-cell; padding-left:0px;">
                    <label class="sv_apptpro_request_label" id="currency_label"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></label>
                  </div>
                  <div style="display: table-cell; padding-left:5px;">
                    <label id="res_rate"></label>
                  </div>
                </div></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:center"><label id="res_hours"></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><div style="float:right">
                  <div style="display: table-cell; padding-left:0px;">
                    <label class="sv_apptpro_request_label" id="currency_label"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></label>
                  </div>
                  <div style="display: table-cell; padding-left:5px;">
                    <label id="res_total"></label>
                  </div>
                </div></td>
            </tr>
            <?php if ($extras_row_count->count > 0 ){?>
            <tr>
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="extras_fee_label"><?php echo JText::_('RS1_INPUT_SCRN_EXTRAS_FEE');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><div style="display: table-cell; padding-left:5px; float:right">
                  <label id="extras_fee"></label>
                </div></td>
            </tr>
            <?php } ?>
            <?php if ($apptpro_config->additional_fee != 0.00 ){?>
            <tr>
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="additional_fee_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_ADDITIONAL_FEE');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
                  <label id="res_fee"></label>
                </div></td>
            </tr>
            <?php } ?>
            <?php if($apptpro_config->enable_coupons == "Yes" || $apptpro_config->enable_eb_discount == "Yes"){ ?>
            <tr>
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="discount_label"><?php echo JText::_('RS1_INPUT_SCRN_DISCOUNT');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
                  <label id="discount"></label>
                </div></td>
            </tr>
            <?php } ?>
            <?php if($apptpro_config->enable_gift_cert == "Yes"){ ?>
            <tr style="text-align:right" id="gc_row">
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="gc_credit_label"><?php echo JText::_('RS1_INPUT_SCRN_GC_CREDIT');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
                  <label id="gc_credit"></label>
                </div></td>
            </tr>
            <?php } ?>
            <?php if($user_credit != NULL){ ?>
            <tr style="text-align:right" id="uc_row">
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="credit_label"><?php echo JText::_('RS1_INPUT_SCRN_USER_CREDIT');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
                  <label id="uc_credit"></label>
                </div></td>
            </tr>
            <?php } ?>
            <tr style="text-align:right">
              <td style="border-bottom:solid 1px;">&nbsp;
                <input type="hidden" id="additionalfee" value="<?php echo $apptpro_config->additional_fee ?>" />
                <input type="hidden" id="feerate" value="<?php echo $apptpro_config->fee_rate ?>" />
                <input type="hidden" id="rateunit" value="<?php echo $apptpro_config->fee_rate ?>" />
                <input type="hidden" id="grand_total" name="grand_total" value="<?php echo $grand_total ?>" /></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="total_label"><?php echo JText::_('RS1_INPUT_SCRN_RES_RATE_TOTAL');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="display: table-cell; padding-left:5px; float:right">
                  <div style="display: table-cell; padding-left:5px;">
                    <label class="sv_apptpro_request_label" id="currency_label"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></label>
                  </div>
                  <div style="display: table-cell; padding-left:5px;">
                    <label id="res_grand_total"></label>
                  </div>
                </div></td>
            </tr>
            <tr style="text-align:right" id="deposit_only">
              <td style="border-bottom:solid 1px;">&nbsp;</td>
              <td style="border-bottom:solid 1px; border-right:solid 1px; text-align:right"><label class="sv_apptpro_request_label" id="deposit_label"><?php echo JText::_('RS1_INPUT_SCRN_DEPOSIT');?></label></td>
              <td style="border-bottom:solid 1px; border-right:solid 1px;"><div style="float:right">
                  <div style="display: table-cell; padding-left:0px;">
                    <label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></label>
                  </div>
                  <div style="display: table-cell; padding-left:5px;">
                    <label id="display_deposit_amount"></label>
                  </div>
                </div>
                <input type="hidden" id="deposit_amount" name="deposit_amount" value="0.00" /></td>
          </table>
        </div></td>
    </tr>
    <?php } ?>
    <tr class="submit_section">
      <td></td>
      <td colspan="3"><div id="errors" class="sv_apptpro_errors"><?php echo $err ?></div></td>
    </tr>
    <tr class="submit_section" align="center">
      <td colspan="4" style="vertical-align:top; text-align:center" ><input  name="cbCopyMe" type="hidden" value="yes"  />
        <div align="center" >
          <div id="submit_buttons" <?php echo ($pay_proc_enabled ? "style=\"display:table-cell;\"":"")?> >
            <?php if($apptpro_config->cart_enable == "Yes" || $apptpro_config->cart_enable == "Public"){ ?>
            <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_ADD_TO_CART');?>" id="btnAddToCart" onclick="addToCart(); return false;"
                        <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
            <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_VIEW_CART');?>" onclick="viewCart(); return false;"
                        <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
            <?php } else { ?>
            <?php if($show_non_pay){ ?>
            <?php if( ($apptpro_config->non_pay_booking_button == "Yes" || $pay_proc_enabled == false )
                        && $apptpro_config->non_pay_booking_button != "DAB" ){  ?>
            <input type="submit" class="button"  name="submit0" id="submit0" onclick="return doSubmit(0);" 
                            value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT');?>" 
                              <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
            <?php } ?>
            <?php if($apptpro_config->non_pay_booking_button == "DAB"){  ?>
            <input type="submit" class="button"  name="submit3" id="submit4" onclick="return doSubmit(1);" 
                            value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT');?>" 
                              <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
            <input type="hidden" id="PayPal_mode" value="DAB" />
            <?php } ?>
            <?php } ?>
            <?php // put a hidden button on screen in case amount due is $0 and we hide the payment processor button(s)
                if( $apptpro_config->non_pay_booking_button == "No" && $pay_proc_enabled == true) {  ?>
            <div id="hidden_submit" style="display:none; visibility:hidden">
              <input type="submit" class="button"  name="submit0" id="submit0" onclick="return doSubmit(0);" 
                        value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT');?>" 
                          <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> />
            </div>
            <?php } ?>
          </div>
          <div id="pay_proc_buttons" style="display: table-cell;">
            <?php // Step through all the enabled payment processors and drop in book now buttons.
                foreach($pay_procs as $pay_proc){ 
                    // get settings 
                    $prefix = $pay_proc->prefix;
                    $sql = "SELECT * FROM #__sv_apptpro3_".$pay_proc->config_table;
                    try{
                        $database->setQuery($sql);
                        $pay_proc_settings = NULL;
                        $pay_proc_settings = $database -> loadObject();
                    } catch (RuntimeException $e) {
                        logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
                        echo JText::_('RS1_SQL_ERROR').$e->getMessage();
                        exit;
                    }
                    $enable = $prefix."_enable";
                    if($pay_proc_settings->$enable == "Yes"){
                        $submit_function = "doSubmit";
                        include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR.$pay_proc->prefix.DIRECTORY_SEPARATOR.$pay_proc->prefix."_button.php";
                    }
                }?>
          </div>
        </div>
        <?php } ?></td>
    </tr>
  </table>
  <?php 
            if($apptpro_config->recaptcha_enabled == 'Yes'){ 
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('captcha');
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onInit','dynamic_recaptcha_1');
                $recapt_params = new JRegistry(JPluginHelper::getPlugin('captcha', 'recaptcha')->params);
                ?>
  <div class="submit_section">
    <div id="dynamic_recaptcha_1" align="center" class="sv_recaptcha g-recaptcha" data-sitekey="<?php echo $recapt_params->get('public_key', ''); ?>"
    data-size="<?php echo $recapt_params->get('size', ''); ?>"></div>
    <input type="hidden" id="recap_msg" value="<?php echo JText::_('RS1_INPUT_SCRN_RECAPTCHA_REQUIRED');?>" />
  </div>
  <?php } ?>
</div>
