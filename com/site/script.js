/*
 ****************************************************************
 Copyright (C) 2008-2013 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2017 Soft Ventures, Inc. All rights reserved.
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

var xhr = false;
//var xhr2 = false; // need 2 so we can do 2 simultanious ajax calls to get different info
//var xhr3 = false; // need 3 so we can do 3 simultanious ajax calls to get different info
//var xhr4 = false; // need 4 so we can do 4 simultanious ajax calls to get different info
//var xhr5 = false; // need 5 so we can do 5 simultanious ajax calls to get different info
var xhr27 = false; // for mouseovers
var hours = 0.0;
var total = 0.0;
var rate = 0.0;
var startdate = "";
var starttime = "";
var enddate = "";
var endtime = "";
var additionalfee = 0.00;
var feerate = "";
var fee = 0.0;
var res_id = 0;
var res_rate = 0.00;
var submit_status;
//var old_ts = "";
var extras_total_cost = 0.0;
var resource_eb_discount = "0.00"
var resource_eb_discount_unit = "";
var resource_eb_discount_lead = "";
var service_eb_discount = "0.00"
var service_eb_discount_unit = "";
var service_eb_discount_lead = "";
var free_booking_override = 'No';
var need_changeResource = 0;
	
var old_ts = {
	"id": "",
    "ts_width": "0",
	"ts_left": "0",
    "ts_index": "0",
    "ts_height": "0",
	"ts_top": "0"
}
var ts_dur = 0;



function changeCategory(fd){
	var fd_booking = "No";
		if(fd != undefined) { //If fd was passed in, use it
			fd_booking = "Yes";
		}

	// clear old selected timeslot if there is one.
	old_ts["id"] = "";
//	if(document.getElementById("sv_codeblock_submit") != null){		
//		document.getElementById("sv_codeblock_submit").style.display = "none";
//		document.getElementById("sv_codeblock_submit").style.visibility = "hidden";		
//	}
	if(document.getElementById("calcResults") != null){
		document.getElementById("calcResults").style.display = "none";
		document.getElementById("calcResults").style.visibility = "hidden";		
	}

	if(document.getElementById("resources") != null){
		document.getElementById("resources").selectedIndex = 0; // reset resources dropdown
		if(document.getElementById("resources_slick") != null){
			jQuery('#resources_slick').ddslick('select', {index: 1 });
		}

		if(document.getElementById("mode") != null){
			document.getElementById("mode").value = "single_day";
		}
	}

	if(document.getElementById("resource_udfs") != null){
		document.getElementById("resource_udfs").style.display = "none";
		document.getElementById("resource_udfs").style.visibility = "hidden";
	}

	if(document.getElementById("category_id").selectedIndex  === 0){
		// hide stuff
		if(document.getElementById("resources_label") != null){
			document.getElementById("resources_label").style.display = "none";
			document.getElementById("resources_label").style.visibility = "hidden";
		}

		if(document.getElementById("service_selector_div") != null){
			document.getElementById("service_selector_div").style.display = "none";
			document.getElementById("service_selector_div").style.visibility = "hidden";
		}
		if(document.getElementById("service_selector_label") != null){
			document.getElementById("service_selector_label").style.display = "none";
			document.getElementById("service_selector_label").style.visibility = "hidden";
		}

		if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
		document.getElementById("services_div").style.display = "none";
		if(document.getElementById("resources") != null){
			document.getElementById("resources").style.display = "none";
		}
		if(document.getElementById("resources_slick") != null){
			document.getElementById("resources_slick").style.display = "none";
		}
		if(document.getElementById("gad_container") != null){
			document.getElementById("gad_container").style.display = "none";
		}
		if(document.getElementById("subcats_row") != null){
			document.getElementById("subcats_row").style.visibility = "hidden";
			document.getElementById("subcats_row").style.display = "none";
		}
		if(document.getElementById("subcats_div") != null){
			document.getElementById("subcats_div").innerHTML = "";
		}
		if(document.getElementById("service_summary") != null){
			document.getElementById("service_summary").style.display = "none";
			document.getElementById("service_summary").style.visibility = "hidden";
		}
		if(document.getElementById("services") != null){
			document.getElementById("services").style.display = "none";
			document.getElementById("services").style.visibility = "hidden";
		}
		if(document.getElementById("resource_udfs") != null){
			document.getElementById("resource_udfs").style.display = "none";
			document.getElementById("resource_udfs").style.visibility = "hidden";
		}
		if(document.getElementById("resource_seat_types") != null){
			document.getElementById("resource_seat_types").style.display = "none";
			document.getElementById("resource_seat_types").style.visibility = "hidden";
		}
		if(document.getElementById("resource_extras") != null){
			document.getElementById("resource_extras").style.display = "none";
			document.getElementById("resource_extras").style.visibility = "hidden";
		}

		return false;
	} else {
		// if using service selector, hide resources and grid
		if(document.getElementById("service_selector_div") != null){
			if(document.getElementById("resources_label") != null){
				document.getElementById("resources_label").style.display = "none";
				document.getElementById("resources_label").style.visibility = "hidden";
			}
	
			if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
			document.getElementById("services_div").style.display = "none";
			if(document.getElementById("resources") != null){
				document.getElementById("resources").style.display = "none";
			}
			if(document.getElementById("resources_slick") != null){
				document.getElementById("resources_slick").style.display = "none";
			}
			if(document.getElementById("gad_container") != null){
				document.getElementById("gad_container").style.display = "none";
			}
			if(document.getElementById("subcats_row") != null){
				document.getElementById("subcats_row").style.visibility = "hidden";
				document.getElementById("subcats_row").style.display = "none";
			}
			if(document.getElementById("subcats_div") != null){
				document.getElementById("subcats_div").innerHTML = "";
			}
			if(document.getElementById("service_summary") != null){
				document.getElementById("service_summary").style.display = "none";
				document.getElementById("service_summary").style.visibility = "hidden";
			}
			if(document.getElementById("services") != null){
				document.getElementById("services").style.display = "none";
				document.getElementById("services").style.visibility = "hidden";
			}
			if(document.getElementById("resource_udfs") != null){
				document.getElementById("resource_udfs").style.display = "none";
				document.getElementById("resource_udfs").style.visibility = "hidden";
			}
			if(document.getElementById("resource_seat_types") != null){
				document.getElementById("resource_seat_types").style.display = "none";
				document.getElementById("resource_seat_types").style.visibility = "hidden";
			}
			if(document.getElementById("resource_extras") != null){
				document.getElementById("resource_extras").style.display = "none";
				document.getElementById("resource_extras").style.visibility = "hidden";
			}
			
		}
	}

	// if there are sub-categories we need to fetch them rather than any resources
	if(document.getElementById("sub_cat_count").value != "0"){
		if(document.getElementById("sub_category_id")!=null){
			// if we are already showing a sub-category, we need to clear that out 
			document.getElementById("subcats_row").style.visibility = "hidden";
			document.getElementById("subcats_row").style.display = "none";
			document.getElementById("subcats_div").innerHTML = "";
		}
		getSubCategories(document.getElementById("category_id").value, fd_booking);
		// hide resource ans service selector

		return false;
	}
	

	if(document.getElementById("mode") != null){		
		if(document.getElementById("category_id").value === "0"){
			document.getElementById("table_here").innerHTML = "";
			document.getElementById("table_here").visible = false;
			document.getElementById("table_here").display = "none";	
			return false;
		}
		if(document.getElementById("service_selector_div") == null){
			document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;

			document.getElementById("gad_container").style.display = "";
			buildTable();
		}
		
	} else {
		document.getElementById("slots").style.visibility = "hidden";
		document.getElementById("startdate").value = "";
	}

	if(document.getElementById("service_selector_div") != null){
		// get services for service_selector dropdown
		getServices_for_category();
	} else {
		getResources(fd_booking);
	}
	
}

function changeResource(){
	if(document.getElementById("errors") != null){
		document.getElementById("errors").innerHTML = "";
	}
	if(document.getElementById("resources") === null){
		return false;
	}

	// clear old selected timeslot if there is one.
	old_ts["id"] = "";
	
	if(document.getElementById("resources") != null){

		if(document.getElementById("resources_label") != null){
			document.getElementById("resources_label").style.display = "";
			document.getElementById("resources_label").style.visibility = "visible";
		}
		document.getElementById("selected_resource_id").value=document.getElementById("resources").value;
		document.getElementById("user_duration").value="0";
		
//		if(document.getElementById("resources").value === "0"){ // removed Jan 21/12 while woking on service duration issue on resource change
			document.getElementById("services").style.display = "none";
			document.getElementById("services").style.visibility = "hidden";
			document.getElementById("services_div").style.display = "none";
			document.getElementById("services_div").style.visibility = "hidden";
			if(document.getElementById("service_name") != null){
				for (var loop=0; loop < document.getElementById("service_name").options.length; loop++) {
					document.getElementById("service_name").options[loop] = null; // remove the option
				}
				document.getElementById("service_name").options.length = 0;
			}
			if(document.getElementById("datetime") != null){
				document.getElementById("datetime").style.display = "none";
			}
			if(document.getElementById("service_summary") != null){
				document.getElementById("service_summary").style.display = "none";
				document.getElementById("service_summary").style.visibility = "hidden";
			}
			if(document.getElementById("booking_detail") != null){
				document.getElementById("booking_detail").style.visibility = "hidden";
			}

//		}

		submit_section_show_hide("hide");

	}

	if(document.getElementById("mode") != null){
		if(document.getElementById("resources").value === "0"){
			document.getElementById("mode").value = "single_day";
		} else {
			document.getElementById("mode").value = "single_resource";
		}
		document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;
		document.getElementById("gad_container").style.display = "";
		buildTable();
		if(document.getElementById("mobile")!=null){
			if(document.getElementById("mobile").value == "Yes"){	
				// in mobile gad or wizard, only one resource can be shown so the grid datepicker should reflect the resource settings
				getCalDays("display_grid_date");	
			}
		}
	} else {
		document.getElementById("slots").style.visibility = "hidden";
		//document.getElementById("startdate").value = document.getElementById("wait_text").value;
		getCalDays("display_startdate");
	}
	
	if(document.getElementById("coupon_value") != null){
		document.getElementById("coupon_info").innerHTML = "";
		document.getElementById("coupon_value").value = "0";
		document.getElementById("coupon_units").value = "";
	}
	
	getServices(true);
	getResourceUFDs();
	getResourceSeatTypes();
	getExtras();
	hideTotal();
//	if(document.getElementById("resources_slick") != null){
//		// turn on ddslick for the resource_slick ddl
//		jQuery('#resources_slick').ddslick(); 
//		// we need to set the onSelected to call changeResource but cannot do it here are we are in changeResource and would cause recursion
//		jQuery('#resources_slick').ddslick('select', {index: jQuery('#resources').val() });
//	}
	
}



function getSlots(){
	// JQuery verison of getSlots	
	document.getElementById("slots").innerHTML = document.getElementById("wait_text").value;
	document.getElementById("slots").style.visibility = "visible";
	if(document.getElementById("booking_detail") != null){
		document.getElementById("booking_detail").style.display = "none";
	}

	if(document.getElementById("errors") != null){
		document.getElementById("errors").innerHTML = "";
	}

	if(document.getElementById("resources") === null){
		document.getElementById("slots").style.visibility = "hidden";
		return false;
	}

	if(document.getElementById("resources").value === "0"){
		document.getElementById("slots").style.visibility = "hidden";
		return false;
	}

	document.getElementById("enddate").value = document.getElementById("startdate").value;
	
	changeDatePicker();
	jQuery.noConflict();
	var calldata = "startdate=" + document.getElementById("startdate").value;
	calldata = calldata + "&res=" + document.getElementById("resources").value;
	calldata = calldata + "&reg=" + document.getElementById("reg").value;
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	if(document.getElementById("mobile")!=null){
		calldata = calldata + "&mobile=" + document.getElementById("mobile").value;	
	}
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			document.getElementById("slots").innerHTML = data;
			set_starttime();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get getSlots, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get getSlots:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}


function getCalDays(element_name){
	// JQuery verison of getCalDays	
	if(document.getElementById("resources") === null){
		return false;
	}
	if(document.getElementById("resources").value === "0"){
		return false;
	}
	if(document.getElementById("datetime") != null){
		document.getElementById("datetime").style.display = "";
	}
	
	jQuery.noConflict();

	var calldata = "res=" + document.getElementById("resources").value;
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	calldata = calldata + "&el_name=" + element_name;
	calldata = calldata + "&caldays=yes";
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			eval(data.msg);
			if(document.getElementById("datetime") != null){
				document.getElementById("datetime").style.display = "";
			}
			if(document.getElementById("preset_date").value == "" || 
				document.getElementById("preset_date").value == document.getElementById("select_date_text").value){
					document.getElementById("startdate").value = document.getElementById("select_date_text").value;
			} else {
				document.getElementById("startdate").value = document.getElementById("preset_date").value;
				document.getElementById("display_startdate").value = document.getElementById("preset_date").value;
				getSlots();
			}
			//document.getElementById("anchor1").style.display = "";
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get Calendar days, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get Calendar days:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}


function set_starttime(){
	
	if(document.getElementById("errors") != null){
		document.getElementById("errors").innerHTML = "";
	}
	
	var start = document.getElementById("timeslots").value;
	var temp = new Array();
	temp = start.split(',');
		
	document.getElementById("starttime").value = temp[0];
	document.getElementById("endtime").value = temp[1];
	document.getElementById("endtime_original").value = temp[1];
	if(document.getElementById("enable_payproc").value === 'Yes' 
		|| document.getElementById("non_pay_booking_button").value === "DO" || document.getElementById("non_pay_booking_button").value === "DAB" ){
		res_id = document.getElementById("resources").value;
		calcTotal();
	}
	if(temp[2] == "Yes"){
		// free booking 
		if(document.getElementById("grand_total") != null){
			document.getElementById("grand_total").value = "0.00";
		}
	}
	return true;
}


function getResources(fd, service_id){
	// JQuery verison of getResources	
	
	var cat_id ="";
	
	// is this called on changing category
	if(service_id === undefined){
		if(document.getElementById("category_id").value === "0"){
			return false;
		}
		cat_id = document.getElementById("category_id").value;
		
		if(document.getElementById("sub_category_id") != null){
			if(document.getElementById("sub_category_id").value === "0"){
				return false;
			}
			cat_id = document.getElementById("sub_category_id").value;
		}
	}
	// is called on changing service selector (screen running in select service first mode)	
	if (service_id === undefined) {
		service_id = "";	
	}		
		
	if(document.getElementById("service_selector_div") != null){
		// turn on service selector rather than resource dropdown
		document.getElementById("service_selector_div").style.display = "";
		document.getElementById("service_selector_div").style.visibility = "visible";
		
	} else {
		// turn on resource dropdown
		document.getElementById("resources_div").style.display = "";
		document.getElementById("resources_div").innerHTML = document.getElementById("wait_text").value;
		document.getElementById("resources_div").style.visibility = "visible";
		if(document.getElementById("resources_for_service") != null){
			document.getElementById("resources_for_service").style.display = "";
			document.getElementById("resources_for_service").style.visibility = "visible";
		}
	}

	document.getElementById("services").style.display = "none";
	document.getElementById("services").style.visibility = "hidden";
	document.getElementById("services_div").style.display = "none";
	document.getElementById("services_div").style.visibility = "hidden";
	if(document.getElementById("service_summary") != null){
		document.getElementById("service_summary").style.display = "none";
		document.getElementById("service_summary").style.visibility = "hidden";
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "cat=" + cat_id;
	if(document.getElementById("gridwidth")!=null){
		// gad
		data = data + "&gad=Yes";
	} else {
		data = data + "&gad=No";
	}
	data = data + "&browser=" + BrowserDetect.browser;
	data = data + "&reg=" + document.getElementById("reg").value;	
	data = data + "&fd="+fd;
	data = data + "&srv_id="+service_id;
	data = data + "&res=yes";
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async:false,  // needed for ddslick
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("resources_div").innerHTML = data;			
			if(document.getElementById("resources").options.length===2){
				if(document.getElementById("resources_slick") != null){
					jQuery('#resources_slick').ddslick({
					   onSelected: function(data){jQuery('#resources').val(data.selectedData.value);changeResource();}           
					}); 
					jQuery('#resources_slick').ddslick('select', {index: 1 });
					// set by init of ddslick
					//document.getElementById("resources").options[1].selected=true;
					//changeResource();
				} else {
					document.getElementById("resources").options[1].selected=true;
					changeResource();
				}
			} else if(document.getElementById("resources").options.length > 2){
				if(document.getElementById("single_day") != null){
					document.getElementById("mode").value = "single_day";
				}

				if(document.getElementById("resources_slick") != null){
					jQuery('#resources_slick').ddslick({
					   onSelected: function(data){jQuery('#resources').val(data.selectedData.value);changeResource();}           
					}); 
				}
			}
			if(document.getElementById("resources").options.length > 1){
				if(document.getElementById("resources_label") != null){
					document.getElementById("resources_label").style.display = "";
					document.getElementById("resources_label").style.visibility = "visible";
				}
			} else {
				if(document.getElementById("resources_label") != null){
					document.getElementById("resources_label").style.display = "none";
					document.getElementById("resources_label").style.visibility = "hidden";
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getResources, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getResources:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	 
	// cat with single resource, line below casuing extra table load. Removed Feb 21/16 
	//changeResource(); 
	return true;

}

var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index === -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();


function doCancel(){
	// JQuery verison of doCancel	
	if(document.getElementById("cancellation_id")!=null && document.getElementById("cancellation_id").value === ""){
		alert(document.getElementById("cancellation_id").title);
		return false;
	}
	
	var cancel_reason ="";
	if(document.getElementById("cancel_reason_prompt")!=null){
		get_cancel_reason = prompt(document.getElementById("cancel_reason_prompt").value);		
		if(get_cancel_reason == null){
			return false;
		} else {
			cancel_reason = get_cancel_reason;
		}
	}
	
	if(document.getElementById("wait_text")!=null){
		document.getElementById("cancel_results").innerHTML = document.getElementById("wait_text").value;
		document.getElementById("cancel_results").style.visibility = "visible";
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "cancellation_id=" + encodeURIComponent(document.getElementById("cancellation_id").value);
	// need local date/time as yyyy-mm-dd-hh-mm
	var currentTime = new Date();
	data = data + "&userDateTime=" + currentTime.getFullYear() + "-" + (currentTime.getMonth() + 1) + "-" + currentTime.getDate();
	data = data + " " + currentTime.getHours() + ":" + currentTime.getMinutes() + ":00";
	data = data + "&cancel_reason="+cancel_reason;
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);

	var ret_val = "";
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: true,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=cancel_booking&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			ret_val = data;
			document.getElementById("cancel_results").innerHTML = data;
			// if being done from my bookings
			if(document.getElementById("view").value === "mybookings"){
				alert(removeHTMLTags(data));
				return true;
			}			
			// refresh grid to remove booking
			changeDate();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get doCancel, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get doCancel:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	return(ret_val);
}
 

function doDelete(){
	// JQuery verison of doDelete	
	if(document.getElementById("cancellation_id").value === ""){
		alert(document.getElementById("cancellation_id").title);
		return false;
	}
	document.getElementById("cancel_results").innerHTML = document.getElementById("wait_text").value;
	document.getElementById("cancel_results").style.visibility = "visible";
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "cancellation_id=" + encodeURIComponent(document.getElementById("cancellation_id").value);
	// need local date/time as yyyy-mm-dd-hh-mm
	var currentTime = new Date();
	data = data + "&userDateTime=" + currentTime.getFullYear() + "-" + (currentTime.getMonth() + 1) + "-" + currentTime.getDate();
	data = data + " " + currentTime.getHours() + ":" + currentTime.getMinutes() + ":00";
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);

	var ret_val = "";
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=delete_booking&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			ret_val = data;
			document.getElementById("cancel_results").innerHTML = data;
			// if being done from my bookings
			if(document.getElementById("view").value === "mybookings"){
				alert(removeHTMLTags(data));
				window.location.reload();
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get doDelete, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get doDelete:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	return(ret_val);
}


function validateForm(NoCaptcha){
	// JQuery verison of validateForm	
	disable_enableSubmitButtons("disable");

	if (typeof NoCaptcha === 'undefined') {
		if(document.getElementById("recap_msg")!=null){
			if(document.getElementById("g-recaptcha-response")!=null){
				if(document.getElementById("g-recaptcha-response").value == ""){
					document.getElementById("errors").innerHTML = document.getElementById("recap_msg").value;
					return "";
				}
			}	
		}
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "name=" + encodeURIComponent(document.getElementById("name").value);
	data = data + "&phone=" + encodeURIComponent(document.getElementById("phone").value);
	if(document.getElementById("sms_phone")!=null){
		data = data + "&sms_phone=" + encodeURIComponent(document.getElementById("sms_phone").value);
		if(document.getElementById("use_sms").checked){
			data = data + "&use_sms=Checked";
		}
	}
	data = data + "&email=" + encodeURIComponent(document.getElementById("email").value);
	data = data + "&scrn=" + document.getElementById("screen_type").value;
	// To add service validation in fe_val you will need to un-comment the lines below so service is sent
//		if(document.getElementById("service_name") != null){
//			if(document.getElementById("service_name").options.length > 0){
//				var selected_id = document.getElementById("service_name").options[document.getElementById("service_name").selectedIndex].value;
//				data = data + "&srv=" + selected_id;
//			}
//		}
	var udf_count = parseInt(document.getElementById("udf_count").value);
	if(document.getElementById("res_udf_count")!=null){
		// add resource specific
		udf_count += parseInt(document.getElementById("res_udf_count").value);
	}
	data = data + "&udf_count=" + udf_count;
	for(i=0; i<udf_count; i++){
		// To only send required fields to validation 
//			temp_name = "user_field" + i + "_is_required";
//			if(document.getElementById("temp_name ")!=null){
//				// if UDF has _is_required, check to see if it is NO, if so do not send.
//				if(document.getElementById(temp_name).value == "No"){
//					// not a required UDF, do not send
//					continue;
//				}
//			} else {
//				// if UDF has no is required, do not send it
//				continue;
//			}
		udf_name = "user_field" + i + "_label";
		if(document.getElementById(udf_name)!=null){ 
			data = data + "&" + udf_name + "=" + encodeURIComponent(document.getElementById(udf_name).innerHTML);		
			udf_name = "user_field" + i + "_value";
			if(jQuery("#"+udf_name).attr('type') === "checkbox"){
				if(document.getElementById(udf_name).checked){
					if(document.getElementById(udf_name)!=null){ data = data + "&" + udf_name + "=" + 'Checked';}
				} else {
					if(document.getElementById(udf_name)!=null){ data = data + "&" + udf_name + "=" + '';}
				}
			} else if(jQuery("#"+udf_name).attr('type') === "radio"){
				var checked_value = getCheckedValue(udf_name);
				data = data + "&" + udf_name + "=" + checked_value;
			} else {
				if(document.getElementById(udf_name)!=null){ data = data + "&" + udf_name + "=" + encodeURIComponent(document.getElementById(udf_name).value);}		
			}
			udf_name = "user_field" + i + "_is_required";
			if(document.getElementById(udf_name)!=null){ data = data + "&" + udf_name + "=" + encodeURIComponent(document.getElementById(udf_name).value);}
		}
	}
	
	if(document.getElementById("category_id")!=null){
		data = data + "&category_id=" + document.getElementById("category_id").value;
	} else{
		data = data + "&category_id=-1";
	}
	if(document.getElementById("resources")!=null){
		if(document.getElementById("mode")===null){
			// non gad
			data = data + "&resource=" + document.getElementById("resources").value;
		} else {
			if(document.getElementById("gad_mobile_simple")!=null){
				// gad is in use but mobile device is switched to simple
				data = data + "&resource=" + document.getElementById("resources").value;
			} else {
				data = data + "&resource=" + document.getElementById("selected_resource_id").value;
			}
		}

	} else{
		data = data + "&resource=-1";
	}
	data = data + "&user_id=" + document.getElementById("user_id").value;
	data = data + "&startdate=" + document.getElementById("startdate").value;
	data = data + "&starttime=" + document.getElementById("starttime").value;
	data = data + "&enddate=" + document.getElementById("enddate").value;
	data = data + "&endtime=" + document.getElementById("endtime").value;	
	if(document.getElementById("seat_type_count")!=null && document.getElementById("seat_type_count").value != 0){
		var seat_count = 0; 
		for(i=0; i<parseInt(document.getElementById("seat_type_count").value); i++){
			seat_name = "seat_"+i;
			if(document.getElementById(seat_name)!=null){ // null if no resource yet selected
				seat_count = seat_count + parseInt(document.getElementById(seat_name).value);
			}
		}
		data = data + "&booked_seats=" + seat_count;	
	}
	if(document.getElementById("PayPal_mode")!=null){
		data = data + "&PayPal_mode=" + document.getElementById("PayPal_mode").value;
		if(document.getElementById("grand_total")!=null){
			data = data + "&PayPal_due=" + document.getElementById("grand_total").value;
		}
	}
	if(document.getElementById("recaptcha_challenge_field")!=null){
		data = data + "&recap_chal=" + document.getElementById("recaptcha_challenge_field").value;
		data = data + "&recap_resp=" + document.getElementById("recaptcha_response_field").value;
	}
	
	if(document.getElementById("gap")!=null){
		if(document.getElementById("res_spec_gap").value != 0){
			// resource specific gap overrides component level gap
			data = data + "&gap=" + document.getElementById("res_spec_gap").value;	
		} else {
			data = data + "&gap=" + document.getElementById("gap").value;	
		}
	}

	data = data + "&browser=" + BrowserDetect.browser;
	data = data.replace(/'/g, "&rsquo;");
	//alert(data);

	var ret_val = "";
    jQuery.ajax({               
		type: "POST",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_validate&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			ret_val = data;
			document.getElementById("errors").innerHTML = data;
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get validateForm, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get validateForm:\n"+xhr.status + " - " + thrownError);
			}
			disable_enableSubmitButtons("enable");			
      	}
	 });
	return(ret_val);
}


function validateFormEdit(){
	// JQuery verison of validateFormEdit	
	if(document.getElementById("saveLink") != null){
		document.getElementById("saveLink").disabled = true;
	}
	if(document.getElementById("closeLink") != null){
		document.getElementById("closeLink").disabled = true;
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "name=" + encodeURIComponent(document.getElementById("name").value);
	data = data + "&phone=" + encodeURIComponent(document.getElementById("phone").value);
	data = data + "&email=" + encodeURIComponent(document.getElementById("email").value);
	data = data + "&request=" + document.getElementById("id_requests").value;
	data = data + "&request_status=" + document.getElementById("request_status").value;
	data = data + "&resource=" + document.getElementById("resource").value;
	data = data + "&user_id=" + document.getElementById("user_id").value;
	data = data + "&startdate=" + document.getElementById("startdate").value;
	data = data + "&starttime=" + document.getElementById("starttime").value;
	data = data + "&enddate=" + document.getElementById("enddate").value;
	data = data + "&endtime=" + document.getElementById("endtime").value;	
	if(document.getElementById("seat_type_count")!=null && document.getElementById("seat_type_count").value != 0){
		var seat_count = 0; 
		for(i=0; i<parseInt(document.getElementById("seat_type_count").value); i++){
			seat_name = "seat_"+i;
			if(document.getElementById(seat_name)!=null){ // null if no resource yet selected
				seat_count = seat_count + parseInt(document.getElementById(seat_name).value);
			}
		}
		data = data + "&booked_seats=" + seat_count;	
	}
	data = data.replace(/'/g, "&rsquo;");
	//alert(data);

	var ret_val = "";
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_validate_edit&format=raw",
		data: data,
		success: function(data) {
			ret_val = data;
			document.body.style.cursor = "default";
			document.getElementById("errors").innerHTML = data;
	
			if(document.getElementById("saveLink") != null){
				document.getElementById("saveLink").disabled = false;
			}
			if(document.getElementById("closeLink") != null){
				document.getElementById("closeLink").disabled = false;
			}	
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get validateFormEdit, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get validateFormEdit:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	return(ret_val);
}


function changeResourceFE(){
	// JQuery verison of changeResourceFE	
	if(document.getElementById("resource").value === "0"){
		return false;
	}	
	if(document.getElementById("require_validation")!= null){
		document.getElementById("require_validation").value = "Yes";
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "res=" + document.getElementById("resource").value;
	data = data + "&adminserv=yes";
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("service").options.length=0;
			if(outMsg.length>2){
				eval(data);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get changeResourceFE, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get changeResourceFE:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
}


function buildTable(){
	// JQuery verison of buildTable	

	if(document.getElementById("resources") == null && document.getElementById("category_id") == null){
		return;
	}	
	
	var griddiv = null;
	if(document.getElementById("sv_apptpro_request_gad_mobile")!=null){
		griddiv = document.getElementById("sv_apptpro_request_gad_mobile");
	   	curr_width = parseInt(griddiv.clientWidth * .95);		
	} else {
		griddiv = document.getElementById("sv_apptpro_request_gad");
		if(document.getElementById("accordion_view")!=null){
			if(document.getElementById("device")!=null){
				if(document.getElementById("device")!="mobile"){
					if(document.getElementById("device").value == "tablet"){
						curr_width = parseInt(griddiv.clientWidth * .82);
					} else if(document.getElementById("device").value == "iPad"){
						curr_width = parseInt(griddiv.clientWidth * .82);
					} else {
						curr_width = parseInt(griddiv.clientWidth * .85);
					}
				}
			} else {
				curr_width = parseInt(griddiv.clientWidth * .90);
			}
		} else {
			if(document.getElementById("device")!=null){
				if(document.getElementById("device").value == "tablet"){
					curr_width = parseInt(griddiv.clientWidth * .92);
				} else if(document.getElementById("device").value == "iPad"){
					curr_width = parseInt(griddiv.clientWidth * .92);
				} else {
					curr_width = parseInt(griddiv.clientWidth * .95);
				}
			} else {
				curr_width = parseInt(griddiv.clientWidth * .95);
			}
		}
	}
//   	curr_width = parseInt(griddiv.clientWidth * .95);
	if(document.getElementById("mobile")!=null){
		if(document.getElementById("mobile").value == "Yes"){
			if(document.getElementById("accordion_view")!=null){
				curr_width = parseInt(griddiv.clientWidth * .65);
			} else {
				curr_width = parseInt(griddiv.clientWidth * .70);
			}
		}
	}
	//alert(curr_width);
	document.getElementById("booking_detail").style.display = "none";
	document.getElementById("booking_detail").style.visibility = "hidden";
	document.getElementById("selected_resource_id").value="-1";
	document.getElementById("startdate").value="";
	document.getElementById("enddate").value="";
	document.getElementById("starttime").value="";
	document.getElementById("endtime").value="";
	document.getElementById("errors").innerHTML = "";
	document.getElementById("user_duration").value="0";
	submit_section_show_hide("hide");
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "gridstarttime=" + document.getElementById("gridstarttime").value;
	data = data + "&gridendtime=" + document.getElementById("gridendtime").value;
	if(document.getElementById("category_id")!=null){
		if(document.getElementById("sub_category_id")!=null){
			data = data + "&category=" + document.getElementById("sub_category_id").value;
		} else {
			data = data + "&category=" + document.getElementById("category_id").value;
		}
	} else{
		data = data + "&category=0";
	}
	if(document.getElementById("resources")!=null){
		data = data + "&mode=" + document.getElementById("mode").value;	
		data = data + "&resource=" + document.getElementById("resources").value;	
	} else {
		data = data + "&mode=single_day";	
		data = data + "&resource=0";	
	}
	if(document.getElementById("preset_service")!=null){
		if(document.getElementById("preset_service").value != ""){
			data = data + "&srv_res_ids=" +document.getElementById("preset_service_res_ids").value;	
		}
	}
	data = data + "&grid_date=" + document.getElementById("grid_date").value;	
	data = data + "&grid_days=" + document.getElementById("grid_days").value;	
	if(document.getElementById("gridwidth")!=null){
		data = data + "&gridwidth=" + document.getElementById("gridwidth").value;	
		data = data + "&namewidth=" + document.getElementById("namewidth").value;	
	} else {
		data = data + "&gridwidth=" + curr_width;	
		if(document.getElementById("mobile")===null){
			data = data + "&namewidth=" + (parseInt(curr_width) * .15);	
		} else {
			data = data + "&namewidth=" + (parseInt(curr_width) * .25);	
		}
	}
	data = data + "&reg=" + document.getElementById("reg").value;	
	if(document.getElementById("mobile")!=null){
		data = data + "&mobile=" + document.getElementById("mobile").value;	
	}
	if(document.body.clientWidth < 980){
		data = data + "&mobile=Yes";	
	}

	if(document.getElementById("fd")!=null){
		data = data + "&fd=Yes";	
	}
	if(document.getElementById("gap")!=null){
		data = data + "&gap=" + document.getElementById("gap").value;	
	}
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);
	if(document.getElementById("mobile")===null){
		document.body.style.cursor = "wait";    
	}
	//alert(data);
	var task = "ajax_gad";
	if(document.getElementById("gad2").value === "Yes" || document.getElementById("mobile") != null){
		task = "ajax_gad2";
	}
	var url = presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task="+task+"&format=raw";
	//alert(url);
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: url,
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			var show_grid_for_single_resource_mobile = false;
			if(document.getElementById("mode") != null){
				if(document.getElementById("mode").value == "single_resource"){
					show_grid_for_single_resource_mobile = true;
				}
			}
			if((document.getElementById("resources") != null && document.getElementById("resources").selectedIndex == 0 && !show_grid_for_single_resource_mobile) 
				&& document.getElementById("mobile") != null){
				document.getElementById("gad_container").style.visibility = "hidden";
				document.getElementById("gad_container").style.display = "none";
				document.getElementById("table_here").style.visibility = "hidden";
				document.getElementById("table_here").style.display = "none";
			} else {				
				document.getElementById("gad_container").style.visibility = "visible";
				document.getElementById("gad_container").style.display = "";
				document.getElementById("table_here").style.visibility = "visible";
				document.getElementById("table_here").style.display = "";
			}

			if(document.getElementById("mobile")===null){
				document.body.style.cursor = "default";    
			}
			document.getElementById("table_here").innerHTML = data;

			// if using ddslick with images in the grid, you can use the code below to make the slots taller
			if(document.getElementById("mode").value == "single_day" && document.getElementById("ddslick_grid_image")!=null){
				var new_height = document.getElementById("ddslick_grid_image").value + "px"; // use a number that works for the image sizes you have
				var myElements = document.querySelectorAll(".sv_gad_timeslot_available");
				for (var i = 0; i < myElements.length; i++) {
					myElements[i].style.height = new_height;
				}
				var myElements = document.querySelectorAll(".sv_gad_timeslot_booked");
				for (var i = 0; i < myElements.length; i++) {
					myElements[i].style.height = new_height;
				}
				var myElements = document.querySelectorAll(".sv_gad_timeslot_book-off");
				for (var i = 0; i < myElements.length; i++) {
					myElements[i].style.height = new_height;
				}
				var myElements = document.querySelectorAll(".sv_gad_timeslot_gap");
				for (var i = 0; i < myElements.length; i++) {
					myElements[i].style.height = new_height;
				}
				var myElements = document.querySelectorAll(".sv_gad_non_work_day");
				for (var i = 0; i < myElements.length; i++) {
					myElements[i].style.height = new_height;
				}				
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildTable, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildTable:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
}

function changeGrid(){

	if(document.getElementById("category_id")!=null){
		if(document.getElementById("category_id").value === "0"){
		document.getElementById("table_here").innerHTML = "";
		document.getElementById("table_here").visible = false;
		document.getElementById("table_here").display = "none";	
			return false;
		}
	}

	document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;
	
	document.getElementById("gad_container").style.display = "";
	buildTable();
	
}

function changeDate(){

	if(document.getElementById("errors") != null){
		document.getElementById("errors").innerHTML = "";
	}

	if(document.getElementById("category_id")!=null){
		if(document.getElementById("category_id").value === "0"){
		document.getElementById("table_here").innerHTML = "";
		document.getElementById("table_here").visible = false;
		document.getElementById("table_here").display = "none";	
			return false;
		}
	}

	//changeDatePicker();
	
	//document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;
	
	if(document.getElementById("grid_date_floor")!=null){
		if(Date.parse(jQuery("#grid_date").val()) <= Date.parse(jQuery("#grid_date_floor").val())){
			jQuery("#btnPrev").attr('disabled','disabled');
		} else {
			jQuery("#btnPrev").removeAttr('disabled');
		}
	}
	document.getElementById("gad_container").style.display = "";
	buildTable();
	
}

function gridPrevious(){
	// this may seem redundant as the button is disabled but if an image is used you can't disable an image so we chcek again.
	if(document.getElementById("grid_date_floor")!=null){
		if(Date.parse(jQuery("#grid_date").val()) <= Date.parse(jQuery("#grid_date_floor").val())){
			return;
		}
	}
	document.getElementById("grid_date").value = document.getElementById("grid_previous").value;		
	changeDate();
	// set datepicker from grid_date
	var d = jQuery.datepicker.parseDate("yy-mm-dd", document.getElementById("grid_date").value);
	jQuery('#display_grid_date').datepicker('setDate', d);	
}

function gridNext(){
	document.getElementById("grid_date").value = document.getElementById("grid_next").value;	
	changeDate();
	// set datepicker from grid_date
	var d = jQuery.datepicker.parseDate("yy-mm-dd", document.getElementById("grid_date").value);
	jQuery('#display_grid_date').datepicker('setDate', d);	
}


function selectTimeslot(selected, e){
	//alert(selected);
	// may be used in the future to detect a shift-click
	//shiftPressed=e.shiftKey;
	//alert(shiftPressed);
	
	submit_section_show_hide("show")

	// clear any coupon discount that may have been applied to a prevuiously seleect slot.
	if(document.getElementById("coupon_code") != null && document.getElementById("coupon_code").value != ""){
		document.getElementById("coupon_code").value = "";
		document.getElementById("coupon_info").innerHTML = "";
		document.getElementById("coupon_value").value = "0.00";		
	}
	
	var starttime_from_slot = "";
	var endtime_from_slot = "";
	document.getElementById("errors").innerHTML = "";

	document.getElementById("booking_detail").style.display = "";
	document.getElementById("booking_detail").style.visibility = "visible";
	
	ary_selected = selected.split("|");
	document.getElementById("selected_resource_id").value=ary_selected[0];
	res_id = document.getElementById("selected_resource_id").value;

//  the replace messes up Chinese resource names	
//	document.getElementById("selected_resource").innerHTML = svBase64.decode(ary_selected[1].replace(/\+/g,  " "));
	if(document.getElementById("selected_resource") != null){
		document.getElementById("selected_resource").innerHTML = svBase64.decode(ary_selected[1]);
	}
	document.getElementById("startdate").value=ary_selected[2];
	document.getElementById("enddate").value=ary_selected[2];
	if(document.getElementById("selected_date") != null){
//		document.getElementById("selected_date").innerHTML = svBase64.decode(ary_selected[3].replace(/\+/g,  " "));
		document.getElementById("selected_date").innerHTML = svBase64.decode(ary_selected[3]);
	}
	starttime_from_slot = ary_selected[4];
	document.getElementById("starttime").value = starttime_from_slot
	document.getElementById("selected_starttime").innerHTML = svBase64.decode(ary_selected[5].replace(/\+/g,  " "));

	endtime_from_slot = ary_selected[6];
	document.getElementById("endtime").value = endtime_from_slot;
	document.getElementById("endtime_original").value = endtime_from_slot;// used as starting point when adding extras durations
	document.getElementById("selected_endtime").innerHTML = svBase64.decode(ary_selected[7].replace(/\+/g,  " "));
	
	res_spec_gap = ary_selected[9];
	if(document.getElementById("res_spec_gap") != null){
		document.getElementById("res_spec_gap").value = res_spec_gap;
	}
	
	ts_div = ary_selected[8];

	free_booking_override = ary_selected[10];

	ts_dur = (Date.parse(document.getElementById("startdate").value + " " + document.getElementById("endtime").value) - Date.parse(document.getElementById("startdate").value + " " + document.getElementById("starttime").value))/60000;

	if(old_ts['id'] != "" && (old_ts['ts_width'] != 0 || old_ts['ts_height'] != 0)){
		// there is an old ts (previously selected) so set it back
		if(document.getElementById(old_ts['id']) != null){
			if(document.getElementById("gad2") != null && document.getElementById("gad2").value == "Yes"){
			    document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available_timeony";
			} else {
			    document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available";
			}
			if(document.getElementById("pxm") != null){
				document.getElementById(old_ts['id']).style.width = old_ts['ts_width']+"px";
				document.getElementById(old_ts['id']).style.left = old_ts['ts_left'];
				document.getElementById(old_ts['id']).style.zIndex = parseInt(old_ts['ts_index']);
				old_ts['ts_width'] = 0;
				old_ts['ts_left'] = 0;
				old_ts['ts_index'] = 0;
			}
			if(document.getElementById("pxm2") != null){
				document.getElementById(old_ts['id']).style.height = old_ts['ts_height']+"px";
				document.getElementById(old_ts['id']).style.top = old_ts['ts_top'];
				document.getElementById(old_ts['id']).style.zIndex = parseInt(old_ts['ts_index']);
				old_ts['ts_height'] = 0;
				old_ts['ts_top'] = 0;
				old_ts['ts_index'] = 0;
			}
		}
	}

	if(document.getElementById("gad2") != null && document.getElementById("gad2").value == "Yes" || (document.getElementById("mobile") != null && document.getElementById("mobile").value == "Yes")){
		document.getElementById(ary_selected[8]).className = "sv_gad_timeslot_selected_timeony";
	} else {
		document.getElementById(ary_selected[8]).className = "sv_gad_timeslot_selected";
	}
	old_ts['id'] = ary_selected[8];
	if(document.getElementById("pxm") != null){
		old_ts['ts_width'] = ""+document.getElementById(old_ts['id']).clientWidth;
		old_ts['ts_index'] = "0";
		old_ts['ts_left'] = document.getElementById(old_ts['id']).style.left;
	}
	if(document.getElementById("pxm2") != null){
		old_ts['ts_index'] = "0";
		old_ts['ts_top'] = document.getElementById(old_ts['id']).style.top;
		old_ts['ts_height'] = ""+document.getElementById(old_ts['id']).clientHeight-2;
	}
// if in day view, we need to selec the chosen resoure in order to show its services
	// But only load services if we have changed resources since last click or this is the first click on the screen in daily view
	var LoadServices = false;
	if(document.getElementById("resources").value!=document.getElementById("selected_resource_id").value){
		LoadServices = true;
	}

	if(document.getElementById("resources_slick") != null){
		jQuery('#resources_slick li').each(function( index ) {		
		  var curValue = jQuery( this ).find('.dd-option-value').val();
		  if(document.getElementById("selected_resource_id").value != document.getElementById("resources").value){
			// do this only if the selected resource is different from the current list selected item	  
			  if(curValue == document.getElementById("selected_resource_id").value)
			  {
				need_changeResource = 0; // flag to tell ddslick onSelected not to call changeResource as it would reset seletced timeslot
				jQuery('#resources_slick').ddslick('select', {index: jQuery(this).index()});
			  }
		  }
		});
	} else {
		document.getElementById("resources").value=document.getElementById("selected_resource_id").value;
	}
	
	//changeResource();
	if(document.getElementById("mode").value === "single_day" && LoadServices === true){
		if(document.getElementById("service_name") != null){
			for (var loop=0; loop < document.getElementById("service_name").options.length; loop++) {
				document.getElementById("service_name").options[loop] = null; // remove the option
			}
			document.getElementById("service_name").options.length = 0;
		}		
		jQuery('html, body').css("cursor", "wait");  
		setTimeout(function()
            {	getServices(false);
				getResourceUFDs();
				getResourceSeatTypes();
				getExtras();
		 }
   		, 20);		
//		getServices(true);
//		getResourceUFDs();
//		getResourceSeatTypes();
//		getExtras();


		jQuery('html, body').css("cursor", "auto");  
	}

	// if resize enabled..
	resizable_setting = aryUserDragEnable[res_id];
	ary_resizable = resizable_setting.split("|");
	res_user_drag = ary_resizable[0];
	res_user_drag_snap = ary_resizable[1];

	// The next line clears any user size when a new slot is selected. To have the size remain you can comment out the next line.
	// Note: doing maens if the screen shows a mix of resiable and on-resiable, the resized slot will be allied to the new selection.
	document.getElementById("user_duration").value = "0";
	
	if(res_user_drag == "Yes"){
		var snap_to = res_user_drag_snap;
			
		if(document.getElementById("gad2").value == "Yes" || (document.getElementById("mobile") != null && document.getElementById("mobile").value == "Yes")){
			jQuery( "#"+ts_div ).resizable({
			  handles: "s",
			  grid: [ 10, parseFloat(document.getElementById("pxm2").value)*snap_to],
			  containment: "parent",
			  stop: function( event, ui ) {resize_end(ts_div, "gad2", starttime_from_slot, res_id, snap_to);}
			}); 
		} else {	
			jQuery( "#"+ts_div ).resizable({
			  handles: "e",
			  grid: [ parseFloat(document.getElementById("pxm").value)*snap_to, 10 ],
			  containment: "#timeslot_resize_container",
			  stop: function( event, ui ) {resize_end(ts_div, "gad", starttime_from_slot, res_id, snap_to);}
			});
		}
		// end - if resize enabled..
	}
	
	// to display service name with resource on the selected appointment section, uncomment the line below
	//document.getElementById("selected_resource").innerHTML = svBase64.decode(ary_selected[1]) + " " + jQuery("select[id=service_name] option:selected").text();

	checkForBookingOverlap(document.getElementById("startdate").value, starttime_from_slot,
			document.getElementById("enddate").value, endtime_from_slot, res_id);
	
	// moved to after async call returns
	//setDuration();
	//calcTotal();
}

function parse_service_durations(inStr, i, which){
	aryDurations = inStr.split(",");
	for(x=0; x<aryDurations.length; x++)
	{
		aryTemp = aryDurations[x].split(":");
		if(aryTemp[0]===i){
			if(which==="value"){
				return aryTemp[1];
			} else {
				return aryTemp[2];
			}
		}
	}
}		


function changeMode(id){
	document.getElementById("resources").value=id;
	if(document.getElementById("resources_slick") != null){
		jQuery('#resources_slick li').each(function( index ) {		
		  var curValue = jQuery( this ).find('.dd-option-value').val();
		  if(curValue == id)
		  {
			  jQuery('#resources_slick').ddslick('select', {index: jQuery(this).index()});
		  }
		});
	} else {	
		changeResource();
	}
}		   
	
function changeMode2(newdate){
	document.getElementById("resources").selectedIndex=0;
	if(document.getElementById("resources_slick") != null){
		jQuery('#resources_slick').ddslick('select', {index: 0 });
	}	
	changeResource();
	document.getElementById("grid_date").value = newdate;
	changeDate();
}		
// advadm


function changeUser(){
	// JQuery verison of changeUser	
	document.getElementById("user_fetch").innerHTML = document.getElementById("wait_text").value;
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "id=" + document.getElementById("users").value;
	if(document.getElementById("screen_type").value === "fd_gad"){
		data = data + "&fd_gad=1";
	}
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_fetch&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			aryResults = data.split("~");
			
			aryNameEmail = aryResults[0].split("|");
			document.getElementById("name").value = trim(aryNameEmail[0]);
			document.getElementById("email").value = aryNameEmail[1];
			document.getElementById("uc").value = aryNameEmail[2];
			document.getElementById("user_id").value =  document.getElementById("users").value;
	
			if(aryResults.length > 1){
				for(i=1; i<aryResults.length; i++){
					aryUdfs = aryResults[i].split("|")
					if(document.getElementById(aryUdfs[0])!=null){
						document.getElementById(aryUdfs[0]).value = aryUdfs[1];
					}
				}
			}					
			document.getElementById("user_fetch").innerHTML = "";
			if(document.getElementById("enable_overrides").value != null && document.getElementById("enable_overrides").value == 'Yes'){
				// get rate overrides
				getRateOverrides(document.getElementById("users").value);
				getServices();
				getExtras();
				getResourceSeatTypes();
			}
			calcTotal(); // changing user means new user credit
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get changeUser, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get changeUser:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function checkAll2( n, fldName, tab ) {
    if (!fldName) {
       fldName = 'cb';
    }
      var f = document.adminForm;
	  switch (tab)
	  {
		case 2: { var c = f.toggle2.checked; break }

		case 3: { var c = f.toggle3.checked; break }
	 
		case 4: { var c = f.toggle4.checked; break }

		case 5: { var c = f.toggle5.checked; break }

		case 6: { var c = f.toggle6.checked; break }

		case 7: { var c = f.toggle7.checked; break }

		case 8: { var c = f.toggle8.checked; break }

		case 9: { var c = f.toggle9.checked; break }

		case 10: { var c = f.toggle10.checked; break }
		case 11: { var c = f.toggle11.checked; break }
		case 12: { var c = f.toggle12.checked; break }
		case 13: { var c = f.toggle13.checked; break }
		default: { var c = f.toggle.checked; break }
	  }
      var n2 = 0;
      for (i=0; i < n; i++) {
          cb = eval( 'f.' + fldName + '' + i );
          if (cb) {
              cb.checked = c;
              n2++;
          }
      }
      if (c) {
          document.adminForm.boxchecked.value = n2;
      } else {
          document.adminForm.boxchecked.value = 0;
      }
}

function parse_service_rates(inStr, i, which){
	aryServices = inStr.split(",");
	for(x=0; x<aryServices.length; x++)
	{
		aryTemp = aryServices[x].split(":");
		if(aryTemp[0]===i){
			switch(which) {
				case "value":
					return aryTemp[1];
					break;
				case "unit":
					return aryTemp[2];
					break;
				case "lead":
					return aryTemp[3];
					break;
			}
//			if(which==="value"){
//				return aryTemp[1];
//			} else {
//				return aryTemp[2];
//			}
		}
	}
}	


function calcTotal() {
	if(document.getElementById("enable_payproc").value === 'No'
		&& document.getElementById("non_pay_booking_button").value != 'DO' 
		&& document.getElementById("non_pay_booking_button").value != 'DAB' ){
		// do not show financials
		calcSeatsTotal();
		return false;
	}
	
	if(document.getElementById("screen_type").value === "non-gad"){
		if( document.getElementById("timeslots") === null || document.getElementById("timeslots").selectedIndex === 0){	
			hideTotal();
			calcSeatsTotal();
			return true;
		}
	}

	startdate = document.getElementById("startdate").value;
	starttime = document.getElementById("starttime").value;
	if(document.getElementById("enddate") === null) {
		enddate = startdate;
	} else {
		enddate = document.getElementById("enddate").value;
	}
	endtime = document.getElementById("endtime").value;
	if(document.getElementById("additionalfee") != null){
		additionalfee = document.getElementById("additionalfee").value;
	}
	if(document.getElementById("feerate") != null){
		feerate = document.getElementById("feerate").value;
	}
	// service rates trump resource rates
	if(document.getElementById("service_rates") != null 
							   && (document.getElementById("services").style.display != "none" || (document.getElementById("service_selector") != null && document.getElementById("service_selector").style.display != "none"))
							   && document.getElementById("service_rates").value != ""){
		// get parse service rates
		var selected_id = document.getElementById("service_name").options[document.getElementById("service_name").selectedIndex].value;
		var service_rate = parse_service_rates(document.getElementById("service_rates").value, selected_id, 'value');
		var service_rate_unit = parse_service_rates(document.getElementById("service_rates").value, selected_id, 'unit');
		
		// get early booking discounts
		service_eb_discount = parse_service_rates(document.getElementById("service_eb_discount").value, selected_id, 'value');
		if(service_eb_discount != "0.00"){
			service_eb_discount_unit = parse_service_rates(document.getElementById("service_eb_discount").value, selected_id, 'unit');
			service_eb_discount_lead = parse_service_rates(document.getElementById("service_eb_discount").value, selected_id, 'lead');
			// see if we are before early bird date
			eb_date = Date.parseExact(startdate, "yyyy-MM-dd")
			if(Date.today().add({ days: parseInt(service_eb_discount_lead) }) > Date.parseExact(startdate, "yyyy-MM-dd")){	
				// not soon enough for discount
				service_eb_discount = "0.00";
			}
		}
		
		//alert(service_eb_discount);
		//alert(service_eb_discount_unit);
		//alert(service_eb_discount_lead);
		//alert(selected_id);
		//alert(service_rate);
		//alert(service_rate_unit);
		if(service_rate==="0.00"){
			rate = parseFloat(aryRates[res_id]);
			rate_unit = aryRateUnits[res_id];
			res_rate=rate.toFixed(2);
		} else {
			rate = parseFloat(service_rate);
			rate_unit = service_rate_unit;	
			res_rate=rate;
		}
		
	} else {
		rate = parseFloat(aryRates[res_id]);
		rate_unit = aryRateUnits[res_id];
		res_rate=rate;
	}

	calcSeatsTotal(); // also sets res_rate

//  If you want to make a UDF adjust cost, see http://appointmentbookingpro.com/how-to/172-have-a-udf-selection-adjust-cost.html	
//  If udf is dropdown list use this line..
//	my_udf_dropdown_value = document.getElementById("user_field2_value").options[document.getElementById("user_field2_value").selectedIndex].value;
//  If udf is a radio button set use this line..
//	my_udf_dropdown_value = jQuery('input[name=user_field2_value]:checked', '#frmRequest').val();
//	// for testing you can uncomment the line below to chcek you have the correct user_field selected
//	alert(my_udf_dropdown_value);
//	switch(my_udf_dropdown_value){
//		case "Birthday Party":
//		  res_rate = res_rate + .01;
//		  break;
//		case "Team Party":
//		  res_rate = res_rate *2;
//		  break;
//	}
//	rate = parseFloat(res_rate);
//
//	
	// -------------------------------------------------------------------
	// start date/time = end date/time -> do nothing
	// -------------------------------------------------------------------
	if(startdate === enddate && starttime === endtime){
		hideTotal();
		return true;

	}

	var rateObj = { rate_adjustments: "" };
	if(Date.parse(startdate) != null){
		getRateAdjustment("resource", res_id, startdate, starttime, endtime, rateObj);
		//alert(rateObj.rate_adjustments);	
		if(rateObj.rate_adjustments != 0){		
			// could be two adjustments, day and time
			if(rateObj.rate_adjustments.time != ""){
				if(rateObj.rate_adjustments.time_unit == "Percent"){
					rate = rate + (rate * parseFloat(rateObj.rate_adjustments.time/100));					
				} else if(rateObj.rate_adjustments.time_unit == "Per_Seat") {
					if(document.getElementById("booked_seats") != null){
						var booked_seats_count = parseInt(document.getElementById("booked_seats").value);
						rate = rate + (parseFloat(rateObj.rate_adjustments.time) * booked_seats_count);					
					} else {
						rate = rate + (parseFloat(rateObj.rate_adjustments.time));					
					}
				} else {
					rate = rate + parseFloat(rateObj.rate_adjustments.time);
				}
			}
			if(rateObj.rate_adjustments.day != ""){
				if(rateObj.rate_adjustments.day_unit == "Percent"){
					rate = rate + (rate * parseFloat(rateObj.rate_adjustments.day/100));
				} else if(rateObj.rate_adjustments.day_unit == "Per_Seat") {
					if(document.getElementById("booked_seats") != null){
						var booked_seats_count = parseInt(document.getElementById("booked_seats").value);
						rate = rate + (parseFloat(rateObj.rate_adjustments.day) * booked_seats_count);					
					} else {
						rate = rate + (parseFloat(rateObj.rate_adjustments.day));					
					}
				} else {
					rate = rate + parseFloat(rateObj.rate_adjustments.day);
				}
			}
			res_rate = rate;
		}
	}
	// -------------------------------------------------------------------
	// start date = end date -> single day just calc based on times
	// -------------------------------------------------------------------
	if(startdate === enddate){
		//alert("startdate === enddate");
		var startdecimal = 0.0;
		var enddecimal = 0.0;
		var startminutes_as_decimal = 0.0
		var endminutes_as_decimal = 0.0
		
		starttemp = starttime.split(":", 2);
		// minutes as decimal
		startminutes_as_decimal = parseInt(starttemp[1])/60;
		startdecimal = parseFloat(starttemp[0]) + startminutes_as_decimal;


		endtemp = endtime.split(":", 2);
		// minutes as decimal
		if(endtemp[0] == "23" && (endtemp[1] == "59") || endtemp[1] == "55"){
			endtemp[0] = "24";
			endtemp[1] = "00";
		}
		endminutes_as_decimal = parseInt(endtemp[1])/60;
		enddecimal = parseFloat(endtemp[0]) + endminutes_as_decimal;
		hours = enddecimal - startdecimal;
		if(hours <0){
			document.getElementById("res_hours").innerHTML = "err";	
			document.getElementById("res_total").innerHTML = "";
			document.getElementById("res_fee").innerHTML = "";
			document.getElementById("res_grand_total").innerHTML = "err";
			return true;
		}

		if(rate_unit === "Hour"){
			total = hours * rate;					
		} else {
			total = rate;					
		}
		
		showTotal();
	
	} else {
		// ABPro should never get here
		begintemp = startdate.split("-",3);
		endtemp = enddate.split("-",3);
		var begingdate = new Date(begintemp[0], (begintemp[1]-1), begintemp[2]);
		var endingdate = new Date(endtemp[0], (endtemp[1]-1), endtemp[2]);
		var one_day=1000*60*60*24;
	
	
		//Calculate difference btw the two dates, and convert to days
		diffdays = Math.ceil((endingdate-begingdate)/(one_day));
	
	
		// -------------------------------------------------------------------
		// start and end on consecutive dates -> calc as start days hours + end days hours
		// -------------------------------------------------------------------
		if(diffdays === 1){
			//alert("diffdays === 1");	
			//alert(getStartDayHours());
			//alert(getEndDayHours());
			hours = getStartDayHours() + getEndDayHours();
			if(hours <0){
				document.getElementById("res_hours").innerHTML = "err";	
				document.getElementById("res_total").innerHTML = "";
				document.getElementById("res_fee").innerHTML = "";
				document.getElementById("res_grand_total").innerHTML = "err";
				return true;
			}
	
			total = hours * rate;	
		}
		
		// -------------------------------------------------------------------
		// start and end date > 1 day apart -> start day + end day + days between
		// -------------------------------------------------------------------
		if(diffdays > 1){
			
			// how many hours in a day
			var hoursperday = parseInt(document.getElementById("endhour").value)+1 - parseInt(document.getElementById("starthour").value);
			
			hours = ((diffdays-1)*hoursperday)+getStartDayHours() + getEndDayHours();
			
			if(hours <0){
				document.getElementById("res_hours").innerHTML = "err";	
				document.getElementById("res_total").innerHTML = "err";
				document.getElementById("res_fee").innerHTML = "err";

				document.getElementById("res_grand_total").innerHTML = "err";
				return true;
			}
	
			total = hours * rate;			
		}
		
		if(diffdays < 1){
			document.getElementById("res_hours").innerHTML = "err";	
			document.getElementById("res_total").innerHTML = "err";
			document.getElementById("res_fee").innerHTML = "err";
			document.getElementById("res_grand_total").innerHTML = "err";
			return true;
		}
		
	
	}
	showTotal();

}

function calcSeatsTotal(){
	// but seat rates trump all 
	if(document.getElementById("seat_type_count") != null && parseInt(document.getElementById("seat_type_count").value) > 0 ){
		var seat_count = 0; 
		rate = 0.00;
		for(i=0; i<parseInt(document.getElementById("seat_type_count").value); i++){
			seat_name_cost = "seat_type_cost_"+i;
			seat_name = "seat_"+i;
			group_seat_name = "seat_group_"+i;
			seat_count += parseInt(document.getElementById(seat_name).value);
			if(document.getElementById(group_seat_name).value === "Yes"){
				if(document.getElementById(seat_name).selectedIndex > 0){
					seat_type_cost_x_qty = parseFloat(document.getElementById(seat_name_cost).value);
				} else {
					seat_type_cost_x_qty = 0;
				}
			} else {
				seat_type_cost_x_qty = parseFloat(document.getElementById(seat_name_cost).value)*parseInt(document.getElementById(seat_name).value);
			}
			rate = rate + seat_type_cost_x_qty;
			// to have sliding discount based on number of seats booked uncomment the following lines and add/adjust to you requirement
//			switch(seat_count){
//				case 2:
//					rate -= 1; // (goes through twice so $2 off for 2 seats
//					break;
//				case 3:
//					rate -= 2; // $4 off for 3 seats
//					break;
//				case 4:
//					rate -= 3; // $6 off for 4 seats
//					break;
//				// etc..		
//			}
		}
		document.getElementById("booked_seats_div").innerHTML = seat_count;
		document.getElementById("booked_seats").value = seat_count;
		res_rate=rate;
		// rate units come from the resource (per hour or per booking)
	}
}

function showTotal() {
	if(document.getElementById("startdate").value.indexOf("-") === -1){
		// not a date in startdate, probably says 'Select a Date' but cannot check for that in case non-English
		return;
	}
	if(free_booking_override == "Yes"){
		hideTotal();
		if(document.getElementById("pay_proc_buttons") != null){
			document.getElementById("pay_proc_buttons").style.visibility = "hidden";
		}
		if(document.getElementById("hidden_submit") != null){
			document.getElementById("hidden_submit").style.visibility = "visible";
			document.getElementById("hidden_submit").style.display = "";
		}
		
		return;
	} else {
		if(document.getElementById("pay_proc_buttons") != null){
			document.getElementById("pay_proc_buttons").style.visibility = "visible";
		}
		if(document.getElementById("hidden_submit") != null){
			document.getElementById("hidden_submit").style.visibility = "hidden";
			document.getElementById("hidden_submit").style.display = "none";
		}
	}
	
	if(document.getElementById("calcResults") != null){
		document.getElementById("calcResults").style.visibility = "visible";
	}
	if(typeof(res_rate) === "string"){
    	document.getElementById("res_rate").innerHTML = res_rate;
	} else {
    	document.getElementById("res_rate").innerHTML = res_rate.toFixed(2);
	}
	//document.getElementById("res_rate").innerHTML = aryRates[res_id].toFixed(2);
	if(rate_unit==="Flat"){
		document.getElementById("res_hours_label").innerHTML = document.getElementById("flat_rate_text").value;
		document.getElementById("res_hours").innerHTML = "";
	} else {
		document.getElementById("res_hours_label").innerHTML = document.getElementById("non_flat_rate_text").value;
		document.getElementById("res_hours").innerHTML = hours.toFixed(2);
	}
	
	// add extras
	calcExtrasTotal();
	
	
	document.getElementById("res_total").innerHTML = total.toFixed(2);
	if(feerate === "Fixed"){
		fee = parseFloat(additionalfee);
	} else if(feerate === "Percent") {
		//fee = (total * parseFloat(additionalfee)/100);
		fee = ((total + extras_total_cost) * parseFloat(additionalfee)/100);
		fee = Math.round(fee * 100) / 100
	}
	if(fee > 0){
		document.getElementById("res_fee").innerHTML = fee.toFixed(2);
	}
	
	var discount = 0;
	// Note: Discounts are not additive
	// Early Booking Discounts override Coupons
	// Service Early Booking Discounts override Resource Early Booking dicsounts

	// Coupon discounts
	if(document.getElementById("coupon_value") != null){
		if(document.getElementById("coupon_value").value != ""){
			if(document.getElementById("coupon_units").value === "percent"){
				discount = (total + fee + extras_total_cost) * parseFloat(document.getElementById("coupon_value").value)/100;				
			} else {
				discount = parseFloat(document.getElementById("coupon_value").value);			
			}
			document.getElementById("discount").innerHTML = "("+discount.toFixed(2)+")";
		}
	}
	
	// Resource Early Booking Discounts
	if(document.getElementById("resource_eb_discount") != null){
		selected_id = document.getElementById("selected_resource_id").value;
		resource_eb_discount = parse_service_rates(document.getElementById("resource_eb_discount").value, selected_id, 'value');
		if(resource_eb_discount != "0.00"){
			resource_eb_discount_unit = parse_service_rates(document.getElementById("resource_eb_discount").value, selected_id, 'unit');
			resource_eb_discount_lead = parse_service_rates(document.getElementById("resource_eb_discount").value, selected_id, 'lead');
			// see if we are before early bird date
			eb_date = Date.parseExact(startdate, "yyyy-MM-dd")
			if(Date.today().add({ days: parseInt(resource_eb_discount_lead) }) > Date.parseExact(startdate, "yyyy-MM-dd")){	
			// If you want to give a discount for last minute bookings you can reverse the early bird by commening out the above line 
			// and un-commnet the line below
			//if(Date.today().add({ days: parseInt(resource_eb_discount_lead) }) < Date.parseExact(startdate, "yyyy-MM-dd")){	

				// not soon enough for discount
				resource_eb_discount = "0.00";
			}
		}

		if(resource_eb_discount != "0.00"){
			if(resource_eb_discount_unit == "Flat"){
				discount = parseFloat(resource_eb_discount);
			} else {
				discount = (total + fee + extras_total_cost) * parseFloat(resource_eb_discount)/100;
			}
			if(document.getElementById("discount") != null){
				document.getElementById("discount").innerHTML = "("+discount.toFixed(2)+")";
			}
		}	
	}
	// Service Early Booking Discounts
	if(service_eb_discount != "0.00"){
		if(service_eb_discount_unit == "Flat"){
			discount = parseFloat(service_eb_discount);
		} else {
			discount = (total + fee + extras_total_cost) * parseFloat(service_eb_discount)/100;
		}
		if(document.getElementById("discount") != null){
			document.getElementById("discount").innerHTML = "("+discount.toFixed(2)+")";
		}
	}

	if(discount == 0.00) {
		if(document.getElementById("discount") != null){
			document.getElementById("discount").innerHTML = "";
		}
	}
	
	// If user has both a user credit and a gift cretificate, the cost will first be deducted from their
	// gift certifiate, then if there is still an outstanding balance, use their credit balance.
	// use gift cert credit if available
	applied_credit = 0.00;
	gc_credit = 0.00;
	gc_used = 0.00;
	uc_used = 0.00;
	
	// clear both credit used place holders
	if(document.getElementById("uc_used") != null){
		document.getElementById("uc_used").value = "0.00";		
	}
	if(document.getElementById("gc_used") != null){
		document.getElementById("gc_used").value = "0.00";
	}
	if(document.getElementById("applied_credit") != null){
		document.getElementById("applied_credit").value = "0.00";
	}
	
	total_cost_before_credit = total + fee + extras_total_cost - discount;
	total_cost_running_tally = total_cost_before_credit; 
	
	user_credit_available = 0;
	if(document.getElementById("uc") != null){
		if(document.getElementById("uc").value != ""){
			user_credit_available = parseFloat(document.getElementById("uc").value);
		}
	}
	gc_credit_available = 0;
	if(document.getElementById("gift_cert_bal") != null){
		if(document.getElementById("gift_cert_bal").value != ""){
			gc_credit_available = parseFloat(document.getElementById("gift_cert_bal").value);
		}
	}
	// use gift cert credit first
	if(gc_credit_available > 0){
		if(total_cost_before_credit <= gc_credit_available){
			// credit covers all costs
			applied_credit += total_cost_running_tally;
			gc_used = total_cost_running_tally;				
		} else {
			// gc only covers part
			applied_credit += gc_credit_available;
			gc_used = gc_credit_available;								
		}
		
		document.getElementById("gc_used").value = gc_used.toFixed(2);
		total_cost_running_tally = total_cost_running_tally - gc_used;
	}
	
	// now apply user credit
	if(user_credit_available > 0 && total_cost_running_tally > 0){
		if(total_cost_running_tally <= user_credit_available){
			// credit covers all costs
			applied_credit += total_cost_running_tally;
			uc_used = total_cost_running_tally;				
		} else {
			// gc only covers part
			applied_credit += user_credit_available;
			uc_used = user_credit_available;								
		}
		document.getElementById("uc_used").value = uc_used.toFixed(2);
		total_cost_running_tally = total_cost_running_tally - uc_used;
	}
	
	document.getElementById("applied_credit").value = applied_credit.toFixed(2);
	if(document.getElementById("gc_credit") != null){
		document.getElementById("gc_credit").innerHTML = "("+gc_used.toFixed(2)+")";
	}
	if(document.getElementById("uc_credit") != null){
		document.getElementById("uc_credit").innerHTML = "("+uc_used.toFixed(2)+")";
	}
	if(gc_used > 0){
		show_hide_row("gc_row", "show")
	} else {
		show_hide_row("gc_row", "hide")
	}
	if(uc_used > 0){
		show_hide_row("uc_row", "show")
	} else {
		show_hide_row("uc_row", "hide")
	}
	

	manual_payment = 0;
	if(document.getElementById("manual_payment_collected") != null){
		if(!isNaN(parseFloat(document.getElementById("manual_payment_collected").value))){
			manual_payment = parseFloat(document.getElementById("manual_payment_collected").value);						  
		}
	}
	
	gr_total = total + fee + extras_total_cost - discount - applied_credit - manual_payment;
	if(gr_total < 0){
		// to deal with discounting total to a negative value
		gr_total = 0.00;
	}
	document.getElementById("res_grand_total").innerHTML = gr_total.toFixed(2);
	document.getElementById("grand_total").value = gr_total.toFixed(2);


	if(aryDeposit != null){
		if(typeof aryDeposit[res_id] != 'undefined'){
			deposit = parseFloat(aryDeposit[res_id]);
		} else {
			deposit = 0;
		}
		deposit_unit = "Flat";
		if(deposit > 0){
			document.getElementById("deposit_only").style.visibility = "visible";
			document.getElementById("deposit_only").style.display = "";
			deposit_unit = aryDepositUnits[res_id];
			if(deposit_unit == "Flat"){
				document.getElementById("display_deposit_amount").innerHTML = deposit.toFixed(2);
				document.getElementById("deposit_amount").value = deposit.toFixed(2);		
			} else {
				grand_total = parseFloat(document.getElementById("grand_total").value);
				document.getElementById("display_deposit_amount").innerHTML = (deposit * grand_total/100).toFixed(2);
				document.getElementById("deposit_amount").value = (deposit * grand_total/100).toFixed(2);	
			}
		} else {
			document.getElementById("deposit_only").style.visibility = "hidden";
			document.getElementById("deposit_only").style.display = "none";
			document.getElementById("display_deposit_amount").innerHTML = "";		
			document.getElementById("deposit_amount").value = "0.00";		
		}
	} else {
		document.getElementById("deposit_only").style.visibility = "hidden";
		document.getElementById("deposit_only").style.display = "none";
		document.getElementById("display_deposit_amount").innerHTML = "";		
		document.getElementById("deposit_amount").value = "0.00";		
	}

	document.getElementById("calcResults").style.height = "auto";
	document.getElementById("calcResults").style.display = "block";
	
	if(document.getElementById("grand_total").value == "0.00" ){
		// show hidden submit and hide payproc buttons
		if(document.getElementById("hidden_submit") != null){		
			document.getElementById("hidden_submit").style.visibility = "visible";
			document.getElementById("hidden_submit").style.display = "";		
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
}


function hideTotal(){
  	if(document.getElementById("calcResults")!=null){
    	document.getElementById("calcResults").style.visibility = "hidden";
	    document.getElementById("calcResults").style.height = "1px";
    	document.getElementById("calcResults").style.display = "none";
	}
}


function buildFrontDeskView(day, month, year, week_offset){
	// JQuery verison of buildFrontDeskView	
   	var Itemid = document.getElementById('frompage_item').value;

   	var view_list_field = document.getElementById('front_desk_view');
    var view_list_selected_index = view_list_field.selectedIndex;
    var view = view_list_field.options[view_list_selected_index].value;

   	var resource_list_field = document.getElementById('resource_filter');
    var resource_list_selected_index = resource_list_field.selectedIndex;
    var resource = resource_list_field.options[resource_list_selected_index].value;
	if(document.getElementById("single_res_mode")!= null && document.getElementById('single_res_mode').value == "Yes"){
		resource = document.getElementById('single_res_value').value;
		document.getElementById('resource_filter').selectedIndex = 1;
	}

	var category = "";
	if(view != "day"){
		if(document.getElementById("chkSeatTotals")){
			document.getElementById("chkSeatTotals").style.visibility="hidden";
		}
	} else {
		if(document.getElementById("chkSeatTotals")){
			document.getElementById("chkSeatTotals").style.visibility="";
		}
	}
	if(view == "month"){
		if(document.getElementById("reminder_links")!=null){
			document.getElementById("reminder_links").style.display="none";		
			document.getElementById("reminder_links").style.visibility="hidden";		
		}
	} else {
		if(document.getElementById("reminder_links")!=null){
			document.getElementById("reminder_links").style.display="";		
			document.getElementById("reminder_links").style.visibility="visible";		
		}
	}

    if(document.getElementById("category_filter")!=null){
		var category_list_field = document.getElementById('category_filter');
	    var category_list_selected_index = category_list_field.selectedIndex;
	    category = category_list_field.options[category_list_selected_index].value;
	}
	if(document.getElementById("single_cat_mode")!= null && document.getElementById('single_cat_mode').value == "Yes"){
		category = document.getElementById('single_cat_value').value;
		document.getElementById('category_filter').selectedIndex = 1;
	}
    
    if (typeof day === "undefined") {
        if(document.getElementById("cur_day")!=null){
            day = document.getElementById("cur_day").value;
        } else {
		    var d = new Date();
		    day = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
        }
    }
    if (typeof month === "undefined") {
        if(document.getElementById("cur_month")!=null){
            month = document.getElementById("cur_month").value;
        } else {
            month="";
        }
    }
    if (typeof year === "undefined") {
        if(document.getElementById("cur_year")!=null){
            year = document.getElementById("cur_year").value;
        } else {
	        year = "";
		}
    }
    if (typeof week_offset === "undefined") {
        if(document.getElementById("cur_week_offset")!=null){
            week_offset = document.getElementById("cur_week_offset").value;
        } else {
            week_offset="0";
        }
    } 
       
   	var select_list_field = document.getElementById('status_filter');
    var select_list_selected_index = select_list_field.selectedIndex;
    var status = select_list_field.options[select_list_selected_index].value;

	var payment_status = "";
	if(document.getElementById("payment_status_filter")!=null){
	   	var payment_list_field = document.getElementById('payment_status_filter');
	    var payment_list_selected_index = payment_list_field.selectedIndex;
	    payment_status = payment_list_field.options[payment_list_selected_index].value;
	}
//	if(document.getElementById("wait_text")!=null){
//	    document.getElementById("calview_here").innerHTML = document.getElementById("wait_text").value
//	}
	jQuery.noConflict();
	var data = "front_desk_view=" + view;
	data = data + "&day=" + day;
	data = data + "&month=" + month;
	data = data + "&year=" + year;
	data = data + "&resource=" + resource;
	data = data + "&category=" + category;
	data = data + "&user=" + document.getElementById("uid").value;
	data = data + "&status=" + status;
	data = data + "&payment_status=" + payment_status;
	data = data + "&weekoffset=" + week_offset;
	data = data + "&user_search=" + document.getElementById("user_search").value;
	data = data + "&listpage=" + document.getElementById("listpage").value;
	if(document.getElementById("showSeatTotals")!=null){
		data = data + "&showSeatTotals=" + document.getElementById("showSeatTotals").checked;
	}
	if(document.getElementById("printer_view")!=null){
		data = data + "&printer=" + document.getElementById("printer_view").value;
	}
	data = data + "&Itemid=" + Itemid;
	data = data + "&Menuid=" +  document.getElementById("menu_id").value;
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_calview&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("calview_here").style.visibility = "visible";
			document.getElementById("calview_here").style.display = "";
			document.getElementById("calview_here").innerHTML = data;
			
			if(document.getElementById('front_desk_view').selectedIndex < 2){
				document.getElementById("reminder_links").style.visibility = "visible";
				document.getElementById("reminder_links").style.display = "";
			} else {
				document.getElementById("reminder_links").style.visibility = "hidden";
				document.getElementById("reminder_links").style.display = "none";
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildFrontDeskView, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildFrontDeskView:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}


function checkForBookingOverlap(startdate, starttime, enddate, endtime, resource){
	// JQuery verison of checkForBookingOverlap	
	document.getElementById("selected_resource_wait").innerHTML = "("+document.getElementById("wait_text").value+")";
	if(document.getElementById("submit") != null){
		submit_status = document.getElementById("submit").disabled;
		document.getElementById("submit").disabled = true;
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "startdate=" + encodeURIComponent(startdate);
	data = data + "&starttime=" + encodeURIComponent(starttime);
	data = data + "&enddate=" + encodeURIComponent(enddate);
	data = data + "&endtime=" + encodeURIComponent(endtime);
	data = data + "&res_id=" + resource;
	var gap_to_use = 0;
	if(document.getElementById("gap")!=null){
		gap_to_use = document.getElementById("gap").value;				
	}
	if(document.getElementById("res_spec_gap")!=null){
		if(document.getElementById("res_spec_gap").value > 0){
			// res_spec_gap overrides component level gap
			gap_to_use = document.getElementById("res_spec_gap").value;				
		}
	}
	data = data + "&gap=" + gap_to_use;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_check_overlap&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("adjusted_starttime").value = data;
			document.getElementById("selected_resource_wait").innerHTML = "";
			if(document.getElementById("mobile")===null){
				document.body.style.cursor = "default";    
			}		
			setDuration();
			calcTotal();
			if(document.getElementById("submit") != null){
				document.getElementById("submit").disabled = submit_status;
			}
			if(document.getElementById("sv_accordion") != null){
				// open submit block
				accordion = jQuery("#sv_accordion").accordion();
				//var current = accordion.accordion("option","active");
				var maximum = accordion.find("sv_h3").length;
				accordion.accordion("option","active",maximum-1); //0 based
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get checkForBookingOverlap, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get checkForBookingOverlap:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function setDuration(){

	service_duration = 0;
	service_duration_unit = "Minute";
	extras_duration = 0;
	extras_duration_unit = "Minute";
	cat_duration = 0;
	cat_duration_unit = "Minute";
	gap = 0;
	user_duration = 0;
	
		
// as of 2.0.2 duration can be effected by service OR extras
//	if(document.getElementById("service_durations") === null || document.getElementById("service_durations").value==="") {
//		return;
//	}

	// do services first as service duration SETS the duration, whereas extras only ADD TO the duration.
	
	if(document.getElementById("service_name") != null){
		if(document.getElementById("service_name").options.length > 0){
			var which_service = document.getElementById("service_name").value;
			var selected_id = document.getElementById("service_name").options[document.getElementById("service_name").selectedIndex].value;
			service_duration = Math.abs(parse_service_durations(document.getElementById("service_durations").value, selected_id, 'value'));
			service_duration_unit = parse_service_durations(document.getElementById("service_durations").value, selected_id, 'unit');
		}
	}

	// if there are extras durations, add to service duration OR add to timslot then adjust duration as if it were a normal servce duration
	if(document.getElementById("extras_count") != null){
		extras_duration = getExtras_duration();
	}
	
	if(document.getElementById("category_id") != null){
		if(document.getElementById("category_id").options.length > 0){
			var which_cat = document.getElementById("category_id").value;
			var selected_cat_id = document.getElementById("category_id").options[document.getElementById("category_id").selectedIndex].value;
			cat_duration = Math.abs(parse_service_durations(document.getElementById("category_durations").value, selected_cat_id, 'value'));
			cat_duration_unit = parse_service_durations(document.getElementById("category_durations").value, selected_cat_id, 'unit');
		}
	}
	// if there is a category duration defined, it OVERRIDES service duration.
	if(cat_duration != 0){
		service_duration = cat_duration;
		service_duration_unit = cat_duration_unit;	
	}

	// user set duration overrides all
	if(document.getElementById("user_duration") != null){
		user_duration = document.getElementById("user_duration").value;	
		if(user_duration != "0"){
			// use service duration as the code is already there and I'm lazy
			service_duration = parseInt(user_duration);
			service_duration_unit = "Minute";
		}
	}
	
	var startdate = document.getElementById("startdate").value;
	var starttime;
	if(document.getElementById("gap") != null && startdate != ""){			
		gap = parseInt(document.getElementById("gap").value);
		if(document.getElementById("res_spec_gap") != null){
			if(document.getElementById("res_spec_gap").value > 0){
				gap = document.getElementById("res_spec_gap").value;
			}
		}
		if(service_duration == 0 && gap != 0){
			// get timeslot duration and set that to service_duration so we can adjsuted start/end times
			var ts1 = Date.parse(startdate + " " + document.getElementById("starttime").value).getTime();
			var ts2 = Date.parse(startdate + " " + document.getElementById("endtime_original").value).getTime();
			timeslot_duration = (ts2-ts1)/60000;
			service_duration = timeslot_duration;
		}
	}
	
	if(service_duration != 0 || gap != 0){
		if(document.getElementById("adjusted_starttime") === null || trim(document.getElementById("adjusted_starttime").value) === ""){
			starttime = document.getElementById("starttime").value;
		} else {
			// adjusted_starttime holds both the actual and display versions
			aryStarttimes = document.getElementById("adjusted_starttime").value.split("|");

			if(document.getElementById("pxm") != null){
				var pxminute = parseFloat(document.getElementById("pxm").value);
				// change timeslot div to reflact new starttime
				// first get difference in minutes between original and new start times
				var d1 = Date.parse(startdate + " " + document.getElementById("starttime").value).getTime();
				var d2 = Date.parse(startdate + " " + aryStarttimes[1]).getTime();
				var newLeft = (((d2-d1)/(1000*60))*pxminute) + parseFloat(document.getElementById(old_ts['id']).style.left);
				document.getElementById(old_ts['id']).style.left = newLeft+"px";
			}
			if(document.getElementById("pxm2") != null){
				var pxminute = parseFloat(document.getElementById("pxm2").value);
				// change timeslot div to reflact new starttime
				// first get difference in minutes between original and new start times
				var d1 = Date.parse(startdate + " " + document.getElementById("starttime").value).getTime();
				var d2 = Date.parse(startdate + " " + aryStarttimes[1]).getTime();
				var newTop = (((d2-d1)/(1000*60))*pxminute) + parseFloat(document.getElementById(old_ts['id']).style.top);
				document.getElementById(old_ts['id']).style.top = newTop+"px";
			}
			
			starttime = aryStarttimes[1];
			document.getElementById("starttime").value=aryStarttimes[1];
			document.getElementById("selected_starttime").innerHTML = aryStarttimes[0];
		}
		var enddate = document.getElementById("enddate").value;
		var endtime = document.getElementById("endtime").value;
		// calculate new endtime
		var d1 = Date.parse(startdate + " " + starttime);
		if(d1 != null){
			if(service_duration_unit === "Minute"){
				d1.add({ minute: service_duration });
			} else {
				d1.add({ hour: service_duration });
			}
			
			// add extras is applicable
			if(extras_duration > 0){
				d1.add({ minute: extras_duration });
			}

			// if adding yields next day 00:00:00 then set to same day 23:59:59
			if(d1.toString("yyyy-MM-dd") != startdate){
				d1.add({ seconds: -1 })
			}
			var timeformatstring = "";
			if(document.getElementById("timeFormat").value === "12"){
				timeformatstring = "h:mm tt";
			} else {
				timeformatstring = "HH:mm";
			}

// Normally the end of the day is the end of the final timeslot, but if using Service based duration 
// bookings are not constrained by timeslot boundaries. In that case the default end time of the grid is end-of-day,
// and ABPro will block bookings going beyond that time.
//
// IF you are using Service based duration AND want DIFFERENT end of day for different week days you can use 
// the code below.
				
			// To hard code different end of day by weekday, uncomment the code below (only affects servce based duration)
			// First we need to determin the day-of-the-week of the selected timeslot
//			var ts_date = Date.parse(startdate + " " + starttime);
//			var day_of_the_week = ts_date.getDay();			
//			switch (day_of_the_week) {
//				case 0: { document.getElementById("end_of_day").value = "16:00"; break } // Sunday
//				case 1: { document.getElementById("end_of_day").value = "17:00"; break } // Monday
//				case 2: { document.getElementById("end_of_day").value = "17:00"; break } // Tuesday
//				case 3: { document.getElementById("end_of_day").value = "17:00"; break } // Wednesday
//				case 4: { document.getElementById("end_of_day").value = "20:00"; break } // Thursday
//				case 5: { document.getElementById("end_of_day").value = "20:00"; break } // Friday
//				case 6: { document.getElementById("end_of_day").value = "17:00"; break } // Saturday
//			}
			
			//To hard code different end of day by resource, uncomment the code below (only affects servce based duration)
//			var my_res = document.getElementById("selected_resource_id").value;
//			switch (my_res) {
//				case "2": { document.getElementById("end_of_day").value = "15:00"; break } // end-of-day is 15:00 for resource 2
//				case "50": { document.getElementById("end_of_day").value = "13:00"; break } // end-of-day is 13:00 for resource 50.
//			}
						
			if(document.getElementById("end_of_day").value === "24:00"){
				//not a valid time to parse
				document.getElementById("end_of_day").value = "23:59:59";
			}
			if(	d1 > Date.parse(startdate + " " + document.getElementById("end_of_day").value, "yyyy-MM-dd H:mm")){
				alert(document.getElementById("beyond_end_of_day").value);
				document.getElementById("booking_detail").style.display = "none";
				document.getElementById("booking_detail").style.visibility = "hidden";		
				document.getElementById("startdate").value="";
				document.getElementById("enddate").value="";
				document.getElementById("starttime").value="";
				document.getElementById("endtime").value="";
				document.getElementById("user_duration").value="0";
				if(old_ts['id'] != 0){
					if(document.getElementById("gad2") != null && document.getElementById("gad2").value == "Yes"){
						document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available_timeony";
					} else {
						document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available";
					}
					document.getElementById(old_ts['id']).style.width = old_ts['ts_width']+"px";
					document.getElementById(old_ts['id']).style.left = old_ts['ts_left'];
					document.getElementById(old_ts['id']).style.zIndex = old_ts['ts_index'];
					old_ts['ts_width'] = 0;
					old_ts['ts_left'] = 0;
					old_ts['ts_index'] = 0;
				} 
			} else {
				if(document.getElementById("selected_endtime") != null){
					document.getElementById("selected_endtime").innerHTML = d1.toString(timeformatstring);
					
					if(document.getElementById("pxm") != null){
						// adjust the timeslot disply size to reflect adjusted duration
						// store origincal values (only for time on Y axis = No)						
						var pxminute = parseFloat(document.getElementById("pxm").value);
						var newWidth = 0;
						if(service_duration_unit === "Minute"){
							newWidth = service_duration * pxminute;
						} else {
							newWidth = service_duration * 60 * pxminute;
						}
						if(extras_duration > 0){
							newWidth += (extras_duration * pxminute);
						}						
						document.getElementById(old_ts['id']).style.width = (newWidth-2)+"px";
						document.getElementById(old_ts['id']).style.zIndex = 1000;
					}
					if(document.getElementById("pxm2") != null){
						// adjust the timeslot disply size to reflect adjusted duration
						// store origincal values (only for time on Y axis = No)						
						var pxminute = parseFloat(document.getElementById("pxm2").value);
						var newHeight = 0;
						if(service_duration_unit === "Minute"){
							newHeight = service_duration * pxminute;
						} else {
							newHeight = service_duration * 60 * pxminute;
						}
						if(extras_duration > 0){
							newHeight += extras_duration;
						}						
						document.getElementById(old_ts['id']).style.height = (newHeight-2)+"px";
						document.getElementById(old_ts['id']).style.zIndex = 1000;
					}
				}
				document.getElementById("endtime").value = d1.toString("H:mm:ss");
			}
		}
	} // if(service_duration != 0 )
	
	var extras_count = 0;
	if(document.getElementById("extras_count") != null){
		extras_count = parseInt(document.getElementById("extras_count").value);
	}

	if(service_duration === 0 ){//&& extras_duration != 0){
		// There is no service duration BUT there is an extras duration to be added to the timeslot
		var startdate = document.getElementById("startdate").value;
		var starttime;
		if(document.getElementById("adjusted_starttime") === null || trim(document.getElementById("adjusted_starttime").value) === ""){
			starttime = document.getElementById("starttime").value;
		} else {
			// adjusted_starttime holds both the actual and display versions
			aryStarttimes = document.getElementById("adjusted_starttime").value.split("|");

			if(document.getElementById("pxm") != null){
				// change timeslot div to reflact new starttime
				// first get difference in minutes between original and new start times
				
				var d1 = Date.parse(startdate + " " + document.getElementById("starttime").value).getTime();
				var d2 = Date.parse(startdate + " " + aryStarttimes[1]).getTime();
				var newLeft = (d2-d1)/(1000*60) + parseFloat(document.getElementById(old_ts['id']).style.left);
				document.getElementById(old_ts['id']).style.left = newLeft+"px";
			}
			if(document.getElementById("pxm2") != null){
				// change timeslot div to reflact new starttime
				// first get difference in minutes between original and new start times
				
				var d1 = Date.parse(startdate + " " + document.getElementById("starttime").value).getTime();
				var d2 = Date.parse(startdate + " " + aryStarttimes[1]).getTime();
				var newTop = (d2-d1)/(1000*60) + parseFloat(document.getElementById(old_ts['id']).style.top);
				document.getElementById(old_ts['id']).style.left = newTop+"px";
			}
			
			starttime = aryStarttimes[1];
			document.getElementById("starttime").value=aryStarttimes[1];
			document.getElementById("selected_starttime").innerHTML = aryStarttimes[0];
		}
		var enddate = document.getElementById("enddate").value;
		var endtime = document.getElementById("endtime").value;
		// calculate new endtime
		var d1 = Date.parse(startdate + " " + starttime);
		if(d1 != null){
			// set starting duration as timeslot size
			d1.add({minute: ts_dur});
			
			// add extras is applicable
			if(extras_duration > 0){
				d1.add({ minute: extras_duration });
			}
			
			// if adding yields next day 00:00:00 then set to same day 23:59:59
			if(d1.toString("yyyy-MM-dd") != startdate){
				d1.add({ seconds: -1 })
			}
			var timeformatstring = "";
			if(document.getElementById("timeFormat").value === "12"){
				timeformatstring = "h:mm tt";
			} else {
				timeformatstring = "H:mm";
			}

		}

		if(document.getElementById("end_of_day").value === "24:00"){
			//not a valid time to parse
			document.getElementById("end_of_day").value = "23:59:59";
		}
		if(	d1 > Date.parse(startdate + " " + document.getElementById("end_of_day").value, "yyyy-MM-dd H:mm")){
			alert(document.getElementById("beyond_end_of_day").value);
			document.getElementById("booking_detail").style.display = "none";
			document.getElementById("booking_detail").style.visibility = "hidden";		
			document.getElementById("startdate").value="";
			document.getElementById("enddate").value="";
			document.getElementById("starttime").value="";
			document.getElementById("endtime").value="";
			document.getElementById("user_duration").value="0";
			if(old_ts['id'] != 0){
				if(document.getElementById("gad2") != null && document.getElementById("gad2").value == "Yes"){
					document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available_timeony";
				} else {
					document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available";
				}
				document.getElementById(old_ts['id']).style.width = old_ts['ts_width']+"px";
				document.getElementById(old_ts['id']).style.left = old_ts['ts_left'];
				document.getElementById(old_ts['id']).style.zIndex = old_ts['ts_index'];
				old_ts['ts_width'] = 0;
				old_ts['ts_left'] = 0;
				old_ts['ts_index'] = 0;
			} 
		} else {
			if(document.getElementById("selected_endtime") != null && timeformatstring != null){
				document.getElementById("selected_endtime").innerHTML = d1.toString(timeformatstring);
				
				if(document.getElementById("pxm") != null){
					// adjust the timeslot disply size to reflect adjusted duration
					// store origincal values (only for time on Y axis = No)						
					var pxminute = parseFloat(document.getElementById("pxm").value);
					var newWidth = 0;
					newWidth = ts_dur * pxminute;

					if(extras_duration > 0){
						newWidth += (extras_duration * pxminute);
					}						
					document.getElementById(old_ts['id']).style.width = newWidth+"px";
					document.getElementById(old_ts['id']).style.zIndex = 1000;
				}
				if(document.getElementById("pxm2") != null){
					// adjust the timeslot disply size to reflect adjusted duration
					// store origincal values (only for time on Y axis = No)						
					var pxminute = parseFloat(document.getElementById("pxm2").value);
					var newHeight = 0;
					newHeight = ts_dur * pxminute;

					if(extras_duration > 0){
						newWidth += (extras_duration * pxminute);
					}						
					document.getElementById(old_ts['id']).style.height = newHeight+"px";
					document.getElementById(old_ts['id']).style.zIndex = 1000;
				}
			}
			if(d1!=null){
				document.getElementById("endtime").value = d1.toString("H:mm:ss");
			}
		}
		
	}
	if(service_duration != 0 || gap !=0){
		temp_endtime = document.getElementById("endtime").value;
		if(gap > 0){
			//add gap to endtime for overrun check
			var d1 = Date.parse(temp_endtime);
			d1.add({ minute : gap });
			temp_endtime = d1.toString("H:mm:ss");
		}
		check_for_overrun( document.getElementById("startdate").value, document.getElementById("starttime").value,
			temp_endtime, document.getElementById("resources").value);
	}
}


function check_for_overrun(startdate, starttime, endtime, resource){
	// after adjusting duration, check to see the booking is not overringing an exiting one
	if(document.getElementById("startdate").value == ""){
		// nothing set yet
		return;
	}
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "res=" + resource;
	data = data + "&bk_date=" + startdate;
	data = data + "&bk_start=" + starttime;
	data = data + "&bk_end=" + endtime;
	
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_check_overrun&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			if(data != "\r\n"){
				ary_dlg = data.split("|");

				alert_dialog = jQuery("<div id='alert_dialog'>"+ary_dlg[0]+"</div>").dialog({
					autoOpen: false,
					modal: true,
					minWidth: 300,
					resizable: false,
					height: "auto",
					close: function () {
					}			
				});						
				alert_dialog.dialog("option", "buttons", [ { text: ary_dlg[2], click: function() { jQuery( this ).dialog( "close" ); } } ] );
				alert_dialog.dialog("option", "title", ary_dlg[1]).dialog("open");
				
				// To clear selected slot on an overrun detection uncomment the code below.
//				if(old_ts['id'] != 0){
//					if(document.getElementById("gad2") != null && document.getElementById("gad2").value == "Yes"){
//						document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available_timeony";
//					} else {
//						document.getElementById(old_ts['id']).className = "sv_gad_timeslot_available";
//					}
//					document.getElementById(old_ts['id']).style.width = old_ts['ts_width']+"px";
//					document.getElementById(old_ts['id']).style.left = old_ts['ts_left'];
//					document.getElementById(old_ts['id']).style.zIndex = old_ts['ts_index'];
//					old_ts['ts_width'] = 0;
//					old_ts['ts_left'] = 0;
//					old_ts['ts_index'] = 0;
//				} 

			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to check overrun, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to  check overrun:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

	
}


function getCoupon( optionalArg ){
	// JQuery verison of getCoupon	
	if(document.getElementById("resources") === null){
		return false;
	}
	fd = (typeof optionalArg === "undefined") ? "No" : "Yes";
	if(document.getElementById("coupon_code").value === ""){
		document.getElementById("coupon_info").innerHTML = "";
		document.getElementById("coupon_value").value = "0";
		document.getElementById("coupon_units").value = "";
	} else {
		document.getElementById("coupon_info").innerHTML = document.getElementById("wait_text").value;
	}
	
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "getcoup=yes";
	data = data + "&res=" + document.getElementById("resources").value;
	data = data + "&cc=" + document.getElementById("coupon_code").value;
	data = data + "&browser=" + BrowserDetect.browser;
	data = data + "&bk_date=" + startdate;
	if(fd == "Yes"){
		data = data + "&uid=" + document.getElementById("user_id").value;
	}
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			if(trim(data) != ""){
				ary = data.split("|");
				document.getElementById("coupon_info").innerHTML = ary[0];
				document.getElementById("coupon_value").value = ary[1];
				document.getElementById("coupon_units").value = ary[2];
				calcTotal();
			} else {
				document.getElementById("coupon_info").innerHTML = "";
				document.getElementById("coupon_value").value = "0";
				document.getElementById("coupon_units").value = "";
				calcTotal();
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getCoupon, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getCoupon:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}



function getSubCategories(cat_id, fd){
	// JQuery verison of getSubCategories	
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "getsubcats=yes";
	data = data + "&cat=" + cat_id;		
	data = data + "&fd=" + fd;
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false, // needed for ddslick init
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			if(trim(data) != ""){
				document.getElementById("subcats_row").style.visibility = "visible";
				document.getElementById("subcats_row").style.display = "";
				document.getElementById("subcats_div").innerHTML = data;
				if(data.indexOf("sub_category_id_slick") > -1){
				   jQuery('#sub_category_id_slick').ddslick({
					   onSelected: function(data){jQuery('#sub_category_id').val(data.selectedData.value);changeSubCategory();}           
				   });
				}				
				// hide any resources and grid from previous pick
				if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
				document.getElementById("services_div").style.display = "none";
				if(document.getElementById("service_summary") != null){
					document.getElementById("service_summary").style.display = "none";
					document.getElementById("service_summary").style.visibility = "hidden";
				}
				if(document.getElementById("resources")!=null){ document.getElementById("resources").style.display = "none";}
				if(document.getElementById("service_selector_div")!=null){ document.getElementById("service_selector_div").style.display = "none";}
				if(document.getElementById("resources_slick")!=null){ document.getElementById("resources_slick").style.display = "none";}			
				if(document.getElementById("gad_container")!=null){ document.getElementById("gad_container").style.display = "none";}
				if(document.getElementById("resources")!=null){ 
					if(document.getElementById("subcats_row_div")!=null){ 
						document.getElementById("subcats_row_div").style.display = "none";}
				}
				
			} else {
				// no sub categories for that categogy
				document.getElementById("subcats_row").style.visibility = "hidden";
				document.getElementById("subcats_row").style.display = "none";
	
				// if using service selector, show it, else so resources
				if(document.getElementById("service_selector_div") != null){

				} else {
					
					if(document.getElementById("mode") != null){
						if(document.getElementById("category_id").value === "0"){
							document.getElementById("table_here").innerHTML = "";
							document.getElementById("table_here").style.visibility = "hidden";
							document.getElementById("table_here").style.display = "none";	
							return false;
						}
						document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;
				
						document.getElementById("gad_container").style.display = "";
						buildTable();
					} else {
						document.getElementById("slots").style.visibility = "hidden";
						document.getElementById("startdate").value = "";
					}
					$fd = "No";
					if(document.getElementById("fd") != null){
						$fd = document.getElementById("fd").value;
					}
					getResources($fd);
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getSubCategories, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getSubCategories:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}


function changeSubCategory(fd){
	var fd_booking = "No";
		if(fd != undefined) { //If fd was passed in, use it
			fd_booking = fd;
		}

	// clear old selected timeslot if there is one.
	old_ts["id"] = "";

//	if(document.getElementById("sv_codeblock_submit") != null){		
//		document.getElementById("sv_codeblock_submit").style.display = "none";
//		document.getElementById("sv_codeblock_submit").style.visibility = "hidden";		
//	}
	if(document.getElementById("calcResults") != null){
		document.getElementById("calcResults").style.display = "none";
		document.getElementById("calcResults").style.visibility = "hidden";		
	}

	if(document.getElementById("resources") != null){
		document.getElementById("resources").selectedIndex = 0; // reset resources dropdown
		if(document.getElementById("resources_slick") != null){
			jQuery('#resources_slick').ddslick('select', {index: 1 });
		}

		if(document.getElementById("mode") != null){
			document.getElementById("mode").value = "single_day";
		}
	}

	if(document.getElementById("resource_udfs") != null){
		document.getElementById("resource_udfs").style.display = "none";
		document.getElementById("resource_udfs").style.visibility = "hidden";
	}
	
	if(document.getElementById("sub_category_id").selectedIndex  === 0){
		// hide stuff
		if(document.getElementById("resources_label") != null){
			document.getElementById("resources_label").style.display = "none";
			document.getElementById("resources_label").style.visibility = "hidden";
		}

		if(document.getElementById("service_selector_div") != null){
			document.getElementById("service_selector_div").style.display = "none";
			document.getElementById("service_selector_div").style.visibility = "hidden";
		}
		if(document.getElementById("service_selector_label") != null){
			document.getElementById("service_selector_label").style.display = "none";
			document.getElementById("service_selector_label").style.visibility = "hidden";
		}
		if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
		document.getElementById("services_div").style.display = "none";
		if(document.getElementById("resources") != null){
			document.getElementById("resources").style.display = "none";
		}
		if(document.getElementById("resources_slick") != null){
			document.getElementById("resources_slick").style.display = "none";
		}
		if(document.getElementById("gad_container") != null){
			document.getElementById("gad_container").style.display = "none";
		}
		if(document.getElementById("service_summary") != null){
			document.getElementById("service_summary").style.display = "none";
			document.getElementById("service_summary").style.visibility = "hidden";
		}
		return false;
	} else {
		// if using service selector, hide resources and grid
		if(document.getElementById("service_selector_div") != null){
			if(document.getElementById("resources_label") != null){
				document.getElementById("resources_label").style.display = "none";
				document.getElementById("resources_label").style.visibility = "hidden";
			}
	
			if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
			document.getElementById("services_div").style.display = "none";
			if(document.getElementById("resources") != null){
				document.getElementById("resources").style.display = "none";
			}
			if(document.getElementById("resources_slick") != null){
				document.getElementById("resources_slick").style.display = "none";
			}
			if(document.getElementById("gad_container") != null){
				document.getElementById("gad_container").style.display = "none";
			}
			if(document.getElementById("service_summary") != null){
				document.getElementById("service_summary").style.display = "none";
				document.getElementById("service_summary").style.visibility = "hidden";
			}
			if(document.getElementById("services") != null){
				document.getElementById("services").style.display = "none";
				document.getElementById("services").style.visibility = "hidden";
			}
			if(document.getElementById("resource_udfs") != null){
				document.getElementById("resource_udfs").style.display = "none";
				document.getElementById("resource_udfs").style.visibility = "hidden";
			}
			if(document.getElementById("resource_seat_types") != null){
				document.getElementById("resource_seat_types").style.display = "none";
				document.getElementById("resource_seat_types").style.visibility = "hidden";
			}
			if(document.getElementById("resource_extras") != null){
				document.getElementById("resource_extras").style.display = "none";
				document.getElementById("resource_extras").style.visibility = "hidden";
			}
			
		}
	}

	if(document.getElementById("mode") != null){
		if(document.getElementById("category_id").value === "0"){
			document.getElementById("table_here").innerHTML = "";
			document.getElementById("table_here").visible = false;
			document.getElementById("table_here").display = "none";	
			return false;
		}
		if(document.getElementById("service_selector_div") == null){
			document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;

			document.getElementById("gad_container").style.display = "";
			buildTable();
		}

	} else {
		document.getElementById("slots").style.visibility = "hidden";
		document.getElementById("startdate").value = "";
	}

	if(document.getElementById("service_selector_div") != null){
		// get services for service_selector dropdown
		getServices_for_category();
	} else {
		getResources(fd_booking);
	}
}

function calcExtrasTotal(){
	extras_total_cost = 0.0;
	if(document.getElementById("extras_count") != null && parseInt(document.getElementById("extras_count").value) > 0 ){
		var extras_count = 0; 
		for(i=0; i<parseInt(document.getElementById("extras_count").value); i++){
			extras_cost = "extras_cost_"+i;
			extras_cost_unit = "extras_cost_unit_"+i;
			extras_name = "extra_"+i;
			//if(document.getElementById(extras_name).selectedIndex > 0){
				extra_qty = parseInt(document.getElementById(extras_name).value);
				if(document.getElementById(extras_name).type === "checkbox"){
					if(document.getElementById(extras_name).checked){
						extra_qty = 1;
					} else {
						extra_qty = 0;
					}
				}
				if(document.getElementById(extras_cost_unit).value === "Hour"){
						extras_total_cost += (extra_qty * parseFloat(document.getElementById(extras_cost).value) * hours);
				} else {
					extras_total_cost += (extra_qty * parseFloat(document.getElementById(extras_cost).value));
				}
			//}
		}
		if(document.getElementById("extras_fee") != null){
			document.getElementById("extras_fee").innerHTML = extras_total_cost.toFixed(2);
		}
	}
}

// removed encoed/decode as it caused JED checker to fail
var svBase64 = {
	encode : function (input) {
		return input;
	},
	decode : function (input) {
		return input;
	}
}



/* end Base64 */

