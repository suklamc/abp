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


class adv_admin_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$user = JFactory::getUser();		
		if($user->guest){
			$this->setRedirect( 'index.php', JText::_('RS1_FRONT_END_ACCESS_ERROR'), 'warning');
		} else{
			$database = JFactory::getDBO();
			// check to see id user is an admin		
			$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
				"resource_admins LIKE '%|".$user->id."|%';";
			try{
				$database->setQuery($sql);
				$check = NULL;
				$check = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "controller blocker", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			if($check->count == 0){
				$this->setRedirect( 'index.php', JText::_('RS1_FRONT_END_ACCESS_ERROR'), 'warning');
			}	
		}

		
	}

	function edit($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'requests_detail' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'listpage', 'advadmin');
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}
      
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();

		$cid = $jinput->post->get('cid', array(), 'ARRAY');
		$post['id'] = $cid[0];
		$frompage = $jinput->getString('frompage');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model = new admin_detailModelrequests_detail;
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
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=admin',$msg );
	}

	function remove()
	{
		global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		$model = $this->getModel('admin_detail');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=admin' );
		}
	}
	
	
	
	function cancel($key=null)
	{
		$jinput = JFactory::getApplication()->input;
		// Checkin the detail
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model = new admin_detailModelrequests_detail;

		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=admin',$msg );
	}	

	

}

