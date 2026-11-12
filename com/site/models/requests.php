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

class adminModelrequests extends JModelLegacy
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
		$limit	= 0;//$mainframe->getUserStateFromRequest( $context.'req_limit', 'req_limit', 0, 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'req_limitstart', 'req_limitstart', 0 );

		$this->setState('req_limit', $limit);
		$this->setState('req_limitstart', $limitstart);


		$filter_user_search	= $mainframe->getUserStateFromRequest( $context.'filter_user_search', 'user_search', "");
		$this->setState('filter_user_search', $filter_user_search);

		$filter_startdate = $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d"));
		$this->setState('filter_startdate', $filter_startdate);

		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
		$this->setState('filter_enddate', $filter_enddate);

		$filter_category	= $mainframe->getUserStateFromRequest( $context.'filter_category', 'categoryFilter', "0");
		$this->setState('filter_category', $filter_category);

		$filter_request_resource	= $mainframe->getUserStateFromRequest( $context.'filter_request_resource', 'request_resourceFilter', "0");
		$this->setState('filter_request_resource', $filter_request_resource);

		$filter_request_status	= $mainframe->getUserStateFromRequest( $context.'filter_request_status', 'request_status', "all");
		$this->setState('filter_request_status', $filter_request_status);

		$filter_payment_status	= $mainframe->getUserStateFromRequest( $context.'filter_payment_status', 'payment_status', "all");
		$this->setState('filter_payment_status', $filter_payment_status);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$params = JComponentHelper::getParams('com_languages');
		$sql = "SET lc_time_names = '".str_replace("-", "_", $params->get("site", 'en-GB'))."';";
		$this->_db->setQuery($sql);
		if (!$this->_db->execute()) {
			echo $this->_db->getErrorMsg();
		}
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";
		$this->_db->setQuery($sql);
		if (!$this->_db->execute()) {
			echo $this->_db->getErrorMsg();
		}

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
			$this->_data = $this->_getList($query, $this->getState('req_limitstart'), $this->getState('req_limit'));
		}

		return $this->_data;
	}

	function getData2()
	{
		//DEVNOTE: Lets load the content if it doesn't already exist
		if (empty($this->_data2))
		{
			$query = $this->_buildQueryForListScreen();
			$this->_data2 = $this->_getList($query, $this->getState('req_limitstart'), $this->getState('req_limit'));
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
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('req_limitstart'), $this->getState('req_limit') );
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
			$filter = $filter."request_status = '".$this->getDbo()->escape($this->getState('filter_request_status')) ."' ";
		}
		if($this->getState('filter_payment_status') != "" && $this->getState('filter_payment_status') != "all"){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."payment_status = '".$this->getDbo()->escape($this->getState('filter_payment_status')) ."' ";
		}
		if($this->getState('filter_user_search') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."LCASE(#__sv_apptpro3_requests.name) LIKE '%".strtolower(str_replace("'","\'",$this->getDbo()->escape($this->getState('filter_user_search'))) )."%' ";
		}
		if($this->getState('filter_request_resource') != "0"){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."resource = '".$this->getDbo()->escape($this->getState('filter_request_resource'))."' ";
		}
		if($this->getState('filter_category') != "0"){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."category_id = '".$this->getDbo()->escape($this->getState('filter_category'))."' ";
		}
		if($this->getState('filter_startdate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."startdate >= '".$this->getDbo()->escape($this->getState('filter_startdate'))."' ";
		}
		if($this->getState('filter_enddate') != ""){
			if($filter != ""){
				$filter = $filter." AND ";
			}	 
			$filter = $filter."enddate <= '".$this->getDbo()->escape($this->getState('filter_enddate'))."' ";
		}

		$user = JFactory::getUser();

		$query = ' SELECT '.
				'#__sv_apptpro3_requests.*, #__sv_apptpro3_resources.name AS '.
				'ResourceName, #__sv_apptpro3_services.name AS ServiceName, '.
				'#__sv_apptpro3_categories.name AS CategoryName, '.
				"CONCAT(#__sv_apptpro3_requests.startdate,#__sv_apptpro3_requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e ') as display_startdate, ";
//				if($apptpro_config->timeFormat == "12"){
//					$query .= "DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %h:%i %p') as display_starttime, ";
//				} else {
					$query .= "DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %H:%i') as display_starttime, ";
//				}
				$query .= '#__sv_apptpro3_paypal_transactions.id_paypal_transactions AS id_transaction '.
				'FROM ('.
				'#__sv_apptpro3_requests LEFT JOIN '.
				'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
				'#__sv_apptpro3_resources.id_resources LEFT JOIN '.
				'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
				'#__sv_apptpro3_services.id_services LEFT JOIN '.
				'#__sv_apptpro3_categories ON #__sv_apptpro3_resources.category_id = '.
				'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
				'#__sv_apptpro3_paypal_transactions ON '.
				'#__sv_apptpro3_paypal_transactions.custom = '.
				'#__sv_apptpro3_requests.id_requests) '.

				' WHERE #__sv_apptpro3_resources.resource_admins LIKE \'%|'.$user->id.'|%\' ';
				
				if($filter != ""){
					$query = $query." AND ".$filter;
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

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'req_filter_order',      'req_filter_order', 	  'startdatetime' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'req_filter_order_Dir',  'req_filter_order_Dir', 'desc' );		

		$orderby 	= ' ORDER BY '.$this->getDbo()->escape($filter_order).' '.$this->getDbo()->escape($filter_order_Dir);			
		$orderby .= ", startdatetime";
		
		return $orderby;
	}
	
	
}
?>
