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
	$force_disable_payage = false;
	$no_payage = false;
	if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_payage/api.php'))	{
		echo JText::_('COM_PAYAGE_NOT_INSTALLED');
		$force_disable_payage = true;
		$no_payage = true;
	} else {	
		$sql = 'SELECT * FROM #__sv_apptpro3_payage_settings;';
		try{
			$database->setQuery($sql);
			$payage_settings = NULL;
			$payage_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_payage_settings_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
	}
	
	// get groups
	if(in_array($database->replacePrefix('#__usergroups'), $tables)){
		try{
			$database->setQuery("SELECT title, id FROM #__usergroups WHERE id>2 ORDER BY title" );
			$user_groups = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_payage_settings_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}
		
?>
	<?php if($no_payage == false){ ?>
        <table class="table table-striped" >
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_ENABLE');?>: </td>
          <td><select name="payage_enable">
          <?php if(!$force_disable_payage){ ?>
              <option value="Yes" <?php if($payage_settings->payage_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
          <?php } ?>    
              <option value="No" <?php if($payage_settings->payage_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
	        </select></td>
          <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_ENABLE_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_GROUP');?>:</td>
          <td><input type="text" size="3" maxsize="999" style="width:30px" name="payage_group" value="<?php echo $payage_settings->payage_group; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_GROUP_HELP');?></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_CURRENCY');?>: </td>
          <td><select name="payage_currency"> 
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($currency_rows ); $i++) {
                $currency_row = $currency_rows[$i];
                ?>
                      <option value="<?php echo $currency_row->code; ?>" <?php if($payage_settings->payage_currency == $currency_row->code){echo " selected='selected' ";} ?>><?php echo $currency_row->code." - ".$currency_row->description; ?></option>
                      <?php $k = 1 - $k; 
                } ?>
            </select></td>
            <td></td>
        </tr>
      </table>
		<?php echo JText::_('RS1_ADMIN_CONFIG_PAYAGE_NOTE');?>

	<?php } ?>
	