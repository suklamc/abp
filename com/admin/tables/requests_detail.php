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
* requests Table class
*/
class Tablerequests_detail extends JTable
{
	protected $_supportNullValue = true; 
	
	var $id_requests = null;
	var $user_id = null;
	var $operator_id = null;
	var $name = null;
	var $unit_number = null;
	var $phone = null;
	var $email = null;
	var $resource = null;
	var $category = null;
	var $starttime = null;
	var $startdate = null;
	var $enddate = null;
	var $endtime = null;
	var $comment = null;
	var $admin_comment = null;
	var $request_status = null;
	var $payment_status = null;
	var $show_on_calendar = null;
	var $calendar_category = null;
	var $calendar_calendar = null;
	var $calendar_comment = null;
	var $created = null;
	var $cancellation_id = null;
	var $service = null;
	var $txnid = null;
	var $sms_reminders = null;
	var $sms_phone = null;
	var $sms_dial_code = null;
	var $google_event_id = '';
	var $google_calendar_id = '';
	var $booking_total = 0.00;
	var $booking_deposit = 0.00;
  	var $booking_due = 0.00;
	var $coupon_code = null;
	var $booked_seats = 0;
	var $booking_language = 'en-GB';
	var $credit_used = "0.00";
	var $manual_payment_collected = "0.00";
	var $invoice_number = "";
	var $gift_cert = "";
	var $cancel_reason = "";	var $checked_out = null;
	var $checked_out_time = '0000-00-00 00:00:00';
	var $ordering = 1;
	var $published = 0;
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
	  
		parent::__construct($this->_table_prefix.'requests', 'id_requests', $db);
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
//		$query = 'SELECT id FROM '.$this->_table_prefix.'requests  WHERE name = "'.$this->name;
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

