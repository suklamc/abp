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
* resourcee Table class
*/
class Tableresources_detail extends JTable
{
	var $id_resources = null;
	var $category_id = null;
	var $category_scope = null;
	var $mail_id = null;
	var $name = null;
	var $description = null;
	var $cost = null;
	var $resource_email = null;
	var $prevent_dupe_bookings = null;
	var $max_dupes = null;
	var $resource_admins = null;
	var $rate = null;
	var $rate_unit = null;
	var $default_calendar_category = null;
	var $default_calendar = null;
	var $allowSunday = null;
	var $allowMonday = null;
	var $allowTuesday = null;
	var $allowWednesday = null;
	var $allowThursday = null;
	var $allowFriday = null;
	var $allowSaturday = null;
	var $timeslots = null;
	var $disable_dates_before = null;
	var $disable_dates_before_days = null;
	var $min_lead_time = null;
	var $disable_dates_after = null;
	var $disable_dates_after_days = null;
	var $sms_phone = null;
	var $google_user = null;
	var $google_password = null;
	var $google_default_calendar_name = null;
	var $access = null;
	var $enable_coupons = null;
	var $max_seats = null;
	var $non_work_day_message = null;
	var $non_work_day_hide = null;
	var $paypal_account = null;
	var $auto_accept = null;
	var $deposit_only = null;
	var $deposit_amount = null;
	var $deposit_unit = null;	
	var $resource_eb_discount = 0.00;
	var $resource_eb_discount_unit = "Flat";
	var $resource_eb_discount_lead = 7;
	var $gap = 0;
	var $mailchimp_list_id = null;
	var $acymailing_list_id = null;
	var $google_client_id = null;
	var $google_app_name = null;
	var $google_app_email_address = null;
	var $google_p12_key_filename = null;
	var $ddslick_image_path = null;
	var $ddslick_image_text = null;
	var $show_image_in_grid = null;
	var $free_booking = null;
	var $date_specific_booking = null;
	var $res_user_drag_duration_enable = null;
	var $res_user_drag_duration_snap = null;	
	var $res_stripe_sk = null;
	var $res_stripe_pk = null;
	var $checked_out = null;
	var $checked_out_time = null;
	var $ordering = null;
	var $published = null;
	var $_table_prefix = null;

	protected $_supportNullValue = true; 

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct($db) {
	
	  $this->_table_prefix = '#__sv_apptpro3_';
	  
		parent::__construct($this->_table_prefix.'resources', 'id_resources', $db);
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
//		$query = 'SELECT id FROM '.$this->_table_prefix.'resources  WHERE name = "'.$this->name;
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

