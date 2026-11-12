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

	$req_id = $jinput->getString( 'req_id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );
	$which_message = $jinput->getString( 'which_message', 'cancellation' );
	$cc = $jinput->getString( 'cc', '' );

	$header_text = $jinput->getString('gdpr_header_text','');
	$footer_text = $jinput->getString('gdpr_footer_text','');



	$database = JFactory::getDBO(); 
	// get config stuff
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gdpr_link_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$lang = JFactory::getLanguage();
	$langTag =  $lang->getTag();
	if($langTag == ""){
		$langTag = "en_GB";
	}
	$sql = "SET NAMES 'utf8';";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
	}		
	$sql = "SET lc_time_names = '".str_replace("-", "_",$langTag)."';";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	
	// get booking details
	$sql = "SELECT ".
				'#__sv_apptpro3_requests.*, #__sv_apptpro3_resources.name AS '.
				'ResourceName, #__sv_apptpro3_services.name AS ServiceName, '.
				'#__sv_apptpro3_categories.name AS CategoryName, '.
				"CONCAT(#__sv_apptpro3_requests.startdate,#__sv_apptpro3_requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '".php_date_string_to_sql($apptpro_config->long_date_format, "SQL")."') as display_startdate, ";
				if($apptpro_config->timeFormat == "12"){
					$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %h:%i %p') as display_starttime, ";
				} else {
					$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %H:%i') as display_starttime, ";
				}
				$sql .= '#__sv_apptpro3_paypal_transactions.id_paypal_transactions AS id_transaction '.
				'FROM ('.
				'#__sv_apptpro3_requests LEFT JOIN '.
				'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
				'#__sv_apptpro3_resources.id_resources LEFT JOIN '.
				'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
				'#__sv_apptpro3_services.id_services LEFT JOIN '.
				'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
				'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
				'#__sv_apptpro3_paypal_transactions ON '.
				'#__sv_apptpro3_paypal_transactions.custom = '.
				'#__sv_apptpro3_requests.id_requests) '.
				' WHERE cancellation_id = "'.$database->escape($cc).'"';
	try{
		$database->setQuery($sql);
		$booking_detail = NULL;
		$booking_detail = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gdpr_link_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get udf details
	if($booking_detail != NULL){
		$sql = "SELECT ".
			"#__sv_apptpro3_udfvalues.udf_value, #__sv_apptpro3_udfs.udf_label FROM #__sv_apptpro3_udfvalues ".
			" INNER JOIN #__sv_apptpro3_udfs ON #__sv_apptpro3_udfvalues.udf_id = #__sv_apptpro3_udfs.id_udfs ".
			" WHERE  #__sv_apptpro3_udfvalues.request_id = ".$booking_detail->id_requests;
		try{
			$database->setQuery($sql);
			$booking_udfs = NULL;
			$booking_udfs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage()." - ".$sql, "gdpr_link_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}

?>
<form name="frmRequest" action="<?php echo JRoute::_( $this->request_url) ?>" method="post">
<div id="sv_apptpro_gdpr">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/script.js"></script>

<script language="JavaScript">
// uses local doCancel as it requires slightly different process.
var xhr = false;
function local_doCancel(){
	document.getElementById("cancel_results").innerHTML = document.getElementById("wait_text").value;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else {
		if (window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) { }
		}
	}
	
	if (xhr) {
		xhr.onreadystatechange = local_showCancel_Results;
		var data = "cancellation_id=" + encodeURIComponent(document.getElementById("cancellation_id").value);
		// need local date/time as yyyy-mm-dd-hh-mm
		var currentTime = new Date();
		data = data + "&userDateTime=" + currentTime.getFullYear() + "-" + (currentTime.getMonth() + 1) + "-" + currentTime.getDate();
		data = data + " " + currentTime.getHours() + ":" + currentTime.getMinutes() + ":00";
		if(document.getElementById("cancel_reason") != null){
			data = data + "&cancel_reason=" + document.getElementById("cancel_reason").value;
		}
		data = data + "&gdpr=Yes";
		data = data + "&browser=" + BrowserDetect.browser;
		//alert(data);
	
		// asynchronous
		xhr.open("GET", presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=cancel_booking&format=raw&" + data, true);
		xhr.send('');
	} else {
		alert("Sorry, but I couldn't create an XMLHttpRequest");
		// synchronous
		return false;
	}
	return true;
}

function local_showCancel_Results(){
	if (xhr.readyState === 4) {
		if (xhr.status === 200) {		
			var outMsg = xhr.responseText;
		} 
		else {
			var outMsg = "There was a problem with the request " + xhr.status;
		}
		document.getElementById("cancel_results").innerHTML = outMsg;
	}
	return true;
}
</script>
<div id="sv_header"><label><?php echo JText::_($header_text); ?></label></div>
<p>
<?php echo JText::_('RS1_GDPR_BOOKING_TEXT');?>
</p>

<?php if($booking_detail != NULL){ ?>
  <table border="0" cellpadding="4" cellspacing="2" width="100%" class="table table-striped">
    <tr>
      <td width="20%" ><?php echo JText::_('RS1_ADMIN_SCRN_NAME');?>: </td>
      <td><?php echo stripslashes($booking_detail->name); ?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PHONE');?>:</td>
      <td><?php echo $booking_detail->phone; ?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_EMAIL');?>: </td>
      <td><?php echo $booking_detail->email; ?></td>
    </tr>
    <?php if($booking_detail->CategoryName != ""){ ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CATEGORY_COL_HEAD');?>:</td>
      <td><?php echo $booking_detail->CategoryName; ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE');?>:</td>
      <td><?php echo $booking_detail->ResourceName; ?></td>
    </tr>
    <?php if($booking_detail->ServiceName != ""){ ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD');?>: </td>
      <td><?php echo $booking_detail->ServiceName; ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STARTDATE');?>: </td>
      <td><?php echo $booking_detail->display_startdate; ?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_STARTTIME');?>:</td>
      <td><?php echo $booking_detail->display_starttime; ?></td>        
    </tr>
	<?php foreach ($booking_udfs as $booking_udf) { ?>
		<tr>
          <td><?php echo $booking_udf->udf_label?>:</td>
          <td><?php echo $booking_udf->udf_value;?></td>        
        </tr>
	<?php } ?>    
	</table>	
	<br />	
    <?php if($apptpro_config->allow_cancellation != "No"){ ?>
        <table border="0" class="sv_apptpro_request_cancel_row" width="100%">
        <tr>
          <td><?php echo JText::_('RS1_CANCEL_REASON');?>  </td>
        </tr>
        <tr>  
          <td><textarea rows="3" cols="60" name="cancel_reason" id="cancel_reason"></textarea></td>
        </tr>  
        <tr >
          <td colspan="2" valign="top"> 
          <input type="hidden" name="cancellation_id" id="cancellation_id" value="<?php echo $cc ?>" />
          <input type="button" class="button"  name="btnCancel" id="btnCancel" onclick="local_doCancel()"
          value="<?php echo JText::_('RS1_INPUT_SCRN_GDPR_CANCEL_BUTTON');?>"/></td>
        </tr>
        </table>
	<?php } ?>    
    <div class="sv_apptpro_errors" style="padding-top:20px; font-size:14px">
    <label id="cancel_results" />
	</div>
    <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
<?php } else { echo JText::_('RS1_GDPR_CODE_NOT_FOUND'); } ?>
<p></p>
<div id="sv_footer"><label><?php echo JText::_($footer_text) ?></label></div>   
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
</form>
