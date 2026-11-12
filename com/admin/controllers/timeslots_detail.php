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


class timeslots_detailController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'save2new', 'save2new' );		
		$this->registerTask( 'create_timeslot_series', 'create_timeslot_series' );		
		
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
		$jinput->set( 'view', 'timeslots_detail' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'current_resource_id', $jinput->getInt("resource_id"));
		$jinput->set( 'current_day_number', $jinput->getInt("day_number"));

		parent::display();

		// Checkin the timeslots
		$model = $this->getModel('timeslots_detail');
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
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$delete_b4_create = $jinput->getWord( 'delete_b4_create', "No");
		$unpublish_b4_create = $jinput->getWord( 'unpublish_b4_create', "No");
		
		$post['id'] = $cid[0];

		$model = $this->getModel('timeslots_detail');
		
		$db = JFactory::getDBO();
		// unpublish or delete old before making new
		if($unpublish_b4_create == "Yes"){
			$sql = "UPDATE #__sv_apptpro3_timeslots ".
			" SET published = 0 ".
			" WHERE resource_id = ".$post['resource_id']." AND day_number = ".$post['day_number'];	
			try{
				$db->setQuery( $sql );
				$db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				//exit;
			}						
		}
		if($delete_b4_create == "Yes"){
			$sql = "DELETE FROM #__sv_apptpro3_timeslots ".
			" WHERE resource_id = ".$post['resource_id']." AND day_number = ".$post['day_number'];	
			//echo $sql;
			try{
				$db->setQuery( $sql );
				$db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				//exit;
			}						
		}
		
		// check new slot does not overlapp any existing ones
		$sql = "SELECT count(*) FROM #__sv_apptpro3_timeslots WHERE ".
			" published = 1	AND resource_id = ".$post['resource_id']." AND day_number = ".$post['day_number']." ";
			if($post['id_timeslots'] != ""){
				$sql .= " AND id_timeslots != ".$post['id_timeslots'];				
			}
			$sql .= " AND (start_publishing='0000-00-00' OR start_publishing IS NULL)";
			$sql .= " AND ";
			$sql .= "(";
			// new slot starts inside as exiting slot				
			$sql .= "(timeslot_starttime < '".$post['timeslot_starttime']."' AND timeslot_endtime > '".$post['timeslot_starttime']."')";
			$sql .= " OR ";
			// new slot ends inside as exiting slot				
			$sql .= "(timeslot_starttime < '".$post['timeslot_endtime']."' AND timeslot_endtime > '".$post['timeslot_endtime']."')";
			$sql .= " OR ";
			// new slot exact match with exiting slot				
			$sql .= "(timeslot_starttime = '".$post['timeslot_starttime']."' OR timeslot_endtime = '".$post['timeslot_endtime']."')";				
			$sql .= " OR ";
			// new slot encloses an exsiting slot				
			$sql .= "(timeslot_starttime > '".$post['timeslot_starttime']."' AND timeslot_endtime < '".$post['timeslot_endtime']."') ";
			$sql .= ")";

		try{
			$db->setQuery( $sql );
			$overlap_count = 0;
			// if you are experiencing conflict blocking on timeslots with start/end publich dates, you can 
			// bybass the chcek by commenting out the line below.
			$overlap_count = $db->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		if($overlap_count > 0 ){	
			$msg = 	JText::_('RS1_ADMIN_SCRN_SLOT_CONFLICT');	
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots', $msg, "error" );
		} else {
		
			if ($model->store($post)) {
				$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
			} else {
				$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
				logit($model->getError(), $model->getName()); 
			}
	
			// Check the table in so it can be edited.... we are done with it anyway
			$model->checkin();
			if($key=="2new"){
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots_detail&task=edit&cid[]=-1',$msg );
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots',$msg );
			}
		}
	}

	function save2new(){
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

		$model = $this->getModel('timeslots_detail');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots' );
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

		$model = $this->getModel('timeslots_detail');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots');
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

		$model = $this->getModel('timeslots_detail');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots');
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
		$model = $this->getModel('timeslots_detail');
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots',$msg );
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
		$model = $this->getModel('timeslots_detail');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots');
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
		$model = $this->getModel('timeslots_detail');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots');
	}

	/** function saveorder
	*
	* saveorder of the timeslots items
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

		$model = $this->getModel('timeslots_detail');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots', $msg );
	}
	
	function create_timeslot_series($key=null){
// validations to be done in client before we come here
//	start before end
//  start + first slot not beyond end

		$jinput = JFactory::getApplication()->input;
		$range_start_time_hour = $jinput->getInt( 'range_start_time_hour', "00");
		$range_start_time_minute = $jinput->getInt( 'range_start_time_minute', "00");
		$range_end_time_hour = $jinput->getInt( 'range_end_time_hour', "00");
		$range_end_time_minute = $jinput->getInt( 'range_end_time_minute', "00");
		$resource = $jinput->getInt( 'resource_id', "0");
		$day_number = $jinput->getInt( 'day_number', "0");
		$slot_description = $jinput->getString( 'timeslot_description', "");
		$staff_only = $jinput->getWord( 'staff_only', "No");
		$published = $jinput->getInt( 'published', "0");
		$publish_start = $jinput->getString( 'start_publishing', "");
		$publish_end = $jinput->getString( 'end_publishing', "");
		
		$slot_duration = $jinput->getInt( 'slot_duration', "0");
		$slot_duration_sec = $slot_duration * 60; // seconds
		
		$delete_b4_create = $jinput->getWord( 'delete_b4_create', "No");
		$unpublish_b4_create = $jinput->getWord( 'unpublish_b4_create', "No");
		
		$start_time = strtotime($range_start_time_hour.":".$range_start_time_minute.":00");
		$end_time = strtotime($range_end_time_hour.":".$range_end_time_minute.":00");
		$end_of_last_slot_added = $start_time;
		
		$overlaping_slot_not_created = "";
		
		//echo "start:".$start_time."<br/>";
		//echo "end:".$end_time."<br/>";
		//echo "slot_duration_sec:".$slot_duration_sec."<br/>";
		
		$db = JFactory::getDBO();

		// unpublish or delete old before making new
		if($unpublish_b4_create == "Yes"){
			$sql = "UPDATE #__sv_apptpro3_timeslots ".
			" SET published = 0 ".
			" WHERE resource_id = ".$resource." AND day_number = ".$day_number;	
			//echo $sql;
			try{
				$db->setQuery( $sql );
				$db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				//exit;
			}						
		}
		if($delete_b4_create == "Yes"){
			$sql = "DELETE FROM #__sv_apptpro3_timeslots ".
			" WHERE resource_id = ".$resource." AND day_number = ".$day_number;	
			//echo $sql;
			try{
				$db->setQuery( $sql );
				$db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				//exit;
			}						
		}

		while($end_of_last_slot_added + $slot_duration_sec <= $end_time){
			$slot_being_added_start = $end_of_last_slot_added;
			$slot_being_added_end = $slot_being_added_start + $slot_duration_sec;
			$end_of_last_slot_added = $slot_being_added_end;
			$slot_being_added_start_str = strftime("%H:%M", $slot_being_added_start);
			$slot_being_added_end_str = strftime("%H:%M", $slot_being_added_end);
						
			// check new slot does not overlapp any existing ones
			$sql = "SELECT count(*) FROM #__sv_apptpro3_timeslots WHERE ".
				" published = 1	AND resource_id = ".$resource." AND day_number = ".$day_number." ";		
				// check start/end publishing if set
				if($publish_start != "" && $publish_end != ""){
					$sql .= " AND ( start_publishing BETWEEN CAST('$publish_start' AS DATE) AND CAST('$publish_end' AS DATE) ".
						" OR end_publishing BETWEEN CAST('$publish_start' AS DATE) AND CAST('$publish_end' AS DATE) ) ";
				}
				$sql .= " AND ";
				$sql .= "(";
				// new slot starts inside as exiting slot				
				$sql .= "(timeslot_starttime < '".$slot_being_added_start_str."' AND timeslot_endtime > '".$slot_being_added_start_str."')";
				$sql .= " OR ";
				// new slot ends inside as exiting slot				
				$sql .= "(timeslot_starttime < '".$slot_being_added_end_str."' AND timeslot_endtime > '".$slot_being_added_end_str."')";
				$sql .= " OR ";
				// new slot exact match with exiting slot				
				$sql .= "(timeslot_starttime = '".$slot_being_added_start_str."' OR timeslot_endtime = '".$slot_being_added_end_str."')";				
				$sql .= " OR ";
				// new slot encloses an existing slot				
				$sql .= "(timeslot_starttime > '".$slot_being_added_start_str."' AND timeslot_endtime < '".$slot_being_added_end_str."')";
				$sql .= ")";
			//echo $sql."<br/>";
			//exit;
			try{
				$db->setQuery( $sql );
				$overlap_count = 0;
				$overlap_count = $db->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			if($overlap_count > 0 ){
				$overlaping_slot_not_created .= "(".$slot_being_added_start_str."-".$slot_being_added_end_str.") ";
			} else {
				// ok to add
				$sql = "INSERT INTO #__sv_apptpro3_timeslots (resource_id, timeslot_starttime, timeslot_endtime, timeslot_description,".
				" day_number, staff_only, published, start_publishing, end_publishing) ".
				"VALUES (".
				$resource.", ".
				"'".$slot_being_added_start_str."',".
				"'".$slot_being_added_end_str."',".
				"'".$slot_description."', ".
				$day_number.", ".
				"'".$staff_only."', ".
				$published.", ";
				if($publish_start == ""){
					$sql .= "null, ";
				} else {
					$sql .= "'".$publish_start."', ";
				}
				if($publish_end == ""){
					$sql .= "null ";
				} else {
					$sql .= "'".$publish_end."' ";
				}
				$sql .= ");";
				//echo $sql;
				try{
					$db->setQuery( $sql );
					$db->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_timelsots_detail", "", "");
					echo JText::_('RS1_SQL_ERROR');
					//exit;
				}		
			}
		}
		
		$msg = JText::_('RS1_ADMIN_SCRN_TS_INSERT_OK');
		$msg_type = "message";
		if($overlaping_slot_not_created != ""){
			$msg = JText::_('RS1_ADMIN_SCRN_TS_INSERT_INCOMPLETE').$overlaping_slot_not_created;
			$msg_type = "warning";
			//echo "Slots not created: ".	$overlaping_slot_not_created;
		}
//		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots', $msg, $msg_type );
			if($key=="2new"){
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots_detail&task=edit&cid[]=-1',$msg, $msg_type);
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=timeslots',$msg, $msg_type);
			}

	}
	
	
	function create_timeslot_series2new(){
		$this->create_timeslot_series("2new");
	}


}

