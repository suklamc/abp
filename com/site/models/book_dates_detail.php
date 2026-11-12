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


class admin_detailModelbook_dates_detail extends JModelLegacy
{
	var $_id_book_dates = null;
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
	 * Method to set the book_dates identifier
	 *
	 * @access	public
	 * @param	int book_dates identifier
	 */
	function setId($id_book_dates)
	{
		// Set book_dates id and wipe data
		$this->_id_book_dates		= $id_book_dates;
		$this->_data	= null;
	}

	/**
	 * Method to get a book_dates
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the book_dates data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	/**
	 * Method to checkout/lock the book_dates
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_book_dates)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$book_dates = $this->getTable();
			
			
			if(!$book_dates->checkout($uid, $this->_id_book_dates)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the book_dates
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_book_dates)
		{
			$book_dates = $this->getTable();
			if(! $book_dates->checkin($this->_id_book_dates)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if book_dates is checked out
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
		$query = "SELECT #__users.name FROM #__users JOIN #__sv_apptpro3_book_dates ON #__sv_apptpro3_book_dates.checked_out = #__users.id ".
		" WHERE #__sv_apptpro3_book_dates.id_book_dates = ". $this->getDbo()->escape($this->_id_book_dates);			
		$this->_db->setQuery($query);
		$locked_by = $this->_db->loadResult();
		return $locked_by;
	}	
		
		
	/**
	 * Method to load content book_dates data
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
			$query = 'SELECT * FROM '.$this->_table_prefix.'book_dates WHERE id_book_dates = '. $this->getDbo()->escape($this->_id_book_dates);
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the book_dates data
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
			$detail->id_book_dates = null;
			$detail->resource_id = null;			
			$detail->description = null;
			$detail->book_date = null;
			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->published = 0;
			$this->_data	= $detail;
			return (boolean) $this->_data;
		}
		return true;
	}
  	

	/**
	 * Method to store the book_dates text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/book_dates_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the book_dates table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// Store the book_dates table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}
	
		/**
	 * Method to (un)publish a book_dates
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

			$query = 'UPDATE '.$this->_table_prefix.'book_dates'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id_book_dates IN ( '.$this->getDbo()->escape($cids).' )'
				. ' AND ( checked_out = 0 OR ISNULL(checked_out) OR ( checked_out = ' .$user->get('id'). ' ) )';
				
			$this->_db->setQuery( $query );
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

		return true;
	}

	function delete($cid = array())
	{
		$result = false;


		if (sv_count_($cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM '.$this->_table_prefix.'book_dates WHERE id_book_dates IN ( '.$this->getDbo()->escape($cids).' )';
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
