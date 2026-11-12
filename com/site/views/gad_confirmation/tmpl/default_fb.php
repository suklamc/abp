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


/*
 ************************************************************
    template for facebook iFrame display
 ************************************************************
*/


defined('_JEXEC') or die('Restricted access');



	$jinput = JFactory::getApplication()->input;
	$req_id = $jinput->getString( 'req_id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );
	$which_message = $jinput->getString( 'which_message', 'confirmation' );
	$cc = $jinput->getString( 'cc', '' );

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	$message = buildMessage($req_id, $which_message, "No", $cc, "Yes");
	
	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_conf_tmpl_default_fb", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

?>
<script language="javascript">

	function do_book_another(){
		document.getElementById("task").value="do_book_another";
		document.frmRequest.submit();
	}
	
</script>
<form name="frmRequest" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/sv_apptpro_fb.css" rel="stylesheet">

<div id="sv_apptpro_fb_conf">
	<?php echo $message; ?>
    
	<p>
	<!--Commented out as it does not work with Joolma SEO which is defaulted ON with J1.6-->
    <!--<a href=# onclick="do_book_another();return(false);"><?php echo JText::_('RS1_GAD_CONFIRMATION_BOOK_ANOTHER');?></a>-->
    </p>

</div>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="booking_screen_gad" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</form>
