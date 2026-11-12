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
include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );



	$jinput = JFactory::getApplication()->input;
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );
	$user = JFactory::getUser();

	$request = $jinput->getString( 'id', '' );
	$format = $jinput->getString( 'tmpl', '' );
	$startdate = $jinput->getString( 'startdate', '' );
	$starttime = $jinput->getString( 'starttime', '' );
	$endtime = $jinput->getString( 'endtime', '' );
	$resid = $jinput->getString( 'resid', '' );

	if($format != "component"){
		$listpage = $jinput->getString('listpage', 'list');
		setSessionStuff("front_desk");
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("status_filter", $jinput->getString('filter', ''));
		$session->set("request_resourceFilter", $jinput->getString('resourceFilter', ''));
	}
	
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "disp_mani_tmpl_default_prt", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	 
	if(!$user->guest){

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

		$database = JFactory::getDBO();
			
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "disp_manifest_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		// find requests
		$sql = "SELECT ".
		"#__sv_apptpro3_requests.*, #__sv_apptpro3_requests.payment_status,  ".
  		"#__sv_apptpro3_seat_types.seat_type_label, ".
		"#__sv_apptpro3_seat_counts.seat_type_qty, ".
		"#__sv_apptpro3_seat_types.ordering ".
		"FROM ".
		"#__sv_apptpro3_requests LEFT JOIN ".
		"#__sv_apptpro3_seat_counts ON #__sv_apptpro3_requests.id_requests = ".
		"#__sv_apptpro3_seat_counts.request_id LEFT JOIN ".
		"#__sv_apptpro3_seat_types ON #__sv_apptpro3_seat_counts.seat_type_id = ".
		"#__sv_apptpro3_seat_types.id_seat_types ".
		"WHERE ".
		"#__sv_apptpro3_requests.resource = '".$database->escape($resid)."' AND ".
		"#__sv_apptpro3_requests.startdate = '".$database->escape($startdate)."' AND ".
		"#__sv_apptpro3_requests.starttime = '".$database->escape($starttime)."' ".
		"ORDER BY ".
		"#__sv_apptpro3_requests.name, #__sv_apptpro3_requests.id_requests, #__sv_apptpro3_seat_types.ordering";		
		try{		
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "disp_mani_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}	

		// get resource stuff
		$sql = 'SELECT * FROM #__sv_apptpro3_resources WHERE id_resources = '.(int)$resid;
		try{
			$database->setQuery($sql);
			$resource_details = NULL;
			$resource_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "disp_mani_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}			

	} else{
		echo "<font color='red'>".JText::_('RS1_MANIFEST_SCRN_NO_LOGIN')."</font>";
	}
	
	
	function printcall($resid, $startdate, $starttime, $endtime)
	{
		$url = 'index.php?option=com_rsappt_pro3&view=front_desk&task=display_manifest&tmpl=component&resid='.$resid.'&startdate='.$startdate.'&starttime='.$starttime.'&endtime='.$endtime;;
		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=yes,resizable=yes,width=640,height=480,directories=no,location=no';

		$text = JText::_('RS1_ADMIN_SCRN_BTN_PRINT');
		$attribs['title']	= JText::_( 'RS1_ADMIN_SCRN_BTN_PRINT' );
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHTML::_('link', JRoute::_($url), $text, $attribs);
	}
	
?>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<?php if($format != "component"){ ?>
<script language="javascript">
	function doCancel(){
		Joomla.submitform("cancel");
	}		


</script>
<?php } else { ?>
<script>
	window.onload = function() {
		window.print();	
	} 	
</script>

<?php } ?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
  <table width="100%" border="0">
        <tr>
          <td align="left"> <h3><?php echo JText::_('RS1_MANIFEST_SCRN_TITLE');?></h3></td>
        </tr>
<?php if($format != "component"){ ?>
		<tr>
          <td height="40px" 
          style="background-color:#FFFFCC; border-top:solid #333333 1px;border-bottom:solid #333333 1px; text-align:right;">
          <?php echo printcall($resid, $startdate, $starttime, $endtime); ?>&nbsp;&nbsp;|&nbsp;
          <a href="#" onclick="doCancel();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
        </tr>       
<?php } ?>
<?php 	if($apptpro_config->timeFormat == '24'){
			$fmtString = "G:i";
		} else {
			$fmtString = "g:i A";
		} ?>	

<?php
		$lang = JFactory::getLanguage();
		setlocale(LC_ALL, $lang->getTag()); 
		?>
        
     <tr><td><?php echo JText::_('RS1_MANIFEST_HEADER'); ?></td></tr>
     <tr style="font-size:14px;	font-weight:bold"><td><?php echo JText::_($resource_details->description) ?> </td></tr>
     <tr style="font-size:14px;	font-weight:bold"><td><?php echo iconv('ISO-8859-2', 'UTF-8',strftime("%A, %B %d %Y", strtotime($startdate)))."  /  ".date($fmtString, strtotime($starttime))." - ".date($fmtString, strtotime($endtime))  ?> </td></tr>
    </table>
  <table cellpadding="4" cellspacing="0" border="0" width="100%" >
    <tr class="adminheading"  bgcolor="#F4F4F4">
      <th class="sv_title" align="left" width="5%">&nbsp;</th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_NAME_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_PHONE_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_EMAIL_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_STATUS_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_PAYMENT_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_SEAT_TYPE_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_MANIFEST_SEAT_COUNT_COL_HEAD'); ?></th>
    </tr>
    <?php
	$seat_tally = 0;
	$current_booking = "";
	$previous_booking= "";
	$show_booking_header = true;
	
	$k = 0;
	for($i=0; $i < sv_count_($rows ); $i++) {
		$row = $rows[$i];
		$current_booking = $row->id_requests;

		if($i == 0){
			$previous_booking = $current_booking;
		}
		
		if($i > 0){
			if($current_booking == $previous_booking){
				$show_booking_header = false;
				if($row->request_status == 'accepted'){
					$seat_tally  += $row->seat_type_qty;
				}
			} else {
				//moved to next booking
				$previous_booking = $current_booking;
				$show_booking_header = true;
			}
		}
   ?>
    <tr class="<?php echo "row$k"; ?>" <?php echo ($show_booking_header==true?" style=\"border-top:solid 1px\"":"")?>>
      <?php if($show_booking_header==true){?>
      <td align="center" style="border-top:solid 1px"><img src='<?php echo getImageSrc('box.png') ?>' />&nbsp;</td>
      <td align="left" style="border-top:solid 1px"><?php echo $row->name; ?><?php // echo $row->id; ?></td>
      <td align="left" style="border-top:solid 1px"><?php echo $row->phone; ?> </td>
      <td align="left" style="border-top:solid 1px"><?php echo $row->email; ?> </td>
      <td align="left" style="border-top:solid 1px"><?php echo translated_status($row->request_status); ?> </td>
      <td align="left" style="border-top:solid 1px"><?php echo translated_status($row->payment_status); ?> </td>
      <?php } else { ?>
      <td align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
      <?php } ?>
      <td align="left" <?php echo ($show_booking_header==true?" style=\"border-top:solid 1px\"":"")?>><?php echo JText::_($row->seat_type_label); ?> </td>
      <td align="left" <?php echo ($show_booking_header==true?" style=\"border-top:solid 1px\"":"")?>><?php echo $row->seat_type_qty; ?> </td>
    </tr>
    <?php } 

?>
  </table>
  <?php echo JText::_('RS1_MANIFEST_FOOTER'); ?>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
