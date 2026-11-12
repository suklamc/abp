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

class adminModelservices extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	var $_services_tocopy = null;
	
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
		$srv_limit			= $mainframe->getUserStateFromRequest( $context.'srv_limit', 'srv_limit', $mainframe->getCfg('list_srv_limit'), 0);
		$srv_limitstart = $mainframe->getUserStateFromRequest( $context.'srv_limitstart', 'srv_limitstart', 0 );


		$filter_service_resource	= $mainframe->getUserStateFromRequest( $context.'filter_service_resource', 'service_resourceFilter', 0);
		$this->setState('filter_service_resource', $filter_service_resource);

		$this->setState('srv_limit', $srv_limit);
		$this->setState('srv_limitstart', $srv_limitstart);

	}
	
	
	/**
	 * Method to get a services data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all services for the current data page.
	 * - pagination is spec. by variables srv_limitstart,srv_limit.
	 * - ordering of list is build in _buildContentOrderBy  	 	 	  	 
	 * @since 1.5
	 */
	function getData()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('srv_limitstart'), $this->getState('srv_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('srv_limitstart'), $this->getState('srv_limit'));
		}

		return $this->_data2;
	}

	/**
	 * Method to get the total number of services items
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
	 * Method to get a pagination object for the services
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('srv_limitstart'), $this->getState('srv_limit') );
		}

		return $this->_pagination;
	}

	function getFilter_resource()
	{
		return $this->getState('filter_resource');
	}

	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'services'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = "SELECT #__sv_apptpro3_services.* ".
		" FROM #__sv_apptpro3_services "; //LEFT JOIN #__sv_apptpro3_resources ".
//		" ON #__sv_apptpro3_services.resource_id = #__sv_apptpro3_resources.id_resources ";
//		if($this->getState('filter_service_resource') != 0){

		// 4.0.2 added resource_scope to replace resource_id for multi-resoutce services
//			$query .= " WHERE resource_id =".$this->getState('filter_service_resource')." ";
			$query .= " WHERE #__sv_apptpro3_services.resource_scope LIKE '%|".$this->getDbo()->escape($this->getState('filter_service_resource'))."|%'";
//		}
		$query .= $orderby;
		//echo $query;
		//exit;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'srv_filter_order',      'srv_filter_order', 	  'name' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'srv_filter_order_Dir',  'srv_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
	function setServices_tocopy($services_tocopy){
		$this->_services_tocopy	= $services_tocopy;
	}

	function getServices_tocopy(){
		return $this->_services_tocopy;
	}
	
}
?>
