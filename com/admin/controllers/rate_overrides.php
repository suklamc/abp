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
 
class rate_overridesController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'ajax_get_override_entity_ids', 'ajax_get_override_entity_ids' );

	}
	/**
	 * Cancel operation
	 * redirect the application to the begining - index.php  	 
	 */
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
		rsappt_pro3Helper::addSubmenu('rate_overrides');
		
	}
	
	function ajax_get_override_entity_ids()
	{
//		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		$jinput = JFactory::getApplication()->input;
		$entity_type = $jinput->getString( 'etype', '' );
		
		$entityArrayString = "";
		
		$database =JFactory::getDBO(); 
		$entity_list = null;
		switch($entity_type){
			case "resource":
				$sql = 'SELECT id_resources as id,name FROM #__sv_apptpro3_resources WHERE published = 1';
				try{
					$database->setQuery($sql);
					$entity_list = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_rate_override", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
					jExit();
				}		
				break;
			case "service":
				// 4.0.5 added resource_scope to replace resource_id for multi-resoutce services
				$sql = 'SELECT id_services as id, #__sv_apptpro3_services.name '.
					' FROM #__sv_apptpro3_services '.
					' WHERE #__sv_apptpro3_services.published = 1 '.
					' ORDER BY #__sv_apptpro3_services.name';	
//				$sql = 'SELECT id_services as id, CONCAT(#__sv_apptpro3_services.name, " (", #__sv_apptpro3_resources.name, ")") as name '.
//					' FROM #__sv_apptpro3_services INNER JOIN #__sv_apptpro3_resources '.
//					' ON #__sv_apptpro3_services.resource_id = #__sv_apptpro3_resources.id_resources'.
//					' WHERE #__sv_apptpro3_services.published = 1 AND #__sv_apptpro3_resources.published =1 '.
//					' ORDER BY #__sv_apptpro3_services.name';	
				try{			
					$database->setQuery($sql);
					$entity_list = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_rate_override", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
					jExit();
				}		
				break;
			case "extra":
				$sql = 'SELECT id_extras as id, extras_label as name FROM #__sv_apptpro3_extras WHERE published = 1';
				try{
					$database->setQuery($sql);
					$entity_list = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_rate_override", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
					jExit();
				}		
				break;
			case "seat":
				$sql = 'SELECT id_seat_types as id, seat_type_label as name FROM #__sv_apptpro3_seat_types WHERE published = 1';
				try{
					$database->setQuery($sql);
					$entity_list = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_rate_override", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
					jExit();
				}		
				break;
		}
		
		//echo json_encode(sv_count_($entity_list));
		//jExit();

		for($i=0; $i<sv_count_($entity_list); $i++){
			$entityArrayString = $entityArrayString.$entity_list[$i]->id.":".$entity_list[$i]->name;
			if($i<sv_count_($entity_list)-1){
				$entityArrayString = $entityArrayString.",";
			}
		}
		
		echo json_encode($entityArrayString);
		jExit();
	}
	
}	
?>

