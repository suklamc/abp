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
	// get authnet settings
	$sql = 'SELECT * FROM #__sv_apptpro3_authnet_settings;';
	try{
		$database->setQuery($sql);
		$authnet_settings = NULL;
		$authnet_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_button", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	  	if($authnet_settings->authnet_button_url != ""){ 
			$authnet_button_url = $authnet_settings->authnet_button_url;?>
	      		<input type="image" id="btnAuthNet"  align="top" src="<?php echo $authnet_button_url ?>" border="0" name="submit" alt="submit this form" onclick="<?php echo $submit_function ?>('authnet'); return false;"
                <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> style="border:none" >
      	<?php } else { ?>
      	<input type="submit" class="button" onclick="<?php echo $submit_function ?>('authnet'); return false;" name="submit3" id="submit3" value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT_AUTHNET');?>"
                <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo " disabled ";} ?> />
      	<?php } ?>
