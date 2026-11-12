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

class requestsModelrequests extends JModelLegacy
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
		$limit			= $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0 );


		$filter_user_search	= $mainframe->getUserStateFromRequest( $context.'filter_user_search', 'user_search', "");
		$this->setState('filter_user_search', $filter_user_search);

		$filter_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d"));
		$this->setState('filter_startdate', $filter_startdate);

		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
		$this->setState('filter_enddate', $filter_enddate);

		$filter_category	= $mainframe->getUserStateFromRequest( $context.'filter_category', 'categoryFilter', "0");
		$this->setState('filter_category', $filter_category);

		$filter_request_resource	= $mainframe->getUserStateFromRequest( $context.'filter_request_resource', 'request_resourceFilter', "0");
		$this->setState('filter_request_resource', $filter_request_resource);

		$filter_request_status	= $mainframe->getUserStateFromRequest( $context.'filter_request_status', 'request_status', "all");
		$this->setState('filter_request_status', $filter_request_status);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a requests data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all requests for the current data page.
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
	 * Method to get the total number of requests items
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
	 * Method to get a pagination object for the requests
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
		$query = ' SELECT * FROM '.$this->_table_prefix.'requests'.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	
	function _buildQueryForListScreen()
	{
		$orderby	= $this->_buildContentOrderBy();
		$filter = "";
		if($this->getState('filter_request_status') != "" && $this->getState('filter_request_status') != "all"){
			$filter = $filter."request_status = '".$this->getState('filter_request_status') ."' ";
		}
		if($this->getState('filter_user_search') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."LCASE(#__sv_apptpro3_requests.name) LIKE '%".strtolower(str_replace("'","\'",$this->getState('filter_user_search')) )."%' ";
		}
		if($this->getState('filter_request_resource') != "0"){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."resource = '".$this->getState('filter_request_resource')."' ";
		}
		if($this->getState('filter_category') != "0"){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."category = '".$this->getState('filter_category')."' ";
		}
		if($this->getState('filter_startdate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."startdate >= '".$this->getState('filter_startdate')."' ";
		}
		if($this->getState('filter_enddate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."enddate <= '".$this->getState('filter_enddate')."' ";
		}

		$query = ' SELECT '.
				'#__sv_apptpro3_requests.*, #__sv_apptpro3_resources.name AS '.
				'ResourceName, #__sv_apptpro3_services.name AS ServiceName, '.
				'#__sv_apptpro3_categories.name AS CategoryName, '.
				"CONCAT(#__sv_apptpro3_requests.startdate,' ',#__sv_apptpro3_requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ".
				'#__sv_apptpro3_paypal_transactions.id_paypal_transactions AS id_transaction '.
				'FROM ('.
				'#__sv_apptpro3_requests LEFT JOIN '.
				'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
				'#__sv_apptpro3_resources.id_resources LEFT JOIN '.
				'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
				'#__sv_apptpro3_services.id_services LEFT JOIN '.
				'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
				'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
				'#__sv_apptpro3_paypal_transactions ON '.
				'#__sv_apptpro3_paypal_transactions.custom = '.
				'#__sv_apptpro3_requests.id_requests) ';
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

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'id_requests' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir;			
		$orderby .= ", startdatetime";

		return $orderby;
	}
	
	
}
?>
