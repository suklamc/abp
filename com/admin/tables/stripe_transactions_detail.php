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
class Tablestripe_transactions_detail extends JTable
{
	protected $_supportNullValue = true; 
	
	var $id_stripe_transactions = null;
	var $stripe_txn_id = null;
	var $request_id = null;
	var $cart = null;
	var $status = null;
	var $amount = null;
	var $currency = null;
	var $description = null;
	var $seller_message = null;
	var $card_brand = null;
	var $card_country = null;
	var $card_last4 = null;
	var $card_exp_month = null;
	var $card_exp_year = null;
	var $stamp = null;	var $checked_out = null;
	var $checked_out_time = '0000-00-00 00:00:00';
	var $ordering = 1;
	var $published = 1;
	var $_table_prefix = null;


	function __construct($db) {
	
	  $this->_table_prefix = '#__sv_apptpro3_';
	  
		parent::__construct($this->_table_prefix.'stripe_transactions', 'id_stripe_transactions', $db);
	}

	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}


}
?>

