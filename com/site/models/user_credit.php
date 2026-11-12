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

class adminModeluser_credit extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
	var $_data3 = null;
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'limit', 'limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0 );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a user_credit data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all user_credit for the current data page.
	 * - pagination is spec. by variables limitstart,limit.
	 * - ordering of list is build in _buildContentOrderBy  	 	 	  	 
	 * @since 1.5
	 */
	function getData()
	{
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen("uc");
			$this->_data2 = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data2;
	}

	function getData3()
	{
		if (empty($this->_data3))
		{
			$query = $this->_buildQueryForListScreen("gc");
			$this->_data3 = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data3;
	}


	/**
	 * Method to get the total number of user_credit items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}
	
	/**
	 * Method to get a pagination object for the user_credit
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
		$query = ' SELECT * FROM '.$this->_table_prefix.'user_credit'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	
	function _buildQueryForListScreen($type = 'uc')
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = "SELECT #__sv_apptpro3_user_credit.* ";
		if($type == "uc"){
			$query .= ", #__users.name, #__users.username ";
			$query .= "FROM #__sv_apptpro3_user_credit INNER JOIN #__users ON #__sv_apptpro3_user_credit.user_id = #__users.id ";
		} else {
			$query .= ", #__sv_apptpro3_user_credit.gift_cert_name as name ";
			$query .= "FROM #__sv_apptpro3_user_credit ";
		}
		$query .= " WHERE credit_type = '".$this->getDbo()->escape($type)."'" .
		$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'uc_filter_order',      'uc_filter_order', 	  'user_id' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'uc_filter_order_Dir',  'uc_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
}
?>
