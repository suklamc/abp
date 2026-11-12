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



//	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
//  	setSessionStuff("request");

	$showform= true;

		$user = JFactory::getUser();
		if($user->guest){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
			$showform = false;
		} else {
	
			include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";
	
		}	
	
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "user_search_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// get statuses
		$sql = "SELECT * FROM #__sv_apptpro3_status ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "user_search_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
?>
<?php if($showform){?>

<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>

<script language="JavaScript">

var xhr = false;

function doSearch(){
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
		xhr.onreadystatechange = showResults;
		var data = "src_for=" + (document.getElementById("search_criteria").value).replace(/'/g, "~");
		//alert(data);
		xhr.open("GET", presetIndex()+"?option=com_rsappt_pro3&controller=ajax&task=ajax_user_search&format=raw&" + data, true);
		xhr.send('');
	}
	else {
		alert("Sorry, but I couldn't create an XMLHttpRequest");
	}
	return true;
}
	

function showResults() {	
	if (xhr.readyState === 4) {
		document.getElementById("search_results").style.visibility = "visible";
		document.getElementById("search_results").style.display = "";

		if (xhr.status === 200) {		
			var outMsg = xhr.responseText;
		} 
		else {
			var outMsg = "There was a problem with the request " + xhr.status;
		}

		document.getElementById("search_results").innerHTML = outMsg;
		//document.getElementById("thename").value = outMsg;
	}
	return true;
}
		

	
function goBack(){
	window.parent.search_postback(document.getElementById('thename').value);
	//J1.5 window.parent.document.getElementById('sbox-window').close();
	window.parent.SqueezeBox.close();
}
	
    </script>
<form name="adminForm" id="adminForm" class="sv_adminForm" onsubmit="doSearch();return false;">
<div id="sv_apptpro_user_search">
<table border="0" cellpadding=2 cellspacing=2>
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_USER_SEARCH')?></h3></td>
    </tr>
	<tr><td><input type="text" size="20" id='search_criteria' /></td><td>&nbsp;<?php echo JText::_('RS1_ADMIN_USER_SEARCH_PROMPT')?></td></tr>
    <tr><td>&nbsp;</td><td></td></tr>
    <tr><td><input type="button" value="<?php echo JText::_('RS1_ADMIN_USER_SEARCH_SUBMIT')?>" onclick="doSearch();return false;" />
    <td></td></tr>
    <tr><td>&nbsp;</td><td></td></tr>
    <tr><td colspan="2"><div id="search_results"></div></td></tr>
  </table>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="operator_id" value="<?php echo $this->user_id; ?>" />

  <br /> 


 </div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>