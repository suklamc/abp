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


/**
 * rsappt_pro3  Controller
 */
 
class purchase_ucController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		$this->registerTask( 'purchase_uc', 'purchase_uc' );
		$this->registerTask( 'pp_return', 'pp_return' );
		$this->registerTask( 'pp_ipn', 'pp_ipn' );
		
	}

	
	function purchase_uc()
	{
		$jinput = JFactory::getApplication()->input;
		$pid = $jinput->getInt('product_id');
		$uid = $jinput->getInt('uid');
		$frompage = $jinput->getString('frompage');
		
		$database =JFactory::getDBO(); 
		$pay_proc_enabled = isPayProcEnabled();
		$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1;';
		try{
			$database->setQuery($sql);
			$pay_procs = NULL;
			$pay_procs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "gad_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}	
		foreach($pay_procs as $pay_proc){ 
			// get settings 
			$prefix = $pay_proc->prefix;
			$sql = "SELECT * FROM #__sv_apptpro3_".$pay_proc->config_table;
			try{
				$database->setQuery($sql);
				$pay_proc_settings = NULL;
				$pay_proc_settings = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}
			$enable = $prefix."_enable";
			if($pay_proc_settings->$enable == "Yes"){
				if($pay_proc->processor_name != "PayPal"){
					echo "Currently only PayPal is supported for purchase of credits.";
				} else {
					include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR.$pay_proc->prefix.DIRECTORY_SEPARATOR.$pay_proc->prefix."_purchase_uc.php";
				}
			}
		}
		
	}	

	function pp_return(){
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'payment_return_msg', JText::_('RS1_UC_PURCHASE_COMPLETE') );

		parent::display();
		
	}
	
	function pp_ipn(){
		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR."paypal".DIRECTORY_SEPARATOR."paypal_purchase_uc_ipn.php";		
	}
}

?>

