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


class config_detailModelconfig_detail extends JModelLegacy
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
	 * Method to initialise the config data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$detail = new stdClass();
			$detail->id_config	= 0;
			$detail->mailTO = null;
			$detail->mailFROM = null;
			$detail->mailSubject = null;
			$detail->requireLogin = null;
			$detail->headerText = null;
			$detail->footerText = null;
			$detail->which_calendar = null;
			$detail->calendar_title = null;
			$detail->calendar_body = null;
			$detail->calendar_body2 = null;
			$detail->prevent_dupe_bookings = null;
			$detail->requirePhone = null;
			$detail->requireEmail = null;
			$detail->multiDay = null;
			$detail->allowSunday = null;
			$detail->allowMonday = null;
			$detail->allowTuesday = null;
			$detail->allowWednesday = null;
			$detail->allowThursday = null;
			$detail->allowFriday = null;
			$detail->allowSaturday = null;
			$detail->hoursLimit = null;
			$detail->timeRangeStart = null;
			$detail->timeRangeEnd = null;
			$detail->timeIncrement = null;
			$detail->bookingIncrement = null;
			$detail->timeFormat = null;
			$detail->accept_when_paid = null;
			$detail->enable_paypal = null;
			$detail->additional_fee = null;
			$detail->fee_rate = null;
			$detail->paypal_button_url = null;
			$detail->paypal_logo_url = null;
			$detail->paypal_currency_code = null;
			$detail->paypal_account = null;
			$detail->paypal_sandbox_url = null;
			$detail->paypal_use_sandbox = null;
			$detail->paypal_production_url = null; 
			$detail->paypal_identity_token = null;
			$detail->html_email = null;
			$detail->timeSlotMode = null;
			$detail->booking_succeeded = null;
			$detail->booking_succeeded_admin = null;
			$detail->booking_succeeded_sms = null;
			$detail->booking_in_progress = null;
			$detail->booking_in_progress_admin = null;
			$detail->booking_in_progress_sms = null;
			$detail->booking_cancel = null;
			$detail->booking_cancel_sms = null;
			$detail->booking_too_close_to_cancel = null;
			$detail->booking_reminder = null;
			$detail->booking_reminder_sms = null;
			$detail->auto_accept = null;
			$detail->hide_logo = null;
			$detail->allow_cancellation = null;
			$detail->hours_before_cancel = null;
			$detail->use_div_calendar = null;
			$detail->def_gad_grid_start =  null;
			$detail->def_gad_grid_end =  null;
			$detail->gad_grid_start_day = null;
			$detail->gad_grid_width = null;
			$detail->gad_name_width = null;
			$detail->gad_grid_num_of_days = null;
			$detail->gad_booked_image = null;
			$detail->gad_available_image = null;
			$detail->enable_clickatell = null;
			$detail->clickatell_user = null;
			$detail->clickatell_password = null;
			$detail->clickatell_api_id = null;
			$detail->clickatell_dialing_code = null;
			$detail->clickatell_what_to_send = null;
			$detail->clickatell_show_code = null;
			$detail->clickatell_enable_unicode = null;
			$detail->gad_grid_start_day_days = null;
			$detail->google_user = null;
			$detail->google_password = null;
			$detail->google_default_calendar_name = null;
			$detail->name_cb_mapping = null;
			$detail->name_read_only = null;
			$detail->phone_profile_mapping = null;
			$detail->phone_cb_mapping = null;
			$detail->phone_read_only = null;
			$detail->email_cb_mapping = null;
			$detail->email_read_only = null;
			$detail->daylight_savings_time = null;
			$detail->popup_week_start_day = null;
			$detail->enable_coupons = null;
			$detail->show_available_seats = null;
			$detail->gad_grid_hide_startend = null;
			$detail->limit_bookings = null;
			$detail->limit_bookings_days = null;
			$detail->activity_logging = null;
			$detail->phone_js_mapping = null;
			$detail->paypal_itemname = null;
			$detail->paypal_on0 = null;
			$detail->paypal_os0 = null;
			$detail->paypal_on1 = null;
			$detail->paypal_os1 = null;
			$detail->paypal_on2 = null;
			$detail->paypal_os2 = null;
			$detail->paypal_on3 = null;
			$detail->paypal_os3 = null;
			$detail->allow_user_credit_refunds = null;
			$detail->use_gad2 = null;
			$detail->gad2_row_height = null;
			$detail->dst_start_date = null;
			$detail->dst_end_date = null;
			$detail->adv_admin_show_resources = null;
			$detail->adv_admin_show_services = null;
			$detail->adv_admin_show_timeslots = null;
			$detail->adv_admin_show_bookoffs = null;
			$detail->adv_admin_show_paypal = null;
			$detail->adv_admin_show_coupons = null;
			$detail->adv_admin_show_extras = null;
			$detail->adv_admin_show_user_credits = null;
			$detail->adv_admin_show_rate_adj = null;
			$detail->attach_ics_resource = null;
			$detail->attach_ics_admin = null;
			$detail->attach_ics_customer = null;

			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->ordering = 0;
			$detail->published = 1;
			$detail->_table_prefix = null;
			$this->_data	= $detail;
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
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/config_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the config table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if (!$row->id_config) {
			$where = 'id_config = ' . $row->id_config ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		//DEVNOTE: Make sure the config table is valid
		//JTable return always true but there is space to put
		//our custom check method
/*		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}*/

		$row->clickatell_password = encrypt_decrypt('encrypt', $row->clickatell_password);
		$row->eztexting_password = encrypt_decrypt('encrypt', $row->eztexting_password);

		// Store the config table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}
	
	

}

?>
