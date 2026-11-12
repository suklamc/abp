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

defined('_JEXEC') or die('Restricted access');

	$jinput = JFactory::getApplication()->input;
	$tid = $jinput->getString( 'tid', '' );
	$cc = "";
	
	$which_message = 'confirmation';

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	require_once JPATH_ADMINISTRATOR.'/components/com_payage/api.php';
	$payment_data = PayageApi::Get_Payment_Data($tid);
	$req_id = $payment_data->app_transaction_id;
	if(strpos($req_id, "cart|") > -1 ){
		// used cart confirmation stored in session
		$session = JFactory::getSession();
		$message = $session->get('confirmation_message');
	} else {
		$message = buildMessage($req_id, $which_message, "No", $cc, "Yes");
	}
	

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "paysuccess_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		


?>

<form name="frmRequest" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<div id="sv_apptpro_request_gad">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>

	<?php echo $message; ?>
    
	<?php 
		$appWeb      = JFactory::getApplication();
		if(!$appWeb->client->mobile){ ?>
	<p>
    <input type="button" value="<?php echo JText::_('RS1_PRINT_THIS_PAGE');?>" onclick="window.print();">
    </p>
	<?php } ?>
    
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="booking_screen_gad" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
</form>
