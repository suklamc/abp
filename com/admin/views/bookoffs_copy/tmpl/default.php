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

// Get resources for list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources  ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_bo_copy_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_bo_copy_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}

?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/date.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
</script>

<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		var ok = "yes";
		if (pressbutton == 'docopy_bookoff'){
			var selected = getSelected(document.getElementById("dest_resource_id[]"));
			if(selected.length == 0){
				alert("Please select one or more resources.");
				ok = "no";
				return false;
			}	
			if(ok == "yes"){				
				Joomla.submitform(pressbutton);
			}
		} else {
			Joomla.submitform(pressbutton);
		}		
  });
});

   function getSelected(opt) {
      var selected = new Array();
      var index = 0;
      for (var intLoop=0; intLoop < opt.length; intLoop++) {
         if (opt[intLoop].selected) {
            index = selected.length;
            selected[index] = new Object;
            selected[index].value = opt[intLoop].value;
            selected[index].index = intLoop;
         }
      }
      return selected;
   }

</script>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" >
<div id="sv_be_admin">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
  <table border="0" cellpadding="4" cellspacing="4" width="100%">
    <tr>
      <td>
      <?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY');?></td>
    </tr>
    <tr>
      <td width="322" ><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_DEST');?></td>
    </tr>
    <tr>
      <td ><p>
        <select name="dest_resource_id[]" id="dest_resource_id[]" style="background-color:#FFFFCC" size="10" multiple="multiple">
          <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>" ><?php echo stripslashes($res_row->name); ?></option>
          <?php $k = 1 - $k; 
				} ?>
        </select>
      &nbsp;</p>
      <?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_SELECT_DEST');?></td>
    </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_NEW_DATE_HELP');?><br /><?php echo JText::_('RS1_ADMIN_SCRN_BOOKOFF_COPY_NEW_DATE');?>:
          <input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="new_off_date" id="new_off_date" value="" />
		        <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].new_off_date,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a></td>
        </tr>
  </table>
  <p>&nbsp;</p>
  <p>
  <input type="hidden" name="controller" value="bookoffs" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="bookoffs_tocopy" value="<?php echo $this->bookoffs_tocopy; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</div>
</form>
