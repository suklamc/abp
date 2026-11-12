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


class payment_processorsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		require_once JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'rsappt_pro3.php';
		rsappt_pro3Helper::addSubmenu('payment_processors');
				
	}


	function cancel($key=null)
	{
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel' );
	}
      
	/** function save
	*
	* Save the selected item specified by id
	* @return set Redirection
	*/
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		
		// Save General tab to Config, then save each processor tabe to its own table.
		
		$filter = JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$jinput = new JInput( null, array('filter' => $filter) );
		$post = $jinput->post->getArray();
		
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];

		$data = $post;

		$database = JFactory::getDBO();

		$query = "UPDATE #__sv_apptpro3_config ".
		"SET ".
		"additional_fee='".$database->escape($data['additional_fee'])."', ".
		"fee_rate='".$database->escape($data['fee_rate'])."', ".
		"enable_coupons='".$database->escape($data['enable_coupons'])."', ".
		"purge_stale_paypal='".$database->escape($data['purge_stale_paypal'])."', ".
		"minutes_to_stale='".$database->escape($data['minutes_to_stale'])."', ".
		"non_pay_booking_button='".$database->escape($data['non_pay_booking_button'])."', ".		
		"non_pay_btn_group='".$database->escape($data['non_pay_btn_group'])."' ".		
		" WHERE id_config = 1";
		try{
			$database->setQuery($query);
			$database->execute();
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_pay_proc", "", "");
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$e->getMessage();
		}


		// get payment processor list
		$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1;';
		try{
			$database->setQuery($sql);
			$pay_procs = NULL;
			$pay_procs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
				
		$pay_proc_columns = null;
		foreach($pay_procs as $pay_proc){ 

			// skip Payage if not installed.
			if($pay_proc->processor_name == "Payage"){
				if (!JComponentHelper::getComponent('Payage', true)->enabled){
					continue;
				}
			}
			
			// get columns for processor
			$sql = "show columns from #__sv_apptpro3_".$pay_proc->config_table;
			try{
				$database->setQuery($sql);
				$pay_proc_columns = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_pay_proc", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			}	
			$sql = "UPDATE #__sv_apptpro3_".$pay_proc->config_table." ".
			"SET ";
			for($i=1;$i<sv_count_($pay_proc_columns);$i++){
				$fieldname = $pay_proc_columns[$i]->Field;
				$sql .= " ".$fieldname."='".$database->escape($data[$fieldname])."'";
				if($i<(sv_count_($pay_proc_columns))-1){
					$sql = $sql.", ";
				}
			}
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_pay_proc", "", "");
				$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$e->getMessage();
			}	
		}

		if($apply=="yes"){
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=payment_processors',$msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel',$msg );
		}
	}

	function apply()
	{
		$this->save("yes");
	}

}

