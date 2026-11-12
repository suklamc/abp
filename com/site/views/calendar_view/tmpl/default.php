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
 
 This screen was derived from the Front Desk and may contain code relevant to the Front Desk only.
 
*/


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

	
	
	jimport( 'joomla.application.helper' );

	JHtml::_('jquery.framework');


	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	$jinput = JFactory::getApplication()->input;

	$user = JFactory::getUser();
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$calendar_view_view = $this->calendar_view_view;
//	$calendar_view_resource_filter = $this->calendar_view_resource_filter;
//	$calendar_view_category_filter = $this->calendar_view_category_filter;
//	$calendar_view_status_filter = $this->calendar_view_status_filter;
//	$calendar_view_payment_status_filter = $this->calendar_view_payment_status_filter;
//	$calendar_view_user_search = $this->calendar_view_user_search;

	$calendar_view_cur_week_offset = $this->calendar_view_cur_week_offset;
	$calendar_view_cur_day = $this->calendar_view_cur_day;
	$calendar_view_cur_month = $this->calendar_view_cur_month;
	$calendar_view_cur_year = $this->calendar_view_cur_year;
	//$mainframe = JFactory::getApplication();
	//$params = $mainframe->getParams('com_rsappt_pro3');

	$menu = JFactory::getApplication()->getMenu(); 
	$active = $menu->getActive(); 
	$menu_id = $active->id;
	$params = $menu->getParams($menu_id);

	$resadmin_only = true;
	$month_view_only = true;
	$single_service_mode = false;
	$service_resource_ids = "";
	
	$retore_settings = "";
	switch($calendar_view_view){
		case "month":
			if($calendar_view_cur_month != ""){
				$retore_settings = "'', '".$calendar_view_cur_month."', '".$calendar_view_cur_year."', ''";
			}		
			break;
		case "week":
			if($calendar_view_cur_week_offset != ""){
				$retore_settings = "'', '', '', '".$calendar_view_cur_week_offset."'";
			}
			break;
		case "day":
			if($calendar_view_cur_day != ""){
				$retore_settings = "'".$calendar_view_cur_day."', '', '', ''";
			}		
			break;
	}

	$booking_screen = $params->get('booking_screen', "gad");
	$header_text = $params->get('header_text', "");
	$footer_text = $params->get('footer_text', "");
	$slots_popup = $params->get('slots_popup', "");
	
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "calendar_view_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$single_category_mode = false;
	$single_category_id = "";
	if($params->get('res_or_cat') == 2 && $params->get('passed_id') != ""){
		// single category mode on, set by menu parameter
		$single_category_mode = true;
		$single_category_id = $params->get('passed_id');
		//echo "single category mode (menu), id=".$single_category_id;
	}
	
	if($params->get('res_or_cat') == 3 && $params->get('passed_id') != ""){
		// single service mode on, set by menu parameter
		$single_service_mode = true;
		$single_service_id = $params->get('passed_id');
		//echo "single service mode (menu), id=".$single_service_id;
	}

	if($jinput->getInt('cat','')!=""){
		// single category mode on, set by querystring arg
		$single_category_mode = true;
		$single_category_id = $jinput->getInt('cat','');
		//echo "single category mode (querystring), id=".$single_category_id;
	}

	$single_resource_mode = false;
	$single_resource_id = "";
	if($params->get('res_or_cat') == 1 && $params->get('passed_id') != ""){
		// single resource mode on, set by menu parameter
		$single_resource_mode = true;
		$single_resource_id = $params->get('passed_id');
		//echo "single resource mode (menu), id=".$single_resource_id;
	}
	if($jinput->getInt('res','')!=""){
		// single resource mode on, set by querystring arg
		$single_resource_mode = true;
		$single_resource_id = $jinput->getInt('res','');
		//echo "single resource mode (querystring), id=".$single_resource_id;
	}
	if($single_service_mode){
		// get resources for the service
		$sql = 'SELECT resource_scope FROM #__sv_apptpro3_services WHERE id_services = '.(int)$single_service_id.' AND published = 1;';
		try{
			$database->setQuery($sql);
			$single_service_resource = null;
			$single_service_resource = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($single_service_resource != null){
			//$single_resource_mode = true;
			// make a sting for the IN clause
			$temp = str_replace("||",",",$single_service_resource);
			$temp = str_replace("|","",$temp);			
			//echo $temp;
			$service_resource_ids = $temp;
		}
	}


	$header_text = $params->get('header_text', $jinput->getString('header_text',''));

	$showform= true;

	if(!$user->guest || $apptpro_config->requireLogin == "No"){
	
		$database = JFactory::getDBO();
		$andClause = "";
		
		
		// purge stale paypal bookings
		if($apptpro_config->purge_stale_paypal == "Yes"){
			purgeStalePayPalBookings($apptpro_config->minutes_to_stale);
		}

	} else{
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	}


	$document = JFactory::getDocument();
	$document->addStyleSheet( "//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css");

	
?>

<?php if($showform){?>
<link href="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<script>
	var iframe = null;
	var jq_dialog = null;
	var jq_dialog_title = "";		
	var jq_dialog_close = "<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE')?>";		
	
</script>
<script language="JavaScript" src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/script.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/snap.svg-min.js"></script>
<script src="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/prism.js"></script>

<script language="javascript">
	window.onload = function() {
		buildCalendarDeskView( <?php echo $retore_settings ?>);	
	} 	

  	jQuery(function() {
		jQuery( "#availability" ).dialog({
			autoOpen: false
		});			
	});			
	
	function goDayView(day){
		document.getElementById("calendar_view_view").selectedIndex=0;
		buildCalendarDeskView(day);
	}
	
	function goToday(){
		var currentdate = new Date(); 
		buildCalendarDeskView(currentdate.getDate(), (currentdate.getMonth()+1), currentdate.getFullYear());
	}

</script>
<form name="adminForm" id="adminForm" action="<?php echo JRoute::_($this->request_url) ?>" method="post">
<div id="sv_apptpro_calendar_view">
<div id="sv_apptpro_calendar_view_top">
    <table width="100%">
        <tr>
          <td align="left" colspan="2"> <h3>
          <?php 
		  	echo JText::_('RS1_CALVIEW_SCRN_TITLE');
		 	?>
            </h3></td>
          <td style="text-align:right"><?php echo $user->name ?></td>
        </tr>
        <tr>
			<td colspan="3" style="vertical-align:top; text-align:center"><div id="sv_header"><label><?php echo JText::_($header_text); ?></label></div></td>
        </tr>      
    </table>
</div>
<div id="calview_here">&nbsp;</div>

<div id="sv_footer"><label><?php echo JText::_($footer_text); ?></label></div>


<input type="hidden" name="uid" id="uid" value="<?php echo $user->id; ?>">
<input type="hidden" name="redirect" id="redirect" value="" />
<input type="hidden" name="listpage" id="listpage" value="calendar_view" />
<input type="hidden" name="startdate" id="startdate" value="" />
<input type="hidden" name="starttime" id="starttime" value="" />
<input type="hidden" name="endtime" id="endtime" value="" />
<input type="hidden" name="resid" id="resid" value="" />

  	<input type="hidden" name="option" value="<?php echo $option; ?>" />
  	<input type="hidden" name="controller" value="calendar_view" />
	<input type="hidden" name="task" id='task' value="" />
	<input type="hidden" name="frompage" value="calendar_view" />
  	<input type="hidden" name="frompage_item" id="frompage_item" value="<?php echo $itemid ?>" />

  	<input type="hidden" name="menu_id" id="menu_id" value="<?php echo $menu_id ?>"/>
  	<input type="hidden" name="wait_text" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT'); ?>"/>

  	<input type="hidden" name="single_cat_mode" id="single_cat_mode" value="<?php echo ($single_category_mode?"Yes":"No") ?>"/>
  	<input type="hidden" name="single_cat_value" id="single_cat_value" value="<?php echo $single_category_id?>"/>
  	<input type="hidden" name="single_res_mode" id="single_res_mode" value="<?php echo ($single_resource_mode?"Yes":"No") ?>"/>
  	<input type="hidden" name="single_res_value" id="single_res_value" value="<?php echo $single_resource_id?>"/>

  	<input type="hidden" name="booking_screen" id="booking_screen" value="<?php echo $booking_screen?>"/>
  	<input type="hidden" name="slots_popup" id="slots_popup" value="<?php echo $slots_popup?>"/>
	<input type="hidden" name="preset_service_res_ids" id="preset_service_res_ids" value="<?php echo $service_resource_ids; ?>" />

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?> 
</div>
</form>
<div id="availability" title="<?php echo JText::_('RS1_AVAILABILITY_TIP_TITLE'); ?>" style="overflow:hidden"><svg id="svg"></svg></div>

<?php } ?>