function trim(inStr){
	return inStr.replace(/^\s+|\s+$/g, '') ;
}

function removeHTMLTags(strInputCode){
 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
	 	return (p1 === "lt")? "<" : ">";
	});
	var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
 	return strTagStrippedText;	
}

function presetIndex(){
	// this function modified the ajax call syntax so it works with SEO ON or OFF
	var loc = ""+document.location;
	if(loc.indexOf(".html")> -1 ){
		// SEO is adding .html, remove it
		loc = loc.replace(".html", "");
	}
	if(loc.indexOf("view=front_desk&task=add_booking")>-1){
		return("~/index.php");
	}
	if(loc.indexOf("?")> -1 && loc.indexOf("index.php")> -1){
		// not SEO
		return("./index.php");
	} else if(loc.indexOf("?")> -1){
	    // seo with qs param, strip the qs param
	    var first_bit = loc.substring(0, loc.indexOf("?"));
// change for J4 rc4
//		return(first_bit+"/index.php");
		return(first_bit);
	} else if(loc.indexOf("index.php")>-1){
	    return loc.substring(0, loc.indexOf("index.php")+9);
	} else {
		return(window.location.href.replace(".html","")+"/index.php");
	}
}


function checkWhoBooked(which){
	// JQuery verison of checkWhoBooked
	if(document.getElementById("gad_who_booked")===null){
		return;
	}
	if(document.getElementById("gad_who_booked").value==='No'){
		return;
	}

	// get the onclick so we can parse out the ts date start and end times.
	var ts_onclick = ""+document.getElementById(which).childNodes[0].onclick;
	var i = ts_onclick.indexOf("selectTimeslot(",0);
	ts_onclick = ts_onclick.substring(i+16);
	//document.getElementById(which).setAttribute('title', ts_onclick);
	//return;

	var ary_selected = ts_onclick.split("|");
	var resource = ary_selected[0];
	var startdate = ary_selected[2];
	var enddate = ary_selected[2];
	var starttime = ary_selected[4];
	var endtime = ary_selected[6];
	
	jQuery.noConflict();

	var data = "startdate=" + encodeURIComponent(startdate);
	data = data + "&starttime=" + encodeURIComponent(starttime);
	data = data + "&enddate=" + encodeURIComponent(enddate);
	data = data + "&endtime=" + encodeURIComponent(endtime);
	data = data + "&res_id=" + resource;
	data = data + "&ts=" + which;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_who_booked&format=raw",
		data: data,
		success: function(data) {
			if(data.length == 0) return;
			var ary_booked_info = data.split("|");
			if(ary_booked_info.length === 0 || data == "\n"){
				return;
			} else {
				//alert(ary_booked_info[1]);
				eval("document.getElementById("+ary_booked_info[0]+").setAttribute('title', '"+ary_booked_info[1]+"');");		
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get checkWhoBooked, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get checkWhoBooked:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function getCheckedValue(udf_name){
    var radio_length = document.frmRequest[udf_name].length;
    for(udf_i=0; udf_i<radio_length; udf_i++){
        if(document.frmRequest[udf_name][udf_i].checked){
            return document.frmRequest[udf_name][udf_i].value;
        }
    }
    return "";    
}

function getExtras_duration(){
	var extras_count = 0; 
	var total_extras_duration = 0; // in minutes, this release will only support extra duration in minutes
	
	for(i=0; i<parseInt(document.getElementById("extras_count").value); i++){
		extras_duration = "extras_duration_"+i;
		extras_duration_effect = "extras_duration_effect_"+i;
		//extras_cost_unit = "extras_duration_unit_"+i;
		extras_name = "extra_"+i;
		if(document.getElementById(extras_name).type === "checkbox"){
			if(document.getElementById(extras_name).checked){
    			total_extras_duration += parseFloat(document.getElementById(extras_duration).value);
			}			
		} else {
			if(jQuery("#"+extras_name).val() > 0){
				if(document.getElementById(extras_duration_effect).value === "PerUnit"){
					total_extras_duration += (parseInt(document.getElementById(extras_name).value) * parseFloat(document.getElementById(extras_duration).value) );
				} else {
					total_extras_duration += (parseFloat(document.getElementById(extras_duration).value) );
				}
			}
		}
	}
	return total_extras_duration;	
}

function changeExtra(){
	setDuration();
	calcTotal();
}


function addToCart(){

	jQuery.noConflict();
	// add to cart will add the booking as 'pending', locking the slot.
	
	// First validate screen..
	result = validateForm();
	//alert("|"+result+"|");

	if(result.indexOf(document.getElementById("validate_text").value)==-1){
		return false;
	}
	// get all input elements
	pagedata = decodeURIComponent("&"+jQuery(document.frmRequest).find('select, textarea, input:not([name=option], [name=controller], [name=task])').serialize());
	
	if(document.getElementById("selected_resource_id") != null){
		// gad and wiz
		pagedata += "&resource="+document.getElementById("selected_resource_id").value;
	} else {
		// simple
		pagedata += "&resource="+document.getElementById("resources").value;
	}
	pagedata += "&ppsubmit=4"; // add to cart

	// add timestamp so IE caching will not block the server call in the case of rebooking the same slot	
	// not needed with POST
	//pagedata += "&x="+ new Date();
	
	//var pagedata = encodeURIComponent(pagedata);
	//alert(pagedata); 

    jQuery.ajax({               
		type: "POST",
		dataType: 'json',
		url: presetIndex()+"?option=com_rsappt_pro3&controller=booking_screen_gad&task=process_booking_request",
//		url: presetIndex()+"?option=com_rsappt_pro3&controller=booking_screen_gad&task=process_booking_request"+pagedata,
		data: pagedata,
		success: function(data) {
			if(document.getElementById("controller").value != "booking_screen_simple"){
				buildTable();
			}
			if(document.getElementById("controller").value == "bookingscreengadwiz"){
				gowiz1();
			}		
			document.getElementById("errors").innerHTML = "";
			alert_dialog = jQuery("<div id='alert_dialog'>"+data.msg+"</div>").dialog({
				autoOpen: false,
				modal: true,
				minWidth: 300,
				resizable: false,
				height: "auto",
				close: function () {
				}			
			});						
			alert_dialog.dialog( "option", "buttons", [ { text: cart_close, click: function() { jQuery( this ).dialog( "close" ); } } ] );
			alert_dialog.dialog("option", "title", cart_title).dialog("open");
			//viewCart();
		},
		error: function(data) {
			alert(data.responseText);
		}					
	 });
	
}

function viewCart(){
	var x_size = 750;
	var	y_size = 500;
	if(navigator.userAgent.match(/iPad/i) != null){
		// iPad
		if (screen.height > screen.width){
			x_size = 600;
			y_size = 500;
		}else{
			x_size = 500;
			y_size = 600;
		}
	}
	if(document.getElementById("mobile")!= null){
		if (screen.height > screen.width){
			x_size = 250;
			y_size = 350;
		}else{
			x_size = 350;
			y_size = 250;
		}
	}
	var fd = "&fd=No";
	if(document.getElementById("fd")!= null){
		fd = "&fd="+document.getElementById("fd").value;
	}

	jQuery.noConflict();
	iframe = jQuery('<iframe id="cart_iframe" frameborder="0" marginwidth="0" marginheight="0" allowfullscreen></iframe>');
	cart_dialog = jQuery("<div id='cart_dialog'></div>").append(iframe).appendTo("body").dialog({
		autoOpen: false,
		modal: true,
		//resizable: false,
		width: "auto",
		height: "auto",
		minHeight: y_size, 
		close: function () {
			iframe.attr("src", "");
			cart_window_close_process();
		}			
	});			
	var src = "index.php?option=com_rsappt_pro3&view=cart&task=view&tmpl=component"+fd;
	iframe.attr({
		width: x_size,
		height: y_size,
		src: src
	});
		
//	dialog.dialog( "option", "buttons", [ { text: cart_close, click: function() { jQuery( this ).dialog( "close" ); } } ] );

	cart_dialog.dialog("option", "title", cart_title).dialog("open");

//	window.parent.SqueezeBox.open('index.php?option=com_rsappt_pro3&view=cart&task=view&tmpl=component'+fd, {handler: 'iframe', size: {x: x_size, y: y_size}, onClose: function(){cart_window_close();}});

}

function cart_window_close(){
	cart_dialog.dialog("close");
	return false;
}

function cart_window_close_process(){
	if(localStorage["gw_confirm"] == "yes"){
		localStorage["gw_confirm"] = ""
		document.body.style.cursor = "wait";
		document.location = "index.php?option=com_rsappt_pro3&view="+jQuery('#frompage').val()+"&Itemid="+jQuery('#frompage_item').val()+"&task=show_confirmation&req_id=''&cc=cart";
		return true;		
	}
	if(localStorage["checkout_required"] == "yes"){
		// off to PayPal or Authnet
		localStorage["checkout_required"] = "";
		var dest_url = "index.php?option=com_rsappt_pro3&view=cart&task=checkout&sid="
		+localStorage["checkout_sid"]+"&pp="+localStorage["checkout_dest"]
		+"&cart_total="+localStorage["checkout_cart_total"]+"&frompage="+document.getElementById('frompage').value
		+"&frompage_item="+document.getElementById('frompage_item').value;
		if(localStorage["payage"] == "yes"){
			dest_url += "&xsp="+localStorage["sp"];
		} else {
			dest_url += "&xfo="+localStorage["xfo"];
		}
	//alert(dest_url);
	//return false;
		document.body.style.cursor = "wait";
		document.location.href = dest_url;
		return true;		
	}
	if(document.getElementById("controller").value != "booking_screen_simple"){
		// Refresh the grid
		buildTable(); // to show pending bookings
		calcTotal(); // to clear out the costs display
	}
	if(localStorage["checkout_complete"] == "yes"){
		localStorage["checkout_complete"] = "";
		if(document.getElementById("controller").value == "bookingscreengadwiz"){
			gowiz1();
		}		
		if(document.getElementById("controller").value == "simple_booking_screen"){
			document.location.reload(true);
		}		
	} else {
		if(document.getElementById("controller").value == "bookingscreengadwiz"){
			gowiz1();
		}		
	}
}

function getRateOverrides(selected_user_id){
	jQuery.noConflict();

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_get_rate_overrides&id="+selected_user_id,
		success: function(data) {
			//alert(aryRates[3]);
			aryRates = new Array();
			var temp = data.split(",");
			for (var i = 0; i < temp.length; i++) {
				var temp2 = temp[i].split(":");
				aryRates[temp2[0]] = temp2[1];
			}
			//alert(aryRates[3]);
			calcTotal();
		},
		error: function(data) {
			alert(data);
		}					
	 });
	
}

Joomla.tableOrdering = function tableOrdering( order, dir, prefix ) {
	// I am overriding Joomla's function to make it support ordering different tabs in a form
	var form = document.adminForm;
	//alert(prefix+'filter_order');
	ctl = prefix+'filter_order';
	ctl2 = prefix+'filter_order_Dir';
	document.adminForm.elements[ctl].value = order;
	document.adminForm.elements[ctl2].value	= dir;

	if(document.getElementById("current_tab") != null){
	  switch (prefix)
	  {
		case "req_": { 	document.getElementById("current_tab").value="0"; break }
		case "res_": { 	document.getElementById("current_tab").value=document.getElementById("resources_tab").value; break }
		case "srv_": { 	document.getElementById("current_tab").value=document.getElementById("services_tab").value; break }
		case "ts_": { 	document.getElementById("current_tab").value=document.getElementById("timeslots_tab").value; break }
		case "bo_": { 	document.getElementById("current_tab").value=document.getElementById("bookoffs_tab").value; break }
		case "bd_": { 	document.getElementById("current_tab").value=document.getElementById("book_dates_tab").value; break }
		case "pp_": { 	document.getElementById("current_tab").value=document.getElementById("paypal_tab").value; break }
		case "an_": { 	document.getElementById("current_tab").value=document.getElementById("authnet_tab").value; break }
		case "an_aim_": { 	document.getElementById("current_tab").value=document.getElementById("authnet_aim_tab").value; break }
		case "goog_": { 	document.getElementById("current_tab").value=document.getElementById("google_wallet_tab").value; break }
		case "2co_": { 	document.getElementById("current_tab").value=document.getElementById("_2co_tab").value; break }
		case "coup_": { document.getElementById("current_tab").value=document.getElementById("coupons_tab").value; break }
		case "ext_": { document.getElementById("current_tab").value=document.getElementById("extras_tab").value; break }
		case "ra_": { document.getElementById("current_tab").value=document.getElementById("rate_adjustments_tab").value; break }
		case "uca_": { document.getElementById("current_tab").value=document.getElementById("user_credit_tab").value; break }
	  }
	}
	document.adminForm.submit();
}

function getExtras(){
	// JQuery verison of getExtras

	if(document.getElementById("resource_extras_div")===null){
		return false;
	}
	
	// clear out old stuff
	if(document.getElementById("resource_extras") != null){
		document.getElementById("resource_extras").style.display = "none";
		document.getElementById("resource_extras").style.visibility = "hidden";
		document.getElementById("resource_extras_div").innerHTML = "";
	}
	if(document.getElementById("resource_extras_div") != null){
		document.getElementById("resource_extras_div").style.display = "none";
		document.getElementById("resource_extras_div").style.visibility = "hidden";
		document.getElementById("resource_extras_div").innerHTML = "";
	}

	if(document.getElementById("resources") === null){
		return false;
	}
	if(document.getElementById("resources").value === "0"){
		return false;
	}
	
	jQuery.noConflict();

	var calldata = "res=" + document.getElementById("resources").value;
	//if(document.getElementById("service_name") != null){
	//	data = data + "&srv=" + document.getElementById("resources").value;		
	//}
	if(document.getElementById("mobile")!=null){
		calldata = calldata + "&mobile=" + document.getElementById("mobile").value;	
	}
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	calldata = calldata + "&extras=yes";
	if(document.getElementById("users")!=null){
		calldata = calldata + "&uid="+document.getElementById("users").value;		
	}
	if(document.getElementById("fd") != null){
		calldata = calldata + "&fd="+document.getElementById("fd").value;
	}
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,  // needed to ensure extras are rendered before calculations are done in case minimum extra is enforced.
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			if(trim(data) != ""){
				document.getElementById("resource_extras").style.display = "";
				document.getElementById("resource_extras").style.visibility = "visible";
				document.getElementById("resource_extras_div").style.display = "";
				document.getElementById("resource_extras_div").style.visibility = "visible";
				document.getElementById("resource_extras_div").innerHTML = data;
			} else {
				document.getElementById("resource_extras_div").style.visibility = "hidden";
				document.getElementById("resource_extras_div").style.display = "none";
				document.getElementById("resource_extras_div").innerHTML = "";
				document.getElementById("resource_extras").style.visibility = "hidden";
				document.getElementById("resource_extras").style.display = "none";
				document.getElementById("resource_extras").style.height = "1px";
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get Extras, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get Extras:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function getResourceSeatTypes(){
	if(document.getElementById("resources") === null){
		return false;
	}
	if(document.getElementById("resources").value === "0"){
		return false;
	}

	if(document.getElementById("resource_seat_types_div")===null){
		return false;
	}
	
	jQuery.noConflict();
	var calldata = "res=" + document.getElementById("resources").value;
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	calldata = calldata + "&res_seats=yes";
	if(document.getElementById("users")!=null){
		calldata = calldata + "&uid="+document.getElementById("users").value;		
	}
	if(document.getElementById("mobile")!=null){
		calldata = calldata + "&mobile=" + document.getElementById("mobile").value;	
	}
	//alert(calldata);
    
	jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false, // or else calcseatstotal can happen before seat counts are updated
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			if(trim(data) != ""){
				document.getElementById("resource_seat_types").style.display = "";
				document.getElementById("resource_seat_types").style.visibility = "visible";
				document.getElementById("resource_seat_types_div").style.display = "";
				document.getElementById("resource_seat_types_div").style.visibility = "visible";
				document.getElementById("resource_seat_types_div").innerHTML = data;
			} else {
				document.getElementById("resource_seat_types_div").style.visibility = "hidden";
				document.getElementById("resource_seat_types_div").style.display = "none";
				document.getElementById("resource_seat_types_div").innerHTML = "";
				document.getElementById("resource_seat_types").style.visibility = "hidden";
				document.getElementById("resource_seat_types").style.display = "none";
				document.getElementById("resource_seat_types").style.height = "0px";
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
        	alert("Error on server call to get Resource Seat Types:\n"+xhr.status + " - " + thrownError);
      	}
	 });	
}


function getResourceUFDs(){
	if(document.getElementById("resources") === null){
		return false;
	}
	if(document.getElementById("resources").value === "0"){
		return false;
	}
	
	jQuery.noConflict();
	var calldata = "res=" + document.getElementById("resources").value;
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	if(document.getElementById("fd") != null){
		calldata = calldata + "&fd="+document.getElementById("fd").value;
	}
	if(document.getElementById("mobile")!=null){
		calldata = calldata + "&mobile=" + document.getElementById("mobile").value;	
	}
	calldata = calldata + "&res_udfs=yes";
	//alert(calldata);
    
	jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			if(document.getElementById("resource_udfs")!= null){
				if(trim(data) != ""){
					document.getElementById("resource_udfs").style.display = "";
					document.getElementById("resource_udfs").style.visibility = "visible";
					document.getElementById("resource_udfs_div").style.display = "";
					document.getElementById("resource_udfs_div").style.visibility = "visible";
					document.getElementById("resource_udfs_div").innerHTML = data;
					// if the UDFs have script for help we need to ferret it out and eval it		
					var matches = [];
					data.replace(/<script>(.*?)<\/script>/g, function () {
						//arguments[0] is the entire match
						matches.push(arguments[1]);
					});			
					for (script_index = 0; script_index < matches.length; ++script_index) {
						jQuery.globalEval(matches[script_index]);
					}
				} else {
					document.getElementById("resource_udfs_div").style.visibility = "hidden";
					document.getElementById("resource_udfs_div").style.display = "none";
					document.getElementById("resource_udfs_div").innerHTML = "";
					document.getElementById("resource_udfs").style.visibility = "hidden";
					document.getElementById("resource_udfs").style.display = "none";
					document.getElementById("resource_udfs").style.height = "1px";
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
        	alert("Error on server call to get Resource UDFs:\n"+xhr.status + " - " + thrownError);
      	}
	 });	
}


