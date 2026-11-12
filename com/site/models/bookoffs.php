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

class adminModelbookoffs extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	var $_bookoffs_tocopy = null;
	
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'bo_limit', 'bo_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'bo_limitstart', 'bo_limitstart', 0 );


		$filter_bookoffs_resource	= $mainframe->getUserStateFromRequest( $context.'filter_bookoffs_resource', 'bookoffs_resourceFilter', 0);
		$this->setState('filter_bookoffs_resource', $filter_bookoffs_resource);

		$this->setState('bo_limit', $limit);
		$this->setState('bo_limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a bookoffs data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all bookoffs for the current data page.
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
			$this->_data = $this->_getList($query, $this->getState('bo_limitstart'), $this->getState('bo_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('bo_limitstart'), $this->getState('bo_limit'));
		}

		return $this->_data2;
	}

	/**
	 * Method to get the total number of bookoffs items
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
	 * Method to get a pagination object for the bookoffs
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('bo_limitstart'), $this->getState('bo_limit') );
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
		$query = ' SELECT * FROM '.$this->_table_prefix.'bookoffs'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = "SELECT #__sv_apptpro3_bookoffs.*, #__sv_apptpro3_bookoffs.published, resource_id, #__sv_apptpro3_resources.name, ".
		"DATE_FORMAT(off_date, '%W %M %e, %Y') as off_date_display, off_date, ".
		"DATE_FORMAT(off_date, '%b %e/%y') as off_date_display_mobile, ".
		"#__sv_apptpro3_bookoffs.description, CONCAT(DATE_FORMAT(bookoff_starttime, '%H:%i'), '-', DATE_FORMAT(bookoff_endtime, '%H:%i')) as hours ".
		"FROM #__sv_apptpro3_bookoffs LEFT JOIN #__sv_apptpro3_resources ".
		"ON #__sv_apptpro3_bookoffs.resource_id = #__sv_apptpro3_resources.id_resources ".
		" WHERE #__sv_apptpro3_resources.id_resources = ".$this->getDbo()->escape($this->getState('filter_bookoffs_resource', 0)).
		$orderby;
		//echo $query;
		//exit;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'bo_filter_order',      'bo_filter_order', 	  'off_date' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'bo_filter_order_Dir',  'bo_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir).' , ordering ';			

		return $orderby;
	}
	
	function setbookoffs_tocopy($bookoffs_tocopy){
		$this->_bookoffs_tocopy	= $bookoffs_tocopy;
	}

	function getbookoffs_tocopy(){
		return $this->_bookoffs_tocopy;
	}
	
}
?>
