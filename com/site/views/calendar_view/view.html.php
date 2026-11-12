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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//DEVNOTE: import VIEW object class
jimport( 'joomla.application.component.view' );

/**
 [controller]View[controller]
 */
 
class calendar_viewViewcalendar_view extends JViewLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $config = array())
	{
	 /** set up global variable for sorting etc.
	  * $context is used in VIEW abd in MODEL
	  **/	  
	 
 	 global $context;
	 $context = 'calendar_view.';
 
 	 parent::__construct( $config );
	}
 

   
	function display($tpl = null)
	{
		global $context;
	  	$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		
		$menu = JFactory::getApplication()->getMenu(); 
		$menu_id = $jinput->getString( 'menu_id', '' ); // passed from normal view on 'print'
		if($menu_id == ""){
			$active = $menu->getActive(); 
			$menu_id = $active->id;
		}
		$params = $menu->getParams($menu_id);
		$start_screen_view = "month";
		if($params->get('fd_start_screen') != ''){
			$start_screen_view = $params->get('fd_start_screen');
			//echo $start_screen_view;
		}
		
		// get filters
		$calendar_view_view	= $mainframe->getUserStateFromRequest( $context.'calendar_view_view', 'calendar_view_view', $start_screen_view);

		$calendar_view_cur_week_offset = $mainframe->getUserState('calendar_view_cur_week_offset');
		$calendar_view_cur_day = $mainframe->getUserState('calendar_view_cur_day');
		$calendar_view_cur_month = $mainframe->getUserState('calendar_view_cur_month');
		$calendar_view_cur_year = $mainframe->getUserState('calendar_view_cur_year');


		$uri = JUri::getInstance()->toString();
		$user = JFactory::getUser();
		
		$this->user = $user;	
		$this->request_url = $uri;

		$frompage  = 'calendar_view';
		$this->frompage = $frompage;
		$this->calendar_view_view = $calendar_view_view;
		//$this->calendar_view_resource_filter = $calendar_view_resource_filter;
		//$this->calendar_view_category_filter = $calendar_view_category_filter;

		$this->calendar_view_cur_week_offset = $calendar_view_cur_week_offset;
		$this->calendar_view_cur_day = $calendar_view_cur_day;
		$this->calendar_view_cur_month = $calendar_view_cur_month;
		$this->calendar_view_cur_year = $calendar_view_cur_year;


		$appWeb      = JFactory::getApplication();
		$layout = ($appWeb->client->mobile ? 'mobile' : null);
		$agent = $appWeb->client->userAgent;
		$this->agent = $agent;
		// dev only hard code mobile view
		//$layout = 'mobile';
		
    	parent::display($layout);

  }
}

?>
