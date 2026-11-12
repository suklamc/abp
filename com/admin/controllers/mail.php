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
 
class mailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'copy', 'copy_messages' );
		
	}

	function cancel($key=null)
	{
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel' );
	}	

	/**
	 * Method display
	 * 
	 * 1) create a classVIEWclass(VIEW) and a classMODELclass(Model)
	 * 2) pass MODEL into VIEW
	 * 3)	load template and render it  	  	 	 
	 */

	function display($cachable=false, $urlparams=false) {
		parent::display();
		
		require_once JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'rsappt_pro3.php';
		rsappt_pro3Helper::addSubmenu('mail');
		
	}

	function copy_messages(){
		
		$jinput = JFactory::getApplication()->input;
		$id	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (sv_count_($id )){
			$database = JFactory::getDBO();
			$msg = "";
			// first get source rows
			$ids = implode( ',', $id );
			$query = 'SELECT * FROM #__sv_apptpro3_mail '
				. ' WHERE id_mail IN ( '.$ids.' )';
			try{
				$database->setQuery( $query );
				$rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_mail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}
			foreach($rows as $row) {
				$sql = "INSERT INTO #__sv_apptpro3_mail (".
					"mail_label,booking_succeeded,booking_succeeded_admin,booking_succeeded_sms,booking_in_progress,".
					"booking_in_progress_admin,booking_in_progress_sms,booking_cancel,booking_cancel_sms,booking_too_close_to_cancel,".
					"booking_reminder,booking_reminder_sms,attach_ics_resource,attach_ics_admin,attach_ics_customer,thank_you_msg,".
					"send_on_status,rebook_msg)".
				" VALUES(".
					"'".$row->mail_label."(copy)','".
					$database->escape($row->booking_succeeded)."','".
					$database->escape($row->booking_succeeded_admin)."','".
					$database->escape($row->booking_succeeded_sms)."','".
					$database->escape($row->booking_in_progress)."','".
					$database->escape($row->booking_in_progress_admin)."','".
					$database->escape($row->booking_in_progress_sms)."','".
					$database->escape($row->booking_cancel)."','".
					$database->escape($row->booking_cancel_sms)."','".
					$database->escape($row->booking_too_close_to_cancel)."','".
					$database->escape($row->booking_reminder)."','".
					$database->escape($row->booking_reminder_sms)."','".
					$database->escape($row->attach_ics_resource)."','".
					$database->escape($row->attach_ics_admin)."','".
					$database->escape($row->attach_ics_customer)."','".
					$database->escape($row->thank_you_msg)."','".
					$database->escape($row->send_on_status)."','".
					$database->escape($row->rebook_msg)."'".
					")";
				try{
					$database->setQuery( $sql );
					$database->execute();
					$msg = JText::_('RS1_ADMIN_TOOLBAR_MESSAGE_COPY_OK');					
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_mail", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}
				
			}
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=mail', $msg );
		}

	}

}	
?>

