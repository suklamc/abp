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
 
class advadminController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'printer', 'printer' );

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

	function list_bookings()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'advadmin' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

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
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=advadmin',$msg );
	}	

	function printer(){
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'advadmin' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'layout', 'default_prt');
		$jinput->set( 'tmpl', 'component');

		parent::display();
	}

}

?>

