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

//DEVNOTE: import html tooltips


//Ordering allowed ?
$ordering = ($this->lists['order'] == 'ordering');
	$user = JFactory::getUser();
	$showform= true;	 
	if(!$user->guest){
		$database = JFactory::getDBO();
	
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
			$showform = false;
		}	
		// get resources
		$sql = "SELECT * FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' and published=1 ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

	} else{
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	}

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}

	// get statuses
	$sql = "SELECT * FROM #__sv_apptpro3_status ORDER BY ordering ";
	try{
		$database->setQuery($sql);
		$statuses = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "admin_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// was planning to use pdf but Joomla pdf is useless with tables
	$pdflink = JRoute::_( "index.php?option=com_rsappt_pro3&controller=admin&task=printer&frompage=admin".
	"&admin.list.startdateFilter=".$this->filter_startdate.
	"&admin.list.enddateFilter=".$this->filter_enddate.
	"&admin.list.statusFilter=".$this->filter_request_status.
	"&admin.list.request_resourceFilter=".$this->filter_request_resource.
	"&admin.list.filter_order=".$this->lists['order_req'].
	"&admin.list.filter_order_Dir=".$this->lists['order_Dir_req'].
	"&tmpl=component");

	$startdateFilter = $this->filter_startdate;
	$enddateFilter = $this->filter_enddate;

	$display_picker_date = $this->filter_startdate;	
	if($display_picker_date != ""){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date = date("Y-m-d", strtotime($this->filter_startdate));
				break;
			case "dd-mm-yy":
				$display_picker_date = date("d-m-Y", strtotime($this->filter_startdate));
				break;
			case "mm-dd-yy":
				$display_picker_date = date("m-d-Y", strtotime($this->filter_startdate));
				break;
			default:	
				$display_picker_date = date("Y-m-d", strtotime($this->filter_startdate));
				break;
		}
	}

	$display_picker_date2 = $this->filter_enddate;	
	if($display_picker_date2 != ""){
		switch ($apptpro_config->date_picker_format) {
			case "yy-mm-dd":
				$display_picker_date2 = date("Y-m-d", strtotime($this->filter_enddate));
				break;
			case "dd-mm-yy":
				$display_picker_date2 = date("d-m-Y", strtotime($this->filter_enddate));
				break;
			case "mm-dd-yy":
				$display_picker_date2 = date("m-d-Y", strtotime($this->filter_enddate));
				break;
			default:	
				$display_picker_date2 = date("Y-m-d", strtotime($this->filter_enddate));
				break;
		}
	}


?>

<link href="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/script.js"></script> 
<?php 
$document = JFactory::getDocument();
$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");
?>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

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
			altField: "#startdateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#enddateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});

	function cleardate(){
		document.getElementById("startdateFilter").value="";
		document.getElementById("enddateFilter").value="";
		Joomla.submitbutton('');
		return false;		
	}
	
	function doSearch(){
		Joomla.submitbutton('');
		return false;
	}
	
	function exportCSV(){
		if(!check_somthing_is_checked("cid_req[]")){
			alert('<?php echo JText::_('RS1_ADMIN_SCRN_SELECT_ONE_OR_MORE');?>');
			return;
		}
		document.getElementById("task").value="export_csv";
		document.adminForm.submit();
		document.getElementById("task").value="";
	}

	function dateCheck(){
		if(document.getElementById("startdateFilter").value != "" && document.getElementById("enddateFilter").value != "" ){
			if(Date.parse(document.getElementById("startdateFilter").value) > Date.parse(document.getElementById("enddateFilter").value)){
				alert(document.getElementById("date_check_text").value);
				document.getElementById("enddateFilter").value = "";
				return false;
			} 
		}
		Joomla.submitbutton('');
		return false;	
	}
	
</script>
<script>
	var iframe = null;
	var jq_dialog = null;
	var jq_dialog_title = ""		
	var jq_dialog_close = "<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE')?>"		
