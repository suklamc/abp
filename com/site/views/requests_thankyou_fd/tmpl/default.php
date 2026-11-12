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

//DEVNOTE: import html tooltips

	$jinput = JFactory::getApplication()->input;

	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "requests_thankyou_fd_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	

?>
<script language="javascript" type="text/javascript">
	function done() {
		Joomla.submitform("cancel");
	}

</script>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<p><?php echo JText::_('RS1_ADMIN_SCRN_THANKYOU');?></p>
<p></p>
<p> <?php echo $jinput->getString("results"); ?></p>
<p></p>
<p><?php echo JText::_('RS1_ADMIN_SCRN_THANKYOU_NOTE');?>
<p></p>
<a href="#" onclick="done();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_REM_CONTINUE');?></a>
<p></p>

  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" name="controller" value="front_desk" />
	<input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="frompage" value="front_desk" />
  	<input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />

  <br />
   <?php if($apptpro_config->hide_logo == 'No'){ ?>
	  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
   <?php } ?>
</form>
