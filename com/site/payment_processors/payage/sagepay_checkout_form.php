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
?>

<?php 
if(!isset($isMobile)){$isMobile = "no";};

if($isMobile != "yes"){ ?>
	
	<div id="sv_apptpro_view_checkout">
      <fieldset>
      <fieldset>
        <div>
          <label for="sp_first_name"><?php echo JText::_('RS1_INPUT_PAYAGE_FIRST_NAME');?></label>
          <input type="text" class="sagepay" size="15" name="sp_first_name" id="sp_first_name" value="" ></input>
        </div>
        <div>
          <label for="sp_last_name"><?php echo JText::_('RS1_INPUT_PAYAGE_LAST_NAME');?></label>
          <input type="text" class="sagepay" size="14" name="sp_last_name" id="sp_last_name" value=""></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label for="sp_address"><?php echo JText::_('RS1_INPUT_PAYAGE_ADDRESS');?></label>
          <input type="text" class="sagepayY" size="26" name="sp_address" id="sp_address" value=""></input>
        </div>
        <div>
          <label for="sp_city"><?php echo JText::_('RS1_INPUT_PAYAGE_CITY');?></label>
          <input type="text" class="sagepay" size="15" name="sp_city" id="sp_city" value=""></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label for="sp_state"><?php echo JText::_('RS1_INPUT_PAYAGE_STATE');?></label>
          <input type="text" class="sagepay_state" size="4" name="sp_state" id="sp_state" value=""></input>
        </div>
        <div>
          <label for="sp_zip"><?php echo JText::_('RS1_INPUT_PAYAGE_ZIP');?></label>
          <input type="text" class="sagepay_zip" size="9" name="sp_zip" id="sp_zip" value=""></input>
        </div>
        <div>
          <label for="sp_country"><?php echo JText::_('RS1_INPUT_PAYAGE_COUNTRY');?></label>
<!--          <input type="text" class="sagepay_country" size="22" name="sp_country" id="sp_country" value=""></input>-->
          <select id="sp_country" class="sagepay_country" name="sp_country"></select>
        </div>
      </fieldset>
      <div id="theButtons">
      <input type="submit" id="btnSubmit" value="<?php echo JText::_('RS1_INPUT_PAYAGE_SUBMIT');?>" >
      <input type="button" id="btnCancel" value="<?php echo JText::_('RS1_INPUT_PAYAGE_CANCEL');?>" >
      </div>
	</div>

<?php } else { ?>
	<div id="sv_apptpro_view_checkout_mobile">
    	<table style="border-collapse:collapse">
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_FIRST_NAME');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="15" name="sp_first_name" id="sp_first_name" value="" ></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_LAST_NAME');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="14" name="sp_last_name" id="sp_last_name" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_ADDRESS');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="26" name="sp_address" id="sp_address" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_CITY');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="15" name="sp_city" id="sp_city" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_STATE');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="4"  name="sp_state" id="sp_state" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_ZIP');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <input type="text" class="controls sv_sp_input" size="9" name="sp_zip" id="sp_zip" value=""></input>
        </div>
        </td></tr>
        <tr><td>
        <div>
          <label class="control-label"><?php echo JText::_('RS1_INPUT_PAYAGE_COUNTRY');?></label>
        </div>
        </td></tr>
        <tr><td>
        <div>
		  <select id="sp_country" name="sp_country" class="sagepay_country"></select>
        </div>
        </td></tr>
        </table>
      <div id="theButtons_mobile">
      <input type="submit" id="btnSubmit" value="<?php echo JText::_('RS1_INPUT_PAYAGE_SUBMIT');?>" >
      <input type="button" id="btnCancel" value="<?php echo JText::_('RS1_INPUT_PAYAGE_CANCEL');?>" >
      </div>

	</div>
