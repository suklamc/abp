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
 
class booking_screen_simpleViewbooking_screen_simple extends JViewLegacy
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
	 $context = 'simple_booking_screen.';
 
 	 parent::__construct( $config );
	}
 

   
	function display($tpl = null)
	{
		global $context;
		
		$uri = JUri::getInstance()->toString();
		$user = JFactory::getUser();
		
		$this->user = $user;	
		$this->request_url = $uri;

		$frompage  = 'simple_booking_screen';
		$this->frompage = $frompage;

		$jinput = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams('com_rsappt_pro3');
		$view_to_use = $params->get('layout', $jinput->getInt( 'layout', '' )); // so the view can be set via querystring for popup caller and iframes

		//echo $agent;
		$device = "";
		$layout = null;

		$appWeb      = JFactory::getApplication();
		$layout = ($appWeb->client->mobile ? 'mobile' : null);		
		$agent = $appWeb->client->userAgent;
		$this->agent = $agent;
		if($layout == "mobile"){
			$device = "mobile";

			// get config stuff
			$database =JFactory::getDBO(); 
			$sql = 'SELECT * FROM #__sv_apptpro3_config';
			try{
				$database->setQuery($sql);
				$apptpro_config = NULL;
				$apptpro_config = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "gad_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			if($apptpro_config->mobile_show_simple == "Yes"){
				$layout = 'mobile_simple';
			}
			if($view_to_use == 1){
				$layout = 'accordion';
			}
		} else if($view_to_use == 1){
			$layout = 'accordion';
		}


		/* 
			There is no guaranteed way to detect the device from the user agent. In general if the agent contains:
				'Android' and 'Mobile' = phone
				'Android' only = tablet
			Note: This is not universal among manufactures, some use Android Mobile for tablets ;-(
			Also, iPad reports as 'mobile'.
		*/

		// if you want iPad to display a simple booking screen, comment out the line below.
		if(strpos($agent, "iPad") !== false ){ 
			$device = "iPad";
			}

		// if you want Android tablets to display a desktop booking screen, comment out the line below.
		if(strpos($agent, "Android") !== false && strpos($agent, "Mobile") === false ){ 
			$device = "tablet";
			if($view_to_use == 1){
				$layout = 'accordion';
			}
		}
		// dev only hard code mobile view
		//$device = "tablet";
		//$layout = 'mobile_simple';
		//$layout = 'mobile';
		//$layout = 'mobile_accordion';
		//$layout = 'fb';
		//$device = "mobile";

		$this->device = $device;
		$this->layout = $layout;
		
	   	parent::display(null);  }
}

?>
