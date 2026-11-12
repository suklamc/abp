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
		logIt($e->getMessage(), "be_services_copy_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

?>
<script language="javascript">
	
	function submitbutton(pressbutton) {
		var ok = "yes";
		if (pressbutton == 'docopy_service'){
//			var selected = getSelected(document.getElementById("dest_resource_id[]"));
//			if(selected.length == 0){
//				alert("Please select one or more resources.");
//				ok = "no";
//			}	
//			if(ok == "yes"){				
				submitform(pressbutton);
//			}
		} else {
			submitform(pressbutton);
		}		
	}

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
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" >
  <table border="0" cellpadding="4" cellspacing="4">
    <tr>
      <td>
      <?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COPY');?></td>
    </tr>
<!--    <tr>
      <td width="322" ><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COPY_DEST');?></td>
    </tr>
    <tr>
      <td ><p>
        <div class="sv-select"><select name="dest_resource_id[]" id="dest_resource_id[]" style="background-color:#FFFFCC" size="10" multiple="multiple">
          <?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
				$res_row = $res_rows[$i];
				?>
          <option value="<?php echo $res_row->id_resources; ?>" ><?php echo stripslashes($res_row->name); ?></option>
          <?php $k = 1 - $k; 
				} ?>
        </select></div>
      &nbsp;</p>
      <?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COPY_SELECT_DEST');?></td>
    </tr>-->
  </table>
  <p>&nbsp;</p>
  <p>
  <input type="hidden" name="controller" value="services" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="services_tocopy" value="<?php echo $this->services_tocopy; ?>" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
