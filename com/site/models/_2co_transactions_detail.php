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


class admin_detailModel_2co_transactions_detail extends JModelLegacy
{
		var $_id__2co_transactions = null;
		var $_data = null;
		var $_table_prefix = null;

	function __construct()
	{
		parent::__construct();
		
		//initialize class property
	  	$this->_table_prefix = '#__sv_apptpro3_';			
		$jinput = JFactory::getApplication()->input;
		$array = $jinput->get('cid', array(), 'ARRAY');
		
		$this->setId((int)$array[0]);

	}

	/**
	 * Method to set the _2co_transactions identifier
	 *
	 * @access	public
	 * @param	int _2co_transactions identifier
	 */
	function setId($id__2co_transactions)
	{
		// Set _2co_transactions id and wipe data
		$this->_id__2co_transactions	= $id__2co_transactions;
		$this->_data	= null;
	}

	/**
	 * Method to get a _2co_transactions
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the _2co_transactions data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		
   	return $this->_data;
	}
	
		
	/**
	 * Method to load content _2co_transactions data
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
			$query = 'SELECT * FROM '.$this->_table_prefix.'_2co_transactions WHERE id__2co_transactions = '. $this->getDbo()->escape($this->_id__2co_transactions);
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//echo $query;
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	

	function delete($cid = array())
	{
		$result = false;


		if (sv_count_($cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM '.$this->_table_prefix.'_2co_transactions WHERE id__2co_transactions IN ( '.$this->getDbo()->escape($cids).' )';
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
