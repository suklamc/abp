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

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' ); 


class front_desk_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks	
		$this->registerTask( 'request_detail', 'go_request_detail' );
		$this->registerTask( 'save_request_detail', 'save_request_detail' );

	}

	function go_request_detail()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'requests_detail' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'listpage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}



	function save_request_detail()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model = new front_desk_detailModelrequests_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item, $msg ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item, $msg );
		}
	}

	
	
	function cancel($key=null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		
		// Checkin the detail
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model = new front_desk_detailModelrequests_detail;

		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}

	}	



}

