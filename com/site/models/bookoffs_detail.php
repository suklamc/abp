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


class admin_detailModelbookoffs_detail extends JModelLegacy
{
	var $_id_bookoffs = null;
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
	 * Method to set the bookoffs identifier
	 *
	 * @access	public
	 * @param	int bookoffs identifier
	 */
	function setId($id_bookoffs)
	{
		// Set bookoffs id and wipe data
		$this->_id_bookoffs		= $id_bookoffs;
		$this->_data	= null;
	}

	/**
	 * Method to get a bookoffs
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the bookoffs data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	/**
	 * Method to checkout/lock the bookoffs
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_bookoffs)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$bookoffs = $this->getTable();
			
			
			if(!$bookoffs->checkout($uid, $this->_id_bookoffs)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the bookoffs
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_bookoffs)
		{
			$bookoffs = $this->getTable();
			if(! $bookoffs->checkin($this->_id_bookoffs)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if bookoffs is checked out
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
		$query = "SELECT #__users.name FROM #__users JOIN #__sv_apptpro3_bookoffs ON #__sv_apptpro3_bookoffs.checked_out = #__users.id ".
		" WHERE #__sv_apptpro3_bookoffs.id_bookoffs = ". $this->getDbo()->escape($this->_id_bookoffs);			
		$this->_db->setQuery($query);
		$locked_by = $this->_db->loadResult();
		return $locked_by;
	}	
		
		
	/**
	 * Method to load content bookoffs data
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
			$query = 'SELECT * FROM '.$this->_table_prefix.'bookoffs WHERE id_bookoffs = '. $this->getDbo()->escape($this->_id_bookoffs);
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the bookoffs data
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
			$detail->id_bookoffs = null;
			$detail->resource_id = null;			
			$detail->description = null;
			$detail->off_date = null;
			$detail->full_day = "Yes";
			$detail->bookoff_starttime = null;
			$detail->bookoff_endtime = null;
			$detail->rolling_bookoff = "No";
			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->ordering = 1;
			$detail->published = 0;
			$this->_data	= $detail;
			return (boolean) $this->_data;
		}
		return true;
	}
  	

	/**
	 * Method to store the bookoffs text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/bookoffs_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the bookoffs table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// check for bookings, no book-off over 'Accepted' bookings
		if($row->full_day == "Yes"){
			// just check by date
			$mystartdatetime = "STR_TO_DATE('".$this->getDbo()->escape($row->off_date) ." 00:00:00', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$this->getDbo()->escape($row->off_date) ." 23:59:00', '%Y-%m-%d %T')- INTERVAL 1 SECOND";				
		} else {
			$mystartdatetime = "STR_TO_DATE('".$this->getDbo()->escape($row->startdate) ." ". $this->getDbo()->escape($row->bookoff_starttime) ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$this->getDbo()->escape($row->enddate) ." ". $this->getDbo()->escape($row->bookoff_endtime) ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		}
		$sql = "select count(*) from #__sv_apptpro3_requests "
		." where (resource = '". $this->getDbo()->escape($row->resource_id) ."')"
		." and (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")"
		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
		//print $sql; exit();
		$this->_db->setQuery( $sql );
		$overlapcount = $this->_db->loadResult();
		if ($overlapcount >0){
			$this->setError(JText::_('RS1_BOOKOFF_CONFLICT'));
			return false;
		}


		// Store the bookoffs table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}
	
		/**
	 * Method to (un)publish a bookoffs
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

			$query = 'UPDATE '.$this->_table_prefix.'bookoffs'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id_bookoffs IN ( '.$this->getDbo()->escape($cids).' )'
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
	 * Method to move a bookoffs_detail
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order)
	{
		//DEVNOTE: Load table class from com_sv_ser/tables/bookoffs_detail.php		
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
	 * Method to move a bookoffs 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
	//DEVNOTE: Load table class from com_sv_ser/tables/bookoffs_detail.php	
		$row = $this->getTable();
	//DEVNOTE: we need to pass here id of bookoffs detail 		
		if (!$row->load($this->_id_bookoffs)) {
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
			$query = 'DELETE FROM '.$this->_table_prefix.'bookoffs WHERE id_bookoffs IN ( '.$this->getDbo()->escape($cids).' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

		return true;
	}
	

}

?>
