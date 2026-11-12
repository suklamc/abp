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
class Tableauthnet_transactions_detail extends JTable
{
	protected $_supportNullValue = true; 
	
	var $id_authnet_transactions = null;
	var $x_response_code = null;
	var $x_response_subcode = null;
	var $x_response_reason_code = null;
	var $x_response_reason_text = null;
	var $x_auth_code = null;
	var $x_avs_code = null;
	var $x_trans_id = null;
	var $x_invoice_num = null;
	var $x_description = null;
	var $x_amount = null;
	var $x_method = null;
	var $x_type = null;
	var $x_cust_id = null; 
	var $x_first_name = null;
	var $x_last_name = null;
	var $x_company = null;
	var $x_address = null;
	var $x_city = null;
	var $x_state = null;
	var $x_zip = null;
	var $x_country = null;
	var $x_phone = null;
	var $x_fax = null;
	var $x_email = null;
	var $x_tax = null;
	var $x_duty = null;
	var $x_freight = null;
	var $x_tax_exempt = null;
	var $x_po_num = null;
	var $x_MD5_Hash = null;
	var $x_cavv_response = null;
	var $x_test_request = null;
	var $x_subscription_id = null;
	var $x_subscription_paynum = null;
	var $x_cim_profile_id = null;
	var $datecreation = null;
	var $stamp = null;	var $checked_out = null;
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
	
	  $this->_table_prefix = '#__sv_apptpro3_';
	  
		parent::__construct($this->_table_prefix.'authnet_transactions', 'id_authnet_transactions', $db);
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
//		$query = 'SELECT id FROM '.$this->_table_prefix.'authnet_transactions  WHERE name = "'.$this->name;
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

