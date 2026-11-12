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

	$showform= true;
	$jinput = JFactory::getApplication()->input;
	$listpage = $jinput->getString('listpage', 'list');

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
			logIt($e->getMessage(), "bd_detail_tmpl_form", "", "");
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
			logIt($e->getMessage(), "bd_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$display_picker_date = $this->detail->book_date;	
		if($display_picker_date != "" && $display_picker_date != "0000-00-00"){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date = date("Y-m-d", strtotime($this->detail->book_date));
					break;
				case "dd-mm-yy":
					$display_picker_date = date("d-m-Y", strtotime($this->detail->book_date));
					break;
				case "mm-dd-yy":
					$display_picker_date = date("m-d-Y", strtotime($this->detail->book_date));
					break;
				default:	
					$display_picker_date = date("Y-m-d", strtotime($this->detail->book_date));
					break;
			}
		}

	}	
	
?>
<?php if($showform){?>

<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/date.js"></script>
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.8.2/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.8.2/jquery-ui.js"></script>

<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/datepicker_locale/datepicker-<?php echo PICKER_LANG?>.js"></script>
<script language="JavaScript">
	jQuery(function() {
  		jQuery( "#display_picker_date" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#book_date",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	window.onload = function() {
	};
<?php
	// To limit the New Book-offs date picker so operators cannot make book-offs too far in advance, uncomment the lines below.
	// Shown here limiting to 90 days, chaneg the 90 as required.
	
	//$day = strtotime("now");
	//$day = $day + (86400*90);
	//echo "cal.addDisabledDates('".strftime("%Y-%m-%d", $day)."', null);"; 
?>
		
	function doCancel(){
		Joomla.submitform("bd_cancel");
	}		

	function doClose(){
		Joomla.submitform("bd_close");
	}		
	
	function doSave(){
		if(document.getElementById('resource_id').selectedIndex == 0){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_RESOURCE_ERR');?>');
			return(false);
		}
		if(document.getElementById('book_date').value == ""){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_ERR');?>');
			return(false);
		}

		document.getElementById("task").value = "save_book_dates_detail"; // needed for IE9 strangeness
		Joomla.submitform("save_book_dates_detail");
	}
	
	
	function setDatePicker(which_one){
		if(document.getElementById("date_picker_format")!=null){
			if(which_one == 1){	
				var tempdate;
				tempdate = Date.parse(document.getElementById("book_date").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date").value = tempdate.toString("yyyy-MM-dd");
				}		
			} else {
				var tempdate;
				tempdate = Date.parse(document.getElementById("book_date2").value);	
					
				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("dd-MM-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
					document.getElementById("display_picker_date2").value = tempdate.toString("MM-dd-yyyy");
				}
				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
					document.getElementById("display_picker_date2").value = tempdate.toString("yyyy-MM-dd");
				}
			}
		}
	}	

	
	</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<div id="sv_apptpro_fe_book_date_detail">
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<h3><?php echo JText::_('RS1_ADMIN_SCRN_RESOURCE_BOOK_DATE_TITLE_MOBILE');?></h3>
  <table class="table table-striped" width="100%" >
    <tr>
      <td class="fe_header_bar">
      <div class="controls sv_yellow_bar" align="center">
      <?php if($this->lock_msg != ""){?>
	      <?php echo $this->lock_msg?>
    	  <input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
      <?php } else { ?>
 		<input type="button" id="saveLink" onclick="doSave();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?>">
		<input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
      <?php } ?>
      </div>
      </td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_DETAIL_ID');?>&nbsp;<?php echo $this->detail->id_book_dates ?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BD_RESOURCE');?>
      <div class="controls">
      <?php if($this->detail->resource_id == ""){ ?>
	      <select name="resource_id" id="resource_id" class="sv_apptpro3_request_text">
          <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_SERV_RESOURCE_SELECT');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
        	  <option value="<?php echo $res_row->id_resources; ?>"  <?php if($this->filter_book_dates_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
              <?php $k = 1 - $k; 
				} ?>
    	  </select>
      <?php } else { ?>
      			<input type="hidden" name="resource_id" id="resource_id" value=<?php echo $this->detail->resource_id;?> />
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
					$res_row = $res_rows[$i];
					if($this->detail->resource_id == $res_row->id_resources){
        	  			echo JText::_(stripslashes($res_row->name));
              		}
					$k = 1 - $k; 
				}     		
      		} 
			?>    
      </div>
      </td>
    </tr>    	    
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BD_DATE');?></div>
      <div class="controls">
        <input readonly="readonly" name="book_date" id="book_date" type="hidden" 
          class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->detail->book_date; ?>" />
    
        <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
            value="<?php echo $display_picker_date ?>" onchange="setDatePicker(1);">
           
		</div>
        </td>
    </tr>
    <tr>
      <td>
	  <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BD_DESC');?></div>
      <div class="controls">
      	<input type="text" size="40" maxsize="80" name="description" class="sv_apptpro3_request_text" value="<?php echo stripslashes($this->detail->description); ?>" />
      </div>
      </td>
    </tr>
    <tr>
      <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_DETAIL_PUBLISHED');?></div>
      <div class="controls">
        <select name="published" class="sv_apptpro3_request_text">
        <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
        <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
        </select>
      </div>
      </td>
    </tr>

  </table>
  <input type="hidden" name="id_book_dates" value="<?php echo $this->detail->id_book_dates; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="controller" value="admin_detail" />
  <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
  <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
  <input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
  <input type="hidden" name="mobile" id="mobile" value="Yes" />    
  <br />
      <?php if($apptpro_config->hide_logo == 'No'){ ?>
        <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 <br/> Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
      <?php } ?>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
