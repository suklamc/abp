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


class stripe_transactions_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'view_txn', 'view_txn' );
		
	}

	function edit($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'stripe_transactions_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 1);


		parent::display();

		// Checkin the stripe_transactions
		$model = $this->getModel('stripe_transactions_detail');
		$model->checkout();
	}
      
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];

		$model = $this->getModel('stripe_transactions_detail');
	
		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=stripe_transactions',$msg );
	}

	function remove()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;
		$cid = $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		$model = $this->getModel('stripe_transactions_detail');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=stripe_transactions' );
		}
	}
	
	
	
	function cancel($key=null)
	{
		$jinput = JFactory::getApplication()->input;
		// Checkin the detail
		$model = $this->getModel('stripe_transactions_detail');
		$model->checkin();
		if($jinput->getString('frompage') == 'requests'){
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=requests');
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=stripe_transactions',$msg );
		}
	}	


	function view_txn()
	{
		$jinput = JFactory::getApplication()->input;
		// get id from txnid
		$database = JFactory::getDBO();
		$sql = "SELECT id_stripe_transactions FROM #__sv_apptpro3_stripe_transactions WHERE stripe_txn_id = '". $jinput->getString('txnid')."'";
		//echo $sql;
		$database->setQuery($sql);
		$id_to_view = $database->loadResult();
		$jinput->set( 'view', 'stripe_transactions_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'cid', array($id_to_view,null));
		$jinput->set( 'frompage', 'requests');


		parent::display();

	}

}

