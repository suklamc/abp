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


// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE timeslots != 'Global' ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_timeslots_copy_tmpl_default", "", "");
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
		logIt($e->getMessage(), "be_timeslots_copy_tmpl_default", "", "");
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
<script language="JavaScript">
$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		Joomla.submitform(pressbutton);
  });
});



</script>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<div id="sv_be_admin">
  <table class="table table-striped" >
    <tr>
      <td colspan="2">
      <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY');?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_DEST_RESOURCE');?></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_DEST_DAYS');?></td>
    </tr>
    <tr>
      <td ><select style="width:auto;" name="dest_resource_id">
              <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_GLOBAL');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
              <option value="<?php echo $res_row->id_resources; ?>" ><?php echo stripslashes($res_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
      </select></td>
      <td ><table >
          <tr align="center">
            <td><?php echo JText::_('RS1_SUN');?></td>
            <td><?php echo JText::_('RS1_MON');?></td>
            <td><?php echo JText::_('RS1_TUE');?></td>
            <td><?php echo JText::_('RS1_WED');?></td>
            <td><?php echo JText::_('RS1_THU');?></td>
            <td><?php echo JText::_('RS1_FRI');?></td>
            <td><?php echo JText::_('RS1_SAT');?></td>
            <td>&nbsp;</td>
          </tr>
          <tr align="center">
            <td class="center"><input type="checkbox" name="chkSunday" id="chkSunday" /></td>
            <td class="center"><input type="checkbox" name="chkMonday" id="chkMonday" /></td>
            <td class="center"><input type="checkbox" name="chkTuesday" id="chkTuesday" /></td>
            <td class="center"><input type="checkbox" name="chkWednesday" id="chkWednesday" /></td>
            <td class="center"><input type="checkbox" name="chkThursday" id="chkThursday" /></td>
            <td class="center"><input type="checkbox" name="chkFriday" id="chkFriday" /></td>
            <td class="center"><input type="checkbox" name="chkSaturday" id="chkSaturday" /></td>
            <td></td>
          </tr>
        </table>
        <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_NOTE1');?></td>
    </tr>
    <tr>
      <td >&nbsp;</td>
      <td style="border-top:solid #666 1px"><table >
        <tr>
          <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_PUB_START');?>:</td>
          <td ><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="new_start_publishing" id="new_start_publishing" value="" />
		        <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].new_start_publishing,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a></td>
        </tr>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_PUB_END');?>:</td>
          <td><input class="sv_date_box" type="text" size="12" maxsize="10" readonly="readonly" name="new_end_publishing" id="new_end_publishing" value="" />
		        <a href="#" id="anchor2" onclick="cal.select(document.forms['adminForm'].new_end_publishing,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor2"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a></td>
        </tr>
        <tr>
        <td colspan="2"><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_PUB_HELP');?></td>
      </table></td>
    </tr>
 
  </table>
  <hr />
  <?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_COPY_NOTE2');?>

 <p>&nbsp;</p>
  <p>
  <input type="hidden" name="controller" value="timeslots" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="timeslots_tocopy" value="<?php echo $this->timeslots_tocopy; ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</div>
</form>
