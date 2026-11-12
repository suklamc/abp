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
 
class adminController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		// Register Extra tasks
		$this->registerTask( 'reminders', 'send_reminders' );
		$this->registerTask( 'reminders_sms', 'send_sms_reminders' );
		$this->registerTask( 'thankyou', 'send_thankyou' );
		
		$this->registerTask( 'publish_resource', 'publish_resource' );
		$this->registerTask( 'unpublish_resource', 'unpublish_resource' );
		$this->registerTask( 'remove_resource', 'remove_resource' );
		$this->registerTask( 'copy_resource', 'copy_resource' );


		$this->registerTask( 'res_sort', 'res_sort' );

		$this->registerTask( 'publish_service', 'publish_service' );
		$this->registerTask( 'unpublish_service', 'unpublish_service' );
		$this->registerTask( 'remove_service', 'remove_service' );
		$this->registerTask( 'copy', 'copy_services' );
		$this->registerTask( 'docopy_service', 'do_copy_services' );

		$this->registerTask( 'publish_timeslot', 'publish_timeslot' );
		$this->registerTask( 'unpublish_timeslot', 'unpublish_timeslot' );
		$this->registerTask( 'remove_timeslot', 'remove_timeslot' );
		$this->registerTask( 'copy_timeslots', 'copy_timeslots' );
		$this->registerTask( 'docopy_timeslot', 'do_copy_timeslots' );
		$this->registerTask( 'do_global_import_timeslots', 'do_global_import_timeslots' );

		$this->registerTask( 'publish_bookoff', 'publish_bookoff' );
		$this->registerTask( 'unpublish_bookoff', 'unpublish_bookoff' );
		$this->registerTask( 'remove_bookoff', 'remove_bookoff' );
		$this->registerTask( 'copy_bookoffs', 'copy_bookoffs' );
		$this->registerTask( 'docopy_bookoffs', 'do_copy_bookoffs' );

		$this->registerTask( 'publish_coupon', 'publish_coupon' );
		$this->registerTask( 'unpublish_coupon', 'unpublish_coupon' );
		$this->registerTask( 'remove_coupon', 'remove_coupon' );
		$this->registerTask( 'copy_coupons', 'copy_coupons' );
		$this->registerTask( 'docopy_coupons', 'do_copy_coupons' );

		$this->registerTask( 'publish_extra', 'publish_extra' );
		$this->registerTask( 'unpublish_extra', 'unpublish_extra' );
		$this->registerTask( 'remove_extra', 'remove_extra' );

		$this->registerTask( 'publish_rate_adjustment', 'publish_rate_adjustment' );
		$this->registerTask( 'unpublish_rate_adjustment', 'unpublish_rate_adjustment' );
		$this->registerTask( 'remove_rate_adjustment', 'remove_rate_adjustment' );

		$this->registerTask( 'publish_seat_adjustment', 'publish_seat_adjustment' );
		$this->registerTask( 'unpublish_seat_adjustment', 'unpublish_seat_adjustment' );
		$this->registerTask( 'remove_seat_adjustment', 'remove_seat_adjustment' );


		$this->registerTask( 'ipn', 'ipn' ); //PayPal
		$this->registerTask( 'relay_resp', 'relay_resp' );
		$this->registerTask( 'ins', 'ins' ); //2CO

		$this->registerTask( 'export_csv', 'export_csv_fe' );

		$this->registerTask( 'printer', 'printer' );

		$this->registerTask( 'remove_user_credit', 'remove_user_credit' );

		$this->registerTask( 'publish_book_date', 'publish_book_date' );
		$this->registerTask( 'unpublish_book_datef', 'unpublish_book_datef' );
		$this->registerTask( 'remove_book_date', 'remove_book_date' );

		$user = JFactory::getUser();		
		if($user->guest){	
			$jinput = JFactory::getApplication()->input;
			$task = $jinput->getString('task');	
			if($task != "ipn" && $task != "ins" && $task != "relay_resp"){  // non admin callback tasks 
				$this->setRedirect( 'index.php', JText::_('RS1_FRONT_END_ACCESS_ERROR'), 'error');
			}
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
		$jinput->set( 'view', 'admin' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

	}

	function cancel($key=null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab);
		}
	}	

	function publish_resource()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid = $jinput->post->get('cid_res', array(), 'ARRAY');

		$post['id_resources'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'resources_detail.php');
		$model = new admin_detailModelresources_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab);
		}
		
	}	

	function unpublish_resource()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_res', array(0), 'post', 'array' );
		$post['id_resources'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'resources_detail.php');
		$model = new admin_detailModelresources_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab);
		}
	}	

	function remove_resource()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_res', array(0), 'post', 'array' );
		$post['id_resources'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'resources_detail.php');
		$model = new admin_detailModelresources_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {
			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}


	function copy_resource()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_res', array(0), 'post', 'array' );
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to copy' ), error);

		}
		
		$database = JFactory::getDBO();
		// first get source rows
		$ids = implode( ',', $cid );
		$query = 'SELECT * FROM #__sv_apptpro3_resources '
			. ' WHERE id_resources IN ( '.$ids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
		foreach($rows as $row) {
			$sql = "INSERT INTO #__sv_apptpro3_resources (".
				"category_scope,mail_id,name,description,cost,ordering,resource_email,prevent_dupe_bookings,max_dupes,resource_admins,rate,rate_unit,".
				"allowSunday,allowMonday,allowTuesday,allowWednesday,allowThursday,allowFriday,allowSaturday,timeslots,disable_dates_before,".
				"disable_dates_before_days,min_lead_time,disable_dates_after,disable_dates_after_days,published,default_calendar_category,default_calendar,".
				"sms_phone,google_user,google_password,google_default_calendar_name,access,enable_coupons,max_seats,non_work_day_message,".
				"resource_eb_discount,resource_eb_discount_unit,resource_eb_discount_lead,".
				
				"gap,mailchimp_list_id,acymailing_list_id,google_client_id,google_app_name,google_app_email_address,google_p12_key_filename,".
				"ddslick_image_path,ddslick_image_text,show_image_in_grid".			
			")".
			" VALUES(".
				"'".$row->category_scope."',".
				$row->mail_id.",'".
				$row->name."','".
				$row->description."','".
				$row->cost."',".
				$row->ordering.",'".
				$row->resource_email."','".
				$row->prevent_dupe_bookings."',".
				$row->max_dupes.",'".
				$row->resource_admins."','".
				$row->rate."','".
				$row->rate_unit."','".
				$row->allowSunday."','".
				$row->allowMonday."','".
				$row->allowTuesday."','".
				$row->allowWednesday."','".
				$row->allowThursday."','".
				$row->allowFriday."','".
				$row->allowSaturday."','".
				$row->timeslots."','".
				$row->disable_dates_before."',".
				$row->disable_dates_before_days.",".
				$row->min_lead_time.",'".
				$row->disable_dates_after."',".
				$row->disable_dates_after_days.",".
				$row->published.",'".
				$row->default_calendar_category."','".
				$row->default_calendar."','".
				$row->sms_phone."','".
				$row->google_user."','".
				$row->google_password."','".
				$row->google_default_calendar_name."','".
				$row->access."','".
				$row->enable_coupons."','".
				$row->max_seats."','".
				$row->non_work_day_message."',".
				$row->resource_eb_discount.",'".
				$row->resource_eb_discount_unit."',".
				$row->resource_eb_discount_lead.",".
				$row->gap.",'".
				$row->mailchimp_list_id."','".
				$row->acymailing_list_id."','".
				$row->google_client_id."','".
				$row->google_app_name."','".
				$row->google_app_email_address."','".
				$row->google_p12_key_filename."','".
				$row->ddslick_image_path."','".
				$row->ddslick_image_text."','".
				$row->show_image_in_grid."'".			
				")";
			try{
				$database->setQuery( $sql );
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ctrl_admin", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}
			}
		
			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item, JText::_( 'RS1_RESOURCE_COPY_OK' ) ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item, JText::_( 'RS1_RESOURCE_COPY_OK' ) );
			}
	}


	function publish_service()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid = $jinput->post->get('cid_srv', array(), 'ARRAY');
		$post['id_services'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'services_detail.php');
		$model = new admin_detailModelservices_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_service()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid = $jinput->post->get('cid_srv', array(), 'ARRAY');
		$post['id_services'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'services_detail.php');
		$model = new admin_detailModelservices_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_service()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid = $jinput->post->get('cid_srv', array(), 'ARRAY');
		
		$post['id_services'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'services_detail.php');
		$model = new admin_detailModelservices_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function copy_services(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));


		$jinput = JFactory::getApplication()->input;
		$cid = $jinput->post->get('cid_srv', array(), 'ARRAY');
		
		$frompage = $jinput->getString( 'frompage', '' );
		$fromtab = $jinput->getString( 'current_tab', '' );
		$jinput->set( 'view', 'services_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'services_tocopy', implode(',', $cid));
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function do_copy_services(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');

		$cids = $jinput->getString( 'services_tocopy' );
		//$dest_ids = $jinput->post->get('dest_resource_id', array(), 'ARRAY');

		$database = JFactory::getDBO();
		// first get source rows
		//$cids = implode( ',', $cid );
		$query = 'SELECT * FROM #__sv_apptpro3_services '
			. ' WHERE id_services IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		//now do inserts
		$msg = "";
		foreach($rows as $row) {
			//for($x=0; $x<sv_count_($dest_ids); $x++){
				$sql = "INSERT INTO #__sv_apptpro3_services (resource_scope,description,name,service_duration,service_duration_unit,service_rate,service_rate_unit,".
				"service_eb_discount,service_eb_discount_unit,service_eb_discount_lead,ordering,published)".
				" VALUES('".$row->resource_scope."','".$row->description."','".$row->name."',".
				$row->service_duration.",'".
				$row->service_duration_unit."',".
				$row->service_rate.",'".
				$row->service_rate_unit."',".
				$row->service_eb_discount.",'".
				$row->service_eb_discount_unit."',".
				$row->service_eb_discount_lead.",".
				$row->ordering.",".
				$row->published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_admin", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
			//}
		}		
		if($msg == ""){
			$msg = JText::_('RS1_SERVICE_COPY_OK');
		}
	
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab), $msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab, $msg );
		}

	}

	function publish_timeslot()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ts', array(0), 'post', 'array' );
		$post['id_timeslots'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'timeslots_detail.php');
		$model = new admin_detailModeltimeslots_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_timeslot()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ts', array(0), 'post', 'array' );
		$post['id_timeslots'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'timeslots_detail.php');
		$model = new admin_detailModeltimeslots_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	


	function remove_timeslot()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ts', array(0), 'post', 'array' );
		$post['id_timeslots'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'timeslots_detail.php');
		$model = new admin_detailModeltimeslots_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function do_global_import_timeslots()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ts', array(0), 'post', 'array' );
		$post['id_timeslots'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');
		$new_resource_id = $jinput->getString('timeslots_resourceFilter');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		$database = JFactory::getDBO();
		$query = "SELECT * FROM #__sv_apptpro3_timeslots "
			." WHERE ISNULL(resource_id) OR resource_id = ''";
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
		
		//now do inserts
		foreach($rows as $row) {
			$sql = "INSERT INTO #__sv_apptpro3_timeslots (day_number,resource_id,timeslot_starttime,timeslot_endtime,published)".
			" VALUES(".$row->day_number.",".$new_resource_id.",'".$row->timeslot_starttime."','".$row->timeslot_endtime."',".$row->published.")";
			try{
				$database->setQuery( $sql );
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ctrl_admin", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}
		}		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}

	}

	function copy_timeslots(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;

		$cid	= $jinput->get( 'cid_ts', array(0), 'post', 'array' );
		
		$frompage = $jinput->getString( 'frompage', '' );
		$fromtab = $jinput->getString( 'current_tab', '' );
		$jinput->set( 'view', 'timeslots_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'timeslots_tocopy', implode(',', $cid));
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function do_copy_timeslots(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cids = $jinput->get( 'timeslots_tocopy', array(0), 'post', 'array' ); 
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');
		$new_resource_id = $jinput->getString('dest_resource_id');

		$database = JFactory::getDBO();
		// first get source rows
		$query = 'SELECT * FROM #__sv_apptpro3_timeslots '
			. ' WHERE id_timeslots IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		// make an array of days
		$i = 0;
		$daylist[] = null;
		if($jinput->getString('chkSunday') == "on"){
			$daylist[$i] = 0;
			$i++;  
		}
		if($jinput->getString('chkMonday') == "on"){
			$daylist[$i] = 1;
			$i++;  
		}
		if($jinput->getString('chkTuesday') == "on"){
			$daylist[$i] = 2;
			$i++;  
		}
		if($jinput->getString('chkWednesday') == "on"){
			$daylist[$i] = 3;
			$i++;  
		}
		if($jinput->getString('chkThursday') == "on"){
			$daylist[$i] = 4;
			$i++;  
		}
		if($jinput->getString('chkFriday') == "on"){
			$daylist[$i] = 5;
			$i++;  
		}
		if($jinput->getString('chkSaturday') == "on"){
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
					$start_pub = $row->start_publishing;
				}
				if($jinput->getString("new_end_publishing", "") != ""){
					$end_pub = $jinput->getString("new_end_publishing", "");
				} else {
					$end_pub = $row->end_publishing;
				}
				
				if($start_pub == ""){					
				  $start_pub = "null";			
				}
				if($end_pub == ""){					
				  $end_pub = "null";			
				}
				
				$sql = "INSERT INTO #__sv_apptpro3_timeslots (day_number,resource_id,timeslot_starttime,timeslot_endtime,timeslot_description,start_publishing,end_publishing,published)".
				" VALUES(".$daylist[$x].",".$new_resource_id.",'".$row->timeslot_starttime."','".$row->timeslot_endtime."','".$row->timeslot_description.
									"',".$start_pub.",".$end_pub.",".$row->published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_admin", $sql, "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
			}
		}	
		$msg = JText::_( 'RS1_TIMESLOT_COPY_OK' );
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab), $msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab, $msg );
		}

	}


	function publish_bookoff()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bo', array(0), 'post', 'array' );
		$post['id_bookoffs'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bookoffs_detail.php');
		$model = new admin_detailModelbookoffs_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect(  'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_bookoff()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bo', array(0), 'post', 'array' );
		$post['id_bookoffs'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bookoffs_detail.php');
		$model = new admin_detailModelbookoffs_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_bookoff()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bo', array(0), 'post', 'array' );
		$post['id_bookoffs'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bookoffs_detail.php');
		$model = new admin_detailModelbookoffs_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function copy_bookoffs(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid_bo', array(0), 'post', 'array' );
		
		$frompage = $jinput->getString( 'frompage', '' );
		$fromtab = $jinput->getString( 'current_tab', '' );
		$jinput->set( 'view', 'bookoffs_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'bookoffs_tocopy', implode(',', $cid));
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function do_copy_bookoffs(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cids = $jinput->getString( 'bookoffs_tocopy' );
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');
		$dest_ids = $jinput->get('dest_resource_id', array(0), 'post', 'array');

		$newdate = $jinput->getString('new_off_date',"");
		$database = JFactory::getDBO();
		// first get source rows
		$query = 'SELECT * FROM #__sv_apptpro3_bookoffs '
			. ' WHERE id_bookoffs IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		//now do inserts
		foreach($rows as $row) {
			for($x=0; $x<sv_count_($dest_ids); $x++){
				$sql = "INSERT INTO #__sv_apptpro3_bookoffs (resource_id,description,off_date,full_day,bookoff_starttime,bookoff_endtime,rolling_bookoff,published)".
				" VALUES(".$dest_ids[$x].",'".$database->escape($row->description)."','".($newdate == ""?$row->off_date:$newdate)."','".$row->full_day."','".$row->bookoff_starttime."','".$row->bookoff_endtime."','".$row->rolling_bookoff."',".$row->published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_admin", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
			}
		}		
		$msg = JText::_( 'RS1_BOOKOFF_COPY_OK' );
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab), $msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab, $msg );
		}

	}

	function publish_coupon()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_coup', array(0), 'post', 'array' );
		$post['id_coupons'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'coupons_detail.php');
		$model = new admin_detailModelcoupons_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_coupon()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_coup', array(0), 'post', 'array' );
		$post['id_coupons'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'coupons_detail.php');
		$model = new admin_detailModelcoupons_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_coupon()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_coup', array(0), 'post', 'array' );
		$post['id_coupons'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'coupons_detail.php');
		$model = new admin_detailModelcoupons_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function copy_coupons(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid_coup', array(0), 'post', 'array' );
		
		$frompage = $jinput->getString( 'frompage', '' );
		$fromtab = $jinput->getString( 'current_tab', '' );
		$jinput->set( 'view', 'coupons_copy' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'coupons_tocopy', implode(',', $cid));
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		if (!is_array( $cid ) || $cid[0] == 0) {
			$msg = JText::_( 'RS1_ADMIN_SCRN_COPY_SELECT_ERROR' );
			$config = JFactory::getConfig();
			$seo = $config->get( 'config.sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab), $msg );
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab, $msg );
			}
		}

		parent::display();

	}

	function do_copy_coupons(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		
		$post	= $jinput->post->getArray();
		$cids = $jinput->getString( 'coupons_tocopy' );
		$number_of_copies = $jinput->getString('number_of_copies');
		$newdate = $jinput->getString('new_coupon_date',"");
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');
		
		$database =JFactory::getDBO();
		// first get source rows
		//$cids = implode( ',', $cid );
		$query = 'SELECT * FROM #__sv_apptpro3_coupons '
			. ' WHERE id_coupons IN ( '.$cids.' )';
		try{
			$database->setQuery( $query );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
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
				$row->max_user_use.",'".
				($newdate == ""?$row->expiry_date:$newdate)."','".
				$row->scope."',".
				$row->ordering.",".
				$row->published.")";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_admin", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
			}
		}		
		
		if($msg == ""){
			$msg = JText::_('RS1_ADMIN_TOOLBAR_COUPONS_COPY_OK');
		} else {
			logit($msg,"do_copy_coupons"); 
		}
	
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab), $msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&current_tab='.$fromtab, $msg );
		}
		
	}


	function publish_extra()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ext', array(0), 'post', 'array' );
		$post['id_extras'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'extras_detail.php');
		$model = new admin_detailModelextras_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_extra()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ext', array(0), 'post', 'array' );
		$post['id_extras'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'extras_detail.php');
		$model = new admin_detailModelextras_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_extra()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ext', array(0), 'post', 'array' );
		$post['id_extras'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'extras_detail.php');
		$model = new admin_detailModelextras_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function publish_rate_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ra', array(0), 'post', 'array' );
		$post['id_rate_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'rate_adjustments_detail.php');
		$model = new admin_detailModelrate_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_rate_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ra', array(0), 'post', 'array' );
		$post['id_rate_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'rate_adjustments_detail.php');
		$model = new admin_detailModelrate_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_rate_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_ra', array(0), 'post', 'array' );
		$post['id_rate_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'rate_adjustments_detail.php');
		$model = new admin_detailModelrate_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function publish_seat_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_sa', array(0), 'post', 'array' );
		$post['id_seat_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'seat_adjustments_detail.php');
		$model = new admin_detailModelseat_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_seat_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_sa', array(0), 'post', 'array' );
		$post['id_seat_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'seat_adjustments_detail.php');
		$model = new admin_detailModelseat_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_seat_adjustment()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_sa', array(0), 'post', 'array' );
		$post['id_seat_adjustments'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'seat_adjustments_detail.php');
		$model = new admin_detailModelseat_adjustments_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}



	function send_reminders($sms="No"){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');

		$cid	= $jinput->get( 'cid_req', array(0), 'post', 'array' );
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		// set MySQL locale
		$lang = JFactory::getLanguage();
		$langTag =  $lang->getTag();
		if($langTag == ""){
			$langTag = "en_GB";
		}
		$sql = "SET lc_time_names = '".str_replace("-", "_",$langTag)."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_send_reminders", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		if (sv_count_($cid))
		{
			$ids = implode(',', $cid);
			// get request details
			$sql = "SELECT #__sv_apptpro3_requests.*, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%W %M %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime ,".
				"#__sv_apptpro3_resources.name AS resource_name ".
				"FROM (#__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				" ON  #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources )". 
				" WHERE #__sv_apptpro3_requests.id_requests IN ($ids)";
			try{	
				$database->setQuery($sql);
				$requests = NULL;
				$requests = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "controllers/admin", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
/*			if($apptpro_config->daylight_savings_time == "Yes"){
				$tzoffset = $tzoffset+1;
			}
			$offsetdate = JFactory::getDate();
			$offsetdate->setOffset($tzoffset);
*/		
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);
			date_default_timezone_set ($tzoffset);

			$status = '';
			$subject = JText::_('RS1_REMINDER_EMAIL_SUBJECT');
			
			$k = 0;
			for($i=0; $i < sv_count_($requests ); $i++) {
				$request = $requests[$i];
				$err = "";
				if($request->email == "" && $sms=="No"){
					// no email address
					$err .= JText::_('RS1_SMS_MSG_NO_EMAIL');
				} 
				if($request->request_status != "accepted"){
					// is not 'accepted'?
					$err .= JText::_('RS1_SMS_MSG_NOT_ACCEPTED');
				} else if(strtotime($request->startdate." ".$request->starttime) < strtotime("now")){
					// in the past
					$err .= JText::_('RS1_SMS_MSG_DATE_PASSED');
				}
				if($request->user_id != ""){
					$user = $request->user_id;
				} else {
					$user="-1";
				}
				if($err != ""){
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT');											
					logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
					$status .= $line."<br>";
				} else {
					if($sms=="No"){
						if(sendMail($request->email, $subject, "reminder", $request->id_requests)){
							$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_OK');											
							logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status .= $line."<br>";
						} else {
							$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_FAILED');											
							logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status .= $line."<br>";
						}	
					} else {
						if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
							$returnCode = "";
							if(sv_sendSMS($request->id_requests, "reminder", $returnCode )){
								$line = JText::_('RS1_SMS_MSG_TO_RECIP').stripslashes($request->name). JText::_('RS1_SMS_MSG_RET_CODE_OK').$returnCode;											
								logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
								$status .= $line."<br>";
							} else {
								$line = JText::_('RS1_SMS_MSG_TO_RECIP').stripslashes($request->name). JText::_('RS1_SMS_MSG_RET_CODE_FAILED').$returnCode;											
								logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
								$status .= $line."<br>";
							}
						} else {
							logReminder(JText::_('RS1_SMS_MSG_DISABLED'), $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status = JText::_('RS1_SMS_MSG_DISABLED');
						}				
					}
				}
			}
		}
		
		$jinput->set( 'view', 'requests_reminders' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'results', $status);
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();
		
	}

	function send_sms_reminders(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->send_reminders("Yes");
	}

	function send_thankyou(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');

		$cid	= $jinput->get( 'cid_req', array(0), 'post', 'array' );
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		if (!is_array($cid) || sv_count_($cid) < 1) {
			echo "<script> alert('Select an item '); window.history.go(-1);</script>\n";
			exit();
		}
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/admin", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		if (sv_count_($cid))
		{
			$ids = implode(',', $cid);
			// get request details
			$sql = "SELECT #__sv_apptpro3_requests.*, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%W %M %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime ,".
				"#__sv_apptpro3_resources.name AS resource_name ".
				"FROM (#__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				" ON  #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources )". 
				" WHERE #__sv_apptpro3_requests.id_requests IN ($ids)";
			try{	
				$database->setQuery($sql);
				$requests = NULL;
				$requests = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "controllers/admin", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);

			$status = '';
			$subject = JText::_('RS1_THANKYOU_MSG_SUBJECT');
			
			$k = 0;
			for($i=0; $i < sv_count_($requests ); $i++) {
				$request = $requests[$i];
				$err = "";
				if($request->email == "" && $sms=="No"){
					// no email address
					$err .= JText::_('RS1_SMS_MSG_NO_EMAIL');
				} 
				if($request->user_id != ""){
					$user = $request->user_id;
				} else {
					$user="-1";
				}
				if($err != ""){
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT');											
					logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
					$status .= $line."<br>";
				} else {
					if(sendMail($request->email, $subject, "thankyou", $request->id_requests)){
						$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_OK');											
						logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
						$status .= $line."<br>";
					} else {
						$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_FAILED');											
						logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
						$status .= $line."<br>";
					}	
				}
			}
		}
		
		$jinput->set( 'view', 'requests_thankyou' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'results', $status);
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();
	}

	function ipn(){
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/payment_processors/paypal/paypal_ipn.php');
	}

	function ins(){
		include_once(JPATH_SITE.'/components/com_rsappt_pro3/payment_processors/_2co/_2co_ins.php');
	}
	
	function relay_resp(){
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('fromscreen');
		$frompage_item = $jinput->getString('Itemid');

		include_once(JPATH_SITE.'/components/com_rsappt_pro3/payment_processors/authnet/authnet_resp.php');
	}

	function export_csv_fe(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		include_once(JPATH_SITE.'/components/com_rsappt_pro3/functions2.php');
		do_fe_export();
	}
	
	function printer(){
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'admin' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'layout', 'default_prt');
		$jinput->set( 'tmpl', 'component');

		parent::display();
	}

	function remove_user_credit()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_uc', array(0), 'post', 'array' );
		$post['id_user_credit'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'user_credit_detail.php');
		$model = new admin_detailModeluser_credit_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}

	function publish_book_date()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bd', array(0), 'post', 'array' );
		$post['id_book_dates'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'book_dates_detail.php');
		$model = new admin_detailModelbook_dates_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}
		
		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		$model->checkin();
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect(  'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
		
	}	

	function unpublish_book_date()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bd', array(0), 'post', 'array' );
		$post['id_book_dates'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'book_dates_detail.php');
		$model = new admin_detailModelbook_dates_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}
	
		if (!$model->publish($cid,0)) {
			$msg = JText::_( 'COM_RSAPPT_ERROR_SAVING' ).": ".$model->getError();
			logit($model->getError(), $model->getName()); 
		}

		$model->checkin();

		$session = JSession::getInstance($handler=null, $options=null);
		$session->set("current_tab", $current_tab);
		
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){		
			$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	

	function remove_book_date()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$post	= $jinput->post->getArray();
		$cid	= $jinput->get( 'cid_bd', array(0), 'post', 'array' );
		$post['id_book_dates'] = $cid[0];
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$current_tab = $jinput->getString('current_tab');

		if (!is_array( $cid ) || sv_count_($cid ) < 1) {
			JFactory::getApplication()->enqueueMessage(JText::_( 'Select an item to delete' ), error);

		}

		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'book_dates_detail.php');
		$model = new admin_detailModelbook_dates_detail;
 		if($model == null){
			echo "model = null";
			exit;
		}

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		} else {

			$session = JSession::getInstance($handler=null, $options=null);
			$session->set("current_tab", $current_tab);			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($seo == "1"){		
				$this->setRedirect( JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
			}
		}
	}
	
}
?>

