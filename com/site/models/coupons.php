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

class adminModelcoupons extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'coup_limit', 'coup_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'coup_limitstart', 'coup_limitstart', 0 );

		$this->setState('coup_limit', $limit);
		$this->setState('coup_limitstart', $limitstart);

		$filter_coupon_search	= $mainframe->getUserStateFromRequest( $context.'filter_coupon_search', 'coupon_search', "");
		$this->setState('filter_coupon_search', $filter_coupon_search);

	}
	
	
	/**
	 * Method to get a coupons data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all coupons for the current data page.
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
			$this->_data = $this->_getList($query, $this->getState('coup_limitstart'), $this->getState('coup_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('coup_limitstart'), $this->getState('coup_limit'));
		}

		return $this->_data2;
	}



	/**
	 * Method to get the total number of coupons items
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
	 * Method to get a pagination object for the coupons
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('coup_limitstart'), $this->getState('coup_limit') );
		}

		return $this->_pagination;
	}
  	
	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'coupons'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = " SELECT #__sv_apptpro3_coupons.*, DATE_FORMAT(expiry_date, '%a %b %e, %Y') as expiry, ".
		" (SELECT count(*) FROM #__sv_apptpro3_requests WHERE coupon_code = #__sv_apptpro3_coupons.coupon_code  ".
		"   AND ( request_status = 'accepted' OR request_status = 'attended' OR request_status = 'completed' )) as current_count ".
		" FROM #__sv_apptpro3_coupons ";
		if($this->getState('filter_coupon_search') != ""){
			$query .= " WHERE #__sv_apptpro3_coupons.coupon_code LIKE '%".strtolower(str_replace("'","\'",$this->getDbo()->escape($this->getState('filter_coupon_search'))) )."%' ";
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

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'coup_filter_order',      'coup_filter_order', 	  'description' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'coup_filter_order_Dir',  'coup_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
}
?>