function getServices(no_dur){
	// JQuery verison of getServices
	
	if(document.getElementById("service_durations") != null){
		document.getElementById("service_durations").value = "";
	}
	if(document.getElementById("resources") === null){
		return false;
	}
	if(document.getElementById("resources").value === "0"){
		return false;
	}
	
	jQuery.noConflict();

	var calldata = "res=" + document.getElementById("resources").value;
	calldata = calldata + "&browser=" + BrowserDetect.browser;
	calldata = calldata + "&serv=yes";
	if(document.getElementById("screen_type").value === "fd_gad"){
		calldata = calldata + "&fd=Yes";
	}
	if(document.getElementById("users")!=null){
		calldata = calldata + "&uid="+document.getElementById("users").value;		
	}
	if(document.getElementById("preset_service")!=null){
		calldata = calldata + "&preset_service=" + document.getElementById("preset_service").value;	
	}
	if(document.getElementById("sub_category_id")!=null){
		calldata = calldata + "&cat=" + document.getElementById("sub_category_id").value;	
	} else if(document.getElementById("category_id")!=null){
		calldata = calldata + "&cat=" + document.getElementById("category_id").value;	
	}
	
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false, // for ddslick
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: calldata,
		success: function(data) {
			if(data.indexOf("<select name=")>-1){
				// if service_selector is in use, do not turn on the service dropdown
				if(document.getElementById("service_selector_div") == null){				
					document.getElementById("services").style.display = "";
					document.getElementById("services").style.visibility = "visible";
					document.getElementById("services_div").style.display = "";
					document.getElementById("services_div").style.visibility = "visible";
				}
				document.getElementById("services_div").innerHTML = data;
				if(document.getElementById("service_summary") != null){
					document.getElementById("service_summary").style.display = "";
					document.getElementById("service_summary").style.visibility = "visible";
				}
				if(document.getElementById("service_name_slick") != null){
					jQuery('#service_name_slick').ddslick({
					   onSelected: function(data){jQuery('#service_name').val(data.selectedData.value);onchange=setDuration();calcTotal();}           
					}); 
				}				
			} else {
				document.getElementById("services").style.display = "none";
				document.getElementById("services").style.visibility = "hidden";
				document.getElementById("services_div").style.display = "none";
				document.getElementById("services_div").style.visibility = "hidden";
				if(document.getElementById("service_summary") != null){
					document.getElementById("service_summary").style.display = "none";
					document.getElementById("service_summary").style.visibility = "hidden";
				}
			}
			if(!no_dur || document.getElementById("service_selector") != null){			
				setDuration();
			}
			calcTotal();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get Services, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get Services:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function changeDatePicker(){
// not needed with jQuery date picker (??)	
//	if(document.getElementById("screen_type")!=null){
//		screen_type = document.getElementById("screen_type").value;
//		var date_picker_name = "";
//		switch(screen_type){
//			case "gad":
//				date_picker_name = "display_grid_date";
//				break;
//			case "fd_gad":
//				date_picker_name = "display_grid_date";
//				break;
//			case "non-gad":
//				date_picker_name = "display_startdate"
//				break;
//			}
//			
//		if(document.getElementById("date_picker_format")!=null){
//				var tempdate;
//			if( document.getElementById(date_picker_name).value != document.getElementById("select_date_text").value){
//				tempdate = Date.parse(document.getElementById(date_picker_name).value);	
//				if(document.getElementById("date_picker_format").value === "dd-mm-yy"){
//					document.getElementById(date_picker_name).value = tempdate.toString("dd-MM-yyyy");
//				}
//				if(document.getElementById("date_picker_format").value === "mm-dd-yy"){
//					document.getElementById(date_picker_name).value = tempdate.toString("MM-dd-yyyy");
//				}
//				if(document.getElementById("date_picker_format").value === "yy-mm-dd"){
//					document.getElementById(date_picker_name).value = tempdate.toString("yyyy-MM-dd");
//				}		
//			}
//		} else {
//			document.getElementById("display_picker_date").value =  document.getElementById(date_picker_name).value;
//		}
//	}
}

function disable_cart_buttons(){
	 document.getElementById("btnAddMore").disabled=true;
	 if(document.getElementById("btnCheckout")){
		 document.getElementById("btnCheckout").disabled=true;
	 }
	 if(document.getElementById("btnGWallet")){
		 document.getElementById("btnGWallet").disabled=true;
	 }
	 if(document.getElementById("btnAuthNet")){
		 document.getElementById("btnAuthNet").disabled=true;
	 }
	 if(document.getElementById("btnPayPal")){
		 document.getElementById("btnPayPal").disabled=true;
	 }
	 if(document.getElementById("btn2Co")){
		 document.getElementById("btn2Co").disabled=true;
	 }
	 if(document.getElementById("submit0")){ //hidden submit
		 document.getElementById("submit0").disabled=true;
	 }
}


function enable_cart_buttons(){
	 document.getElementById("btnAddMore").disabled=false;
	 document.getElementById("btnCheckout").disabled=false;
	 if(document.getElementById("btnGWallet")){
		 document.getElementById("btnGWallet").disabled=false;
	 }
	 if(document.getElementById("btnAuthNet")){
		 document.getElementById("btnAuthNet").disabled=false;
	 }	
	 if(document.getElementById("btnPayPal")){
		 document.getElementById("btnPayPal").disabled=false;
	 }	
	 if(document.getElementById("btn2Co")){
		 document.getElementById("btn2Co").disabled=false;
	 }	
}

function selectTimeslotSimple(){
	document.getElementById("errors").innerHTML = "";
	if(document.getElementById("timeslots").selectedIndex > 0){
		document.getElementById("booking_detail").style.display = "";
		document.getElementById("booking_detail").style.visibility = "visible";
		submit_section_show_hide("show");
	} else {
		document.getElementById("booking_detail").style.display = "none";
		document.getElementById("booking_detail").style.visibility = "hidden";
		submit_section_show_hide("hide");
	}

	// clear any coupon discount that may have been applied to a prevuiously seleect slot.
	if(document.getElementById("coupon_code") != null && document.getElementById("coupon_code").value != ""){
		document.getElementById("coupon_code").value = "";
		document.getElementById("coupon_info").innerHTML = "";
		document.getElementById("coupon_value").value = "0.00";		
	}
	
//	temp = jQuery('#timeslots option:selected').text();	
//	temp = temp.replace("AM", " AM");
//	temp = temp.replace("PM", " PM");
//    document.getElementById("selected_starttime").innerHTML = temp.substring(0,temp.indexOf(" - "));

	temp = document.getElementById("timeslots").value;
	temp = temp.split(",");
    document.getElementById("selected_starttime").innerHTML = makeDisplay(temp[0]);
	document.getElementById("selected_endtime").innerHTML = makeDisplay(temp[1]);
	free_booking_override = temp[2];
	ts_dur = (Date.parse(document.getElementById("startdate").value + " " + document.getElementById("endtime").value) - Date.parse(document.getElementById("startdate").value + " " + document.getElementById("starttime").value))/60000;
}

function makeDisplay(instr){
	timeFormat = document.getElementById("timeFormat").value;
	retVal = "";
	tmp = instr.split(":");
	if(timeFormat === "24"){
		retVal = tmp[0]+":"+tmp[1];
	} else {
		intTmp0 = parseInt(tmp[0], 10);
		if(intTmp0 > 12){
			intTmp0 = intTmp0 - 12;
			retVal = ""+intTmp0+":"+tmp[1]+" PM";
		} else if(intTmp0 == 12){
			retVal = ""+intTmp0+":"+tmp[1]+" PM";
		} else {
			retVal = ""+intTmp0+":"+tmp[1]+" AM";
		}
	}
	return retVal;
}

function disable_enableSubmitButtons(action){

	if(action === "disable"){		
		if(document.getElementById("submit") != null){
			document.getElementById("submit").disabled = true;
		}
		if(document.getElementById("submit0") != null){
			document.getElementById("submit0").disabled = true;
		}
		if(document.getElementById("submit1") != null){
			document.getElementById("submit1").disabled = true;
		}
		if(document.getElementById("submit2") != null){
			document.getElementById("submit2").disabled = true;
		}
		if(document.getElementById("btnPayPal") != null){
			document.getElementById("btnPayPal").disabled = true;
		}
		if(document.getElementById("btnAuthNet") != null){
			document.getElementById("btnAuthNet").disabled = true;
		}
		if(document.getElementById("btn2Co") != null){
			document.getElementById("btn2Co").disabled = true;
		}
		if(document.getElementById("btnGWallet") != null){
			document.getElementById("btnGWallet").disabled = true;
		}
		if(document.getElementById("btnStripe") != null){
			document.getElementById("btnStripe").disabled = true;
		}
		if(document.getElementById("btnMollie") != null){
			document.getElementById("btnMollie").disabled = true;
		}
	} else {
		if(document.getElementById("submit") != null){
			document.getElementById("submit").disabled = false;
		}
		if(document.getElementById("submit0") != null){
			document.getElementById("submit0").disabled = false;
		}
		if(document.getElementById("submit1") != null){
			document.getElementById("submit1").disabled = false;
		}
		if(document.getElementById("submit2") != null){
			document.getElementById("submit2").disabled = false;
		}
		if(document.getElementById("btnPayPal") != null){
			document.getElementById("btnPayPal").disabled = false;
		}
		if(document.getElementById("btnAuthNet") != null){
			document.getElementById("btnAuthNet").disabled = false;
		}
		if(document.getElementById("btn2Co") != null){
			document.getElementById("btn2Co").disabled = false;
		}
		if(document.getElementById("btnGWallet") != null){
			document.getElementById("btnGWallet").disabled = false;
		}
		if(document.getElementById("btnStripe") != null){
			document.getElementById("btnStripe").disabled = false;
		}
		if(document.getElementById("btnMollie") != null){
			document.getElementById("btnMollie").disabled = false;
		}
	}
}

function show_hidePayProcButtons(action){

	if(action === "hide"){		
		if(document.getElementById("btnPayPal") != null){
			document.getElementById("btnPayPal").style.visibility = "hidden";
			document.getElementById("btnPayPal").style.display = "none";		
		}
		if(document.getElementById("btnAuthNet") != null){
			document.getElementById("btnAuthNet").style.visibility = "hidden";
			document.getElementById("btnAuthNet").style.display = "none";		
		}
		if(document.getElementById("btn2Co") != null){
			document.getElementById("btn2Co").style.visibility = "hidden";
			document.getElementById("btn2Co").style.display = "none";		
		}
		if(document.getElementById("submit_gw") != null){
			document.getElementById("submit_gw").style.visibility = "hidden";
			document.getElementById("submit_gw").style.display = "none";		
		}
		if(document.getElementById("btnStripe") != null){
			document.getElementById("btnStripe").style.visibility = "hidden";
			document.getElementById("btnStripe").style.display = "none";		
		}
		// from staff booking
		if(document.getElementById("btnSubmit") != null){
			document.getElementById("btnSubmit").disabled = true;
		}
		if(document.getElementById("btncancel") != null){
			document.getElementById("btncancel").disabled = true;
		}
	} else {
		if(document.getElementById("btnPayPal") != null){
			document.getElementById("btnPayPal").style.visibility = "visible";
			document.getElementById("btnPayPal").style.display = "";		
		}
		if(document.getElementById("btnAuthNet") != null){
			document.getElementById("btnAuthNet").style.visibility = "visible";
			document.getElementById("btnAuthNet").style.display = "";		
		}
		if(document.getElementById("btn2Co") != null){
			document.getElementById("btn2Co").style.visibility = "visible";
			document.getElementById("btn2Co").style.display = "";		
		}
		if(document.getElementById("submit_gw") != null){
			document.getElementById("submit_gw").style.visibility = "visible";
			document.getElementById("submit_gw").style.display = "";		
		}
		if(document.getElementById("btnStripe") != null){
			document.getElementById("btnStripe").style.visibility = "visible";
			document.getElementById("btnStripe").style.display = "";		
		}
		if(document.getElementById("btnSubmit") != null){
			document.getElementById("btnSubmit").disabled = false;
		}
		if(document.getElementById("btncancel") != null){
			document.getElementById("btncancel").disabled = false;
		}
	}
}

function getRateAdjustment(ent, ent_id, bkg_date, bkg_start, bkg_end, rateObj){
	// Initially only resource rate adjustments are supported
	
	jQuery.noConflict();

	var calldata = "rate_adjust=yes";
	if(document.getElementById("screen_type").value === "fd_gad"){
		calldata +="&fd=Yes";
	}
	calldata += "&ent="+ent;
	calldata += "&ent_id="+ent_id;
	calldata += "&bkg_date="+bkg_date;
	calldata += "&bkg_start="+ bkg_start;
	calldata += "&bkg_end="+ bkg_end;
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		async: false,
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_get_rate_adjustments&format=raw",
		data: calldata,
		success: function(data) {
			rateObj.rate_adjustments = data;
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get Rate Adjustment, please refresh your browser and try again");	
			} else {
	        	alert("Error on server call to get Rate Adjustment:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	return;
}

//function checkWhoBooked(which){
//	// Override to show local time instead of who booked.	
//	// Get the onclick so we can parse out the ts date start and end times.
//	var ts_onclick = ""+document.getElementById(which).childNodes[0].onclick;
//	var i = ts_onclick.indexOf("selectTimeslot(",0);
//	ts_onclick = ts_onclick.substring(i+16);
//
//	var ary_selected = ts_onclick.split("|");
//	var resource = ary_selected[0];
//	var startdate = ary_selected[2];
//	var enddate = ary_selected[2];
//	var starttime = ary_selected[4];
//	var endtime = ary_selected[6];
//	
//	var d = new Date()
//	
//	// get browser timezome offset from UTC
//	var n = -d.getTimezoneOffset();
//	
//	// get site timezone offset from UTC
//	var site_tz_offset = parseInt(document.getElementById("tzo").value, 10);
//	
//	// get difference in offsets
//	var diff_in_minute = n-site_tz_offset;
//	
//	var timeslot_start_as_datetime = new Date.parse(startdate+" "+starttime);
//	var timeslot_end_as_datetime = new Date.parse(startdate+" "+endtime);
//	
//	// add offset diff to teh booking dat/time to get booking in browser local time
//	var original_slot_start = timeslot_start_as_datetime.toString("h:mm tt");	
//	var original_slot_end = timeslot_end_as_datetime.toString("h:mm tt");	
//
//	var slot_start_as_local = timeslot_start_as_datetime;
//	var slot_start_as_local = slot_start_as_local.add({ minutes: diff_in_minute });
//	var slot_end_as_local = timeslot_end_as_datetime.add({ minutes: diff_in_minute });
//	
//	var msg = timeslot_start_as_datetime.toString("d-MMM-yyyy")+"\n";
//	msg += original_slot_start + " - " + original_slot_end+"\n";	
//	msg += "Your time:\n";
//	msg += (slot_start_as_local.toString("d-MMM-yyyy")+"\n");
//	msg += (slot_start_as_local.toString("h:mm tt") + " - " + slot_end_as_local.toString("h:mm tt"));
//	// change the title of the slot to the msg
//	document.getElementById(which).childNodes[0].title = msg;
//
//}

function submit_section_show_hide(which){

	if(document.getElementById("jit_submit") != null){
		if(document.getElementById("jit_submit").value == "No"){
			which = "show";
		}
	}

	if(document.getElementById("btnAddToCart") != null){
		// do not hide if cart in use
		return;
	}
	if(which == "show"){		
		var cols = document.getElementsByClassName('submit_section');
		for(i=0; i<cols.length; i++) {
			cols[i].style.visibilty = 'visible';
			cols[i].style.display = '';
		}
	} else {
		var cols = document.getElementsByClassName('submit_section');
		for(i=0; i<cols.length; i++) {
			cols[i].style.visibilty = 'hidden';
			cols[i].style.display = 'none';
		}
	}
}

function checkday(mydate) {
	tocheck = mydate.getDay();
	if(date_specific_booking){
		var add_month_zero = "";
		var add_day_zero = "";
		if((mydate.getMonth() + 1)<10){
			add_month_zero = "0";
		}
		if(mydate.getDate() < 10){
			add_day_zero = "0";
		}
		dmy = mydate.getFullYear() + "-" + add_month_zero + (mydate.getMonth() + 1) + "-" + add_day_zero + mydate.getDate();
		if (jQuery.inArray(dmy, book_dates)>-1) {
			return [true, ""];
		} else {
			return [false, "", ""];
		}
	}
	if (jQuery.inArray(tocheck, non_booking_days) == -1) {
		// if it is a noraml booking day, check for book-offs
		var add_month_zero = "";
		var add_day_zero = "";
		if((mydate.getMonth() + 1)<10){
			add_month_zero = "0";
		}
		if(mydate.getDate() < 10){
			add_day_zero = "0";
		}
		dmy = mydate.getFullYear() + "-" + add_month_zero + (mydate.getMonth() + 1) + "-" + add_day_zero + mydate.getDate();
		if (jQuery.inArray(dmy, bookoff_dates) == -1) {
			return [true, ""];
		} else {
			return [false, "", ""];
		}
	} else {
		return [false, "", ""];
	}
}

function quick_status_change(id, status_list){
	//alert(id);
	dropdown_name = "booking_status_"+id;
	//alert(document.getElementById(dropdown_name).value);
//	new_status = document.getElementById(dropdown_name).value;
	new_status = status_list.value;
	old_status = status_list.oldvalue;
	
	jQuery.noConflict();
	document.body.style.cursor = "wait";

	var calldata = "stat_quick_change=yes";
	calldata += "&bk="+id;
	calldata += "&new_stat="+new_status;
	//alert(calldata);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_quick_status_change&format=raw",
		data: calldata,
		success: function(data) {
			document.body.style.cursor = "default";
			if(trim(data) != "OK"){
				// could not get jQuery dialog working, for now just use alert
				alert(data.replace(new RegExp("<br>", 'g'), "\n"));
//				alert_dialog = jQuery("<div id='alert_dialog'>"+data+"</div>").dialog({
//					autoOpen: false,
//					modal: true,
//					minWidth: 300,
//					resizable: false,
//					height: "auto"			
//				});						
//				alert_dialog.dialog( "option", "buttons", [ { text: jq_dialog_close, click: function() { jQuery( this ).dialog( "close" ); } } ] );
//				alert_dialog.dialog("option", "title", jq_dialog_title).dialog("open");
				document.getElementById(dropdown_name).value = old_status;
			} else {
				// OK but if seat totals being display we need to refresh
				if(document.getElementById("showSeatTotals") != null){
					if(document.getElementById("showSeatTotals").checked){
						location.reload();
					}
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get Change Status, please refresh your browser and try again");	
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get Change Status:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	return;
	
}

function check_somthing_is_checked(element_name){
	var x = document.getElementsByName(element_name);
	var i;
	var something_checked = false;
	for (i = 0; i < x.length; i++) {
		if(x[i].checked == true){
			something_checked = true;
		}
	}
	return something_checked;
}

function getGiftCert( optionalArg ){
	fd = (typeof optionalArg === "undefined") ? "No" : "Yes";
	if(document.getElementById("gift_cert").value === ""){
		document.getElementById("gift_cert_info").innerHTML = "";
	} else {
		document.getElementById("gift_cert_info").innerHTML = document.getElementById("wait_text").value;
	}
	
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "getcert=yes";
	data = data + "&res=" + document.getElementById("resources").value;
	data = data + "&gc=" + document.getElementById("gift_cert").value;
	data = data + "&browser=" + BrowserDetect.browser;
	data = data + "&bk_date=" + startdate;
	if(fd == "Yes"){
		data = data + "&uid=" + document.getElementById("user_id").value;
	}
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			if(data != ""){
				ary = data.split("|");
				if(ary[1] != -1){
					document.getElementById("gift_cert_info").innerHTML = ary[0]+ary[1];
					document.getElementById("gift_cert_bal").value = ary[1];
				} else {
					document.getElementById("gift_cert_info").innerHTML = ary[0];
					document.getElementById("gift_cert_bal").value = 0;
				}
				calcTotal();
			} else {
				document.getElementById("gift_cert_info").innerHTML = "";
				document.getElementById("gift_cert_bal").value = "0";
				calcTotal();
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getGiftCert, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getGiftCert:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}

function show_hide_row(row, action){
	if(document.getElementById(row) != null){
		switch(action) {
		    case "show":
				document.getElementById(row).style.visibility = "visible";
				document.getElementById(row).style.display = "";
				break;
			case "hide":
				document.getElementById(row).style.visibility = "hidden";
				document.getElementById(row).style.display = "none";
				break;
		}
	}
}

function buildCalendarDeskView(day, month, year, week_offset){
   	var Itemid = document.getElementById('frompage_item').value;

//   	var view_list_field = document.getElementById('front_desk_view');
//    var view_list_selected_index = view_list_field.selectedIndex;
//    var view = view_list_field.options[view_list_selected_index].value;
	var view = "month";

//   	var resource_list_field = document.getElementById('resource_filter');
//    var resource_list_selected_index = resource_list_field.selectedIndex;
//    var resource = resource_list_field.options[resource_list_selected_index].value;
	var resource = "";
	if(document.getElementById('single_res_mode').value == "Yes"){
		resource = document.getElementById('single_res_value').value;
//		document.getElementById('resource_filter').selectedIndex = 1;
	}

	var category = "";
  
    if (typeof day === "undefined") {
        if(document.getElementById("cur_day")!=null){
            day = document.getElementById("cur_day").value;
        } else {
		    var d = new Date();
		    day = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
        }
    }
    if (typeof month === "undefined") {
        if(document.getElementById("cur_month")!=null){
            month = document.getElementById("cur_month").value;
        } else {
            month="";
        }
    }
    if (typeof year === "undefined") {
        if(document.getElementById("cur_year")!=null){
            year = document.getElementById("cur_year").value;
        } else {
	        year = "";
		}
    }
    if (typeof week_offset === "undefined") {
        if(document.getElementById("cur_week_offset")!=null){
            week_offset = document.getElementById("cur_week_offset").value;
        } else {
            week_offset="0";
        }
    } 
       
	jQuery.noConflict();
	var data = "calendarview_view=" + view;
	data = data + "&day=" + day;
	data = data + "&month=" + month;
	data = data + "&year=" + year;
	data = data + "&resource=" + resource;
	data = data + "&category=" + category;
	data = data + "&user=" + document.getElementById("uid").value;
	data = data + "&weekoffset=" + week_offset;
	data = data + "&listpage=" + document.getElementById("listpage").value;
	if(document.getElementById("booking_screen")!=null){
		data = data + "&booking_screen=" + document.getElementById("booking_screen").value;
	}
	if(document.getElementById('single_cat_mode').value == "Yes"){
		data = data + "&single_cat=" + document.getElementById("single_cat_value").value;
	}
	if(document.getElementById('single_res_mode').value == "Yes"){
		data = data + "&single_res=" + document.getElementById("single_res_value").value;
	}
	data = data + "&Itemid=" + Itemid;
	data = data + "&Menuid=" +  document.getElementById("menu_id").value;
	if(document.getElementById("preset_service_res_ids")!=null){
		if(document.getElementById("preset_service_res_ids").value != ""){
			data = data + "&srv_res_ids=" +document.getElementById("preset_service_res_ids").value;	
		}
	}	
	data = data + "&browser=" + BrowserDetect.browser;
	//alert(data);
	document.body.style.cursor = "wait";

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_calviewview&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("calview_here").style.visibility = "visible";
			document.getElementById("calview_here").style.display = "";
			document.getElementById("calview_here").innerHTML = data;			
		},
		error: function (xhr, ajaxOptions, thrownError) {
			document.body.style.cursor = "default";
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildCalendarDeskView, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get buildCalendarDeskView:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });

}


function getAvailability(resource, cal_date, e){

	if(document.getElementById("slots_popup")===null){
		return;
	}
	if(document.getElementById("slots_popup").value==='No'){
		return;
	}

	jQuery.noConflict();
	jQuery("#availability").dialog( "close" );
	var data = "ts_date=" + encodeURIComponent(cal_date);
	data = data + "&res_id=" + resource;
	//alert(data);	
	var url = presetIndex()+"?option=com_rsappt_pro3&view=json_x&task=get_timeslots&fileout=yes&format=raw,";
    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		cache: false,
		async: false,
		url: url,
		data: data,
		success: function(data) {
			var myobj = data;
			var line_height = 25;
			var v_padding = 5;
			var h_padding = 0;
			var h_text_padding = 10;
			var dialog_width = 180;
			var rect_width = 70;
			var font_size = 12;
			//alert(myobj.data.length);
			jQuery("#availability").on( "dialogopen", function( event, ui ) {
				var s = Snap("#svg");
				s.clear();
				var line = 0;
				for(i=0; i<myobj.data.length; i++){
					var c = s.rect(h_padding, line+v_padding, rect_width, 20, 5);
					if(myobj.data[i].booked != "Booked" && myobj.data[i].booked != "Unavailable"){
						c.attr({
							fill: "#CCFFCC",
							stroke: "#33CC00",
							strokeWidth: 1, 
							width: "*=2" 
						});
						t = s.text(h_text_padding, line+line_height-v_padding, myobj.data[i].startendtime);
						t.attr({ 
							font: "Helvetica, sans-serif",
							"font-size": font_size
						});
					} else if(myobj.data[i].booked == "Unavailable"){
						c.attr({
							fill: "#FFFFCC",
							stroke: "#FFCC00",
							strokeWidth: 1, 
							width: "*=2" 
						});
						t = s.text(h_text_padding, line+line_height-v_padding, myobj.data[i].startendtime);
						t.attr({ 
							font: "Helvetica, sans-serif",
							"font-size": font_size
						});
					} else {
						c.attr({
							fill: "#FFCECE",
							stroke: "#FF0000",
							strokeWidth: 1, 
							width: "*=2" 
						});
						t = s.text(h_text_padding, line+line_height-v_padding, myobj.data[i].startendtime);
						t.attr({ 
							font: "Helvetica, sans-serif",
							"text-decoration": "line-through",
							"font-size": font_size
						});
					}
					line +=line_height;
				}	
			} );		
			jQuery("#availability").data( "myobj", myobj );
			jQuery("#availability").dialog( "option", "height", "auto");
//			jQuery("#availability").dialog( "option", "height", (myobj.data.length*(line_height+v_padding)));
			jQuery("#availability").dialog( "option", "width", dialog_width );
			jQuery(".ui-dialog-titlebar").hide();
			jQuery("#svg").height(myobj.data.length*(line_height));			
 			jQuery( "#availability" ).dialog("option", "position", {
				my: "left",
				at: "left",
				of: e,
				offset: "5 70"
			});				
			jQuery("#availability").dialog( "open" );		
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
	        	alert("Error on server call to get getAvailability, please refresh your browser and try again");			
			} else {
	        	alert("Error on server call to get getAvailability:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
}

function closeAvailability(){
	jQuery("#availability").dialog( "close" );
}

function addtoNotificationList(resource, startdate, starttime, remove){
	// show dialog with email address and ask if user wants to be added to list
	// the code to show the dialog in is sv_codeblocks_js1
	// the html is in add_to_notification_list.php
	jQuery( "#addto_notification_dialog" ).data( "resource", resource );
	jQuery( "#addto_notification_dialog" ).data( "starttime", starttime );
	jQuery( "#addto_notification_dialog" ).data( "startdate", startdate );
	jQuery( "#addto_notification_dialog" ).data( "remove", remove );
	jQuery( "#addto_notification_dialog" ).dialog( "open" );
}

function do_addtoNotificationList(resource, startdate, starttime, email, remove){
	// do the ajax call to add this email to the list
	document.getElementById("results").innerHTML = "";
	
	var data = "res=" + resource;
	data = data + "&startdate="+startdate;
	data = data + "&starttime="+starttime;
	data = data + "&email="+email;
	data = data + "&remove="+remove;
	//alert(data);
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		async: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_add_to_notification_list&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			ret_val = data;
			document.getElementById("results").innerHTML = data;
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get do_addtoNotificationList, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get do_addtoNotificationList:\n"+xhr.status + " - " + thrownError);
			}
			disable_enableSubmitButtons("enable");			
      	}
	 });
}

function changeServiceSelector(){
	// fetch resources for the selected service then create/populate the resource dropdown and grid.
	// clear old selected timeslot if there is one.
	old_ts["id"] = "";
	
	if(document.getElementById("resources") != null){
		document.getElementById("resources").selectedIndex = 0; // reset resources dropdown
		if(document.getElementById("resources_slick") != null){
			jQuery('#resources_slick').ddslick('select', {index: 1 });
		}

		if(document.getElementById("mode") != null){
			document.getElementById("mode").value = "single_day";
		}
	}

	if(document.getElementById("resource_udfs") != null){
		document.getElementById("resource_udfs").style.display = "none";
		document.getElementById("resource_udfs").style.visibility = "hidden";
	}
	
	if(document.getElementById("service_selector").selectedIndex  === 0){
		// hide stuff
		if(document.getElementById("resources_label") != null){
			document.getElementById("resources_label").style.display = "none";
			document.getElementById("resources_label").style.visibility = "hidden";
		}
		if(document.getElementById("datetime")!=null){ document.getElementById("datetime").style.display = "none";}
		document.getElementById("services_div").style.display = "none";
		if(document.getElementById("resources") != null){
			document.getElementById("resources").style.display = "none";
		}
		if(document.getElementById("resources_slick") != null){
			document.getElementById("resources_slick").style.display = "none";
		}
		if(document.getElementById("gad_container") != null){
			document.getElementById("gad_container").style.display = "none";
		}
		if(document.getElementById("subcats_row") != null){
			document.getElementById("subcats_row").style.visibility = "hidden";
			document.getElementById("subcats_row").style.display = "none";
		}
		if(document.getElementById("subcats_div") != null){
			document.getElementById("subcats_div").innerHTML = "";
		}
		if(document.getElementById("service_summary") != null){
			document.getElementById("service_summary").style.display = "none";
			document.getElementById("service_summary").style.visibility = "hidden";
		}
		if(document.getElementById("services") != null){
			document.getElementById("services").style.display = "none";
			document.getElementById("services").style.visibility = "hidden";
		}
		if(document.getElementById("resource_udfs") != null){
			document.getElementById("resource_udfs").style.display = "none";
			document.getElementById("resource_udfs").style.visibility = "hidden";
		}
		if(document.getElementById("resource_seat_types") != null){
			document.getElementById("resource_seat_types").style.display = "none";
			document.getElementById("resource_seat_types").style.visibility = "hidden";
		}
		if(document.getElementById("resource_extras") != null){
			document.getElementById("resource_extras").style.display = "none";
			document.getElementById("resource_extras").style.visibility = "hidden";
		}

		return false;
	}

	if(document.getElementById("preset_service") != null){
		document.getElementById("preset_service").value = jQuery("#service_selector").val();
	}

	getResources("", jQuery("#service_selector").val());
	getResources_for_service(jQuery("#service_selector").val());	

	if(document.getElementById("resources_for_service") != null){
		document.getElementById("resources_for_service").style.display = "";	
		document.getElementById("resources_for_service").style.visibility = "visible";
	}

	document.getElementById("resources_label").style.display = "";	
	document.getElementById("resources_label").style.visibility = "visible";
	document.getElementById("resources_div").style.visibility = "visible";

	if(document.getElementById("mode") != null){
		document.getElementById("table_here").innerHTML = document.getElementById("wait_text").value;

		document.getElementById("gad_container").style.display = "";
		//buildTable();
	} else {
		document.getElementById("slots").style.visibility = "hidden";
		document.getElementById("startdate").value = "";
	}
//	if(document.getElementById("sv_codeblock_submit") != null){		
//		document.getElementById("sv_codeblock_submit").style.display = "none";
//		document.getElementById("sv_codeblock_submit").style.visibility = "hidden";		
//	}
	if(document.getElementById("calcResults") != null){
		document.getElementById("calcResults").style.display = "none";
		document.getElementById("calcResults").style.visibility = "hidden";		
	}

}

function getResources_for_service(service_id){
	var data = "sid=" + service_id;
	//alert(data);
    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_resources_for_service&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			if(document.getElementById("preset_service_res_ids") != null){
				document.getElementById("preset_service_res_ids").value = data;
			}			
			buildTable();	
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getResources_for_service, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getResources_for_service:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
}

function getServices_for_category(){
	// this gets called when using categories and a new category is selected.
	document.body.style.cursor = "wait";
	jQuery.noConflict();
	var data = "";
	if(document.getElementById("sub_category_id") == null){
		// no sub cat, use cat
		data = "cat=" + jQuery("#category_id").val();
	} else {
		data = "cat=" + jQuery("#sub_category_id").val();
	}
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'html',
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_services_for_category&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			document.getElementById("service_selector_div").innerHTML = data;			
			if(document.getElementById("service_selector_div") != null){
				document.getElementById("service_selector_div").style.display = "";
				document.getElementById("service_selector_div").style.visibility = "visible";
			}
			if(document.getElementById("service_selector_label") != null){
				document.getElementById("service_selector_label").style.display = "";
				document.getElementById("service_selector_label").style.visibility = "visible";
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getServices_for_category, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get getServices_for_category:\n"+xhr.status + " - " + thrownError);
			}
      	}
	 });
	 
	return true;
}

function set_enable_book_dates(){
	document.body.style.cursor = "wait";
	jQuery.noConflict();

	var data = "";
	data = "nv="+document.getElementById("enable_book_dates").value;
	data = data + "&res="+document.getElementById("book_dates_resourceFilter").value;
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_set_book_dates_enable&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			alert(data);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get set_enable_book_dates, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get set_enable_book_dates:\n"+xhr.status + " - " + thrownError);
			}
      	}
			
	 });	
}

