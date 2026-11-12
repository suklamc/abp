<?php
/*
 ****************************************************************
 Copyright (C) 2008-2013 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2013 Soft Ventures, Inc. All rights reserved.
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

?>

<script>
	var sv_event = "click";
	var date_specific_booking = false;

	jQuery(document).ready(function() {
	  jQuery(window).keydown(function(event){
		if(event.keyCode == 13) {
		  event.preventDefault();
		  return false;
		}
	  });
	});	

	if(accordion_hover_open == true){
		sv_event = "click hoverintent";
	}
	jQuery(function() {
	 	jQuery( "#sv_accordion" ).accordion({
			  heightStyle: "content",
			  header: "sv_h3",
			  event: sv_event
		});		
  		jQuery( "#display_grid_date" ).datepicker({
			minDate: <?php echo $mindate;?>,		
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#grid_date",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});

	});

	jQuery.event.special.hoverintent = {
		setup: function() {
		  jQuery( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
		},
		teardown: function() {
		  jQuery( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
		},
		handler: function( event ) {
		  var currentX, currentY, timeout,
			args = arguments,
			target = jQuery( event.target ),
			previousX = event.pageX,
			previousY = event.pageY;
	 
		  function track( event ) {
			currentX = event.pageX;
			currentY = event.pageY;
		  };
	 
		  function clear() {
			target
			  .unbind( "mousemove", track )
			  .unbind( "mouseout", clear );
			clearTimeout( timeout );
		  }
	 
		  function handler() {
			var prop,
			  orig = event;
	 
			if ( ( Math.abs( previousX - currentX ) +
				Math.abs( previousY - currentY ) ) < 7 ) {
			  clear();
	 
			  event = jQuery.Event( "hoverintent" );
			  for ( prop in orig ) {
				if ( !( prop in event ) ) {
				  event[ prop ] = orig[ prop ];
				}
			  }
			  // Prevent accessing the original event since the new event
			  // is fired asynchronously and the old event is no longer
			  // usable (#6028)
			  delete event.originalEvent;
	 
			  target.trigger( event );
			} else {
			  previousX = currentX;
			  previousY = currentY;
			  timeout = setTimeout( handler, 100 );
			}
		  }
	 
		  timeout = setTimeout( handler, 100 );
		  target.bind({
			mousemove: track,
			mouseout: clear
		  });
		}
	  };	
	  
	  	
	function doSubmit(pp){
	
		document.getElementById("errors").innerHTML = document.getElementById("wait_text").value

		// ajax validate form
		result = validateForm();
		//alert("|"+result+"|");

		if(result.indexOf('<?php echo JText::_('RS1_INPUT_SCRN_VALIDATION_OK');?>')>-1){
			document.getElementById("ppsubmit").value = pp;
		    document.body.style.cursor = "wait"; 
			document.frmRequest.task.value = "process_booking_request";
			document.frmRequest.submit();
			return true;
		} else {
			disable_enableSubmitButtons("enable");
			return false;
		}
		return false;
	}
	
	function checkSMS(){
		if(document.getElementById("use_sms").checked == true){
			document.getElementById("sms_reminders").value="Yes";
		} else {
			document.getElementById("sms_reminders").value="No";
		}	
	}
	
	function closeMe(){
		parent.SqueezeBox.close();
	}

	function doReturntoCalendar(){
		document.getElementById("task").value="returntocalendar"
		document.frmRequest.submit();		
	}		
	
	wiz_alert_dialog = jQuery("<div id='wiz_alert_dialog'></div>").dialog({
		autoOpen: false,
		modal: true,
		minWidth: 300,
		resizable: false,
		height: "auto",
		close: function () {
		}			
	});						
	
</script>
<script >
		window.onload = function() {
			if(document.getElementById("resources_slick") != null){
				jQuery('#resources_slick').ddslick({
				//   onSelected: function(data){jQuery('#resources').val(data.selectedData.value);changeResource();}    	   
				   onSelected: function(data){
					   jQuery('#resources').val(data.selectedData.value);
					   if(need_changeResource === 1){
						   changeResource();
					   } else {
						   need_changeResource = 1;
					   }
				}   
			   }); 
			}
			if(document.getElementById("category_id_slick") != null){
			   jQuery('#category_id_slick').ddslick({
				   onSelected: function(data){jQuery('#category_id').val(data.selectedData.value);changeCategory();}           
			   }); 
			}
		<?php if($single_category_mode){ ?>
				if(document.getElementById("category_id_slick") != null){
					jQuery('#category_id_slick').ddslick('select', {index: 1 });
				} else {
					if(document.getElementById("category_id") != null){
						document.getElementById("category_id").options[1].selected=true;
						changeCategory();
					}
				}
				//document.getElementById("category_id").options[1].selected=true;
				//changeCategory();		
		<?php } ?>
			if(document.getElementById("resources")!=null){
				if(document.getElementById("resources").options.length==2){
					if(document.getElementById("resources_slick") != null){
						jQuery('#resources_slick').ddslick('select', {index: 1 });										
						changeResource();
					} else {
						document.getElementById("resources").options[1].selected=true;
						changeResource();
					}
				} else {
					changeResource();
				}
			}

		<?php if($default_resource_specified){ ?>
			if(document.getElementById("resources_slick") != null){
				// get index from value of he normal dropdown..
				//the value for which we are searching
				var searchBy = '<?php echo $default_resource_id;?>';
				
				//#resources_slick is the id of ddSlick selectbox
				jQuery('#resources_slick li').each(function( index ) {				
					  //traverse all the options and get the value of current item
					  var curValue = jQuery( this ).find('.dd-option-value').val();					
					  //check if the value is matching with the searching value
					  if(curValue == searchBy){
						  //if found then use the current index number to make selected    
						  jQuery('#resources_slick').ddslick('select', {index: jQuery(this).index()});
					  }
				});				
				changeResource();
			} else {
				jQuery('#resources').val('<?php echo $default_resource_id;?>');
				changeResource();
			}
		<?php } ?>

		<?php if($default_category_specified){ ?>
			if(document.getElementById("category_id_slick") != null){
				// get index from value of he normal dropdown..
				//the value for which we are searching
				var searchBy = '<?php echo $default_category_id;?>';
				
				//#resources_slick is the id of ddSlick selectbox
				jQuery('#category_id_slick li').each(function( index ) {				
					  //traverse all the options and get the value of current item
					  var curValue = jQuery( this ).find('.dd-option-value').val();					
					  //check if the value is matching with the searching value
					  if(curValue == searchBy){
						  //if found then use the current index number to make selected    
						  jQuery('#category_id_slick').ddslick('select', {index: jQuery(this).index()});
					  }
				});				
				changeCategory();
			} else {
				jQuery('#category_id').val('<?php echo $default_category_id;?>');
				changeCategory();
			}
		<?php } ?>
			submit_section_show_hide("hide");		

			jQuery( "#addto_notification_dialog" ).dialog({ 
				autoOpen: false,
				modal: true,
				buttons: {
					"<?php echo JText::_('RS1_POPUP_BTN_OK');?>": function() {
						if(jQuery("#notification_email").val() == ""){
							jQuery(this).dialog("close");
						} else {
							//Do ajax add to list here
							//alert(jQuery(this).data("resource"));
							do_addtoNotificationList(jQuery(this).data("resource"), 
							jQuery(this).data("startdate"),
							jQuery(this).data("starttime"),
							jQuery("#notification_email").val(),
							jQuery("#chk_remove_from_list").is(':checked')
							);
							//jQuery(this).dialog("close");
						}
					},
					"<?php echo JText::_('RS1_POPUP_BTN_CLOSE');?>": function() {
						jQuery("#results").html("");
						jQuery("#chk_remove_from_list").prop('checked', false);
						jQuery(this).dialog("close");
					}
				},
				open: function( event, ui ) {
					jQuery("#notification_email").val(jQuery("#email").val());
					jQuery("#results").val("");
					}
				});				
		}
		
</script>

<?php if($apptpro_config->requireLogin == "Yes" && $user->guest){ ?>
<script>
	jQuery("#wiz_alert_dialog").html('<?php echo JText::_('RS1_INPUT_SCRN_LOGIN_REQUIRED');?>');			
	wiz_alert_dialog.dialog( "option", "buttons", [ { text: "<?php echo JText::_('RS1_INPUT_SCRN_OK');?>", click: function() { jQuery( this ).dialog( "close" ); } } ] );
	// To have the popup redirect the user to your login screen:
	//	- comment out the wiz_alert_dialog.dialog line above
	//  - un-comment the line below.
	//wiz_alert_dialog.dialog( "option", "buttons", [ { text: "<?php echo JText::_('RS1_INPUT_SCRN_OK');?>", click: function() { window.location="component/users/?view=login" } } ] );
	wiz_alert_dialog.dialog("option", "title", "<?php echo JText::_('RS1_NOTICE');?>").dialog("open");
</script>	
<?php } ?>

