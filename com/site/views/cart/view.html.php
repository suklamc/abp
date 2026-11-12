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

jimport( 'joomla.application.component.view' );


class cartViewcart extends JViewLegacy {

	function __construct( $config = array())
	{
	 /** set up global variable for sorting etc.
	  * $context is used in VIEW abd in MODEL
	  **/	  
	 
 	 global $context;
	 $context = 'cart.view.';
 
 	 parent::__construct( $config );
	}
 

	function display($tpl = null)
	{
		global $context;
	  	$mainframe = JFactory::getApplication();
		
		$document = JFactory::getDocument();
   
		$uri = JUri::getInstance()->toString();
		$user = JFactory::getUser();
		
		$this->user = $user;	
		$this->request_url = $uri;

		$layout = null;
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