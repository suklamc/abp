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
class payment_transactionsViewpayment_transactions extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
	
	  	$mainframe = JFactory::getApplication();

		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - Payment Transactions') );

		$uri 	= JUri::getInstance();
		$user 	= JFactory::getUser();

		$this->setLayout('form');

    	//DEVNOTE: prepare array 
		$lists = array();

		// Set toolbar items for the page
		JToolBarHelper::title( 'ABPro - '.JText::_( 'RS1_ADMIN_TOOLBAR_PAYTRANS' ), 'pay_trans'  );
		JToolBarHelper::divider();
		JToolBarHelper::cancel( 'cancel', 'JTOOLBAR_CLOSE' );

		$uri = $uri->toString();
		$this->request_url = $uri;


		parent::display($tpl);
	}
	
}	

?>
