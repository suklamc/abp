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


class config_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		require_once JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'rsappt_pro3.php';
		rsappt_pro3Helper::addSubmenu('config');
		
		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'apply', 'apply' );
		
	}

	/** function edit
	*
	* Create a new item or edit existing item 
	* 
	* 1) set a custom VIEW layout to 'form'  
	* so expecting path is : [componentpath]/views/[$controller->_name]/'form.php';			
    * 2) show the view
    * 3) get(create) MODEL and checkout item
	*/
	function edit($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'config_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

		// Checkin the config
		$model = $this->getModel('config_detail');
		$model->checkout();
	}
      
	/** function save
	*
	* Save the selected item specified by id
	* and set Redirection to the list of items	
	* 		
	* @param int id - keyvalue of the item
	* @return set Redirection
	*/
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
	
		$filter = JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$jinput = new JInput( null, array('filter' => $filter) );
		$post = $jinput->post->getArray();

		$model = $this->getModel('config_detail');
	
		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel',$msg );
	}


	function apply()
	{
	
		$filter = JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$jinput = new JInput( null, array('filter' => $filter) );
		$post = $jinput->post->getArray();
		
		$model = $this->getModel('config_detail');
	
		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=config_detail&task=edit&cid[]=1',$msg );
	}

	
	/** function cancel
	*
	* Check in the selected detail 
	* and set Redirection to the list of items	
	* 		
	* @return set Redirection
	*/
	function cancel($key=null)
	{
		// Checkin the detail
		$model = $this->getModel('config_detail');
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel',$msg );
	}	

}

