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

class rate_overridesModelrate_overrides extends JModelLegacy
{

	var $_data = null;
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

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	
	
	/**
	 * Method to get a rate_overrides data
	 *
	 * this method is called from the owner VIEW by VIEW->get('Data');
	 * - get list of all rate_overrides for the current data page.
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

	/**
	 * Method to get the total number of rate_overrides items
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
	 * Method to get a pagination object for the rate_overrides
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
		//$query = ' SELECT * FROM '.$this->_table_prefix.'rate_overrides'.$orderby;

		$query = 'SELECT * FROM ((SELECT DISTINCT #__usergroups.id, '.$this->_table_prefix.'rate_overrides.id_rate_overrides, '.
		$this->_table_prefix.'rate_overrides.entity_type,'.$this->_table_prefix.'rate_overrides.entity_id, '.
		$this->_table_prefix.'rate_overrides.published, '.
		$this->_table_prefix.'rate_overrides.rate_override, #__usergroups.title as user_group, '.
		$this->_table_prefix.'resources.name as res_name, "" as extra_name, "" as srv_name,  "" as seat_name '.
		' FROM '.$this->_table_prefix.'rate_overrides  '.
		' INNER JOIN #__usergroups '. 
		' ON #__usergroups.id = '.$this->_table_prefix.'rate_overrides.group_id  '.
		' INNER JOIN '.$this->_table_prefix.'resources  '.
		' ON '.$this->_table_prefix.'rate_overrides.entity_id = '.$this->_table_prefix.'resources.id_resources  '.
		' WHERE entity_type = \'resource\') '.
		
		'UNION '.
		
		'(SELECT DISTINCT #__usergroups.id, '.$this->_table_prefix.'rate_overrides.id_rate_overrides, '.
		$this->_table_prefix.'rate_overrides.entity_type,'.$this->_table_prefix.'rate_overrides.entity_id, '.
		$this->_table_prefix.'rate_overrides.published, '.
		$this->_table_prefix.'rate_overrides.rate_override, #__usergroups.title as user_group,  '.
		'"" as res_name, '.
		$this->_table_prefix.'extras.extras_label as extra_name, "" as srv_name,  "" as seat_name '.
		' FROM '.$this->_table_prefix.'rate_overrides  '.
		' INNER JOIN #__usergroups  '.
		' ON #__usergroups.id = '.$this->_table_prefix.'rate_overrides.group_id  '.
		' INNER JOIN '.$this->_table_prefix.'extras  '.
		' ON '.$this->_table_prefix.'rate_overrides.entity_id = '.$this->_table_prefix.'extras.id_extras  '.
		' WHERE entity_type = \'extra\') '.
		
		' UNION '.
		
		'(SELECT DISTINCT #__usergroups.id, '.$this->_table_prefix.'rate_overrides.id_rate_overrides, '.
		$this->_table_prefix.'rate_overrides.entity_type,'.$this->_table_prefix.'rate_overrides.entity_id, '.
		$this->_table_prefix.'rate_overrides.published, '.
		$this->_table_prefix.'rate_overrides.rate_override, #__usergroups.title as user_group,  '.
		'"" as res_name, "" as extra_name, '.
		$this->_table_prefix.'services.name as srv_name, "" as seat_name '.
		' FROM '.$this->_table_prefix.'rate_overrides  '.
		' INNER JOIN #__usergroups  '.
		' ON #__usergroups.id = '.$this->_table_prefix.'rate_overrides.group_id  '.
		' INNER JOIN '.$this->_table_prefix.'services  '.
		' ON '.$this->_table_prefix.'rate_overrides.entity_id = '.$this->_table_prefix.'services.id_services  '.
// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
//		' INNER JOIN '.$this->_table_prefix.'resources  '.
//		' ON '.$this->_table_prefix.'services.resource_id = '.$this->_table_prefix.'resources.id_resources  '.
		' WHERE entity_type = \'service\') '.
		
		' UNION '.
		
		'(SELECT DISTINCT #__usergroups.id, '.$this->_table_prefix.'rate_overrides.id_rate_overrides, '.
		$this->_table_prefix.'rate_overrides.entity_type,'.$this->_table_prefix.'rate_overrides.entity_id, '.
		$this->_table_prefix.'rate_overrides.published, '.
		$this->_table_prefix.'rate_overrides.rate_override, #__usergroups.title as user_group,  '.
		'"" as res_name, "" as extra_name, "" as srv_name, '.
		$this->_table_prefix.'seat_types.seat_type_label as seat_name '.
		' FROM '.$this->_table_prefix.'rate_overrides  '.
		' INNER JOIN #__usergroups  '.
		' ON #__usergroups.id = '.$this->_table_prefix.'rate_overrides.group_id  '.
		' INNER JOIN '.$this->_table_prefix.'seat_types  '.
		' ON '.$this->_table_prefix.'rate_overrides.entity_id = '.$this->_table_prefix.'seat_types.id_seat_types   '.
		' WHERE entity_type = \'seat\')) as temp '
		.$orderby;
		//echo $query;
		//exit;

		return $query;
	}
	

	function _buildContentOrderBy()
	{
		global $context;
	  	$mainframe = JFactory::getApplication();

		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'entity_type' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir;			

		return $orderby;
	}
	
}
?>