function do_book_date_purge(){
	jQuery.noConflict();

	if(jQuery("#book_dates_resourceFilter").val() == 0){
		alert(jQuery("#book_dates_resourceFilter option:selected").text());
		return false;		
	}

	document.body.style.cursor = "wait";

	var data = "";
	data = "res="+jQuery("#book_dates_resourceFilter").val();
	//alert(data);

    jQuery.ajax({               
		type: "GET",
		dataType: 'json',
		cache: false,
		url: presetIndex()+"?option=com_rsappt_pro3&view=ajax&controller=ajax&task=ajax_purge_old_dates&format=raw",
		data: data,
		success: function(data) {
			document.body.style.cursor = "default";
			alert(data);
			location.reload(true);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(xhr.status == 0){
				document.body.style.cursor = "default";
	        	alert("Error on server call to get ajax_purge_old_dates, please refresh your browser and try again");			
			} else {
				document.body.style.cursor = "default";
	        	alert("Error on server call to get ajax_purge_old_dates:\n"+xhr.status + " - " + thrownError);
			}
      	}
			
	 });	
}

function resize_end(ts_div, which, starttime_from_slot, res_id, round_to){
	
	// get left, right and width
	var slot = jQuery("#"+ts_div);
	var slot_position = slot.position();
	
	var slot_left_px = slot_position.left;
	var slot_right_px = slot_left_px + jQuery("#"+ts_div).width();
	var slot_width = slot_right_px - slot_left_px;
	
	var slot_top_px = slot_position.top;
	var slot_bottom_px = slot_top_px + jQuery("#"+ts_div).height();
	var slot_height = slot_bottom_px - slot_top_px;

	if(which == "gad"){	
		var pxm = parseFloat(jQuery("#pxm").val());
		
		// convert to times
		var new_duration_min = slot_width/pxm;
	
		var start_time_as_date = Date.parse(document.getElementById("startdate").value + " " + document.getElementById("starttime").value);
		var new_end_time_as_date = start_time_as_date.add({ minutes: new_duration_min });  
	
		// round to nearest 'round_to' min
		var rounded_duration_in_min = Math.round(new_duration_min/round_to)*round_to;

		// bring back to end of grid if necessary
		var live_grid_right = jQuery("#timeslot_resize_container").width();
		if((rounded_duration_in_min*pxm) + slot_left_px > live_grid_right){
			jQuery("#"+ts_div).width(live_grid_right - slot_left_px);
		} else {
			jQuery("#"+ts_div).width(rounded_duration_in_min*pxm);
		}

	} else {
		var pxm = parseFloat(jQuery("#pxm2").val());

		// convert to times
		var new_duration_min = slot_height/pxm;
	
		var start_time_as_date = Date.parse(document.getElementById("startdate").value + " " + document.getElementById("starttime").value);
		var new_end_time_as_date = start_time_as_date.add({ minutes: new_duration_min });  
	
		// round to nearest 'round_to' min
		var rounded_duration_in_min = Math.round(new_duration_min/round_to)*round_to;
		
		// bring back to end of grid if necessary
		var live_grid_bottom = jQuery("#cell_container").height() - 50;  // 50*2 is rowheight_header in gad_ajax2.php line 145	
		if((rounded_duration_in_min*pxm) + slot_top_px > live_grid_bottom){
			jQuery("#"+ts_div).height(live_grid_bottom - slot_top_px - 5);
		} else {
			jQuery("#"+ts_div).height(rounded_duration_in_min*pxm);
		}
	}
	
	start_time_as_date = Date.parse(document.getElementById("startdate").value + " " + document.getElementById("starttime").value);
	new_end_time_as_date = start_time_as_date.addMinutes(rounded_duration_in_min);
	document.getElementById("user_duration").value = rounded_duration_in_min;
	setDuration();
	calcTotal();
	check_for_overrun(document.getElementById("startdate").value, starttime_from_slot,
			document.getElementById("enddate").value, new_end_time_as_date.toString("HH:mm:ss"), res_id);
		
}

