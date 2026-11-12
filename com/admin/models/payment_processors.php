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

//DEVNOTE: import MODEL object class
jimport('joomla.application.component.model');


class payment_processorsModelpayment_processors extends JModelLegacy
{
		var $_id_config = null;
		var $_data = null;
		var $_table_prefix = null;

	function __construct()
	{
		parent::__construct();
		
		//initialize class property
	  	$this->_table_prefix = '#__sv_apptpro3_';			

		$jinput = JFactory::getApplication()->input;
		$array = $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$this->setId((int)$array[0]);

	}

	/**
	 * Method to set the config identifier
	 *
	 * @access	public
	 * @param	int config identifier
	 */
	function setId($id_config)
	{
		// Set config id and wipe data
		$this->_id_config		= $id_config;
		$this->_data	= null;
	}

	/**
	 * Method to get a config
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the config data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	/**
	 * Method to checkout/lock the config
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_config)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$config = $this->getTable();
			
			
			if(!$config->checkout($uid, $this->_id_config)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the config
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_config)
		{
			$config = $this->getTable();
			if(! $config->checkin($this->_id_config)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if config is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}	
		
	/**
	 * Method to load content config data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM '.$this->_table_prefix.'config WHERE id_config = 1';
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}


	/**
	 * Method to store the config text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
//		$query = "UPDATE ".$this->_table_prefix."config ".
//		"SET ".
//		"additional_fee=".$data[additional_fee].", ".
//		"fee_rate=".$data[fee_rate].", ".
//		"enable_coupons=".$data[enable_coupons].", ".
//		"enable_paypal=".$data[enable_paypal].", ".
//		"accept_when_paid=".$data[accept_when_paid].", ".
//		"paypal_button_url=".$data[paypal_button_url].", ".
//		"paypal_currency_code=".$data[paypal_currency_code].", ".
//		"paypal_account=".$data[paypal_account].", ".
//		"paypal_sandbox_url=".$data[paypal_sandbox_url].", ".
//		"paypal_production_url=".$data[paypal_production_url].", ".
//		"paypal_use_sandbox=".$data[paypal_use_sandbox].", ".
//		"paypal_logo_url=".$data[paypal_logo_url].", ".
//		"paypal_itemname=".$data[paypal_itemname].", ".
//		"paypal_on0=".$data[paypal_on0].", ".
//		"paypal_os0=".$data[paypal_os0].", ".
//		"paypal_on1=".$data[paypal_on1].", ".
//		"paypal_os1=".$data[paypal_os1].", ".
//		"paypal_on2=".$data[paypal_on2].", ".
//		"paypal_os2=".$data[paypal_os2].", ".
//		"paypal_on3=".$data[paypal_on3].", ".
//		"paypal_os3=".$data[paypal_os3].", ".
//		"purge_stale_paypal=".$data[purge_stale_paypal].", ".
//		"minutes_to_stale=".$data[minutes_to_stale].", ".
//		"authnet_enable=".$data[authnet_enable].", ".
//		"authnet_api_login_id=".$data[authnet_api_login_id].", ".
//		"authnet_transaction_key=".$data[authnet_transaction_key].", ".
//		"authnet_button_url=".$data[authnet_button_url].", ".
//		"authnet_header_text=".$data[authnet_header_text].", ".
//		"authnet_footer_text=".$data[authnet_footer_text]." ".		
//		" WHERE id_config = 1";
//		$this->_db->setQuery($query);
//		if (!$this->_db->execute()) {
//			$this->setError($row->getError());
//			return false;
//		}
//
//		return true;
	}
	

}

?>
