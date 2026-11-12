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

 if($apptpro_config->enable_notification_list == "Yes"){
	// 4.0.2 - purge notification table of bookings that have passed.

	$database = JFactory::getDBO();
	$sql = "DELETE FROM #__sv_apptpro3_notification_list WHERE NOW() > STR_TO_DATE(booking_start, '%Y-%m-%d');";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}	
 }
 
?>
<!--<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/jquery.maskedinput.js"></script>
<script>
jQuery(function($){
   $("#phone").mask("999-999-9999");
   $("#sms_phone").mask("999-999-9999");
});
</script>
-->

<div>
    <div class="sv_table">
     <div class="sv_table_row">
      <div class="sv_table_cell_name"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_NAME');?></label></div>
      <div class="sv_table_cell_value"><input name="name" type="text" id="name"  
           placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_NAME_PLACEHOLDER');?>'             
            size="40" maxlength="50" title="<?php echo JText::_('RS1_INPUT_SCRN_NAME_TOOLTIP');?>" value="<?php echo $name; ?>"
            <?php if($name != "" && $apptpro_config->name_read_only == "Yes"){echo " readonly='readonly'";}?>  />
            <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" /> <?php echo $required_symbol;?>
      </div>
     </div>
    <?php 
        // if cb_mapping value specified, fetch the cb data
        if($user->guest == false and $apptpro_config->phone_cb_mapping != "" and $jinput->getString('phone', '') == ""){
            $phone = getCBdata($apptpro_config->phone_cb_mapping, $user->id);
        } else if($user->guest == false and $apptpro_config->phone_profile_mapping != "" and $jinput->getString('phone', '') == ""){
            $phone = getProfiledata($apptpro_config->phone_profile_mapping, $user->id);
        } else if($user->guest == false and $apptpro_config->phone_js_mapping != "" and $jinput->getString('phone', '') == ""){
            $phone = getJSdata($apptpro_config->phone_js_mapping, $user->id);
        } else {
            $phone = $jinput->getString('phone');
        }
    ?>

    <?php if($apptpro_config->requirePhone == "Hide"){?>
        <input name="phone" type="hidden" id="phone" value="" />
    <?php } else { ?>   
     <div class="sv_table_row">
      <div class="sv_table_cell_name"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_PHONE');?></label></div>
      <div class="sv_table_cell_value"><input name="phone" type="text" id="phone" value="<?php echo $phone ?>" 
           placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_PHONE_PLACEHOLDER');?>'             
           <?php if($apptpro_config->phone_read_only == "Yes" /*&& $apptpro_config->phone_cb_mapping != ""*/){echo " readonly='readonly' ";}?>
            size="15" maxlength="20" title="<?php echo JText::_('RS1_INPUT_SCRN_PHONE_TOOLTIP');?>"/> <?php echo ($apptpro_config->requirePhone == "Yes"?$required_symbol:"")?>
      </div>
     </div>
    <?php } ?>
    
    <?php 
        // if cb_mapping value specified, fetch the cb data
		$sms_phone = "";
        if($user->guest == false and $apptpro_config->sms_phone_cb_mapping != "" and $jinput->getString('sms_phone', '') == ""){
            $sms_phone = getCBdata($apptpro_config->sms_phone_cb_mapping, $user->id);
        } else if($user->guest == false and $apptpro_config->sms_phone_profile_mapping != "" and $jinput->getString('sms_phone', '') == ""){
            $sms_phone = getProfiledata($apptpro_config->sms_phone_profile_mapping, $user->id);
        } else if($user->guest == false and $apptpro_config->sms_phone_js_mapping != "" and $jinput->getString('sms_phone', '') == ""){
            $sms_phone = getJSdata($apptpro_config->sms_phone_js_mapping, $user->id);
        } else {
            $sms_phone = $jinput->getString('sms_phone');
        }
    ?>
    
    <?php if(($apptpro_config->sms_to_resource_only == 'No') 
        && ($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes")){?>
     <div class="sv_table_row">
      <div class="sv_table_cell_name"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_SMS_LABEL');?></label></div>
      <div class="sv_table_cell_value"><input type="checkbox" name="use_sms" id="use_sms" onchange="checkSMS();" />&nbsp;
            <?php echo JText::_('RS1_INPUT_SCRN_SMS_CHK_LABEL');?>&nbsp;<br />
            <?php echo JText::_('RS1_INPUT_SCRN_SMS_PHONE');?>&nbsp;<input name="sms_phone" type="text" id="sms_phone" value="<?php echo $sms_phone; ?>"  
            size="15" maxlength="20" title="<?php echo JText::_('RS1_INPUT_SCRN_SMS_PHONE_TOOLTIP');?> " placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_PHONE_PLACEHOLDER');?>'
             />
             <?php if($apptpro_config->clickatell_show_code == "Yes"){ ?>
                <select name="sms_dial_code" id="sms_dial_code" class="sv_apptpro_request_dropdown" title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_SMS_CODE_TOOLTIP'));?>">
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($dial_rows ); $i++) {
                $dial_row = $dial_rows[$i];
                ?>
          <option value="<?php echo $dial_row->dial_code; ?>"  <?php if($apptpro_config->clickatell_dialing_code == $dial_row->dial_code){echo " selected='selected' ";} ?>><?php echo $dial_row->country." - ".$dial_row->dial_code ?></option>
              <?php $k = 1 - $k; 
                } ?>
            </select>&nbsp;
             <?php } else { ?>
             <input type="hidden" name="sms_dial_code" id="sms_dial_code" value="<?php echo $apptpro_config->clickatell_dialing_code?>" />
             <?php } ?>
             <input type="hidden" name="sms_reminders" id="sms_reminders" value="No" />
      </div>
     </div>
    <?php }?>
    <?php if($apptpro_config->requireEmail == "Hide"){?>
        <input name="email" type="hidden" id="email" value="" />
    <?php } else { ?>
     <div class="sv_table_row">
      <div class="sv_table_cell_name"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_EMAIL');?></label></div>
      <div class="sv_table_cell_value"><input name="email" type="text" id="email" value="<?php echo $email ?>" 
           placeholder= '<?php echo JText::_('RS1_INPUT_SCRN_EMAIL_PLACEHOLDER');?>'             
             title="<?php echo JText::_('RS1_INPUT_SCRN_EMAIL_TOOLTIP');?>" size="40" maxlength="50"
              > <?php echo ($apptpro_config->requireEmail == "Yes"?$required_symbol:"")?>
      </div>
     </div>
    <?php } ?>
    </div>
</div>
  <input type="hidden" id="cancel_reason_prompt" value="<?php echo JText::_('RS1_CANCEL_REASON');?>" />
