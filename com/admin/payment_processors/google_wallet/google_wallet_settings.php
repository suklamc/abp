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
<!-- Authnet settings insert -->       

<?php 
	// get settinsg data for this processor
	$sql = 'SELECT * FROM #__sv_apptpro3_google_wallet_settings;';
	try{
		$database->setQuery($sql);
		$google_wallet_settings = NULL;
		$google_wallet_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}
?>

<table class="table table-striped" >
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ENABLE');?>: </td>
          <td><select name="google_wallet_enable">
              <option value="Yes" <?php if($google_wallet_settings->google_wallet_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($google_wallet_settings->google_wallet_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ENABLE_HELP');?></td>
            <input type="hidden" name="accept_when_paid" value="Yes" />
        </tr>
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SERVER');?>: </td>
          <td><select name="google_wallet_server">
              <option value="Prod" <?php if($google_wallet_settings->google_wallet_server == "Prod"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_PROD');?></option>
              <option value="Test" <?php if($google_wallet_settings->google_wallet_server == "Test"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_TEST');?></option>
            </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SERVER_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SELLER_ID');?>:</td>
          <td><input type="text" size="20" maxsize="40" name="google_wallet_seller_id" value="<?php echo $google_wallet_settings->google_wallet_seller_id; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SELLER_ID_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SELLER_SECRET');?>:</td>
          <td><input type="text" size="30" maxsize="40" name="google_wallet_seller_secret" value="<?php echo $google_wallet_settings->google_wallet_seller_secret; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_SELLER_SECRET_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_BUTTON_URL');?>:</td>
          <td><input type="text" style="width:90%" size="70" maxsize="255" name="google_wallet_button_url" value="<?php echo $google_wallet_settings->google_wallet_button_url; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_BUTTON_URL_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ITEM_NAME');?>:</td>
          <td><input type="text" size="20" maxsize="40" name="google_wallet_item_name" value="<?php echo $google_wallet_settings->google_wallet_item_name; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ITEM_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ITEM_DESC');?>:</td>
          <td><input type="text" size="20" maxsize="40" name="google_wallet_item_description" value="<?php echo $google_wallet_settings->google_wallet_item_description; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GOOGLE_WALLET_ITEM_DESC_HELP');?></td>
        </tr>
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_GOOGLE_WALLET_FE_TAB');?>: </td>
          <td><select name="google_wallet_show_trans_in_fe">
              <option value="No" <?php if($google_wallet_settings->google_wallet_show_trans_in_fe == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
              <option value="Yes" <?php if($google_wallet_settings->google_wallet_show_trans_in_fe == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            <?php
				$k = 0;
				for($i=0; $i < sv_count_($user_groups ); $i++) {
				$user_group = $user_groups[$i];
				?>
            <option value="<?php echo $user_group->id; ?>"  <?php if($google_wallet_settings->google_wallet_show_trans_in_fe == $user_group->id){echo " selected='selected' ";} ?>><?php echo $user_group->title ?></option>
            <?php $k = 1 - $k; 
				} ?>
				</select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_GOOGLE_WALLET_FE_TAB_HELP');?></td>
        </tr>
        </table>
