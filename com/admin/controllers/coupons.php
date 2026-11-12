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
 
class couponsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'copy', 'copy_coupons' );
		$this->registerTask( 'docopy_coupons', 'do_copy_coupons' );
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
		rsappt_pro3Helper::addSubmenu('coupons');
		
	}
	
	function copy_coupons(){

		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$jinput->set( 'view', 'coupons_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'coupons_tocopy', implode(',', $cid));

		parent::display();

	}

	function do_copy_coupons(){
		$jinput = JFactory::getApplication()->input;
		$cids = $jinput->get( 'coupons_tocopy', array(0), 'post', 'array' );
		$number_of_copies = $jinput->getInt('number_of_copies');
		$newdate = $jinput->getString('new_coupon_date',"");
		
		$database =JFactory::getDBO();
		// first get source rows
		//$cids = implode( ',', $cid );
		$query = 'SELECT * FROM #__sv_apptpro3_coupons '
			. ' WHERE id_coupons IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_coupons", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		//now do inserts
		$msg = "";
		foreach($rows as $row) {
			for($x=1; $x<=$number_of_copies; $x++){
				$sql = "INSERT INTO #__sv_apptpro3_coupons (description, coupon_code, discount, discount_unit, max_total_use, max_user_use, expiry_date, ".
				"scope,ordering,published)".
				" VALUES('".
				$database->escape($row->description)."','".
				$row->coupon_code."($x)',".
				$row->discount.",'".
				$row->discount_unit."',".
				$row->max_total_use.",".
				$row->max_user_use.",";
				if($newdate == ""){
					if($row->expiry_date == ""){
						$sql .= "null,'";
					} else {
						$sql .= "'".substr($row->expiry_date, 0, 10)."','";
					}
				}
				else{	
					$sql .= "'".$newdate."','";
				}
				$sql .= $row->scope."',".
				$row->ordering.",".
				$row->published.")";
				//echo $sql."<br>";
				//exit;
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_coupons", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}
			}
		}		
		
		if($msg == ""){
			$msg = JText::_('RS1_ADMIN_TOOLBAR_COUPONS_COPY_OK');
		} else {
			logit($msg,"do_copy_coupons"); 
		}
	
		//global $mainframe;
		if($option=="adv_admin"){
//			$session =JFactory::getSession();
//			$session->set("current_tab", 2);
//			$option = "com_rsappt_pro2";
//			$mainframe->redirect(JURI::base() . "index.php?option=".$option."&page=adv_admin");
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=coupons', $msg );
		}	

	}

}	
?>

