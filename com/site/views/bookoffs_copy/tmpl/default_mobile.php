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
	$bookoffs_tocopy = $jinput->getString('bookoffs_tocopy', '');
	
	
	$id = $jinput->getInt( 'id', '' );
	$itemid = $jinput->getInt( 'Itemid', '' );
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
			logIt($e->getMessage(), "bo_copy_tmpl_default", "", "");
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
			logIt($e->getMessage(), "bo_copy_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

	}	
	
?>
<?php if($showform){?>

<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script src="//code.jquery.com/ui/1.8.2/jquery-ui.js"></script>
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
			altField: "#new_off_date",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});
	
	function doCancel(){
		Joomla.submitform("cancel");
	}		
	
	function doCopyNow(){
		if(document.getElementById('dest_resource_id[]').selectedIndex == -1){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_SELECT_DEST');?>');
			return(false);
		}
		Joomla.submitform("do_copy_bookoffs");
	}
	
	function setDatePicker(){
		if(document.getElementById("new_off_date")!=null){
			var tempdate;
			tempdate = Date.parse(document.getElementById("new_off_date").value);	
				
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
<div id="sv_apptpro_fe_bookoff_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles_mobile.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
</script>
<h3><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_BOOKOFFS_COPY_TITLE_MOBILE');?></h3>
<table width="100%" >
  <table class="table table-striped" width="100%" >
    <tr>
      <td class="fe_header_bar">
      <div class="controls sv_yellow_bar" align="center">
 		<input type="button" id="saveLink" onclick="doCopyNow();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_COPYNOW');?>">
		<input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
      </div>
      </td>
    </tr>
    <tr>
      <td>
      <?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_INTRO');?> </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_DEST');?></div>
	  <div class="controls">
	       <select name="dest_resource_id[]" id="dest_resource_id[]"  class="sv_apptpro3_request_text" style="background-color:#FFFFCC; min-width:100px;min-height:100px !important;"
    	     size="10" multiple="multiple">
          <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>" ><?php echo stripslashes($res_row->name); ?></option>
          <?php $k = 1 - $k; 
				} ?>
        </select><br />
      <?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_SELECT_DEST');?>
      </div>
      </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label">
	  <?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_NEW_DATE_HELP');?><br /><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_NEW_DATE');?>:</div>
 	  <div class="controls">
        <input readonly="readonly" name="new_off_date" id="new_off_date" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="" onchange="setDatePicker();">
      
	    &nbsp;<a href="#" onclick="document.getElementById('display_picker_date').value=''; document.getElementById('new_off_date').value=''; return false; " ><?php echo JText::_('RS1_CLEAR_DATE');?></a>              
	  </div>
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
	<input type="hidden" name="bookoffs_tocopy" value="<?php echo $bookoffs_tocopy; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
	<input type="hidden" id="screen_type" name="screen_type" value="" />			             
    <input type="hidden" name="mobile" id="mobile" value="Yes" />    
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 <br/> Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</table>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
