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
class admin_detailViewresources_detail extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
	
		global $context;
	  	$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;

		$uri = JUri::getInstance()->toString();
		$user = JFactory::getUser();

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'resources_detail.php');
		$model = new admin_detailModelresources_detail;

		$frompage = $jinput->getString('frompage', '');			
		$frompage_item = $jinput->getString('Item_id', '');		
		$fromtab = $jinput->getString('tab');

    	//DEVNOTE: let's be the template 'form.php' instead of 'default.php' 
		$this->setLayout('form');

    	//DEVNOTE: prepare array 
		$lists = array();


		//get the data
		$detail	= $this->get('data');
		//print_r($detail);
		
    	//DEVNOTE: the new record ?  Edit or Create?
		$isNew		= ($detail->id_resources < 1);

		// fail if checked out not by 'me'
		$lock_msg = "";
		if ($model->isCheckedOut( $user->get('id') )) {
			// get name of res-admin who has it locked
			$lock_msg = JText::_('RS1_LOCKED')." (".$model->checkedOutBy().") ";
		} else {
			// Edit or Create?
			if (!$isNew){
				$model->checkout( $user->get('id') );
			} else{
				// initialise new record
				$detail->published = 1;
				$detail->ordering 	= 0;
			}
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
		$this->request_url = $uri;
		$this->user_id = $user->id;
		$this->frompage = $frompage;
		$this->frompage_item = $frompage_item;
		$this->fromtab = $fromtab;
		$this->lock_msg = $lock_msg;

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
