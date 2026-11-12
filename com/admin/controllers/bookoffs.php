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
 
class bookoffsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'copy', 'copy_bookoffs' );
		$this->registerTask( 'docopy_bookoff', 'do_copy_bookoffs' );
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
		rsappt_pro3Helper::addSubmenu('bookoffs');
		
	}
	
	
	function copy_bookoffs(){
		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$jinput->set( 'view', 'bookoffs_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'bookoffs_tocopy', implode(',', $cid));

		parent::display();

	}

	function do_copy_bookoffs(){

		$jinput = JFactory::getApplication()->input;
		$cids = $jinput->getString( 'bookoffs_tocopy' );
		$dest_ids = $jinput->get( 'dest_resource_id', array(0), 'post', 'array' );
		$newdate = $jinput->getString('new_off_date',"");

		$database = JFactory::getDBO();
		// first get source rows
		//$cids = implode( ',', $cid );
		$query = 'SELECT * FROM #__sv_apptpro3_bookoffs '
			. ' WHERE id_bookoffs IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_bookoffs", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		//now do inserts
		$msg = "";
		foreach($rows as $row) {
			for($x=0; $x<sv_count_($dest_ids); $x++){
				$sql = "INSERT INTO #__sv_apptpro3_bookoffs (resource_id,description,off_date,full_day,rolling_bookoff,bookoff_starttime,bookoff_endtime,published)".
				" VALUES(".$dest_ids[$x].",'".$database->escape($row->description)."','".
				($newdate == ""?$row->off_date:$newdate)."','".
				$row->full_day."','".
				$row->rolling_bookoff."','".
				$row->bookoff_starttime."','".
				$row->bookoff_endtime."',".
				$row->published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_bookoffs", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
			}
		}		
		if($msg == ""){
			$msg = JText::_('RS1_ADMIN_TOOLBAR_BOOKOFF_COPY_OK');
		} else {
			logit($msg,"do_copy_bookoffs"); 
		}
	
		//global $mainframe;
		if($option=="adv_admin"){
//			$session =JFactory::getSession();
//			$session->set("current_tab", 2);
//			$option = "com_rsappt_pro3";
//			$mainframe->redirect(JURI::root() . "index.php?option=".$option."&page=adv_admin");
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs', $msg );
		}	

	}

	
}	
?>

