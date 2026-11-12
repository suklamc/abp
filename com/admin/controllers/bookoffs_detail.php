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


class bookoffs_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'create_bookoff_series', 'create_bookoff_series' );
		$this->registerTask( 'save2new', 'save2new' );		
		
	}

	/** function edit
	*
	* Create a new item or edit existing item 
	* 
	* 1) set a custom VIEW layout to 'form'  
	* so expecting path is : [componentpath]/views/[$controller->_name]/'form.php';			
    * 2) show the view
    * 3) get(create) MODEL and checkout item
	*/
	function edit($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'bookoffs_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 1);


		parent::display();

		// Checkin the bookoffs
		$model = $this->getModel('bookoffs_detail');
		$model->checkout();
	}
      
	/** function save
	*
	* Save the selected item specified by id
	* and set Redirection to the list of items	
	* 		
	* @param int id - keyvalue of the item
	* @return set Redirection
	*/
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		
		$filter = JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$jinput = new JInput( null, array('filter' => $filter) );
		$post = $jinput->post->getArray();

		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];

		$model = $this->getModel('bookoffs_detail');

		if ($model->store($post)) {
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} else {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		if($key=="2new"){
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs_detail&task=edit&cid[]=-1',$msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs',$msg );
		}
		
	}


	function save2new()
	{
		$this->save("2new");
	}


	/** function remove
	*
	* Delete all items specified by array cid
	* and set Redirection to the list of items	
	* 		
	* @param array cid - array of id
	* @return set Redirection
	*/
	function remove()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid = $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		$model = $this->getModel('bookoffs_detail');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs' );
		}
	}
	
	
	/** function publish
	*
	* Publish all items specified by array cid
	* and set Redirection to the list of items	
	* 		
	* @param array cid - array of id
	* @return set Redirection
	*/
	function publish()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to publish' ), error);

		}

		$model = $this->getModel('bookoffs_detail');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs');
		}
	}

	/** function unpublish
	*
	* Unpublish all items specified by array cid
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function unpublish()
	{
		//global $mainframe;
		$jinput = JFactory::getApplication()->input;

		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to unpublish' ), error);

		}

		$model = $this->getModel('bookoffs_detail');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs');
		}
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
		// Checkin the detail
		$model = $this->getModel('bookoffs_detail');
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs',$msg );
	}	

	/** function orderup
	*
	* change order up
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function orderup()
	{
		$model = $this->getModel('bookoffs_detail');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs');
	}


	/** function orderdown
	*
	* change order down
	* and set Redirection to the list of items
	* 			
	* @param array cid - array of id
	* @return set Redirection
	*/
	function orderdown()
	{
		$model = $this->getModel('bookoffs_detail');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs');
	}

	/** function saveorder
	*
	* saveorder of the bookoffs items
	* 			
	* @param array cid		- array of id
	* @param array order	- array of order 
	* @return set Redirection
	*/
	function saveorder()
	{
		$jinput = JFactory::getApplication()->input;
		$cid 	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$order 	= $jinput->get( 'order', array(0), 'post', 'array' );

		$model = $this->getModel('bookoffs_detail');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs', $msg );
	}
	

	function create_bookoff_series(){
		$jinput = JFactory::getApplication()->input;
		$off_date = $jinput->getString('off_date');
		$off_date2 = $jinput->getString('off_date2');
		$resource = $jinput->getInt('resource_id');
		$full_day = $jinput->getString('full_day');
		$rolling_bookoff = $jinput->getString('rolling_bookoff');
		$bookoff_starttime = $jinput->getString('bookoff_starttime');
		$bookoff_endtime = $jinput->getString('bookoff_endtime');
		$resource_desc = $jinput->getString('description');
		$published = $jinput->getString('published');
		$bo_Sun = $jinput->getString('chkSunday');
		$bo_Mon = $jinput->getString('chkMonday');
		$bo_Tue = $jinput->getString('chkTuesday');
		$bo_Wed = $jinput->getString('chkWednesday');
		$bo_Thu = $jinput->getString('chkThursday');
		$bo_Fri = $jinput->getString('chkFriday');
		$bo_Sat = $jinput->getString('chkSaturday');
		$bo_days = " ";
		if($bo_Sun == 'on'){$bo_days .= "|0|";}
		if($bo_Mon == 'on'){$bo_days .= "|1|";}
		if($bo_Tue == 'on'){$bo_days .= "|2|";}
		if($bo_Wed == 'on'){$bo_days .= "|3|";}
		if($bo_Thu == 'on'){$bo_days .= "|4|";}
		if($bo_Fri == 'on'){$bo_days .= "|5|";}
		if($bo_Sat == 'on'){$bo_days .= "|6|";}

		$d1 = strtotime($off_date);
		$d2 = strtotime($off_date2);
		$database = JFactory::getDBO();
		while($d1 <= $d2){
			$process_date = getdate($d1);
			//$process_wday = "|".$process_date[wday]."|";
			$process_wday = "|".$process_date['wday']."|";
			if(strpos($bo_days, $process_wday) >0 ) {
				$sql = "INSERT INTO #__sv_apptpro3_bookoffs (resource_id,description,off_date,full_day,bookoff_starttime,bookoff_endtime,rolling_bookoff,published)".
				" VALUES(".$resource.",'".$database->escape($resource_desc)."','".date("Y-m-d", $d1)."','".$full_day."','".$bookoff_starttime."','".$bookoff_endtime."','".$rolling_bookoff."',".$published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_bookoffs_detail", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					//exit;
				}		
			}
			$d1 = $d1+86400; 
		}
		
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=bookoffs' );
		
	}

}

