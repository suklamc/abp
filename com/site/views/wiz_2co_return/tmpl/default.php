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



	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );

	$jinput = JFactory::getApplication()->input;
	$req_id = $jinput->getString( 'req_id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );
	$cart = $jinput->getString( 'cart', 'no' );
	
	// see if ipn has completed, look for a request with this txnid
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3__2co_transactions WHERE merchant_order_id = "'.(int)$req_id.
		'" and credit_card_processed="Y" ';
	try{
		$database->setQuery($sql);
		$transactions = NULL;
		$transactions = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "wiz_2co_return_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	if(sv_count_($transactions) == 0){
		if($cart == "yes"){
			// used cart confirmation stored in session
			$session = JFactory::getSession();
			$message = $session->get('cart_in_progress_message');
		} else {
			$message = buildMessage($jinput->getString('cm'), "in_progress", "Yes");
		}
	} else {	
		if($cart == "yes"){
			// used cart confirmation stored in session
			$session = JFactory::getSession();
			$message = $session->get('confirmation_message');
		} else {
			$message = buildMessage($req_id, "confirmation", "Yes");
		}
	}


?>
<script language="javascript">
	function do_continue(){
		document.getElementById("task").value="do_continue";
		document.frmRequest.submit();
	}

	function do_book_another(){
		document.getElementById("task").value="do_book_another";
		document.frmRequest.submit();
	}
	
</script>
<form name="frmRequest" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>

	<?php echo $message; ?>
    
	<p>
    <!-- only works wit Joomla SEO OFF!-->
<!--    <a href=# onclick="do_book_another()"><?php echo JText::_('RS1_GAD_CONFIRMATION_BOOK_ANOTHER');?></a>-->
    </p>


  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="bookingscreengadwiz" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</form>
