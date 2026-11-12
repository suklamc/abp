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
 
class advadminViewadvadmin extends JViewLegacy
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
	 $context = 'adv_admin.list.';
 
 	 parent::__construct( $config );
	}
 

	/**
	 * Display the view
	 * take data from MODEL and put them into	
	 * reference variables
	 * 
	 * Go to MODEL, execute Method getData and
	 * result save into reference variable $items	 	 	 
	 * $items		= $this->get( 'Data');
	 * - getData gets the course list from DB	 
	 *	  
	 * variable filter_order specifies what is the order by column
	 * variable filter_order_Dir sepcifies if the ordering is [ascending,descending]	 	 	 	  
	 */
    
	function display($tpl = null)
	{
		global $context;
	  	$mainframe = JFactory::getApplication();
		
		//DEVNOTE: set document title
		$document = JFactory::getDocument();
//		$document->setTitle( JText::_('Appointment Booking Pro - My Bookings') );

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests.php');
		$model = new adminModelrequests;
 		if($model == null){
			echo "model = null";
			exit;
		}
   		$this->setModel($model, true);

		//print_r($this->_models);

		$uri	= JFactory::getURI();

		$req_filter_order     = $mainframe->getUserStateFromRequest( $context.'req_filter_order',      'req_filter_order', 	  'startdatetime' );
		$req_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'req_filter_order_Dir',  'req_filter_order_Dir', 'desc' );		

		// get filters
		$filter_user_search	= $mainframe->getUserStateFromRequest( $context.'filter_user_search', 'user_search', "");
		$filter_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d"));
		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
		$filter_category	= $mainframe->getUserStateFromRequest( $context.'filter_category', 'categoryFilter', "0");
		$filter_request_resource	= $mainframe->getUserStateFromRequest( $context.'filter_request_resource', 'request_resourceFilter', "");
		$filter_request_status	= $mainframe->getUserStateFromRequest( $context.'filter_request_status', 'request_status', "");

		$lists['order_req'] = $req_filter_order;  
		$lists['order_Dir_req'] = $req_filter_order_Dir;


		$items			= $this->get('Data2');
		//print_r($items);


		$total			= $this->get('Total');
		$pagination = $this->get( 'Pagination' );

		$filter_resource  = $mainframe->getUserStateFromRequest( $context.'filter_resource', 'filter_resource', '');
		$filter_resource = $this->get('filter_resource');
		
		$user = JFactory::getUser();
		$frompage  = 'advadmin';
		$this->user_id = $user->id;
		$this->frompage = $frompage;		$this->lists = $lists;    
		$this->assignRef('items',		$items); 		
		$this->assignRef('pagination',	$pagination);
		$this->request_url = $uri;
		$this->assignRef('filter_user_search', $filter_user_search);
		$this->assignRef('filter_startdate', $filter_startdate);
		$this->assignRef('filter_enddate', $filter_enddate);

    	parent::display($tpl="prt");
  }
}

?>
