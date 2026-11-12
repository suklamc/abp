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


class services_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'save2new', 'save2new' );		
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
		$jinput->set( 'view', 'services_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 1);


		parent::display();

		// Checkin the services
		$model = $this->getModel('services_detail');
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
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];

		$model = $this->getModel('services_detail');
	
		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		if($key=="2new"){
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services_detail&task=edit&cid[]=-1',$msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services',$msg );
		}
		
	}

	function save2new(){
		$this->save("2new");
	}

	/** function remove
	*
	* Delete all items specified by array cid
	* and set Redirection to the list of items	
	* 		
	* @param array cid - array of id
	* @return set Redirection
	*/
	function remove()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid = $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		$model = $this->getModel('services_detail');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services' );
		}
	}
	
	
	/** function publish
	*
	* Publish all items specified by array cid
	* and set Redirection to the list of items	
	* 		
	* @param array cid - array of id
	* @return set Redirection
	*/
	function publish()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to publish' ), error);

		}

		$model = $this->getModel('services_detail');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services');
		}
	}

	/** function unpublish
	*
	* Unpublish all items specified by array cid
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function unpublish()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to unpublish' ), error);

		}

		$model = $this->getModel('services_detail');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services');
		}
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
		$model = $this->getModel('services_detail');
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services',$msg );
	}	

	/** function orderup
	*
	* change order up
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function orderup()
	{
		$model = $this->getModel('services_detail');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services');
	}


	/** function orderdown
	*
	* change order down
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function orderdown()
	{
		$model = $this->getModel('services_detail');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services');
	}

	/** function saveorder
	*
	* saveorder of the services items
	* 			
	* @param array cid		- array of id
	* @param array order	- array of order 
	* @return set Redirection
	*/
	function saveorder()
	{
		$jinput = JFactory::getApplication()->input;
		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$order 	= $jinput->get( 'order', array(0), 'post', 'array' );

		$model = $this->getModel('services_detail');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=services', $msg );
	}
	

}

