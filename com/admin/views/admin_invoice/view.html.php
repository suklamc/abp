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
class admin_invoiceViewadmin_invoice extends JViewLegacy
{
	function __construct( $config = array())
	{
		global $context;
	  	$mainframe = JFactory::getApplication();
 
 	 parent::__construct( $config );
	}

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
	
	  	$mainframe = JFactory::getApplication();

		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - Invoicing') );

		$uri 	= JUri::getInstance();
		$user 	= JFactory::getUser();

		// Set toolbar items for the page
		JToolBarHelper::title( 'ABPro - '.JText::_( 'RS1_ADMIN_TOOLBAR_ADMIN_INVOICE' ));
		//JToolBarHelper::custom('create_and_send', 'save', 'foo', JText::_('RS1_ADMIN_TOOLBAR_ADMIN_INVOICE_CREATE_AND_SEND'), false);
		JToolBarHelper::custom('create_only', 'save', '', JText::_('RS1_ADMIN_TOOLBAR_ADMIN_INVOICE_CREATE_ONLY'), false);
		JToolBarHelper::cancel();

		$uri = $uri->toString();
		$this->admin_invoice_url = $uri;


		parent::display($tpl);
	}
	
}	

?>