<?php } ?>

    <script>
	jQuery(document).ready(function () {

		var listItems= "";
		for (var i = 0; i < countries.length; i++){
			listItems+= "<option value='" + countries[i].code + "'>" + countries[i].name + "</option>";
		 }
		 jQuery("#sp_country").html(listItems);
	
		jQuery("#frmRequest").validate({
		  onfocusout: false,
		  rules: {
			sp_first_name: "required",
			sp_last_name: "required",
			sp_address: "required",
			sp_city: "required",
			sp_state: "required",
			sp_zip: "required",
			sp_country: "required"
		  },
		  messages: {
			sp_first_name: "",
			sp_last_name: "",
			sp_address: "",
			sp_city: "",
			sp_state: "",
			sp_zip: "",
			sp_country: ""
		  }
		});

		jQuery('#btnCancel').click(function() {
			jQuery( '#sagepay_form' ).hide();
			disable_enableSubmitButtons("enable");	
		});
		
		jQuery('#btnSubmit').click(function() {
				if(jQuery("#frmRequest").valid()){
					if(jQuery("#controller").val() != "cart"){
						document.getElementById("ppsubmit").value = "payage~"+document.getElementById("sagepay_payage_account_id").value;
					    document.body.style.cursor = "wait"; 
						document.frmRequest.task.value = "process_booking_request";
						document.frmRequest.submit();
						return true;
					} else {
						localStorage["checkout_required"] = "yes";
						localStorage["payage"] = "yes";
						localStorage["checkout_sid"] = document.getElementById("sid").value;
						localStorage["checkout_dest"] = "payage~"+document.getElementById("sagepay_payage_account_id").value;
						localStorage["checkout_cart_total"] = document.getElementById("display_total").innerHTML;
						localStorage["cart"] = "yes";	
						var toPass = jQuery('#sp_first_name').val()+"|";					
						toPass += jQuery('#sp_last_name').val()+"|";					
						toPass += jQuery('#sp_address').val()+"|";					
						toPass += jQuery('#sp_city').val()+"|";					
						toPass += jQuery('#sp_state').val()+"|";					
						toPass += jQuery('#sp_zip').val()+"|";					
						toPass += jQuery('#sp_country').val();					
				
					// JED automated check will fail anything with base 64 encoding :-(
					// change 164 to 64 in the following line to make sagepay work
						localStorage["sp"] = svBase64.encode(toPass);
						window.parent.cart_window_close();
						//window.parent.SqueezeBox.close();
					}
				}
			});
    });
	
	var countries = [{code: "GB", name: "United Kingdom"},
//		{code: "AF", name: "Afghanistan"},
//		{code: "AX", name: "Aland Islands"},
//		{code: "AL", name: "Albania"},
//		{code: "DZ", name: "Algeria"},
//		{code: "AS", name: "American Samoa"},
//		{code: "AD", name: "Andorra"},
//		{code: "AO", name: "Angola"},
//		{code: "AI", name: "Anguilla"},
//		{code: "AQ", name: "Antarctica"},
//		{code: "AG", name: "Antigua and Barbuda"},
//		{code: "AR", name: "Argentina"},
//		{code: "AM", name: "Armenia"},
//		{code: "AW", name: "Aruba"},
		{code: "AU", name: "Australia"},
//		{code: "AT", name: "Austria"},
//		{code: "AZ", name: "Azerbaijan"},
//		{code: "BS", name: "Bahamas"},
//		{code: "BH", name: "Bahrain"},
//		{code: "BD", name: "Bangladesh"},
//		{code: "BB", name: "Barbados"},
//		{code: "BY", name: "Belarus"},
//		{code: "BE", name: "Belgium"},
//		{code: "BZ", name: "Belize"},
//		{code: "BJ", name: "Benin"},
//		{code: "BM", name: "Bermuda"},
//		{code: "BT", name: "Bhutan"},
//		{code: "BO", name: "Bolivia"},
//		{code: "BA", name: "Bosnia and Herzegovina"},
//		{code: "BW", name: "Botswana"},
//		{code: "BV", name: "Bouvet Island"},
//		{code: "BR", name: "Brazil"},
//		{code: "IO", name: "British Indian Ocean Territory"},
//		{code: "BN", name: "Brunei Darussalam"},
//		{code: "BG", name: "Bulgaria"},
//		{code: "BF", name: "Burkina Faso"},
//		{code: "BI", name: "Burundi"},
//		{code: "KH", name: "Cambodia"},
//		{code: "CM", name: "Cameroon"},
		{code: "CA", name: "Canada"},
//		{code: "CV", name: "Cape Verde"},
//		{code: "KY", name: "Cayman Islands"},
//		{code: "CF", name: "Central African Republic"},
//		{code: "TD", name: "Chad"},
//		{code: "CL", name: "Chile"},
//		{code: "CN", name: "China"},
//		{code: "CX", name: "Christmas Island"},
//		{code: "CC", name: "Cocos (Keeling) Islands"},
//		{code: "CO", name: "Colombia"},
//		{code: "KM", name: "Comoros"},
//		{code: "CG", name: "Congo"},
//		{code: "CD", name: "Congo, The Democratic Republic of the"},
//		{code: "CK", name: "Cook Islands"},
//		{code: "CR", name: "Costa Rica"},
//		{code: "CI", name: "Côte d'Ivoire"},
//		{code: "HR", name: "Croatia"},
//		{code: "CU", name: "Cuba"},
//		{code: "CY", name: "Cyprus"},
//		{code: "CZ", name: "Czech Republic"},
//		{code: "DK", name: "Denmark"},
//		{code: "DJ", name: "Djibouti"},
//		{code: "DM", name: "Dominica"},
//		{code: "DO", name: "Dominican Republic"},
//		{code: "EC", name: "Ecuador"},
//		{code: "EG", name: "Egypt"},
//		{code: "SV", name: "El Salvador"},
//		{code: "GQ", name: "Equatorial Guinea"},
//		{code: "ER", name: "Eritrea"},
//		{code: "EE", name: "Estonia"},
//		{code: "ET", name: "Ethiopia"},
//		{code: "FK", name: "Falkland Islands (Malvinas)"},
//		{code: "FO", name: "Faroe Islands"},
//		{code: "FJ", name: "Fiji"},
//		{code: "FI", name: "Finland"},
//		{code: "FR", name: "France"},
//		{code: "GF", name: "French Guiana"},
//		{code: "PF", name: "French Polynesia"},
//		{code: "TF", name: "French Southern Territories"},
//		{code: "GA", name: "Gabon"},
//		{code: "GM", name: "Gambia"},
//		{code: "GE", name: "Georgia"},
//		{code: "DE", name: "Germany"},
//		{code: "GH", name: "Ghana"},
//		{code: "GI", name: "Gibraltar"},
//		{code: "GR", name: "Greece"},
//		{code: "GL", name: "Greenland"},
//		{code: "GD", name: "Grenada"},
//		{code: "GP", name: "Guadeloupe"},
//		{code: "GU", name: "Guam"},
//		{code: "GT", name: "Guatemala"},
//		{code: "GG", name: "Guernsey"},
//		{code: "GN", name: "Guinea"},
//		{code: "GW", name: "Guinea-Bissau"},
//		{code: "GY", name: "Guyana"},
//		{code: "HT", name: "Haiti"},
//		{code: "HM", name: "Heard Island and McDonald Islands"},
//		{code: "VA", name: "Holy See (Vatican City State)"},
//		{code: "HN", name: "Honduras"},
//		{code: "HK", name: "Hong Kong"},
//		{code: "HU", name: "Hungary"},
//		{code: "IS", name: "Iceland"},
//		{code: "IN", name: "India"},
//		{code: "ID", name: "Indonesia"},
//		{code: "IR", name: "Iran, Islamic Republic of"},
//		{code: "IQ", name: "Iraq"},
		{code: "IE", name: "Ireland"},
//		{code: "IM", name: "Isle of Man"},
//		{code: "IL", name: "Israel"},
//		{code: "IT", name: "Italy"},
//		{code: "JM", name: "Jamaica"},
//		{code: "JP", name: "Japan"},
//		{code: "JE", name: "Jersey"},
//		{code: "JO", name: "Jordan"},
//		{code: "KZ", name: "Kazakhstan"},
//		{code: "KE", name: "Kenya"},
//		{code: "KI", name: "Kiribati"},
//		{code: "KP", name: "Korea, Democratic People's Republic of"},
//		{code: "KR", name: "Korea, Republic of"},
//		{code: "KW", name: "Kuwait"},
//		{code: "KG", name: "Kyrgyzstan"},
//		{code: "LA", name: "Lao People's Democratic Republic"},
//		{code: "LV", name: "Latvia"},
//		{code: "LB", name: "Lebanon"},
//		{code: "LS", name: "Lesotho"},
//		{code: "LR", name: "Liberia"},
//		{code: "LY", name: "Libyan Arab Jamahiriya"},
//		{code: "LI", name: "Liechtenstein"},
//		{code: "LT", name: "Lithuania"},
//		{code: "LU", name: "Luxembourg"},
//		{code: "MO", name: "Macao"},
//		{code: "MK", name: "Macedonia, The Former Yugoslav Republic of"},
//		{code: "MG", name: "Madagascar"},
//		{code: "MW", name: "Malawi"},
//		{code: "MY", name: "Malaysia"},
//		{code: "MV", name: "Maldives"},
//		{code: "ML", name: "Mali"},
//		{code: "MT", name: "Malta"},
//		{code: "MH", name: "Marshall Islands"},
//		{code: "MQ", name: "Martinique"},
//		{code: "MR", name: "Mauritania"},
//		{code: "MU", name: "Mauritius"},
//		{code: "YT", name: "Mayotte"},
//		{code: "MX", name: "Mexico"},
//		{code: "FM", name: "Micronesia, Federated States of"},
//		{code: "MD", name: "Moldova"},
//		{code: "MC", name: "Monaco"},
//		{code: "MN", name: "Mongolia"},
//		{code: "ME", name: "Montenegro"},
//		{code: "MS", name: "Montserrat"},
//		{code: "MA", name: "Morocco"},
//		{code: "MZ", name: "Mozambique"},
//		{code: "MM", name: "Myanmar"},
//		{code: "NA", name: "Namibia"},
//		{code: "NR", name: "Nauru"},
//		{code: "NP", name: "Nepal"},
		{code: "NL", name: "Netherlands"},
//		{code: "AN", name: "Netherlands Antilles"},
//		{code: "NC", name: "New Caledonia"},
		{code: "NZ", name: "New Zealand"},
//		{code: "NI", name: "Nicaragua"},
//		{code: "NE", name: "Niger"},
//		{code: "NG", name: "Nigeria"},
//		{code: "NU", name: "Niue"},
//		{code: "NF", name: "Norfolk Island"},
//		{code: "MP", name: "Northern Mariana Islands"},
		{code: "NO", name: "Norway"},
//		{code: "OM", name: "Oman"},
//		{code: "PK", name: "Pakistan"},
//		{code: "PW", name: "Palau"},
//		{code: "PS", name: "Palestinian Territory, Occupied"},
//		{code: "PA", name: "Panama"},
//		{code: "PG", name: "Papua New Guinea"},
//		{code: "PY", name: "Paraguay"},
//		{code: "PE", name: "Peru"},
//		{code: "PH", name: "Philippines"},
//		{code: "PN", name: "Pitcairn"},
//		{code: "PL", name: "Poland"},
//		{code: "PT", name: "Portugal"},
//		{code: "PR", name: "Puerto Rico"},
//		{code: "QA", name: "Qatar"},
//		{code: "RE", name: "Réunion"},
//		{code: "RO", name: "Romania"},
//		{code: "RU", name: "Russian Federation"},
//		{code: "RW", name: "Rwanda"},
//		{code: "BL", name: "Saint Barthélemy"},
//		{code: "SH", name: "Saint Helena"},
//		{code: "KN", name: "Saint Kitts and Nevis"},
//		{code: "LC", name: "Saint Lucia"},
//		{code: "MF", name: "Saint Martin"},
//		{code: "PM", name: "Saint Pierre and Miquelon"},
//		{code: "VC", name: "Saint Vincent and the Grenadines"},
//		{code: "WS", name: "Samoa"},
//		{code: "SM", name: "San Marino"},
//		{code: "ST", name: "Sao Tome and Principe"},
//		{code: "SA", name: "Saudi Arabia"},
//		{code: "SN", name: "Senegal"},
//		{code: "RS", name: "Serbia"},
//		{code: "SC", name: "Seychelles"},
//		{code: "SL", name: "Sierra Leone"},
//		{code: "SG", name: "Singapore"},
//		{code: "SK", name: "Slovakia"},
//		{code: "SI", name: "Slovenia"},
//		{code: "SB", name: "Solomon Islands"},
//		{code: "SO", name: "Somalia"},
//		{code: "ZA", name: "South Africa"},
//		{code: "GS", name: "South Georgia and the South Sandwich Islands"},
//		{code: "ES", name: "Spain"},
//		{code: "LK", name: "Sri Lanka"},
//		{code: "SD", name: "Sudan"},
//		{code: "SR", name: "Suriname"},
//		{code: "SJ", name: "Svalbard and Jan Mayen"},
//		{code: "SZ", name: "Swaziland"},
		{code: "SE", name: "Sweden"},
		{code: "CH", name: "Switzerland"},
//		{code: "SY", name: "Syrian Arab Republic"},
//		{code: "TW", name: "Taiwan, Province of China"},
//		{code: "TJ", name: "Tajikistan"},
//		{code: "TZ", name: "Tanzania, United Republic of"},
//		{code: "TH", name: "Thailand"},
//		{code: "TL", name: "Timor-Leste"},
//		{code: "TG", name: "Togo"},
//		{code: "TK", name: "Tokelau"},
//		{code: "TO", name: "Tonga"},
//		{code: "TT", name: "Trinidad and Tobago"},
//		{code: "TN", name: "Tunisia"},
//		{code: "TR", name: "Turkey"},
//		{code: "TM", name: "Turkmenistan"},
//		{code: "TC", name: "Turks and Caicos Islands"},
//		{code: "TV", name: "Tuvalu"},
//		{code: "UG", name: "Uganda"},
//		{code: "UA", name: "Ukraine"},
//		{code: "AE", name: "United Arab Emirates"},
//		{code: "GB", name: "United Kingdom"},
		{code: "US", name: "United States"},
//		{code: "UM", name: "United States Minor Outlying Islands"},
//		{code: "UY", name: "Uruguay"},
//		{code: "UZ", name: "Uzbekistan"},
//		{code: "VU", name: "Vanuatu"},
//		{code: "VE", name: "Venezuela"},
	//	{code: "VN", name: "Viet Nam"},
//		{code: "VG", name: "Virgin Islands, British"},
//		{code: "VI", name: "Virgin Islands, U.S."},
//		{code: "WF", name: "Wallis and Futuna"},
//		{code: "EH", name: "Western Sahara"},
//		{code: "YE", name: "Yemen"},
//		{code: "ZM", name: "Zambia"},
//		{code: "ZW", name: "Zimbabwe"}
		];
		</script>
