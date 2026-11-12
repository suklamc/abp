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
 
class backup_restoreViewbackup_restore extends JViewLegacy
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
	 $context = 'backup_restore.edit.';
 
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
		
		//DEVNOTE: set document title
		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - backup/restore') );
   
   
   		//DEVNOTE: Set ToolBar title
	    JToolBarHelper::title( 'Appointment Booking Pro - '.JText::_('RS1_ADMIN_TOOLBAR_BACKUP'), 'backup.png' );
    
    	//DEVNOTE: Set toolbar items for the page
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();
		JToolBarHelper::help('ABPRO2_HELP_BACKUP', true);

	    //DEVNOTE: Set ToolBar title
		$uri	= JUri::getInstance();
		
		
		//DEVNOTE:save a reference into view	
		$user = JFactory::getUser();
		$this->user = $user;	
	
		$uri = $uri->toString();
		$this->request_url = $uri;


		//DEVNOTE:call parent display
    	parent::display($tpl);
  }
}

?>
