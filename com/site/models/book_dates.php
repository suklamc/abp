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

class adminModelbook_dates extends JModelLegacy
{

	var $_data = null;
	var $_data2 = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	var $_book_dates_tocopy = null;
	
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'bd_limit', 'bd_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'bd_limitstart', 'bd_limitstart', 0 );


		$filter_book_dates_resource	= $mainframe->getUserStateFromRequest( $context.'filter_book_dates_resource', 'book_dates_resourceFilter', 0);
		$this->setState('filter_book_dates_resource', $filter_book_dates_resource);

		$this->setState('bd_limit', $limit);
		$this->setState('bd_limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a book_dates data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all book_dates for the current data page.
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
			$this->_data = $this->_getList($query, $this->getState('bd_limitstart'), $this->getState('bd_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('bd_limitstart'), $this->getState('bd_limit'));
		}

		return $this->_data2;
	}

	/**
	 * Method to get the total number of book_dates items
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
	 * Method to get a pagination object for the book_dates
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('bd_limitstart'), $this->getState('bd_limit') );
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
		$query = ' SELECT * FROM '.$this->_table_prefix.'book_dates'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$query = "SELECT #__sv_apptpro3_book_dates.*, #__sv_apptpro3_book_dates.published, resource_id, #__sv_apptpro3_resources.name, ".
		"DATE_FORMAT(book_date, '%W %M %e, %Y') as book_date_display, book_date, ".
		"DATE_FORMAT(book_date, '%b %e/%y') as bookf_date_display_mobile, ".
		"#__sv_apptpro3_book_dates.description ".
		"FROM #__sv_apptpro3_book_dates LEFT JOIN #__sv_apptpro3_resources ".
		"ON #__sv_apptpro3_book_dates.resource_id = #__sv_apptpro3_resources.id_resources ".
		" WHERE #__sv_apptpro3_resources.id_resources = ".$this->getDbo()->escape($this->getState('filter_book_dates_resource', 0)).
		$orderby;
		//echo $query;
		//exit;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'bd_filter_order',      'bd_filter_order', 	  'book_date' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'bd_filter_order_Dir',  'bd_filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir);			

		return $orderby;
	}
	

}
?>
