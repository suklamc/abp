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
 
class book_datesViewbook_dates extends JViewLegacy
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
	 $context = 'book_dates.list.';
 
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
	 	//DEVNOTE: we need these 2 globals			 
		global $context;
	  	$mainframe = JFactory::getApplication();
		
		//DEVNOTE: set document title
		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - Book Dates') );
   
   
   		//DEVNOTE: Set ToolBar title
	    JToolBarHelper::title( 'Appointment Booking Pro - '.JText::_( 'RS1_ADMIN_TOOLBAR_BOOKDATES' ), 'book_dates' );
    
    	//DEVNOTE: Set toolbar items for the page
		JToolBarHelper::addNew('add', JText::_('RS1_ADMIN_TOOLBAR_BOOKDATES_NEW'));
		JToolBarHelper::editList('edit', JText::_('RS1_ADMIN_TOOLBAR_BOOKDATES_EDIT'));
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::divider();
		JToolBarHelper::deleteList( JText::_('RS1_ADMIN_TOOLBAR_BOOKDATES_DEL_CONF'), 'remove', JText::_('RS1_ADMIN_TOOLBAR_BOOKDATES_DEL') );
		JToolBarHelper::custom('purge_old_dates', 'remove', '', JText::_('RS1_ADMIN_TOOLBAR_BOOKDATES_PURGE'), false);
		JToolBarHelper::divider();
		JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');

	    //DEVNOTE: Set ToolBar title
		$uri	= JUri::getInstance();
		
		//DEVNOTE:give me ordering from request
		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'book_date' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );		

		// get resource to show book_dates for
		$filter_resource  = $mainframe->getUserStateFromRequest( $context.'filter_resource', 'filter_resource', '');

		//DEVNOTE:remember the actual order and column  
		$lists['order'] = $filter_order;  
		$lists['order_Dir'] = $filter_order_Dir;
  	
		//DEVNOTE:Get data from the model
		$items			= $this->get('Data2');
		//print_r($items);
		$total			= $this->get('Total');
		//print_r($total);
		$pagination = $this->get( 'Pagination' );
		
		$filter_resource = $this->get('filter_resource');

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_resource", $filter_resource);

				
		$user = JFactory::getUser();	
		$this->user = $user;		
		$this->lists = $lists;    
		$this->items = $items; 		
		$this->pagination =	$pagination;
		$uri = $uri->toString();
		$this->request_url = $uri;

		$this->filter_resource = $filter_resource;
	
		//DEVNOTE:call parent display
    parent::display($tpl);
  }
}

?>
