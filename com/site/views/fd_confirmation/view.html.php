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
class front_deskViewfd_confirmation extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
	
	  	$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;

		$uri = JUri::getInstance()->toString();
		$user = JFactory::getUser();
		
		$this->user = $user;	
		$this->request_url = $uri;


		$frompage = $jinput->getString('frompage', '');
		$frompage_item = $jinput->getString('Item_id', '');


		parent::display($tpl);
	}
	
}	

?>
