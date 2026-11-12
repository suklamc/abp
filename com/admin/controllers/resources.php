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
 
class resourcesController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'copy', 'copy_resources' );
		
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
		rsappt_pro3Helper::addSubmenu('resources');
		
	}


	function copy_resources(){
		$jinput = JFactory::getApplication()->input;
		$id	= $jinput->get( 'cid', array(0), 'post', 'array' );

		if (sv_count_($id )){
			$database = JFactory::getDBO();
			$msg = "";
			// first get source rows
			$ids = implode( ',', $id );
			$query = 'SELECT * FROM #__sv_apptpro3_resources '
				. ' WHERE id_resources IN ( '.$ids.' )';
			try{
				$database->setQuery( $query );
				$rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_resources", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
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
					"ddslick_image_path,ddslick_image_text,show_image_in_grid,free_booking,res_user_drag_duration_enable,res_user_drag_duration_snap".			
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
					$row->show_image_in_grid."','".			
					$row->free_booking."','".			
					$row->res_user_drag_duration_enable."',".			
					$row->res_user_drag_duration_snap.			
					")";
				try{
					$database->setQuery( $sql );
					$database->execute();
					$msg = JText::_('RS1_ADMIN_TOOLBAR_RESOURCE_COPY_OK');					
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_ctrl_resources", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}
				
			}
			//exit;
			//global $mainframe;
			if($option=="adv_admin"){
	//			$session =JFactory::getSession();
	//			$session->set("current_tab", 1);
	//			$option = "com_rsappt_pro3";
	//			$mainframe->redirect(JURI::root() . "index.php?option=".$option."&page=adv_admin");
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=resources', $msg );
			}
		}

	}
}	
?>

