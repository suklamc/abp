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

class authnet_transactionsModelauthnet_transactions extends JModelLegacy
{

	var $_data = null;
	var $_total = null;
	var $_data2 = null;
	var $_total2 = null;
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0 );

		$filter_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$this->setState('filter_startdate', $filter_startdate);

		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
		$this->setState('filter_enddate', $filter_enddate);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a authnet_transactions data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all authnet_transactions for the current data page.
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
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data2;
	}


	/**
	 * Method to get the total number of authnet_transactions items
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
	
	function getTotal2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_total2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_total2 = $this->_getListCount($query);
		}

		return $this->_total2;
	}

	/**
	 * Method to get a pagination object for the authnet_transactions
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}
  	
	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'authnet_transactions'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$filter = "";
		if($this->getState('filter_startdate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."stamp >= '".$this->getState('filter_startdate')."' ";
		}
		if($this->getState('filter_enddate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."stamp <= '".$this->getState('filter_enddate')."' ";
		}

		$query = ' SELECT * FROM '.$this->_table_prefix.'authnet_transactions';
				if($filter != ""){
					$query = $query." WHERE ".$filter;
				}
				$query = $query.' '.$orderby;
		//echo $query;
		//exit;

		return $query;
	}


	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'stamp' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , ordering ';			

		return $orderby;
	}
	
}
?>
