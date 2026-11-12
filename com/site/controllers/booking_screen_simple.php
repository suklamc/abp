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

	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' );
//

/**
 * rsappt_pro3  Controller
 */
 
class booking_screen_simpleController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// if you want to force redirect to the login screen uncomment the following
//		$user = JFactory::getUser();
//		if($user->guest){
//			$return = JURI::getInstance()->toString();
//			$url    = 'index.php?option=com_users&view=login';
//			$url   .= '&return='.base64_encode($return);
//			$this->setRedirect($url, 'You must login first');
//		}
		
		// Register Extra tasks	
		$this->registerTask( 'process_booking_request', 'process_booking_request' );
		$this->registerTask( 'show_confirmation', 'show_confirmation' );
		$this->registerTask( 'show_in_progress', 'show_in_progress' );
		$this->registerTask( 'pp_return', 'pp_return' );
		$this->registerTask( 'pp_return_cart', 'pp_return_cart' );
		$this->registerTask( 'authnet_return', 'authnet_return' );
		$this->registerTask( 'authnet_return_cart', 'authnet_return_cart' );
		$this->registerTask( 'twoco_return', 'twoco_return' );
		$this->registerTask( 'twoco_return_cart', 'twoco_return_cart' );

	}


	function cancel($key=null)
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function returntocalendar($key=null)
	{
		$jinput = JFactory::getApplication()->input;
		$frompage_item = $jinput->getString('frompage_item');
		$link = 'index.php?option=com_rsappt_pro3&view=calendar_view&Itemid='.$frompage_item;
//		$config = JFactory::getConfig();
//		$seo = $config->get( 'sef' );
//		if($seo == "1"){		
//			$this->setRedirect( JRoute::_( $link ));
//		} else {
			$this->setRedirect( $link );
//		}
	}	

	function process_booking_request(){
		$booking_screen = "booking_screen_simple";
		
		include_once( JPATH_SITE."/components/com_rsappt_pro3/controllers/process_booking_request.php" );
	}


	function show_confirmation()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_confirmation' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));
		$jinput->set( 'which_message', 'confirmation');
		$cc = $jinput->getString( 'cc', '' );
		$jinput->set( 'cc', $cc);
		if($cc==""){
			echo "No Access (1)";
			exit;
		}

		parent::display();
	}

	function show_in_progress()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_confirmation' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));
		$jinput->set( 'which_message', 'in_progress');
		$cc = $jinput->getString( 'cc', '' );
		$jinput->set( 'cc', $cc);
		if($cc==""){
			echo "No Access (1)";
			exit;
		}

		parent::display();
	}


	function pp_return(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_paypal_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));

		parent::display();
	}

	function pp_return_cart(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_paypal_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));
		$jinput->set( 'cart', 'yes');

		parent::display();
	}


	function authnet_return(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_authnet_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));

		parent::display();
	}

	function authnet_return_cart(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_authnet_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));
		$jinput->set( 'cart', 'yes');

		parent::display();
	}

	function twoco_return(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_2co_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));

		parent::display();
	}

	function twoco_return_cart(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'sb_2co_return' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getInt( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getInt( 'req_id'));
		$jinput->set( 'cart', 'yes');

		parent::display();
	}

}
?>

