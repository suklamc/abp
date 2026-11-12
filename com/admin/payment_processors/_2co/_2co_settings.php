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

<!-- 2co settings insert -->  

<?php 
	// get settinsg data for this processor
	$sql = 'SELECT * FROM #__sv_apptpro3__2co_settings;';
	try{
		$database->setQuery($sql);
		$_2co_settings = NULL;
		$_2co_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}
?>

<table class="table table-striped" >
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_2CO_ENABLE');?>: </td>
          <td><select name="_2co_enable">
              <option value="Yes" <?php if($_2co_settings->_2co_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($_2co_settings->_2co_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_2CO_ENABLE_HELP');?></td>
            <input type="hidden" name="accept_when_paid" value="Yes" />
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_2CO_DEMO');?>:</td>
          <td><select name="_2co_demo">
              <option value="No" <?php if($_2co_settings->_2co_demo == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
              <option value="Yes" <?php if($_2co_settings->_2co_demo == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select></td>
            <td><?php echo JText::_('RS1_ADMIN_2CO_DEMO_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_2CO_ACCOUNT_ID');?>:</td>
          <td><input type="text" size="20" maxsize="40" name="_2co_account" value="<?php echo $_2co_settings->_2co_account; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_2CO_ACCOUNT_ID_HELP');?></td>
        </tr>
        </tr>
    <!-- Currency set in your 2CO account   
        <tr>
          <td valign="top"><?php echo JText::_('RS1_ADMIN_2CO_CURRENCY');?>:</td>
          <td valign="top"><select id="_2co_currency" name="_2co_currency">
                    <option <?php echo ($_2co_settings->_2co_currency=='AED'?" selected='selected'":""); ?> value="AED" >AED | United Arab Emirates Dirham</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='ARS'?" selected='selected'":""); ?> value="ARS" >ARS | Argentina Peso</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='AUD'?" selected='selected'":""); ?> value="AUD" >AUD | Australian Dollar</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='BRL'?" selected='selected'":""); ?> value="BRL" >BRL | Brazilian Real</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='CAD'?" selected='selected'":""); ?> value="CAD" >CAD | Canadian Dollar</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='CHF'?" selected='selected'":""); ?> value="CHF" >CHF | Swiss Franc</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='DKK'?" selected='selected'":""); ?> value="DKK" >DKK | Danish Krone</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='EUR'?" selected='selected'":""); ?> value="EUR" >EUR | Euro</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='GBP'?" selected='selected'":""); ?> value="GBP" >GBP | British Pound</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='HKD'?" selected='selected'":""); ?> value="HKD" >HKD | Hong Kong Dollar</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='ILS'?" selected='selected'":""); ?> value="ILS" >ILS | Israeli New Shekel</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='INR'?" selected='selected'":""); ?> value="INR" >INR | Indian Rupee</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='JPY'?" selected='selected'":""); ?> value="JPY" >JPY | Japanese Yen</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='LTL'?" selected='selected'":""); ?> value="LTL" >LTL | Lithuanian Litas</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='MXN'?" selected='selected'":""); ?> value="MXN" >MXN | Mexican Peso</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='MYR'?" selected='selected'":""); ?> value="MYR" >MYR | Malaysian Ringgit</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='NOK'?" selected='selected'":""); ?> value="NOK" >NOK | Norwegian Krone</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='NZD'?" selected='selected'":""); ?> value="NZD" >NZD | New Zealand Dollar</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='PHP'?" selected='selected'":""); ?> value="PHP" >PHP | Philippine Peso</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='RON'?" selected='selected'":""); ?> value="RON" >RON | Romanian New Leu</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='RUB'?" selected='selected'":""); ?> value="RUB" >RUB | Russian Ruble</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='SEK'?" selected='selected'":""); ?> value="SEK" >SEK | Swedish Krona</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='SGD'?" selected='selected'":""); ?> value="SGD" >SGD | Singapore Dollar</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='TRY'?" selected='selected'":""); ?> value="TRY" >TRY | Turkish Lira</option>
                    <option <?php echo ($_2co_settings->_2co_currency=='USD'?" selected='selected'":""); ?> value="USD" >USD | U.S. Dollar</option>
                    <option <?php echo ($_2co_settings->_currency=='ZAR'?" selected='selected'":""); ?> value="ZAR" >ZAR | South African Rand</option>
                </select><div id="svlabel"><?php echo JText::_('RS1_ADMIN_2CO_CURRENCY_NOTE');?></div>
          </td>
        </tr>
    -->    <tr>
          <td><?php echo JText::_('RS1_ADMIN_2CO_BUTTON_URL');?>:</td>
          <td><input type="text" size="70" maxsize="255" name="_2co_button_url" value="<?php echo $_2co_settings->_2co_button_url; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_2CO_BUTTON_URL_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_2CO_ITEM_NAME');?>:</td>
          <td><input type="text" size="50" maxsize="255" name="_2co_item_name" value="<?php echo $_2co_settings->_2co_item_name; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_2CO_ITEM_NAME_HELP');?></td>
        </tr>
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_2CO_FE_TAB');?>: </td>
          <td><select name="_2co_show_trans_in_fe">
              <option value="No" <?php if($_2co_settings->_2co_show_trans_in_fe == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
              <option value="Yes" <?php if($_2co_settings->_2co_show_trans_in_fe == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            <?php
				$k = 0;
				for($i=0; $i < sv_count_($user_groups ); $i++) {
				$user_group = $user_groups[$i];
				?>
            <option value="<?php echo $user_group->id; ?>"  <?php if($_2co_settings->_2co_show_trans_in_fe == $user_group->id){echo " selected='selected' ";} ?>><?php echo $user_group->title ?></option>
            <?php $k = 1 - $k; 
				} ?>
				</select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_2CO_FE_TAB_HELP');?></td>
        </tr>
        </table>