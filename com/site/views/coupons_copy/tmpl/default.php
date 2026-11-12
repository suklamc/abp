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

	
	jimport( 'joomla.application.helper' );

	JHtml::_('jquery.framework');


	$jinput = JFactory::getApplication()->input;
	$showform= true;
	$frompage = $jinput->getString('frompage', '');
	$fromtab = $jinput->getString('fromtab', '');
	$coupons_tocopy = $jinput->getString('coupons_tocopy', '');
	
	
	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

		// get resources
		$sql = "SELECT * FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' and published=1 ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "coup_copy_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
				
		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "coup_copy_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	}	
	
?>
<?php if($showform){?>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>
<script language="JavaScript">
	jQuery(function() {
  		jQuery( "#display_picker_date" ).datepicker({
			showOn: "button",
			autoSize: true,
			changeMonth: true,
			changeYear: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#new_coupon_date",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

		
	function doCancel(){
		Joomla.submitform("cancel");
	}		
	
	function doCopyNow(){
		Joomla.submitform("do_copy_coupons");
	}
	
	function setDatePicker(){
		if(document.getElementById("date_picker_format")!=null){
			var tempdate;
			tempdate = Date.parse(document.getElementById("new_coupon_date").value);	
				
			if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
				document.getElementById("display_picker_date").value = tempdate.toString("dd-MM-yyyy");
			}
			if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
				document.getElementById("display_picker_date").value = tempdate.toString("MM-dd-yyyy");
			}
			if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
				document.getElementById("display_picker_date").value = tempdate.toString("yyyy-MM-dd");
			}		
		}
	}	

	
	</script>
<form action="<?php echo $this->request_url ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_coupon_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
</script>

<table width="100%" >
    <tr>
      <td align="left" colspan="2"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE').JText::_('RS1_ADMIN_SCRN_COUPONS_COPY_TITLE');?></h3></td>
    </tr>
</table>
<table border="0" cellpadding="4" cellspacing="0" width="100%">
   <tr>
      <td colspan="3" style="text-align:right" height="40px" class="fe_header_bar">
      <a href="#" onclick="doCopyNow();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_COPYNOW');?></a>
      &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doCancel();return(false);"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
    </tr>
    <tr>
      <td>
      <?php echo JText::_('RS1_ADMIN_SCRN_COUPONS_COPY_INTRO');?> </td>
    </tr>
    <tr>
      <td width="322" ><?php echo JText::_('RS1_ADMIN_SCRN_COUPONS_COPY_NUMBER');?>
      <input type="number" name="number_of_copies" size="4" value="1" style="width:30px; text-align: center"/></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_COUPONS_COPY_NEW_DATE_HELP');?><br /><?php echo JText::_('RS1_ADMIN_SCRN_COUPONS_COPY_NEW_DATE');?>:
        <input readonly="readonly" name="new_coupon_date" id="new_coupon_date" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="" onchange="setDatePicker();">
      
	    &nbsp;<a href="#" onclick="document.getElementById('display_picker_date').value=''; document.getElementById('new_coupon_date').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
	  </td>
    </tr>
  </table>
  <p>&nbsp;</p>
  <p>
	<input type="hidden" name="option" value="<?php echo $option; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="current_tab" id="current_tab" value="<?php echo $fromtab; ?>" />
    <input type="hidden" name="controller" value="admin_detail" />
    <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
    <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
    <input type="hidden" name="frompage" value="<?php echo $frompage ?>" />
    <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
    <input type="hidden" name="fromtab" value="<?php echo $fromtab ?>" />
	<input type="hidden" name="coupons_tocopy" value="<?php echo $coupons_tocopy; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
