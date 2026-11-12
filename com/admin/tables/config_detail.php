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
defined('_JEXEC') or die('Restricted access');

/**
* config Table class
*/
class Tableconfig_detail extends JTable
{
	protected $_supportNullValue = true; 
	
	var $id_config = null;
	var $mailTO = null;
	var $mailFROM = null;
	var $mailSubject = null;
	var $requireLogin = null;
	var $headerText = null;
	var $footerText = null;
	var $which_calendar = null;
	var $calendar_title = null;
	var $calendar_body = null;
	var $calendar_body2 = null;
	var $prevent_dupe_bookings = null;
	var $requirePhone = null;
	var $requireEmail = null;
	var $multiDay = null;
	var $allowSunday = null;
	var $allowMonday = null;
	var $allowTuesday = null;
	var $allowWednesday = null;
	var $allowThursday = null;
	var $allowFriday = null;
	var $allowSaturday = null;
	var $hoursLimit = null;
	var $timeRangeStart = null;
	var $timeRangeEnd = null;
	var $timeIncrement = null;
	var $bookingIncrement = null;
	var $timeFormat = null;
	var $accept_when_paid = null;
	var $enable_paypal = null;
	var $additional_fee = null;
	var $fee_rate = null;
	var $paypal_button_url = null;
	var $paypal_logo_url = null;
	var $paypal_currency_code = null;
	var $paypal_account = null;
	var $paypal_sandbox_url = null;
	var $paypal_use_sandbox = null;
	var $paypal_production_url = null; 
	var $paypal_identity_token = null;
	var $html_email = null;
	var $timeSlotMode = null;
	var $booking_succeeded = null;
	var $booking_succeeded_admin = null;
	var $booking_succeeded_sms = null;
	var $booking_in_progress = null;
	var $booking_in_progress_admin = null;
	var $booking_in_progress_sms = null;
	var $booking_cancel = null;
	var $booking_cancel_sms = null;
	var $booking_too_close_to_cancel = null;
	var $booking_reminder = null;
	var $booking_reminder_sms = null;
	var $auto_accept = null;
	var $hide_logo = null;
	var $allow_cancellation = null;
	var $hours_before_cancel = null;
	var $use_div_calendar = null;
	var $def_gad_grid_start =  null;
	var $def_gad_grid_end =  null;
	var $gad_grid_start_day = null;
	var $gad_grid_width = null;
	var $gad_name_width = null;
	var $gad_grid_num_of_days = null;
	var $gad_booked_image = null;
	var $gad_available_image = null;
	var $gad_date_format = null;
	var $gad_who_booked = null;
	var $enable_clickatell = null;
	var $clickatell_user = null;
	var $clickatell_password = null;
	var $clickatell_api_id = null;
	var $clickatell_sender_id = null;
	var $clickatell_dialing_code = null;
	var $clickatell_what_to_send = null;
	var $clickatell_show_code = null;
	var $clickatell_enable_unicode = null;
	var $gad_grid_start_day_days = null;
	var $google_user = null;
	var $google_password = null;
	var $google_default_calendar_name = null;
	var $name_cb_mapping = null;
	var $name_read_only = null;
	var $phone_profile_mapping = null;
	var $phone_cb_mapping = null;
	var $phone_read_only = null;
	var $email_cb_mapping = null;
	var $email_read_only = null;
	var $daylight_savings_time = null;
	var $popup_week_start_day = null;
	var $enable_coupons = null;
	var $show_available_seats = null;
	var $gad_grid_hide_startend = null;
	var $limit_bookings = null;
	var $limit_bookings_days = null;
	var $activity_logging = null;
	var $phone_js_mapping = null;
	var $paypal_itemname = null;
	var $paypal_on0 = null;
	var $paypal_os0 = null;
	var $paypal_on1 = null;
	var $paypal_os1 = null;
	var $paypal_on2 = null;
	var $paypal_os2 = null;
	var $paypal_on3 = null;
	var $paypal_os3 = null;
	var $allow_user_credit_refunds = null;
	var $use_gad2 = null;
	var $gad2_row_height = null;
	var $dst_start_date = null;
	var $dst_end_date = null;
	var $adv_admin_show_resources = null;
	var $adv_admin_show_services = null;
	var $adv_admin_show_timeslots = null;
	var $adv_admin_show_bookoffs = null;
	var $adv_admin_show_paypal = null;
	var $adv_admin_show_authnet = null;
	var $adv_admin_show_2co = null;
	var $adv_admin_show_coupons = null;
	var $adv_admin_show_extras = null;
	var $adv_admin_show_user_credits = null;
	var $adv_admin_show_rate_adj = null;
	var $attach_ics_resource = null;
	var $attach_ics_admin = null;
	var $attach_ics_customer = null;
	var $purge_stale_paypal = null;
	var $minutes_to_stale = null;
	var $enable_eztexting = null;
	var $eztexting_user = null;
	var $eztexting_password = null;
	var $authnet_enable = null;
	var $authnet_api_login_id = null;
	var $authnet_transaction_key = null;
	var $authnet_header_text = null;
	var $authnet_footer_text = null;
	var $authnet_button_url = null;
	var $non_pay_booking_button = null;
	var $cal_position_method = null;
	var $site_access_code = null;
	var $checked_out = null;
	var $checked_out_time = '0000-00-00 00:00:00';
	var $ordering = 0;
	var $published = 1;
	var $_table_prefix = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct($db) {
	
	  //initialize class property
	  $this->_table_prefix = '#__sv_apptpro3_';
	  
		parent::__construct($this->_table_prefix.'config', 'id_config', $db);
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/

	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
//	function check()
//	{
//		/** check for valid name */
//		if (trim($this->name) == '') {
//			$this->_error = JText::_('Your resource MUST CONTAIN an Name.');
//			return false;
//		}
//
//
//		/** check for existing name */
//		$query = 'SELECT id FROM '.$this->_table_prefix.'config  WHERE name = "'.$this->name;
//		$this->_db->setQuery($query);
//
//		$xid = intval($this->_db->loadResult());
//		if ($xid && $xid != intval($this->id)) {
//			$this->_error = JText::sprintf('WARNNAMETRYAGAIN', JText::_('resource'));
//			return false;
//		}
//		return true;
//	}
}
?>

