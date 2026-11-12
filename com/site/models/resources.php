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

class adminModelresources extends JModelLegacy
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
		$res_limit			= $mainframe->getUserStateFromRequest( $context.'res_limit', 'res_limit', $mainframe->getCfg('list_res_limit'), 0);
		$res_limitstart = $mainframe->getUserStateFromRequest( $context.'res_limitstart', 'res_limitstart', 0 );

		$filter_resource_category	= $mainframe->getUserStateFromRequest( $context.'filter_resource_category', 'resource_categoryFilter', 0);
		$this->setState('filter_resource_category', $filter_resource_category);

		$this->setState('res_limit', $res_limit);
		$this->setState('res_limitstart', $res_limitstart);

	}
	
	
	/**
	 * Method to get a resources data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all resources for the current data page.
	 * - pagination is spec. by variables res_limitstart,res_limit.
	 * - ordering of list is build in _buildContentOrderBy  	 	 	  	 
	 * @since 1.5
	 */
	function getData()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('res_limitstart'), $this->getState('res_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('res_limitstart'), $this->getState('res_limit'));
		}

		return $this->_data2;
	}

	/**
	 * Method to get the total number of resources items
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
	 * Method to get a pagination object for the resources
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('res_limitstart'), $this->getState('res_limit') );
		}

		return $this->_pagination;
	}
  	
	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'resources'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$user = JFactory::getUser();

		$query = " SELECT ".
		"#__sv_apptpro3_resources.*, #__sv_apptpro3_categories.name AS ".
		"cat_name, #__sv_apptpro3_categories.id_categories AS cat_id ".
		"FROM ".
		"#__sv_apptpro3_resources LEFT JOIN #__sv_apptpro3_categories ".
		"ON #__sv_apptpro3_resources.category_id = #__sv_apptpro3_categories.id_categories ".	
		"WHERE resource_admins LIKE '%|".$user->id."|%' ";
		if($this->getState('filter_resource_category') != 0){
			$query .= " AND #__sv_apptpro3_resources.category_scope LIKE '%|".$this->getDbo()->escape($this->getState('filter_resource_category'))."|%'";
			//echo $query;
			//exit;
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

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'res_filter_order',      'res_filter_order', 	  'name' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'res_filter_order_Dir',  'res_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
}
?>