</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
<?php if($showform){?>
<div id="sv_apptpro_fe_admin">
  <table class="table table-striped" width="100%" >
        <tr>
          <td align="left"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE');?></h3></td>
        </tr>
        <tr>
          <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_SEARCH');?></div>
            <div class="controls"><input type="text" id="user_search" name="user_search" style="font-size:11px;" size="15" title="<?php echo JText::_('RS1_ADMIN_APPT_LIST_SEARCH_HELP');?>" 
            value="<?php echo $this->filter_user_search ?>" onchange="doSearch();" />
            <input type="button" value="<?php echo JText::_('RS1_ADMIN_SCRN_SEARCH_GO');?>" onclick="doSearch();" title="<?php echo JText::_('RS1_ADMIN_APPT_LIST_SEARCH_HELP');?>"> 
            </div>
          </td>
        </tr>
        <tr>  
          <td>
            <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER');?></div>
            
           <div class="controls"><input readonly="readonly" name="startdateFilter" id="startdateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $startdateFilter ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date" name="display_picker_date" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date ?>" onchange="dateCheck(); return false;">          
           </div>     
           <div class="controls"><input readonly="readonly" name="enddateFilter" id="enddateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $enddateFilter ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date2" name="display_picker_date2" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date2 ?>" onchange="dateCheck(); return false;">
                          
            </div>
            <div class="controls">
            <a href="#" onclick="cleardate(); return false;"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER_CLEAR');?></a>
            <hr />
            </div>
            </td>
         </tr>
         <tr>
            <td>
            <div class="controls"><select name="request_resourceFilter" id="request_resourceFilter" onchange="this.form.submit();" style="font-size:11px; width:auto;"  >
              <option value="0" <?php if($this->filter_request_resource == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_RESOURCE_NONE');?></option>
              <?php
                $k = 0;
                for($i=0; $i < sv_count_($res_rows ); $i++) {
                $res_row = $res_rows[$i];
                ?>
              <option value="<?php echo $res_row->id_resources; ?>" <?php if($this->filter_request_resource == $res_row->id_resources){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); ?></option>
              <?php $k = 1 - $k; 
                } ?>
            </select>&nbsp;&nbsp;
            <select name="request_status" onchange="this.form.submit();" style="font-size:11px; width:auto;">
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
            <?php foreach($statuses as $status_row){ ?>
                <option value="<?php echo $status_row->internal_value ?>" <?php if($this->filter_request_status == $status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($status_row->status);?></option>        
            <?php } ?>
            </select>
          </div>
          </td>
        </tr>
    </thead>
    </table>	
        
  <table width="100%">
    <tr class="fe_admin_header">
      <!--<th class="sv_title" align="center" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll2(<?php echo sv_count_($this->items); ?>, 'appt_cb');" /></th>-->
      <!--<th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_requests', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>-->
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'), 'name', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
      <!--<th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_EMAIL_COL_HEAD'), 'email', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>-->
      <!--<th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD'), 'ResourceName', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>-->
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_FROM_COL_HEAD'), 'startdatetime', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
      <!--<th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD'), 'ServiceName', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>-->
      <th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD'), 'request_status', $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>
      <!--<th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PAYMENT_COL_HEAD'), 'payment_status',  $this->lists['order_Dir_req'], $this->lists['order_req'], 'req_'); ?></th>-->
    </tr> 
	<?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];
	$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=edit&cid='. $row->id_requests.'&frompage=admin');

	?>
    <tr class="<?php echo "row$k"."_mobile"; ?>">
      <!--<td align="center"><input type="checkbox" id="appt_cb<?php echo $i;?>" name="cid_req[]" value="<?php echo $row->id_requests; ?>" onclick="isChecked(this.checked);" /></td>-->
      <!--<td align="center"><?php echo $row->id_requests; ?></td>-->
      <td><a href=<?php echo $link; ?>><?php echo stripslashes($row->name); ?></a></td>
      <!--<td align="left"><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a></td>-->
      <!--<td align="left"><?php echo JText::_(stripslashes($row->ResourceName)); ?>&nbsp;</td>-->
      <td align="left"><?php echo $row->display_startdate; ?>&nbsp;<?php echo $row->display_starttime; ?> </td>
      <!--<td align="left"><?php echo JText::_(stripslashes($row->ServiceName)); ?> </td>-->
	  <?php if($apptpro_config->status_quick_change == "No"){ ?>
	      <td align="center"><?php echo translated_status($row->request_status); ?></td>
      <?php } else {?>
		  <td align=\"center\">
			<select id="booking_status_<?php echo $row->id_requests?>" name="booking_status_<?php echo $row->id_requests?>" style="width:auto" 
				onfocus="this.oldvalue = this.value;" onchange="quick_status_change('<?php echo $row->id_requests?>',this);">
				<?php foreach($statuses as $status_row){ ?>
					<option value="<?php echo $status_row->internal_value?>" 
						<?php echo ($row->request_status == $status_row->internal_value ? " selected='selected' ":"");?>
						><?php echo JText::_($status_row->status)?></option>
				<?php } ?>
				</select>
				</td>
      <?php } ?>
      <!--<td align="center"><?php echo translated_status($row->payment_status); ?></td>-->
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
<!--<tfoot>
   	<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
    </tfoot>
--></table>

  <input type="hidden" name="controller" value="admin" />
  <input type="hidden" name="task" id="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="req_filter_order" value="<?php echo $this->lists['order_req']; ?>" />
  <input type="hidden" name="req_filter_order_Dir" value="<?php echo $this->lists['order_Dir_req']; ?>" />
  <input type="hidden" name="hidemainmenu" value="0" />  
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
	<input type="hidden" name="date_picker_format" id="date_picker_format" value="<?php echo  $apptpro_config->date_picker_format;?>" />    
    <input type="hidden" name="mobile" id="mobile" value="Yes" />    

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 <br/> Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
</div>
<?php } ?>
<?php echo JHtml::_('form.token'); ?>
</form>
