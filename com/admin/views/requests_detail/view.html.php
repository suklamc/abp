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
class requests_detailViewrequests_detail extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
	  	$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;

		$document = JFactory::getDocument();
		$document->setTitle( JText::_('Appointment Booking Pro - Appointments List') );

		$uri 	= JUri::getInstance();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();

		$frompage = $jinput->getString('frompage', '');
		$frompage_item = $jinput->getString('frompage_item', '');
		$credit_type = $jinput->getString('type', '');
    	//DEVNOTE: let's be the template 'form.php' instead of 'default.php' 
		$this->setLayout('form');

    	//DEVNOTE: prepare array 
		$lists = array();


		//get the data
		$detail	= $this->get('data');
		//print_r($detail);
		
    	//DEVNOTE: the new record ?  Edit or Create?
		$isNew		= ($detail->id_requests < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'THE DETAIL' ), $detail->descript );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Set toolbar items for the page
		if($frompage != ""){
			$text = JText::_( 'VIEW' );
		} else {
			$text = $isNew ? JText::_( 'NEW' ) : JText::_( 'EDIT' );
		}
		JToolBarHelper::title( 'ABPro - '.JText::_( 'RS1_ADMIN_TOOLBAR_APPOINTMENTS_DETAIL' ), 'addedit'  );
		if($frompage != ""){
			// for existing items the button is renamed `close`
			JToolBarHelper::divider();
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::save();
			if ($isNew)  {
				JToolBarHelper::divider();
				JToolBarHelper::cancel();
			} else {
				// for existing items the button is renamed `close`
				JToolBarHelper::divider();
				JToolBarHelper::cancel( 'cancel', 'Close' );
			}
			JToolBarHelper::divider();		
			JToolBarHelper::help('ABPRO2_HELP_REQUEST_EDIT', true);
		}



		// Edit or Create?
		if (!$isNew){
			$model->checkout( $user->get('id') );
		} else{
			// initialise new record
			$detail->published = 1;
			$detail->ordering 	= 0;
		}

		// build the html select list
		$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $detail->published );


		//DEVNOTE: Clear HTML data
		//         jimport('joomla.filter.output') -> jimport('joomla.filter.filteroutput')
		//         JOutputFilter::objectHTMLSafe ->/JFilterOutput::objectHTMLSafe 
		jimport('joomla.filter.filteroutput');	
		JFilterOutput::objectHTMLSafe( $detail, ENT_QUOTES );			

		$this->lists = $lists;
		$this->detail = $detail;
		$uri = $uri->toString();
		$this->request_url = $uri;

		$this->frompage = $frompage;
		$this->frompage_item = $frompage_item;

		$this->credit_type = $credit_type;

		parent::display($tpl);
	}
	
}	

?>
