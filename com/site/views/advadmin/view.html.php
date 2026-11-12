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
 
class adminViewadvadmin extends JViewLegacy
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
		$jinput = JFactory::getApplication()->input;
		
		//DEVNOTE: set document title
		$document = JFactory::getDocument();

		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// If the operator is res-admin for only one resource, preset the filters so they do not need to 
		// select the resource.
		$user = JFactory::getUser();
		$sql = "SELECT id_resources FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "advadmin_view", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		if(sv_count_($res_rows) == 0 && $apptpro_config->enable_auto_resource == "Yes" && !$user->guest){
			// enable_auto_resource = Yes so we will create a resource for this user and make them admin
			if(auto_resource($user)){
				// re select res_rows 
				$sql = "SELECT id_resources FROM #__sv_apptpro3_resources ".
				"WHERE resource_admins LIKE '%|".$user->id."|%' and published=1 ".
				"ORDER BY ordering;";
				try{
					$database->setQuery($sql);
					$res_rows = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "advadmin_view", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}		
			}
		}

		if(sv_count_($res_rows) == 1){			
			$def_res = $res_rows[0]->id_resources;
			$mainframe->setUserState($context.'filter_request_resource', $def_res);
			$mainframe->setUserState($context.'filter_timeslots_resource', $def_res);
			$mainframe->setUserState($context.'filter_bookoffs_resource', $def_res);
			$mainframe->setUserState($context.'filter_book_dates_resource', $def_res);
			$mainframe->setUserState($context.'filter_service_resource', $def_res);
		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests.php');
		$model = new adminModelrequests;
 		if($model == null){
			echo "model = null";
			exit;
		}
   		$this->setModel($model, true);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'resources.php');
		$model_resources = new adminModelresources;
 		if($model_resources == null){
			echo "model_resources = null";
			exit;
		}
   		$this->setModel($model_resources, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'services.php');
		$model_services = new adminModelservices;
 		if($model_services == null){
			echo "model_services = null";
			exit;
		}
   		$this->setModel($model_services, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'timeslots.php');
		$model_timeslots = new adminModeltimeslots;
 		if($model_timeslots == null){
			echo "model_timeslots = null";
			exit;
		}
   		$this->setModel($model_timeslots, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bookoffs.php');
		$model_bookoffs = new adminModelbookoffs;
 		if($model_bookoffs == null){
			echo "model_bookoffs = null";
			exit;
		}
   		$this->setModel($model_bookoffs, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'book_dates.php');
		$model_book_dates = new adminModelbook_dates;
 		if($model_book_dates == null){
			echo "model_book_dates = null";
			exit;
		}
   		$this->setModel($model_book_dates, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'paypal_transactions.php');
		$model_paypal_transactions = new adminModelpaypal_transactions;
 		if($model_paypal_transactions == null){
			echo "model_paypal_transactions = null";
			exit;
		}
   		$this->setModel($model_paypal_transactions, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'authnet_transactions.php');
		$model_authnet_transactions = new adminModelauthnet_transactions;
 		if($model_authnet_transactions == null){
			echo "model_authnet_transactions = null";
			exit;
		}
   		$this->setModel($model_authnet_transactions, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'authnet_aim_transactions.php');
		$model_authnet_aim_transactions = new adminModelauthnet_aim_transactions;
 		if($model_authnet_aim_transactions == null){
			echo "model_authnet_aim_transactions = null";
			exit;
		}
   		$this->setModel($model_authnet_aim_transactions, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'_2co_transactions.php');
		$model_2co_transactions = new adminModel_2co_transactions;
 		if($model_2co_transactions == null){
			echo "model_2co_transactions = null";
			exit;
		}
   		$this->setModel($model_2co_transactions, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'stripe_transactions.php');
		$model_stripe_transactions = new adminModelstripe_transactions;
 		if($model_stripe_transactions == null){
			echo "model_stripe_transactions = null";
			exit;
		}
   		$this->setModel($model_stripe_transactions, false);


		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'coupons.php');
		$model_coupons = new adminModelcoupons;
 		if($model_coupons == null){
			echo "model_coupons = null";
			exit;
		}
   		$this->setModel($model_coupons, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'extras.php');
		$model_extras = new adminModelextras;
 		if($model_extras == null){
			echo "model_extras = null";
			exit;
		}
   		$this->setModel($model_extras, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'rate_adjustments.php');
		$model_rate_adjustments = new adminModelrate_adjustments;
 		if($model_rate_adjustments == null){
			echo "model_rate_adjustments = null";
			exit;
		}
   		$this->setModel($model_rate_adjustments, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'seat_adjustments.php');
		$model_seat_adjustments = new adminModelseat_adjustments;
 		if($model_seat_adjustments == null){
			echo "model_seat_adjustments = null";
			exit;
		}
   		$this->setModel($model_seat_adjustments, false);

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'user_credit.php');
		$model_user_credit = new adminModeluser_credit;
 		if($model_user_credit == null){
			echo "model_user_credit = null";
			exit;
		}
   		$this->setModel($model_user_credit, false);


		//print_r($this->_models);

		$uri	= JUri::getInstance();

		$req_filter_order     = $mainframe->getUserStateFromRequest( $context.'req_filter_order',      'req_filter_order', 	  'startdatetime' );
		$req_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'req_filter_order_Dir',  'req_filter_order_Dir', 'desc' );		

		$res_filter_order     = $mainframe->getUserStateFromRequest( $context.'res_filter_order',      'res_filter_order', 	  'name' );
		$res_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'res_filter_order_Dir',  'res_filter_order_Dir', '' );		

		$srv_filter_order     = $mainframe->getUserStateFromRequest( $context.'srv_filter_order',      'srv_filter_order', 	  'name' );
		$srv_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'srv_filter_order_Dir',  'srv_filter_order_Dir', '' );		

		$ts_filter_order     = $mainframe->getUserStateFromRequest( $context.'ts_filter_order',      'ts_filter_order', 	  'timeslot_starttime' );
		$ts_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'ts_filter_order_Dir',  'ts_filter_order_Dir', '' );		

		$bo_filter_order     = $mainframe->getUserStateFromRequest( $context.'bo_filter_order',      'bo_filter_order', 	  'off_date' );
		$bo_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'bo_filter_order_Dir',  'bo_filter_order_Dir', '' );		

		$bd_filter_order     = $mainframe->getUserStateFromRequest( $context.'bd_filter_order',      'bd_filter_order', 	  'book_date' );
		$bd_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'bd_filter_order_Dir',  'bd_filter_order_Dir', '' );		

		$pp_filter_order     = $mainframe->getUserStateFromRequest( $context.'pp_filter_order',      'pp_filter_order', 	  'stamp' );
		$pp_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'pp_filter_order_Dir',  'pp_filter_order_Dir', '' );		

		$an_filter_order     = $mainframe->getUserStateFromRequest( $context.'an_filter_order',      'an_filter_order', 	  'stamp' );
		$an_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'an_filter_order_Dir',  'an_filter_order_Dir', '' );		

		$an_aim_filter_order     = $mainframe->getUserStateFromRequest( $context.'an_aim_filter_order',      'an_aim_filter_order', 	  'stamp' );
		$an_aim_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'an_aim_filter_order_Dir',  'an_aim_filter_order_Dir', '' );		

		$goog_filter_order     = $mainframe->getUserStateFromRequest( $context.'goog_filter_order',      'goog_filter_order', 	  'stamp' );
		$goog_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'goog_filter_order_Dir',  'goog_filter_order_Dir', '' );		

		$_2co_filter_order     = $mainframe->getUserStateFromRequest( $context.'_2co_filter_order',      '_2co_filter_order', 	  'stamp' );
		$_2co_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'_2co_filter_order_Dir',  '_2co_filter_order_Dir', '' );		

		$stripe_filter_order     = $mainframe->getUserStateFromRequest( $context.'stripe_filter_order',      'stripe_filter_order', 	  'stamp' );
		$stripe_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'stripe_filter_order_Dir',  'stripe_filter_order_Dir', '' );		


		$coup_filter_order     = $mainframe->getUserStateFromRequest( $context.'coup_filter_order',      'coup_filter_order', 	  'coupon_code' );
		$coup_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'coup_filter_order_Dir',  'coup_filter_order_Dir', '' );		

		$ext_filter_order     = $mainframe->getUserStateFromRequest( $context.'ext_filter_order',      'ext_filter_order', 	  'extras_label' );
		$ext_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'ext_filter_order_Dir',  'ext_filter_order_Dir', '' );		

		$ra_filter_order     = $mainframe->getUserStateFromRequest( $context.'ra_filter_order',      'ra_filter_order', 	  'res_name' );
		$ra_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'ra_filter_order_Dir',  'ra_filter_order_Dir', '' );		

		$sa_filter_order     = $mainframe->getUserStateFromRequest( $context.'sa_filter_order',      'sa_filter_order', 	  'res_name' );
		$sa_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'sa_filter_order_Dir',  'sa_filter_order_Dir', '' );		

		$uc_filter_order     = $mainframe->getUserStateFromRequest( $context.'uc_filter_order',      'uc_filter_order', 	  'user_id' );
		$uc_filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'uc_filter_order_Dir',  'uc_filter_order_Dir', '' );		

		// get filters
		$filter_user_search	= $mainframe->getUserStateFromRequest( $context.'filter_user_search', 'user_search', "");
		$filter_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_startdate', 'startdateFilter', date("Y-m-d"));
		$filter_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_enddate', 'enddateFilter', "");
		$filter_category	= $mainframe->getUserStateFromRequest( $context.'filter_category', 'categoryFilter', "0");
		$filter_request_resource	= $mainframe->getUserStateFromRequest( $context.'filter_request_resource', 'request_resourceFilter', "");
		$filter_request_status	= $mainframe->getUserStateFromRequest( $context.'filter_request_status', 'request_status', "");
		$filter_payment_status	= $mainframe->getUserStateFromRequest( $context.'filter_payment_status', 'payment_status', "");

		$filter_service_resource	= $mainframe->getUserStateFromRequest( $context.'filter_service_resource', 'service_resourceFilter', "");

		$filter_timeslots_resource	= $mainframe->getUserStateFromRequest( $context.'filter_timeslots_resource', 'timeslots_resourceFilter', "");
		$filter_day_number	= $mainframe->getUserStateFromRequest( $context.'filter_day_number', 'day_numberFilter', "1");

		$filter_bookoffs_resource	= $mainframe->getUserStateFromRequest( $context.'filter_bookoffs_resource', 'bookoffs_resourceFilter', "");
		$filter_book_dates_resource	= $mainframe->getUserStateFromRequest( $context.'filter_book_dates_resource', 'book_dates_resourceFilter', "");

		$filter_pp_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_pp_startdate', 'ppstartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_pp_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_pp_enddate', 'ppenddateFilter', "");

		$filter_an_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_an_startdate', 'anstartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_an_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_an_enddate', 'anenddateFilter', "");

		$filter_an_aim_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_an_aim_startdate', 'an_aimstartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_an_aim_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_an_aim_enddate', 'an_aimenddateFilter', "");

		$filter_goog_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_goog_startdate', 'googstartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_goog_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_goog_enddate', 'googenddateFilter', "");

		$filter_2co_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_2co_startdate', '_2costartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_2co_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_2co_enddate', '_2coenddateFilter', "");

		$filter_stripe_startdate	= $mainframe->getUserStateFromRequest( $context.'filter_stripe_startdate', 'stripestartdateFilter', date("Y-m-d", strtotime('first day of last month')));
		$filter_stripe_enddate	= $mainframe->getUserStateFromRequest( $context.'filter_stripe_enddate', 'stripeenddateFilter', "");

		$filter_resource_category	= $mainframe->getUserStateFromRequest( $context.'filter_resource_category', 'resource_categoryFilter', "");
		$filter_coupon_search	= $mainframe->getUserStateFromRequest( $context.'filter_coupon_search', 'coupon_search', "");

		$lists['order'] = "";  
		$lists['order_req'] = $req_filter_order;  
		$lists['order_Dir_req'] = $req_filter_order_Dir;

		$lists['order_res'] = $res_filter_order;  
		$lists['order_Dir_res'] = $res_filter_order_Dir;

		$lists['order_srv'] = $srv_filter_order;  
		$lists['order_Dir_srv'] = $srv_filter_order_Dir;

		$lists['order_ts'] = $ts_filter_order;  
		$lists['order_Dir_ts'] = $ts_filter_order_Dir;

		$lists['order_bo'] = $bo_filter_order;  
		$lists['order_Dir_bo'] = $bo_filter_order_Dir;

		$lists['order_bd'] = $bd_filter_order;  
		$lists['order_Dir_bd'] = $bd_filter_order_Dir;

		$lists['order_pp'] = $pp_filter_order;  
		$lists['order_Dir_pp'] = $pp_filter_order_Dir;

		$lists['order_an'] = $an_filter_order;  
		$lists['order_Dir_an'] = $an_filter_order_Dir;

		$lists['order_an_aim'] = $an_aim_filter_order;  
		$lists['order_Dir_an_aim'] = $an_aim_filter_order_Dir;

		$lists['order_goog'] = $goog_filter_order;  
		$lists['order_Dir_goog'] = $goog_filter_order_Dir;

		$lists['order_2co'] = $_2co_filter_order;  
		$lists['order_Dir_2co'] = $_2co_filter_order_Dir;

		$lists['order_stripe'] = $stripe_filter_order;  
		$lists['order_Dir_stripe'] = $stripe_filter_order_Dir;

		$lists['order_coup'] = $coup_filter_order;  
		$lists['order_Dir_coup'] = $coup_filter_order_Dir;

		$lists['order_ext'] = $ext_filter_order;  
		$lists['order_Dir_ext'] = $ext_filter_order_Dir;

		$lists['order_ra'] = $ra_filter_order;  
		$lists['order_Dir_ra'] = $ra_filter_order_Dir;

		$lists['order_sa'] = $sa_filter_order;  
		$lists['order_Dir_sa'] = $sa_filter_order_Dir;

		$lists['order_uc'] = $uc_filter_order;  
		$lists['order_Dir_uc'] = $uc_filter_order_Dir;

		$items			= $this->get('Data2');
		//print_r($items);

		$items_res		= $this->get('Data2', 'resources' );
		//print_r($items_res);
		//exit;

		$items_srv		= $this->get('Data2', 'services' );
		//print_r($items_srv);
		//exit;

		$items_ts		= $this->get('Data2', 'timeslots' );
		//print_r($items_ts);
		//exit;

		$items_bo		= $this->get('Data2', 'bookoffs' );
		//print_r($items_bo);
		//exit;

		$items_bd		= $this->get('Data2', 'book_dates' );
		//print_r($items_bd);
		//exit;

		$items_pp		= $this->get('Data', 'paypal_transactions' );
		//print_r($items_pp);
		//exit;

		$items_an		= $this->get('Data', 'authnet_transactions' );
		//print_r($items_an);
		//exit;

		$items_an_aim		= $this->get('Data', 'authnet_aim_transactions' );
		//print_r($items_an);
		//exit;

		$items_goog		= $this->get('Data', 'google_wallet_transactions' );
		//print_r($items_an);
		//exit;

		$items_stripe		= $this->get('Data', 'stripe_transactions' );
		//print_r($items_stripe);
		//exit;

		$items_2co		= $this->get('Data', '_2co_transactions' );
		//print_r($items_2co);
		//exit;

		$items_coup		= $this->get('Data2', 'coupons' );
		//print_r($items_coup);
		//exit;

		$items_ext		= $this->get('Data', 'extras' );
		//print_r($items_ext);
		//exit;

		$items_ra		= $this->get('Data', 'rate_adjustments' );
		//print_r($items_ra);
		//exit;

		$items_sa		= $this->get('Data', 'seat_adjustments' );
		//print_r($items_sa);
		//exit;

		$items_uc		= $this->get('Data2', 'user_credit' );
		//print_r($items_uc);
		//exit;

		$total			= $this->get('Total');
		$pagination = $this->get( 'Pagination' );

		$filter_resource  = $mainframe->getUserStateFromRequest( $context.'filter_resource', 'filter_resource', '');
		$filter_resource = $this->get('filter_resource');
		
		$user = JFactory::getUser();
		$frompage  = 'advadmin';
		$this->user_id = $user->id;
		$this->frompage = $frompage;

		$this->lists = 	$lists;    
		$this->items = 	$items; 		
		$this->pagination = $pagination;
		$this->request_url = $uri;
		$this->filter_user_search = $filter_user_search;
		$this->filter_startdate = $filter_startdate;
		$this->filter_enddate = $filter_enddate;
		$this->filter_category = $filter_category;
		$this->filter_request_resource = $filter_request_resource;
		$this->filter_request_status = $filter_request_status;
		$this->filter_payment_status = $filter_payment_status;
		$this->filter_resource_category = $filter_resource_category;

		$this->items_res = $items_res; 		

		$this->items_srv = $items_srv; 		
		$this->filter_service_resource = $filter_service_resource;

		$this->items_ts = $items_ts; 		
		$this->filter_timeslots_resource = $filter_timeslots_resource;
		$this->filter_day_number = $filter_day_number;
		
		$this->items_bo = $items_bo; 		
		$this->filter_bookoffs_resource = $filter_bookoffs_resource;

		$this->items_bd = $items_bd; 		
		$this->filter_book_dates_resource = $filter_book_dates_resource;

		$this->items_pp = $items_pp; 		
		$this->filter_pp_startdate = $filter_pp_startdate;
		$this->filter_pp_enddate = $filter_pp_enddate;

		$this->items_an = $items_an; 		
		$this->filter_an_startdate = $filter_an_startdate;
		$this->filter_an_enddate = $filter_an_enddate;

		$this->items_an_aim = $items_an_aim; 		
		$this->filter_an_aim_startdate = $filter_an_aim_startdate;
		$this->filter_an_aim_enddate = $filter_an_aim_enddate;

		$this->items_goog = $items_goog; 		
		$this->filter_goog_startdate = $filter_goog_startdate;
		$this->filter_goog_enddate = $filter_goog_enddate;

		$this->items_2co = $items_2co; 		
		$this->filter_2co_startdate = $filter_2co_startdate;
		$this->filter_2co_enddate = $filter_2co_enddate;

		$this->items_stripe = $items_stripe; 		
		$this->filter_stripe_startdate = $filter_stripe_startdate;
		$this->filter_stripe_enddate = $filter_stripe_enddate;

		$this->items_coup = $items_coup; 		
		$this->filter_coupon_search = $filter_coupon_search;

		$this->items_ext = $items_ext; 		

		$this->items_ra = $items_ra; 		
		$this->items_sa = $items_sa; 		
		$this->items_uc = $items_uc; 		

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
