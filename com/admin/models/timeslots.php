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

class timeslotsModeltimeslots extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	var $_timeslots_tocopy = null;
	
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


		$filter_resource	= $mainframe->getUserStateFromRequest( $context.'filter_resource', 'resource_id', 0);
		$this->setState('filter_resource', $filter_resource);

		$filter_day_number	= $mainframe->getUserStateFromRequest( $context.'filter_day_number', 'day_number', 1);
		$this->setState('filter_day_number', $filter_day_number);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a timeslots data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all timeslots for the current data page.
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
	 * Method to get the total number of timeslots items
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
	 * Method to get a pagination object for the timeslots
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

	function getFilter_resource()
	{
		return $this->getState('filter_resource');
	}


	function getFilter_day_number()
	{
		return $this->getState('filter_day_number');
	}

	function _buildQuery()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = ' SELECT * FROM '.$this->_table_prefix.'timeslots'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$filter = "";
		if($this->getState('filter_day_number') != "" && $this->getState('filter_day_number') != "all"){
			$filter = $filter."day_number = '".$this->getState('filter_day_number')."' ";
		}

		if($this->getState('filter_resource') == "0"){
			$filter = ($filter != "" ? $filter." and " : $filter);
			$filter = $filter."(resource_ID is NULL or resource_id = 0)";
		} else {
			$filter = ($filter != "" ? $filter." and " : $filter);
			$filter = $filter."resource_id = ".$this->getState('filter_resource')." ";
		}

		$query = "SELECT #__sv_apptpro3_timeslots.id_timeslots, staff_only, day_number, #__sv_apptpro3_timeslots.published, resource_id, ".
		" #__sv_apptpro3_resources.name, ".
		" #__sv_apptpro3_timeslots.start_publishing, #__sv_apptpro3_timeslots.end_publishing, ".
		" TIME_FORMAT(timeslot_starttime,'%H:%i') as timeslot_starttime, ".
		" TIME_FORMAT(timeslot_endtime,'%H:%i') as timeslot_endtime ".
		" FROM #__sv_apptpro3_timeslots LEFT JOIN #__sv_apptpro3_resources ".
		" ON #__sv_apptpro3_timeslots.resource_id = #__sv_apptpro3_resources.id_resources ";
		if($filter != ""){
			$query .= " WHERE ".$filter;
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

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'id_timeslots' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$filter_order." ".$filter_order_Dir.($filter_order=="timeslot_starttime"?' ':',timeslot_starttime ');			

		return $orderby;
	}
	
	function settimeslots_tocopy($timeslots_tocopy){
		$this->_timeslots_tocopy	= $timeslots_tocopy;
	}

	function gettimeslots_tocopy(){
		return $this->_timeslots_tocopy;
	}
	
}
?>
