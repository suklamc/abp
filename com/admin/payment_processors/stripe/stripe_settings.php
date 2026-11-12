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
	// get settinsg data for their processor
	$force_disable_stripe = false;
	if (!file_exists(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'stripe'.DIRECTORY_SEPARATOR.'stripe-php'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Stripe.php'))	{
		echo JText::_('COM_STRIPE_NOT_INSTALLED');
		$force_disable_stripe = true;
	} else {	
		$sql = 'SELECT * FROM #__sv_apptpro3_stripe_settings;';
		try{
			$database->setQuery($sql);
			$stripe_settings = NULL;
			$stripe_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_stripe_settings_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
	}

 	// get data for dropdowns
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_stripe_currency ORDER BY description" );
		$currency_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	

?>
        <table class="table table-striped" >
        <tr >
          <td ><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_ENABLE');?>: </td>
          <td><select name="stripe_enable">
          <?php if(!$force_disable_stripe){ ?>
              <option value="Yes" <?php if($stripe_settings->stripe_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <?php } ?>    
              <option value="No" <?php if($stripe_settings->stripe_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_ENABLE_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_PK');?>:</td>
          <td><input type="text" size="3" maxsize="999"  name="stripe_pk" style="width:250px;" value="<?php echo $stripe_settings->stripe_pk; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_PK_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_SK');?>:</td>
          <td><input type="text" size="3" maxsize="999" name="stripe_sk" style="width:250px;" value="<?php echo $stripe_settings->stripe_sk; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_SK_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_CURRENCY');?>: </td>
          <td><select name="stripe_currency"> 
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($currency_rows ); $i++) {
                $currency_row = $currency_rows[$i];
                ?>
                      <option value="<?php echo $currency_row->code; ?>" <?php if($stripe_settings->stripe_currency == $currency_row->code){echo " selected='selected' ";} ?>><?php echo $currency_row->code." - ".$currency_row->description; ?></option>
                      <?php $k = 1 - $k; 
                } ?>
            </select></td>
            <td></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_BUTTON');?>:</td>
          <td><input type="text" size="70" maxsize="255" name="stripe_button_image" value="<?php echo $stripe_settings->stripe_button_image; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_BUTTON_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_COMPANY_NAME');?>:</td>
          <td><input type="text" size="70" maxsize="255" name="stripe_company_name" value="<?php echo $stripe_settings->stripe_company_name; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_COMPANY_NAME_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_DESCRIPTION');?>:</td>
          <td><input type="text" size="70" maxsize="255" name="stripe_billing_description" value="<?php echo $stripe_settings->stripe_billing_description; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_DESCRIPTION_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_POPUP_IMAGE');?>:</td>
          <td><input type="text" size="70" maxsize="255" name="stripe_image" value="<?php echo $stripe_settings->stripe_image; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_POPUP_IMAGE_HELP');?></td>
        </tr>
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_FE_TAB');?>: </td>
          <td><select name="stripe_show_trans_in_fe">
              <option value="No" <?php if($stripe_settings->stripe_show_trans_in_fe == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
              <option value="Yes" <?php if($stripe_settings->stripe_show_trans_in_fe == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            <?php
				$k = 0;
				for($i=0; $i < sv_count_($user_groups ); $i++) {
				$user_group = $user_groups[$i];
				?>
            <option value="<?php echo $user_group->id; ?>"  <?php if($stripe_settings->paypal_show_trans_in_fe == $user_group->id){echo " selected='selected' ";} ?>><?php echo $user_group->title ?></option>
            <?php $k = 1 - $k; 
				} ?>
				</select></td>
          <td ><?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_FE_TAB_HELP');?></td>
        </tr>      </table>
	<?php echo JText::_('RS1_ADMIN_CONFIG_STRIPE_NOTE');?>
