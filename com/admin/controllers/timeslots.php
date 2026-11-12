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
 
class timeslotsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'copy', 'copy_timeslots' );
		$this->registerTask( 'docopy_timeslots', 'do_copy_timeslots' );
		
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
		rsappt_pro3Helper::addSubmenu('timeslots');
		
	}
	
	
	function copy_timeslots(){

		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$jinput->set( 'view', 'timeslots_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'timeslots_tocopy', implode(',', $cid));

		parent::display();

	}

	function do_copy_timeslots(){
		$jinput = JFactory::getApplication()->input;
		$cids = $jinput->get( 'timeslots_tocopy', array(0), 'post', 'array' );
		$new_resource_id = $jinput->getInt('dest_resource_id');

		$database = JFactory::getDBO();
		// first get source rows
		$query = 'SELECT * FROM #__sv_apptpro3_timeslots '
			. ' WHERE id_timeslots IN ( '.$cids.' )';
		try{	
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_tiemslots", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}

		// make an array of days
		$i = 0;
		$daylist[] = null;
		if($jinput->getWord('chkSunday') == "on"){
			$daylist[$i] = 0;
			$i++;  
		}
		if($jinput->getWord('chkMonday') == "on"){
			$daylist[$i] = 1;
			$i++;  
		}
		if($jinput->getWord('chkTuesday') == "on"){
			$daylist[$i] = 2;
			$i++;  
		}
		if($jinput->getWord('chkWednesday') == "on"){
			$daylist[$i] = 3;
			$i++;  
		}
		if($jinput->getWord('chkThursday') == "on"){
			$daylist[$i] = 4;
			$i++;  
		}
		if($jinput->getWord('chkFriday') == "on"){
			$daylist[$i] = 5;
			$i++;  
		}
		if($jinput->getWord('chkSaturday') == "on"){
			$daylist[$i] = 6;
			$i++;  
		}
		if($i==0){
			// no days selected 
			echo "<script> alert('No Days Selected'); window.history.go(-1);</script>\n";
			exit();
		}

		//now do inserts
		foreach($rows as $row) {
			for($x=0; $x<$i; $x++){
				if($jinput->getString("new_start_publishing", "") != ""){
					$start_pub = $jinput->getString("new_start_publishing", "");
				} else {
					if($row->start_publishing != ""){
						$start_pub = "'".$row->start_publishing."'";
					} else {
						$start_pub = "null";
					}					
				}
				if($jinput->getString("new_end_publishing", "") != ""){
					$end_pub = $jinput->getString("new_end_publishing", "");
				} else {
					if($row->end_publishing != ""){
						$end_pub = "'".$row->end_publishing."'";
					} else {
						$end_pub = "null";
					}					
				}
				$sql = "INSERT INTO #__sv_apptpro3_timeslots (day_number,resource_id,timeslot_starttime,timeslot_endtime,timeslot_description,start_publishing,end_publishing,published, staff_only)".
				" VALUES(".$daylist[$x].",".$new_resource_id.",'".$row->timeslot_starttime."','".$row->timeslot_endtime."','".$row->timeslot_description.
									"',".$start_pub.",".$end_pub.",".$row->published.",'".$row->staff_only."')";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_timeslots", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}
			}
		}	
		
		//global $mainframe;
		if($option=="adv_admin"){
//			$session =JFactory::getSession();
//			$session->set("current_tab", 2);
//			$option = "com_rsappt_pro3";
//			$mainframe->redirect(JURI::root() . "index.php?option=".$option."&page=adv_admin");
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots', $msg );
		}	

	}

	
}	
?>

