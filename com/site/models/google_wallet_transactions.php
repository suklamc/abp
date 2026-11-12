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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class adminModelgoogle_wallet_transactions extends JModelLegacy
{

	var $_data = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		global $context;
	  	$mainframe = JFactory::getApplication();
	  
		//initialize class property
	    $this->_table_prefix = '#__sv_apptpro3_';	
	  
		//DEVNOTE: Get the pagination request variables
		$limit			= $mainframe->getUserStateFromRequest( $context.'goog_limit', 'goog_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'goog_limitstart', 'goog_limitstart', 0 );

		$limit			= $mainframe->getUserStateFromRequest( $context.'goog_limit', 'goog_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'goog_limitstart', 'goog_limitstart', 0 );

		$this->setState('goog_limit', $limit);
		$this->setState('goog_limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a google_wallet_transactions data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all google_wallet_transactions for the current data page.
	 * - pagination is spec. by variables limitstart,limit.
	 * - ordering of list is build in _buildContentOrderBy  	 	 	  	 
	 * @since 1.5
	 */
	function getData()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('goog_limitstart'), $this->getState('goog_limit'));
		}

		return $this->_data;
	}


	/**
	 * Method to get the total number of google_wallet_transactions items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}
	
	/**
	 * Method to get a pagination object for the google_wallet_transactions
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('goog_limitstart'), $this->getState('goog_limit') );
		}

		return $this->_pagination;
	}
  	
	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'google_wallet_transactions ';
		$filter = "WHERE ";	

		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_goog_startdate = $mainframe->getUserStateFromRequest( $context.'filter_goog_startdate', 'filter_goog_startdate', date("Y-m-d", strtotime('first day of last month')) );
		$filter_goog_enddate = $mainframe->getUserStateFromRequest( $context.'filter_goog_enddate',  'filter_goog_enddate', '' );		

		if($filter_goog_startdate != ""){
			$filter .= " stamp>='".$this->getDbo()->escape($filter_goog_startdate)."' ";
		}
		if($filter_goog_enddate != ""){
			if($filter != "WHERE "){
				$filter .= " AND ";
			}
			$filter .= " stamp<='".$this->getDbo()->escape($filter_goog_enddate)."' ";
		}
		if($filter != "WHERE "){
			$query .= $filter;
		}
		$query .= $orderby;
		
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'goog_filter_order',      'goog_filter_order', 	  'stamp' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'goog_filter_order_Dir',  'goog_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
}
?>
