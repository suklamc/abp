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
		$jinput = JFactory::getApplication()->input;

		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - Invoicing') );

		$frompage = $jinput->getString('frompage', '');
		$frompage_item = $jinput->getString('Item_id', '');

		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();

		$uri = $uri->toString();
		$this->assignRef('admin_invoice_url',	$uri);
		$this->frompage = $frompage;
		$this->frompage_item = $frompage_item;


		parent::display($tpl);
	}
	
}	

?>
