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


class admin_detailModelresources_detail extends JModelLegacy
{
	var $_id_resources = null;
	var $_data = null;
	var $_table_prefix = null;

	function __construct()
	{
		parent::__construct();
		
		//initialize class property
	  	$this->_table_prefix = '#__sv_apptpro3_';			
		$jinput = JFactory::getApplication()->input;
		$cid = $jinput->get('cid');

		$this->setId((int)$cid);

	}

	/**
	 * Method to set the resources identifier
	 *
	 * @access	public
	 * @param	int resources identifier
	 */
	function setId($id_resources)
	{
		// Set resources id and wipe data
		$this->_id_resources		= $id_resources;
		$this->_data	= null;
	}

	/**
	 * Method to get a resources
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the resources data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	/**
	 * Method to checkout/lock the resources
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_resources)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$resources = $this->getTable();
			
			
			if(!$resources->checkout($uid, $this->_id_resources)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the resources
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_resources)
		{
			$resources = $this->getTable();
			if(! $resources->checkin($this->_id_resources)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if resources is checked out
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


	function checkedOutBy()
	{
		$query = "SELECT #__users.name FROM #__users JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_resources.checked_out = #__users.id ".
		" WHERE #__sv_apptpro3_resources.id_resources = ". $this->getDbo()->escape($this->_id_resources);			
		$this->_db->setQuery($query);
		$locked_by = $this->_db->loadResult();
		return $locked_by;
	}	
		
	/**
	 * Method to load content resources data
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
			$query = 'SELECT * FROM '.$this->_table_prefix.'resources WHERE id_resources = '. $this->getDbo()->escape($this->_id_resources);
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the resources data
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
			$detail->id_resources	= 0;
			$detail->category_id = 0;
			$detail->category_scope = "";
			$detail->mail_id = 1;
			$detail->name = null;
			$detail->description = null;
			$detail->cost = null;
			$detail->resource_email = null;
			$detail->prevent_dupe_bookings = "Global";
			$detail->max_dupes = 0;
			$detail->resource_admins = null;
			$detail->rate = null;
			$detail->rate_unit = "Hour";
			$detail->allowSunday = "Yes";
			$detail->allowMonday = "Yes";
			$detail->allowTuesday = "Yes";
			$detail->allowWednesday = "Yes";
			$detail->allowThursday = "Yes";
			$detail->allowFriday = "Yes";
			$detail->allowSaturday = "Yes";
			$detail->timeslots = "Global"; // Global or Specific
			$detail->disable_dates_before = "Tomorrow";
			$detail->disable_dates_before_days = 0;
			$detail->min_lead_time = 1;
			$detail->disable_dates_after = "Not Set";
			$detail->disable_dates_after_days = 0;
			$detail->default_calendar_category = 'General';
			$detail->default_calendar = 'Default';
			$detail->sms_phone = null;
			$detail->google_user = null;
			$detail->google_password = null;
			$detail->google_default_calendar_name = null;
			$detail->access = '|1|'; // Public replaces 'everyone'
			$detail->enable_coupons = "No";
			$detail->max_seats = 1;
			$detail->non_work_day_message = "";
			$detail->non_work_day_hide = "Yes";
			$detail->paypal_account = "";
			$detail->auto_accept = "Global";
			$detail->deposit_only = "No";
			$detail->deposit_amount = 0.00;
			$detail->deposit_unit = "Flat";	
			$detail->resource_eb_discount = 0.00;
			$detail->resource_eb_discount_unit = "Flat";
			$detail->resource_eb_discount_lead = 7;
			$detail->gap = 0;
			$detail->mailchimp_list_id = "";
			$detail->acymailing_list_id = "";
			$detail->google_client_id  = "";
			$detail->google_app_name = "";
			$detail->google_app_email_address = "";
			$detail->google_p12_key_filename = "";
			$detail->ddslick_image_path = "";
			$detail->ddslick_image_text = "";
			$detail->show_image_in_grid = "No";
			$detail->free_booking = "No";
			$detail->date_specific_booking = "No";
			$detail->res_user_drag_duration_enable = "No";
			$detail->res_user_drag_duration_snap = 30;	
			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->ordering = 1;
			$detail->published = null;
			
			$this->_data	= $detail;
			return (boolean) $this->_data;
		}
		return true;
	}
  	

	/**
	 * Method to store the resources text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/resources_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the resources table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if (!$row->id_resources) {
			$where = 'id_resources = ' . $this->getDbo()->escape($row->id_resources) ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		// Store the resources table to the database
		
		if (!$row->store()) {
			$this->setError($row->getError());
//			print_r($row);
//			exit;
			return false;
		}

		return true;
	}
	
		/**
	 * Method to (un)publish a resources
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user = JFactory::getUser();

		if (sv_count_($cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE '.$this->_table_prefix.'resources'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id_resources IN ( '.$this->getDbo()->escape($cids).' )'
				. ' AND ( checked_out = 0 OR ISNULL(checked_out) OR ( checked_out = ' .$user->get('id'). ' ) )'
			;

			$this->_db->setQuery( $query );
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

		return true;
	}
	
	/**
	 * Method to move a resources_detail
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order)
	{
		//DEVNOTE: Load table class from com_sv_ser/tables/resources_detail.php		
		$row = $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < sv_count_($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}
			}
		}
		return true;
	}
		
		/**
	 * Method to move a resources 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
	//DEVNOTE: Load table class from com_sv_ser/tables/resources_detail.php	
		$row = $this->getTable();
	//DEVNOTE: we need to pass here id of resources detail 		
		if (!$row->load($this->_id_resources)) {
			$this->setError($row->getError());
		
			return false;
		}
  
	//DEVNOTE: call move method of JTABLE. 
  //first parameter: direction [up/down]
  //second parameter: condition
		if (!$row->move( $direction, ' published >= 0 ' )) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}		

	function delete($cid = array())
	{
		$result = false;


		if (sv_count_($cid ))
		{

			$cids = implode( ',', $cid );
				
			// do not delet resource if tied to active booking
			$sql = "select count(*) from #__sv_apptpro3_requests ".
			" WHERE (request_status = 'accepted' or request_status = 'pending') AND resource IN (".$this->getDbo()->escape($cids).")"; 
			try{
				$this->_db->setQuery( $sql );
				$in_use_count = $this->_db->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "models/resources_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			if ($in_use_count > 0){
				$this->setError('One or more resources currently used in accepted booking. Cannot delete.');				
				return false;
			}
			
			$query = 'DELETE FROM '.$this->_table_prefix.'resources WHERE id_resources IN ( '.$this->getDbo()->escape($cids).' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}

			// remove timeslots, book-offs and services
			$this->_db->setQuery("DELETE FROM #__sv_apptpro3_timeslots WHERE resource_id IN (".$this->getDbo()->escape($cids).")");
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
			$this->_db->setQuery("DELETE FROM #__sv_apptpro3_bookoffs WHERE resource_id IN (".$this->getDbo()->escape($cids).")");
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
			$this->_db->setQuery("DELETE FROM #__sv_apptpro3_services WHERE resource_id IN (".$this->getDbo()->escape($cids).")");
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

	return true;
	
	}
	

}

?>
