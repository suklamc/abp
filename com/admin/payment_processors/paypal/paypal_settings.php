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
<!-- PayPal settings insert -->       
<?php 
	// get settinsg data for their processor
	$sql = 'SELECT * FROM #__sv_apptpro3_paypal_settings;';
	try{
		$database->setQuery($sql);
		$paypal_settings = NULL;
		$paypal_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}

	// get groups
	if(in_array($database->replacePrefix('#__usergroups'), $tables)){
		try{
			$database->setQuery("SELECT title, id FROM #__usergroups WHERE id>2 ORDER BY title" );
			$user_groups = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_config_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}
		
?>
	
        <table class="table table-striped" >
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ENABLE');?>: </td>
          <td><select name="paypal_enable">
              <option value="Yes" <?php if($paypal_settings->paypal_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($paypal_settings->paypal_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
    <!--          <option value="Opt" <?php if($paypal_settings->paypal_enable == "Opt"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL');?></option>
              <option value="DO" <?php if($paypal_settings->paypal_enable == "DO"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_DISPLAY_ONLY');?></option>
              <option value="DAB" <?php if($paypal_settings->paypal_enable == "DAB"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_DISPLAY_AND_BLOCK');?></option>
    -->        </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ENABLE_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_BUTTON');?>:</td>
          <td><input type="text" style="width:90%" size="70" maxsize="255" name="paypal_button_url" value="<?php echo $paypal_settings->paypal_button_url; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_BUTTON_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_CUR_CODE');?>: </td>
          <td><select name="paypal_currency_code"> 
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($currency_rows ); $i++) {
                $currency_row = $currency_rows[$i];
                ?>
                      <option value="<?php echo $currency_row->code; ?>" <?php if($paypal_settings->paypal_currency_code == $currency_row->code){echo " selected='selected' ";} ?>><?php echo $currency_row->code." - ".$currency_row->description; ?></option>
                      <?php $k = 1 - $k; 
                } ?>
            </select></td>
            <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ACCOUNT');?>:</td>
          <td><input type="text" style="width:90%" size="70" maxsize="255" name="paypal_account" value="<?php echo $paypal_settings->paypal_account; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ACCOUNT_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_SANDBOX');?>:</td>
          <td><input type="text" readonly="readonly" style="width:90%" size="70" maxsize="255" name="paypal_sandbox_url" value="<?php echo $paypal_settings->paypal_sandbox_url; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_SANDBOX_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_PROD');?>:</td>
          <td><input type="text" readonly="readonly" style="width:90%" size="70" maxsize="255" name="paypal_production_url" value="<?php echo $paypal_settings->paypal_production_url; ?>" /></td>
          <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_USE_SAND');?>: </td>
          <td><select name="paypal_use_sandbox">
              <option value="Yes" <?php if($paypal_settings->paypal_use_sandbox == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($paypal_settings->paypal_use_sandbox == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            </select></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_USE_SAND_HELP');?><td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_YOUR_LOGO');?>:</td>
          <td><input type="text" style="width:90%" size="70" maxsize="255" name="paypal_logo_url" value="<?php echo $paypal_settings->paypal_logo_url; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_YOUR_LOGO_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ITEM_NAME');?>:</td>
          <td><input type="text" size="70" maxsize="126" name="paypal_itemname" value="<?php echo $paypal_settings->paypal_itemname; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_ITEM_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME');?> 0:</td>
          <td><input type="text" size="70" maxsize="67" name="paypal_on0" value="<?php echo $paypal_settings->paypal_on0; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_VALUE');?> 0:</td>
          <td><input type="text" size="70" maxsize="200" name="paypal_os0" value="<?php echo $paypal_settings->paypal_os0; ?>" /></td>
          <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME');?> 1:</td>
          <td><input type="text" size="70" maxsize="67" name="paypal_on1" value="<?php echo $paypal_settings->paypal_on1; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_VALUE');?> 1:</td>
          <td><input type="text" size="70" maxsize="200" name="paypal_os1" value="<?php echo $paypal_settings->paypal_os1; ?>" /></td>
          <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME');?> 2:</td>
          <td><input type="text" size="70" maxsize="67" name="paypal_on2" value="<?php echo $paypal_settings->paypal_on2; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_VALUE');?> 2:</td>
          <td><input type="text" size="70" maxsize="200" name="paypal_os2" value="<?php echo $paypal_settings->paypal_os2; ?>" /></td>
          <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME');?> 3:</td>
          <td><input type="text" size="70" maxsize="67" name="paypal_on3" value="<?php echo $paypal_settings->paypal_on3; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_OPTIONAL_VALUE');?> 3:</td>
          <td><input type="text" size="70" maxsize="200" name="paypal_os3" value="<?php echo $paypal_settings->paypal_os3; ?>" /></td>
          <td></td>
        </tr>
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_FE_TAB');?>: </td>
          <td><select name="paypal_show_trans_in_fe">
              <option value="No" <?php if($paypal_settings->paypal_show_trans_in_fe == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
              <option value="Yes" <?php if($paypal_settings->paypal_show_trans_in_fe == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            <?php
				$k = 0;
				for($i=0; $i < sv_count_($user_groups ); $i++) {
				$user_group = $user_groups[$i];
				?>
            <option value="<?php echo $user_group->id; ?>"  <?php if($paypal_settings->paypal_show_trans_in_fe == $user_group->id){echo " selected='selected' ";} ?>><?php echo $user_group->title ?></option>
            <?php $k = 1 - $k; 
				} ?>
				</select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYPAL_FE_TAB_HELP');?></td>
        </tr>
      </table>
