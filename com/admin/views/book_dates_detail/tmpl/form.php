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

JHtml::_('jquery.framework');

$jinput = JFactory::getApplication()->input;
	$cur_res = $jinput->getInt( 'resource_id' );
	// Get resources for dropdown list
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_bo_detail_tmpl_form", "", "");
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
		logIt($e->getMessage(), "be_bo_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}

	$session = JSession::getInstance($handler=null, $options=null);
	$current_resource = $session->get("current_resource");

?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white; z-index:99999"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/date.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>

<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);


$(document).ready(function(){
  $("form").submit(function(){
		pressbutton = document.adminForm.task.value;  
		var ok = "yes";
		if (pressbutton == 'save' || pressbutton == 'save2new'){
			if(document.getElementById("resource_id").selectedIndex == 0){
				alert("Please select a resource.");
				ok = "no";
			}
			if(document.getElementById("book_date").value == ""){
				alert("Please select a date.");
				ok = "no";
			}
			if(ok == "yes"){
				Joomla.submitform(pressbutton);
			} else {
				return false;
			}
		} else {
			Joomla.submitform(pressbutton);
		}		
  });
});


</script>

<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
	<?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_DETAIL_INTRO');?>
  <table class="table table-striped" >
    <tr>
      <td width="15%"><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_book_dates ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_RESOURCE');?></td>
      <td colspan="3">
      <?php if($this->detail->resource_id == ""){ ?>
      	<?php // new rather than edit
			if($current_resource == ""){
				// no current resource
			?>	
              <select name="resource_id" id="resource_id">
              <option value="0" ><?php echo JText::_('RS1_ADMIN_SCRN_BOOK_DATE_RESOURCE_SEL');?></option>
                  <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                    $res_row = $res_rows[$i];
                    ?>
                  <option value="<?php echo $res_row->id_resources; ?>"  <?php if($cur_res == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo stripslashes($res_row->name); ?></option>
                  <?php $k = 1 - $k; 
                    } ?>
              </select>
             <?php } else { ?>
                    <input type="hidden" name="resource_id" id="resource_id" value=<?php echo $current_resource;?> />
                  	<?php
                    $k = 0;
                    for($i=0; $i < sv_count_($res_rows ); $i++) {
                        $res_row = $res_rows[$i];
                        if($current_resource == $res_row->id_resources){
                            echo stripslashes($res_row->name);
                        }
                        $k = 1 - $k; 
                    }     		
            		?>
             <?php } ?>
      <?php } else { ?>
      			<input type="hidden" name="resource_id" id="resource_id" value=<?php echo $this->detail->resource_id;?> />
              	<?php
				$k = 0;
				for($i=0; $i < sv_count_($res_rows ); $i++) {
					$res_row = $res_rows[$i];
					if($this->detail->resource_id == $res_row->id_resources){
        	  			echo stripslashes($res_row->name);
              		}
					$k = 1 - $k; 
				}     		
      		} 
			?>    
      &nbsp; </td>
    </tr>   
    <tr>
      <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_BO_DATE');?></td>
      <td valign="top"><input class="sv_date_box"  type="text" size="12" maxsize="10" readonly="readonly" name="book_date" id="book_date" value="<?php echo $this->detail->book_date; ?>" />
		        <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].book_date,'anchor1','yyyy-MM-dd'); return false;"
					 name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
      <td></td>
            </td>
    </tr>   
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BD_DESC');?> </td>
      <td><input type="text" style="width:90%" size="60" maxsize="80" name="description" value="<?php echo stripslashes($this->detail->description); ?>" /></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_BD_DESC_HELP');?>&nbsp;</td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD');?></td>
        <td>
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" ><br /></td>
    </tr>  
  </table>
</fieldset>
  <input type="hidden" name="id_book_dates" value="<?php echo $this->detail->id_book_dates; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="book_dates_detail" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
