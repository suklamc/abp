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
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

	JHtml::_('jquery.framework');
	 

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	$jinput = JFactory::getApplication()->input;
	$user = JFactory::getUser();

	// get contents of cart based on session id
	$session = JFactory::getSession();
	$session_id = $session->getId();
	$database = JFactory::getDBO();
	$fd = $jinput->getWord( 'fd', 'No' );
	
	$show_costs = false;
		
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "cart_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$show_non_pay = true;
	$non_pay_group = $apptpro_config->non_pay_btn_group;
	if($non_pay_group != ""){
		// get user's groups
		$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE ".
			"user_id=".$user->id." AND group_id=".$non_pay_group;	
		try{		
			$database->setQuery($sql);
			$my_groups = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($my_groups == 0){
			$show_non_pay = false;
		}
	}

	// purge stale paypal bookings
	if($apptpro_config->purge_stale_paypal == "Yes"){
		purgeStalePayPalBookings($apptpro_config->minutes_to_stale);
	}

	$pay_proc_enabled = isPayProcEnabled();
	
	if($pay_proc_enabled || $apptpro_config->non_pay_booking_button == "DO"){
		$show_costs = true;
	}
	
	$lang = JFactory::getLanguage();
	$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";		
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "mybookings_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	$sql = "SELECT #__sv_apptpro3_cart.*, #__sv_apptpro3_requests.*, ".
		"#__sv_apptpro3_resources.name as resname, ".
		//"#__sv_apptpro3_services.name as ServiceName, ".
		"CONCAT(#__sv_apptpro3_requests.startdate,#__sv_apptpro3_requests.starttime) as startdatetime, ".
		" IF(CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) > Now(),'no','yes') as expired, ";
		if($apptpro_config->timeFormat == "12"){
			$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '".php_date_string_to_sql($apptpro_config->gad_date_format, "SQL")."') as display_startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%l:%i %p') as display_starttime, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%l:%i %p') as display_endtime ";
		} else {
			$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '".php_date_string_to_sql($apptpro_config->gad_date_format, "SQL")."') as display_startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%k:%i') as display_starttime, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%k:%i') as display_endtime ";
		}
		$sql = $sql." FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
		"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
		"INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
		"WHERE session_id = '".$session_id."' ".
		" AND #__sv_apptpro3_requests.request_status = 'pending' ".
		" ORDER BY id_row_cart";
		try{	
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "cart_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
	// reset timers so they do not timeout while viewing the cart.
	if(sv_count_($rows) > 0){
		$sql = "UPDATE #__sv_apptpro3_cart set created = NOW() WHERE session_id = '".$session_id."'";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "cart_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	}
	
	$pay_proc_enabled = isPayProcEnabled();
	$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1;';
	try{
		$database->setQuery($sql);
		$pay_procs = NULL;
		$pay_procs = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "cart_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

?>


<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/jquery.validate.min.js"></script>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<script>

jQuery(function() {
	if(document.getElementById("display_total") != null && document.getElementById("display_total").innerHTML == "0.00 "){
		// show hidden submit and hide payproc buttons
		if(document.getElementById("hidden_submit") != null){		
			document.getElementById("hidden_submit").style.visibility = "visible";
			document.getElementById("hidden_submit").style.display = "table-cell";		
			show_hidePayProcButtons("hide");
		}
	} else {
		// hide (re-hide) hidden submit
		if(document.getElementById("hidden_submit") != null){		
			document.getElementById("hidden_submit").style.visibility = "hidden";
			document.getElementById("hidden_submit").style.display = "none";		
			show_hidePayProcButtons("show");
		}
	}		
	
    jQuery("#redirect").click(function(){
      jQuery('#sv_alertWindow').show();
      return false;
    });
    jQuery('#btnOk').click(function() {
        jQuery('#sv_alertWindow').hide();
		window.parent.cart_window_close();
		//window.parent.SqueezeBox.close();
    });
    jQuery('#btnPrint').click(function() {
		window.print();
    });
});

function doRemoveFromCart(booking, cart_row){
	jQuery.noConflict();
	document.body.style.cursor = "wait";

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		async: false,
		url: "index.php?option=com_rsappt_pro3&view=cart&task=delete&booking="+booking,
		success: function(data) {
			var row_to_remove = "row"+cart_row;
			document.getElementById(row_to_remove).style.visibility = "hidden";
			document.getElementById(row_to_remove).style.display = "none";
			document.body.style.cursor = "default";
			//alert(data.msg);
		},
		error: function(data) {
			alert("Error:"+data);
		}					
	});
	document.body.style.cursor = "wait";
	var row_count = parseInt(document.getElementById("row_count").value);	 
	document.getElementById("row_count").value = row_count-1;
	 
	// recalc total
	var new_total = 0; 
	for (i=0; i<row_count; i++){
		if(i!=cart_row){
			var temp_row_name = "total_row"+i;
			new_total += parseFloat(document.getElementById(temp_row_name).value);
		}
	}
	document.body.style.cursor = "default";
	if(document.getElementById("display_total") != null){
		document.getElementById("display_total").innerHTML = new_total.toFixed(2);
	}
	location.reload();
}

function doCheckout(destination){
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	disable_cart_buttons();
	var data = "";
	if(destination == 5){ 
		data = data + "cart_total=" + document.getElementById("display_total").innerHTML.replace(",","");
		data = data + "&pp=5";
		data = data + "&st=" + document.getElementById("stripeToken").value;
	}
	// If not PayPal or Authnet, we can checkout via AJAX and popup a confirmation window.
	if(destination == 0 || destination == 5 || document.getElementById("display_total").innerHTML.trim() == "0.00"){
		document.getElementById("errors").innerHTML = document.getElementById("wait_text").value;      
		jQuery.ajax({               
			type: "GET",
			dataType: 'json',
			cache: false,
			url: presetIndex()+"?option=com_rsappt_pro3&view=cart&task=checkout&sid="+document.getElementById("sid").value,
			data: data,			
			success: function(data) {
				document.body.style.cursor = "default";
				document.getElementById("errors").innerHTML = "";     				
				document.getElementById("alert_contents").innerHTML = data.msg;
				localStorage["checkout_complete"] = "yes";
				var x_size = 300;
				if (screen.height > screen.width){
					x_size = 200;
				}
				jQuery('#sv_alertWindow').width(x_size);			
				jQuery('#sv_alertWindow').show();			
			},
			error: function(data) {
				document.getElementById("errors").innerHTML = "";     
				alert(data.responseText);
			}					
		});
	} else {
		// For PayPal or Authnet we need to redirect and that cannot be done from a modal popup (cart view) so we must close view and submit
		localStorage["checkout_required"] = "yes";
		localStorage["checkout_sid"] = document.getElementById("sid").value;
		localStorage["checkout_dest"] = destination;
		localStorage["checkout_cart_total"] = document.getElementById("display_total").innerHTML.replace(",","");
		window.parent.cart_window_close();
		//window.parent.SqueezeBox.close();
	}
	
}

function doAddMore(){
	window.parent.cart_window_close();
	return false;
	//window.parent.SqueezeBox.close();
}

</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="frmRequest" id="frmRequest">
<div id="sv_alertWindow" class="sv_alertWindowMobile" >
    <div id="alert_contents" ></div>
    <hr />
    <p align="center">
    <!--<input type="button" id="btnPrint" value="<?php echo JText::_('RS1_PRINT_THIS_PAGE');?>" /> -->
    <input type="button" id="btnOk" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?>" /> 
    </p>
</div>
<div id="sv_apptpro_view_cart">
  <table cellpadding="2" cellspacing="0" border="1" width="100%" >
    <tr bgcolor="#F4F4F4">
      <th class="sv_title" align="center"><?php echo JText::_('RS1_VIEW_CART_SCRN_RESOURCE_COL_HEAD'); ?></th>
<!--      <th class="sv_title" align="center"><?php echo JText::_('RS1_VIEW_CART_SCRN_DATE_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_VIEW_CART_SCRN_FROM_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_VIEW_CART_SCRN_UNTIL_COL_HEAD'); ?></th>
-->      <?php if($show_costs){?>
	      <th class="sv_title" align="center"><?php echo JText::_('RS1_VIEW_CART_SCRN_TOTAL_COL_HEAD'); ?></th>
      <?php } ?>    
      <th class="sv_title" align="left">&nbsp;</th>
    </tr>
    <?php
	$k = 0;
	$total = 0;
	for($i=0; $i < sv_count_($rows ); $i++) {
		$row = $rows[$i];
		if($row->booking_deposit>0){
			$total += $row->booking_deposit;
		} else {
			$total += $row->booking_due;
		}
   ?>
    <tr id="row<?php echo $i?>" >
      <td align="center"><div class="controls"><?php echo JText::_(stripslashes($row->resname)); ?></div>
 <div class="controls"><?php echo $row->display_startdate; ?></div>
 <div class="controls"><?php echo $row->display_starttime; ?>,&nbsp;
 <?php echo $row->display_endtime; ?></div> </td>
      <?php if($show_costs){?>
	      <td align="right"><?php echo ($row->booking_deposit>0?$row->booking_deposit:$row->booking_due); ?> &nbsp;&nbsp;<input type="hidden" id="total_row<?php echo $i?>" value="<?php echo ($row->booking_deposit>0?$row->booking_deposit:$row->booking_due); ?>" /></td>
      <?php } ?>
      <td align="center" width="26"><img src='<?php echo getImageSrc("list_remove24.png")?>' width="24" 
      onclick="doRemoveFromCart(<?php echo $row->request_id; ?>, <?php echo $i ?>); return false;"/></td>
    </tr>
    <?php $k = 1 - $k; ?>
    <?php } ?>
    <?php if($show_costs){?>
    <tr style="border-top:#000000 solid thin; line-height:30px">
    	<td colspan="1" align="right"><?php echo JText::_('RS1_VIEW_CART_SCRN_TOTAL_COL_HEAD'); ?></td>
        <td align="right"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?>&nbsp;<span id="display_total"><?php echo number_format($total, 2); ?> </span>&nbsp;&nbsp;</td>
        <td></td>
    </tr> 
    <?php } ?>
 <?php if(false){//if($apptpro_config->enable_coupons == "Yes"){ ?>
     <tr>
        <td valign="top"><?php echo JText::_('RS1_INPUT_SCRN_COUPONS');?></td>
        <td colspan="3"><input name="coupon_code" type="text" id="coupon_code" value="" size="20" maxlength="80" 
              title="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_TOOLTIP');?>" />
              <input type="button" class="button" value="<?php echo JText::_('RS1_INPUT_SCRN_COUPON_BUTTON');?>" onclick="getCoupon()" />
              <div id="coupon_info"></div>
              <input type="hidden" id="coupon_value" />
              <input type="hidden" id="coupon_units" />              
        </td>
    </tr>
 <?php } ?>

  </table>
  <?php echo JText::_('RS1_VIEW_CART_SCRN_TIMEOUT_WARNING_START')." ".$apptpro_config->minutes_to_stale." ".JText::_('RS1_VIEW_CART_SCRN_TIMEOUT_WARNING_END'); ?>
<div id="errors" class="sv_apptpro_errors"><?php echo $err ?></div>
  <p align="centre" class="submit_section">
  <input type="button"id="btnAddMore" class="button" value="<?php echo JText::_('RS1_VIEW_CART_ADD_MORE'); ?>" onclick="doAddMore(); return false;" />
  <?php if($show_non_pay){ ?>

		<?php if($apptpro_config->non_pay_booking_button != "No" || $pay_proc_enabled == false){  ?>
                    <input type="button" id="btnCheckout" class="button" value="<?php echo JText::_('RS1_VIEW_CART_CHECKOUT'); ?>" onclick="doCheckout(0); return false;" 
                      <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> /> 
        <?php } ?>
  <?php } ?>

<?php // put a hidden button on screen in case amount due is $0 and we hide the payment processor button(s)
	if( $apptpro_config->non_pay_booking_button == "No" && $pay_proc_enabled == true) {  ?>
    	<div id="hidden_submit" style="display:none; visibility:hidden">
          <input type="submit" class="button"  name="submit0" id="submit0" onclick="return doSubmit(0);" 
            value="<?php echo JText::_('RS1_INPUT_SCRN_SUBMIT');?>" 
              <?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ echo "disabled";} ?> /> 
        </div>      
<?php } ?>
<?php // Step through all the enabled payment processors and drop in book now buttons.
	foreach($pay_procs as $pay_proc){ 
		// get settings 
		$prefix = $pay_proc->prefix;
		$sql = "SELECT * FROM #__sv_apptpro3_".$pay_proc->config_table;
		try{
			$database->setQuery($sql);
			$pay_proc_settings = NULL;
			$pay_proc_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
		$enable = $prefix."_enable";
		if($pay_proc_settings->$enable == "Yes" && $fd === "No"){
			$submit_function = "doCheckout";
			$isMobile = "yes";
			$isCart = "yes";
	    	include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR.$pay_proc->prefix.DIRECTORY_SEPARATOR.$pay_proc->prefix."_button.php";
		}
	}?>
  
  </p>  
  <!--<input type="hidden" name="option" value="<?php echo $option; ?>" />-->
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="view" id="view" value="cart" />
  <input type="hidden" id="controller" name="controller" value="cart" />
  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" name="row_count" id="row_count" value="<?php echo sv_count_($rows);?>" />
  <input type="hidden" name="sid" id="sid" value="<?php echo $session_id; ?>" />
  <input type="hidden" id="ppsubmit" name="ppsubmit" value="" />			             
  <input type="hidden" id="grand_total" name="grand_total" value="<?php echo $total; ?>" />			             
</div>
</form>
	



