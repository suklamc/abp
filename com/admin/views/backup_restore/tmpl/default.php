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

// get count of old appointments
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT count(*) FROM #__sv_apptpro3_requests where startdate < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)" );
		$old_data_count_year = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "backup_restore_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}	
	try{
		$database->setQuery("SELECT count(*) FROM #__sv_apptpro3_requests where startdate < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)" );
		$old_data_count_month = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "backup_restore_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}	
	
	//echo $old_data_count;
	
?>

<script language="javascript" type="text/javascript">
	function doBackup(){
		document.body.style.cursor = "wait";
		Joomla.submitform("backup");
	}

	function doRestore(){
		if (confirm("<?php echo JText::_('RS1_ADMIN_SCRN_CONFIRM_RESTORE');?>") == true) {
			document.body.style.cursor = "wait";
			Joomla.submitform("restore");
		} else {
			return false;
		}
	}
	
	
	function do_purge(){
		if(document.getElementById("purge_count").value == ""){
			alert("<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_PURGE_COUNT_REQUIRED');?>");
			return false;
		}	
		if(parseInt(document.getElementById("purge_count").value) < 0 ){
			document.getElementById("purge_count").value = Math.abs(document.getElementById("purge_count").value);
		}	
		var purge_to_date;	
		if(document.getElementById("purge_unit").value == "MONTH"){				
			d = new Date();
			d.setMonth(d.getMonth() - parseInt(document.getElementById("purge_count").value));
			purge_to_date = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate() + " (yyyy-mm-dd)";
			//alert(purge_to_date);
		}
		if(document.getElementById("purge_unit").value == "YEAR"){				
			d = new Date();
			d.setYear(d.getFullYear() - parseInt(document.getElementById("purge_count").value));
			purge_to_date = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate() + " (yyyy-mm-dd)";
			//alert(purge_to_date);
		}
		jQuery.noConflict();
			document.body.style.cursor = "wait";
			backupFieldset.disabled = true;
			jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			url: "index.php?option=com_rsappt_pro3&controller=ajax&task=ajax_get_purge_count&pcount="+document.getElementById("purge_count").value
				+"&punit="+document.getElementById("purge_unit").value,
			success: function(data) {
				document.body.style.cursor = "default";
				var str = "Confirm you wish to delete all data for appointments prior to "+purge_to_date + " - "+data+" records."
				var r = confirm(str);
				if (r != true) {
					document.body.style.cursor = "default";
					backupFieldset.disabled = false;
					return false;
				}
				document.body.style.cursor = "wait"; 
				backupFieldset.disabled = true;
				
				jQuery.ajax({               
					type: "GET",
					dataType: 'json',
					url: "index.php?option=com_rsappt_pro3&controller=ajax&task=ajax_do_purge&pcount="+document.getElementById("purge_count").value
						+"&punit="+document.getElementById("purge_unit").value,
					success: function(data) {
						document.body.style.cursor = "default";
						backupFieldset.disabled = false;
						alert(data);
						location.reload();
					},
					error: function(data) {
						document.body.style.cursor = "default";
						backupFieldset.disabled = false;
						alert(data);
					}					
				 });	

				return false;
			},
			error: function(data) {
				document.body.style.cursor = "default";
				backupFieldset.disabled = false;
				alert(data);
			}					
		 });	
	}	
</script>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
 <fieldset id="backupFieldset">
  <table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td colspan="2"><p><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INTRO');?><br/><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INTRO_LANG');?></p>
      <p>&nbsp;</p></td>
    </tr>
  <tr>
    <td align="center" width="30%"><input type="button" name="btnBackup" id="btnBackup" value="<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_NOW');?>" onclick="doBackup();"/></td>
    <td align="center" width="30%"><input type="button" name="btnRestore" id="btnRestore" value="<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_RESTORE_NOW');?>" onclick="doRestore();"/></td>
  </tr>
  <tr>
    <td align="center" valign="top" style="border-right:solid 1px #000">
      <br/>
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkBackupErrorLog" id="chkBackupErrorLog" /></div>
	  <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_ERROR');?></label></div>
      <br/>
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkBackupReminderLog" id="chkBackupReminderLog" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_REM');?></label></div>
      <br/>
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkBackupLangFile" id="chkBackupLangFile" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_LANG');?></label></div>
    </td>
    <td align="center">
      <br/>
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkRestoreCSS" id="chkRestoreCSS" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_CSS_REST');?></label></div><br />
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkRestoreErrorLog" id="chkRestoreErrorLog" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_ERROR_REST');?></label></div><br />
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkRestoreReminderLog" id="chkRestoreReminderLog" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_REM_REST');?></label></div><br />
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkRestoreLangFile" id="chkRestoreLangFile" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_INCL_LANG_REST');?></label></div><br />
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkBackfillCats" id="chkBackfillCats" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_BACKFILL_CATS');?></label></div><br/>
      <div style="display: table-cell; padding-left:5px;"><input type="checkbox" name="chkFromV2" id="chkFromV2" /></div>
      <div style="display: table-cell; padding-left:5px;"><label><?php echo JText::_('RS1_ADMIN_SCRN_RESTORE_FROM_2');?></label></div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
    	<hr />
		<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_OLD_DATA_COUNT').
			" <b>".$old_data_count_month."</b> ".JText::_('RS1_ADMIN_SCRN_BACKUP_OLD_DATA_COUNT_MONTH')." ".
			" <b>".$old_data_count_year."</b> ".JText::_('RS1_ADMIN_SCRN_BACKUP_OLD_DATA_COUNT_YEAR');?>
        <br/>
		<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_DO_PURGE')?>
        <input type="text" name="purge_count" id="purge_count" value="1" style="width:50px; text-align:center" />
        <select name="purge_unit" id="purge_unit" style="width:100px; text-align:center" >
              <!--<option value="DAY"><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_PURGE_DAYS');?></option>-->
              <option value="MONTH"><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_PURGE_MONTHS');?></option>
              <option value="YEAR"><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_PURGE_YEARS');?></option>
            </select>
        <br/>    
		<input type="button" value="<?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_DO_PURGE_NOW')?>" onclick="do_purge(); return false;" />
        
    	<hr /><?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_NOTE');?><br/>
      <?php echo JText::_('RS1_ADMIN_SCRN_BACKUP_BACKFILL_CATS_HELP');?></td>
    
    
  </tr>
</table>
  <p>&nbsp;</p>
  <p>
  <input type="hidden" name="controller" value="backup_restore" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="task" value="" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</fieldset>
</form>
