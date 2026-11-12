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
 
class adminViewadmin extends JViewLegacy
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
	 $context = 'admin.list.';
 
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
		$jinput = JFactory::getApplication()->input;
		if($jinput->getString('frompage') == "advadmin"){
			$context = 'adv_admin.list.';
		} else {
			$context = 'admin.list.';
		}

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
		
		$uri = JUri::getInstance()->toString();

		$req_filter_order     = $mainframe->getUserStateFromRequest( $context.'req_filter_order',      'req_filter_order', 	  'startdatetime' );
		$req_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'req_filter_order_Dir',  'req_filter_order_Dir', 'desc' );		

		// get filters
		$filter_user_search	= $mainframe->getUserStateFromRequest( $context.'filter_user_search', 'user_search', "");
		$filter_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d"));
		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
//		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', date("Y-m-d",strtotime("$filter_startdate +1 month")));
		$filter_category	= $mainframe->getUserStateFromRequest( $context.'filter_category', 'categoryFilter', "0");
		$filter_request_resource	= $mainframe->getUserStateFromRequest( $context.'filter_request_resource', 'request_resourceFilter', "0");
		$filter_request_status	= $mainframe->getUserStateFromRequest( $context.'filter_request_status', 'request_status', "all");
		$filter_payment_status	= $mainframe->getUserStateFromRequest( $context.'filter_payment_status', 'payment_status', "all");

		$lists['order'] = "";
		$lists['order_req'] = $req_filter_order;  
		$lists['order_Dir_req'] = $req_filter_order_Dir;
  	
		$items	= $this->get('Data2');
		//print_r($items);
		//exit;
		
		$total			= $this->get('Total');
		$pagination =  $this->get( 'Pagination' );

		$filter_resource  = $mainframe->getUserStateFromRequest( $context.'filter_resource', 'filter_resource', '');
		$filter_resource =  $this->get('filter_resource');
		
		//DEVNOTE:save a reference into view	
		$user = JFactory::getUser();
		$this->user = $user;	
		$this->lists = $lists;    
		$this->items = $items; 		
		$this->pagination = $pagination;
		$this->request_url = $uri;
		$this->filter_user_search = $filter_user_search;
		$this->filter_startdate = $filter_startdate;
		$this->filter_enddate = $filter_enddate;
		$this->filter_category = $filter_category;
		$this->filter_request_resource = $filter_request_resource;
		$this->filter_request_status = $filter_request_status;
		$this->filter_payment_status = $filter_payment_status;

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
