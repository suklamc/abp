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


class sv_payageController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		$this->registerTask( 'view_txn', 'view_txn' );
					
	}


	function view_txn()
	{	
		// Need Payage payments table row id to open the detail screen.
		$jinput = JFactory::getApplication()->input;
		$txnid = $jinput->getString('txnid');
		$database = JFactory::getDBO();
		$sql = "SELECT id FROM  #__payage_payments ".
				" WHERE pg_transaction_id = '".$txnid."'";
		//echo $sql;
		//exit;
		try{
			$database->setQuery($sql);
			$payage_id = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_sv_payage", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		
		$this->setRedirect( 'index.php?option=com_payage&task=detail&controller=payment&cid[]='.$payage_id );
	}

}


