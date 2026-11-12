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
 *
 * This controller is used for json calls made by mobile web apps.
 *
 */


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' );


/**
 * rsappt_pro3  Controller
 */
 
class json_xController extends JControllerForm
{
	var $login_required = "No";
	var $site_access_code = "";
	var $version = "3.1 Nov 21/16 ";
	var $joomla_ver = "";
	var $comp_name = "pro3_";
	
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		$config = JFactory::getConfig();
		$dbtype = $config->get('dbtype');
		//$comp_name = JFactory::getApplication()->input->get('option');

		include_once(JPATH_SITE.'/administrator/components/com_rsappt_pro3/functions_pro2.php');

		$ver = new JVersion;
		$this->joomla_ver = $ver->getShortVersion();
		//logIt($comp_name, "json_x", "", "");
			if($dbtype == "mysqli"){
				include_once(JPATH_SITE.'/components/com_rsappt_pro3/mysqli2json.class.php');
			} else {
				include_once(JPATH_SITE.'/components/com_rsappt_pro3/mysql2json.class.php');
		}

		// get config info
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		$this->login_required = $apptpro_config->requireLogin;
		$this->site_access_code = $apptpro_config->site_access_code;
		
		// Register tasks
		$this->registerTask( 'check_site_code', 'check_site_code' );
		$this->registerTask( 'get_version', 'get_version' );
		$this->registerTask( 'list_resources', 'list_resources' );
		$this->registerTask( 'get_categories', 'get_categories' );
		$this->registerTask( 'get_language_file', 'get_language_file' );
		$this->registerTask( 'get_config', 'get_config' );
		$this->registerTask( 'get_timeslots', 'get_timeslots' );
		$this->registerTask( 'get_services', 'get_services' );
		$this->registerTask( 'get_udfs', 'get_udfs' );
		$this->registerTask( 'get_extras', 'get_extras' );
		$this->registerTask( 'get_seats', 'get_seats' );
		$this->registerTask( 'get_seats_available', 'get_seats_available' );
		$this->registerTask( 'insertBooking', 'insertBooking' );
		$this->registerTask( 'get_mybookings', 'get_mybookings' );
		$this->registerTask( 'get_booking_detail', 'get_booking_detail' );
		$this->registerTask( 'get_user_credit', 'get_user_credit' );
		$this->registerTask( 'get_coupon_data', 'get_coupon_data' );
		$this->registerTask( 'get_seat_values', 'get_seat_values' );
		
		// admin tasks
		$this->registerTask( 'get_adm_resources', 'get_adm_resources' );
		$this->registerTask( 'get_adm_bookings', 'get_adm_bookings' );
		$this->registerTask( 'get_adm_booking_detail', 'get_adm_booking_detail' );
		$this->registerTask( 'get_adm_udf_values', 'get_adm_udf_values' );
		$this->registerTask( 'get_adm_extra_values', 'get_adm_extra_values' );
		$this->registerTask( 'get_adm_seat_values', 'get_adm_seat_values' );
		$this->registerTask( 'adm_update_booking', 'adm_update_booking' );
		$this->registerTask( 'get_adm_bookoffs', 'get_adm_bookoffs' );
		$this->registerTask( 'get_adm_bookoff_detail', 'get_adm_bookoff_detail' );
		$this->registerTask( 'adm_save_bookoff', 'adm_save_bookoff' );
		$this->registerTask( 'get_adm_users', 'get_adm_users' );
		$this->registerTask( 'get_adm_user_search', 'get_adm_user_search' );
		
	}


	function get_version()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
	
		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication or Site Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}

		$retval = "{ \"data\": [ { \"version\" : \"".$this->version."(J".$this->joomla_ver.")"."\" } ] }";
		echo $retval;

	}

	function list_resources()
	{	
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$cat_id = $jinput->getInt( 'cat_id', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = -1;
		if($this->login_required == "Yes" || $username != ""){
			// even if login not required, if user has set username, check so we have a user id for group chceking
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1 && $this->login_required == "Yes"){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		if($cat_id == ""){	
			$sql="select * from #__sv_appt".$this->comp_name."resources where published=1 ORDER BY ordering"; 
		} else {
			// Use this for ABPro below verison 3.0 
			// $sql="select * from #__sv_appt".$this->comp_name."resources where published=1 AND category_id = ".$cat_id." ORDER BY ordering"; 

			// Use this for ABPro 4.0.2 and above
			$safe_search_string = '%|' . $database->escape( $cat_id, true ) . '|%' ;							
			$sql="select * from #__sv_appt".$this->comp_name."resources where published=1 AND category_scope LIKE ".$database->quote( $safe_search_string, false )." ORDER BY ordering"; 
//			$sql="select * from #__sv_appt".$this->comp_name."resources where published=1 AND category_scope LIKE '%|".$cat_id."|%' ORDER BY ordering"; 
		}
		try {		
			$database->setQuery($sql);
			$res_rows_raw = NULL;
			$res_rows_raw = $database -> loadObjectList();
			//$num = $database -> getAffectedRows(); 
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		


		$res_rows_count = sv_count_($res_rows_raw );
		//echo $res_rows_count."<br>";
		$res_ids_to_show = "";
		for($i=0; $i < $res_rows_count; $i++) {
			if($this->local_display_this_resource($res_rows_raw[$i], $auth_result)){
				$res_ids_to_show .= $res_rows_raw[$i]->id_resources;
				$res_ids_to_show .= ",";
			}
		}
		$x = strlen($res_ids_to_show);
		if($x>0){
			$res_ids_to_show = substr($res_ids_to_show,0, $x-1);
		}
		//echo $res_ids_to_show;

		$sql="select id_resources, category_id,category_scope,name,description,cost,ordering,resource_email,prevent_dupe_bookings,".
		"max_dupes,resource_admins,rate,rate_unit,allowSunday,allowMonday,allowTuesday,allowWednesday,allowThursday,allowFriday,".
		"allowSaturday,published,timeslots,disable_dates_before,disable_dates_before_days,min_lead_time,disable_dates_after,".
		"disable_dates_after_days,sms_phone,access,enable_coupons,max_seats,non_work_day_message,non_work_day_hide,auto_accept,".
		"deposit_only,deposit_amount,deposit_unit ".
		" from #__sv_appt".$this->comp_name."resources where id_resources IN (".$database->escape($res_ids_to_show).") ORDER BY ordering"; 
		try{
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		$num = $database -> getAffectedRows(); 
		
		//print_r($res_rows_raw);
		//print_r($res_rows);
		//print_r($result->fetch_row());
		//print_r($result->fetch_field());
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		
//		echo "Ext.util.JSONP.callback(".$objJSON->getJSON($result,$num).");";
		echo $objJSON->getJSON($result,$num);
		
	}

	function get_categories()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql="select * from #__sv_appt".$this->comp_name."categories where published=1 ORDER BY ordering"; 
		try{
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_config()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql="select id_config,requireLogin,requireEmail,requirePhone,allowSunday,allowMonday,allowTuesday,".
			"allowWednesday,allowThursday,allowFriday,allowSaturday,timeFormat,enable_coupons,allow_cancellation,".
			"additional_fee,fee_rate,non_pay_booking_button from #__sv_appt".$this->comp_name."config"; 
		try{
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_ues_counts()
	{
		//ues = udfs, extras and seats
		$jinput = JFactory::getApplication()->input;	
		
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}

		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$database = JFactory::getDBO();
		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql='SELECT count(*) as extras_count FROM #__sv_appt'.$this->comp_name.'extras WHERE published=1 AND (resource_scope = "" OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';		
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval = "{ \"data\": [ { \"extras_count\" : \"".$result."\"";

		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql='SELECT count(*) as udfs_count FROM #__sv_appt'.$this->comp_name.'udfs WHERE published=1 AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';		
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval .= ", \"udfs_count\" : \"".$result."\"";

		// 4.0.2 added resource_scope to replace resource_id for multi-resoutce services
		//$sql .= ' AND resource_id = '.$resource.' ORDER BY ordering' ;
		$safe_search_string = '%|' . $database->escape( (int)$res_id, true ) . '|%' ;
		$sql = 'SELECT count(*) as services_count FROM #__sv_appt'.$this->comp_name.'services WHERE published=1 '.
			' AND (resource_scope = \'\' OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).')'. 
			' ORDER BY ordering';		
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval .= ", \"services_count\" : \"".$result."\"";

		$sql = "SELECT balance FROM #__sv_appt".$this->comp_name."user_credit WHERE user_id=".$auth_result;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval .= ", \"user_credit\" : \"".$result."\"";

		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql = 'SELECT count(*) FROM #__sv_appt'.$this->comp_name.'coupons WHERE published=1 AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).')';
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval .= ", \"coupon_count\" : \"".$result."\"";

		$sql = "SELECT off_date FROM #__sv_appt".$this->comp_name."bookoffs ".
		" WHERE resource_id = ".(int)$res_id.
		" AND off_date > now() ".
		" AND full_day = \"Yes\"";

		//echo $sql;
		//exit;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		
		$date_list = "";
		foreach($result as $bookoff){
			if($date_list != ""){
				$date_list .= ",";
			}
			$date_list .= $bookoff->off_date;
		}		
		$retval .= ", \"black_dates\" : \"".$date_list."\"";

		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql='SELECT count(*) as seats_count FROM #__sv_appt'.$this->comp_name.'seat_types WHERE published=1 AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';		
		try{ 
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$retval .= ", \"seats_count\" : \"".$result."\" } ] }";

		echo $retval;
	}

	function get_language_file()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$lang_code = $jinput->getString( 'lang', 'en-GB' );
		
		$lang = JFactory::getLanguage();
		$directory = JPATH_SITE.DIRECTORY_SEPARATOR."language".DS;
		$file = $directory.$lang_code.DIRECTORY_SEPARATOR.$lang_code.".com_rsappt_pro3.mobile.ini";

		if (!file_exists($file)) {
			$lang_code = 'en-GB';
			$file = $directory.$lang_code.DIRECTORY_SEPARATOR.$lang_code.".com_rsappt_pro3.mobile.ini";
		}

		$outline = "{";
		$lines = file($file);  
		$linecount = sv_count_($lines);
		for($i=$linecount;$i>0;$i--){
			if(!strpos($lines[$i],'=') === false){
				$outline .= "\"".str_replace("=", "\":", str_replace("\r\n","", $lines[$i]));//utf8_encode($lines[$i])));
				$outline .= ", ";
			}
		}
		$outline = substr($outline, 0, strlen($outline)-2); // trim last ,
		$outline .="}";

		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript');
			$document->setCharset('utf-8');
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_language_file.json' );
		}

		echo $outline;		
		//echo '{"RS1_INPUT_SCRN_MIDNIGHT":"Midnight", "RS1_INPUT_SCRN_TITLE":"Appointment Booking"}';		
	}
	
	function get_timeslots()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$resource = $jinput->getInt( 'res_id', '-1' );	
		$date = $jinput->getString( 'ts_date', '' );	
		$ts_date = $jinput->getString( 'ts_date', '' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$admin = $jinput->getString( 'admin', 'No' );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}	
		
		if($this->login_required == "Yes"){
			// This code can be called from the Calendar View's timeslots tooltip.
			// If login_required = Yes, see if the user is logged in ( a mobile app won't be but a Calendar View user can)
			$user = JFactory::getUser();
			if($user->guest){		
				$auth_result = authenticateUser($username, $password);
				if($auth_result == "ABPro not found"){
					$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
					echo $result;
					exit;
				}
				if($auth_result == -1){
					$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
					echo $result;
					exit;
				}
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		// objective is to return timeslots for $date / $resource that are not conflicting with any current bookings or book-offs
		// first get day number from date
		$day = date("w", strtotime($date)); 
		
		$database = JFactory::getDBO();
		// now get timeslots 
		// does this resource use global or resource specific..
		$sql = 'SELECT *, id_resources as id FROM #__sv_appt'.$this->comp_name.'resources WHERE id_resources = '.(int)$resource;
		//logIt($sql, "json_x", "", "");
		try{
			$database->setQuery($sql);
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		
		// get config
		$sql = 'SELECT * FROM #__sv_appt'.$this->comp_name.'config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		// check to see if today is a non-work day
		$day_off = false;
		switch($day){
			 case 0: {
				if($res_detail->allowSunday == "No"){$day_off = true;}
				break;
			  }
			 case 1: {
				if($res_detail->allowMonday == "No"){$day_off = true;}
				break;
			  }
			 case 2: {
				if($res_detail->allowTuesday == "No"){$day_off = true;}
				break;
			  }
			 case 3: {
				if($res_detail->allowWednesday == "No"){$day_off = true;}
				break;
			  }
			 case 4: {
				if($res_detail->allowThursday == "No"){$day_off = true;}
				break;
			  }
			 case 5: {
				if($res_detail->allowFriday == "No"){$day_off = true;}
				break;
			  }
			 case 6: {
				if($res_detail->allowSaturday == "No"){$day_off = true;}
				break;
			  }
		}
		if($day_off){
			echo "{ \"data\": [ { \"Message\" : \"Day Off\" }]}";
			return;
		}

		if($res_detail->timeslots == "Global"){
			$timeslot_resource_id = 0;
		} else {
			$timeslot_resource_id = $resource;
		}

		//$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime from  #__sv_appt".$this->comp_name."timeslots WHERE published = 1 AND day_number = ".$day." AND resource_id = ".$timeslot_resource_id." ORDER BY timeslot_starttime";
		$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime from  #__sv_appt".$this->comp_name."timeslots ".
		" WHERE published = 1 ";
		if($admin == 'No'){
			$sql .= ' AND staff_only = "No"' ;
		}	
		$sql .= " AND day_number = ".$day. 
		" AND resource_id = ".$timeslot_resource_id.
		" AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$ts_date."') ".
		" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing >= '".$ts_date."')".
		" ORDER BY timeslot_starttime";
		try{
			$database->setQuery($sql);
			$timeslot_list = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		if(sv_count_($timeslot_list) == 0){
			echo "{ \"data\": [ { \"Message\" : \"No Timeslots\" }]}";
			return;
		}
		
		//echo $sql;
		//print_r($timeslot_list);
		//exit;

		// now get bookings
		$sql = "SELECT *, id_requests as id FROM #__sv_appt".$this->comp_name."requests WHERE resource = ".(int)$resource." AND (request_status = 'accepted' OR request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") ".
			" AND startdate = '".$database->escape($date)."' ".
			" ORDER BY starttime";
		try{	
			$database->setQuery($sql);
			$bookings_list = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		// now get book-offs
		$sql = "SELECT id_bookoffs as id, description, full_day, bookoff_starttime, bookoff_endtime FROM #__sv_appt".$this->comp_name."bookoffs WHERE off_date = '".$database->escape($date)."' AND resource_id = ".(int)$resource." AND published = 1 ".
			" ORDER BY bookoff_starttime";
		try{	
			$database->setQuery($sql);
			$bookoff_list = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		if(sv_count_($bookoff_list) >0){
			foreach($bookoff_list as $bookoff){
				if($bookoff->full_day == "Yes"){
					// thats it we're outa here, its a full day book off
					// make a bogus quesry to return no rows
					$day_off = true;
					echo "{ \"data\": [ { \"Message\" : \"".($bookoff->description!=""?$bookoff->description:"Unavailable Day")."\" }]}";
					return;
				}
			}
		}
		if(!$day_off){
			if(sv_count_($bookings_list) == 0 && sv_count_($bookoff_list) == 0){
				// no bookings or book-offs send all timeslots
				$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime, timeslot_description, ";
				
				if($apptpro_config->timeFormat == '12'){							
					$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%l:%i%p'), ' - ', DATE_FORMAT(timeslot_endtime, '%l:%i%p') ) as startendtime, ".
					"DATE_FORMAT(timeslot_starttime, '%l:%i%p') as display_starttime, ".
					"DATE_FORMAT(timeslot_endtime, '%l:%i%p') as display_endtime, ";
				} else {
					$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%H:%i'), ' - ', DATE_FORMAT(timeslot_endtime, '%H:%i') ) as startendtime, ".
					"DATE_FORMAT(timeslot_starttime, '%H:%i') as display_starttime, ".
					"DATE_FORMAT(timeslot_endtime, '%H:%i') as display_endtime, ";
				}					
				//$sql .=	"'Available' as booked from  #__sv_appt".$this->comp_name."timeslots WHERE published = 1 AND day_number = ".$day.
				//" AND resource_id = ".$timeslot_resource_id." ORDER BY timeslot_starttime";
				$sql .=	"'Available' as booked from #__sv_appt".$this->comp_name."timeslots ".
				" WHERE published = 1 ";
				if($admin == 'No'){
					$sql .= " AND staff_only = 'No'" ;
				}	
				$sql .=" AND day_number = ".(int)$day. 
				" AND resource_id = ".(int)$timeslot_resource_id.
				" AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$database->escape($ts_date)."') ".
				" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing >= '".$database->escape($ts_date)."')".
				" ORDER BY timeslot_starttime";
				//echo $sql;
				//exit;
			} else {				
				// get bookoff blocked ids
				$ts_blocked_ids = "";
				foreach($timeslot_list as $time_slot){
					foreach($bookoff_list as $bookoff){
						if( strtotime($bookoff->bookoff_starttime) == strtotime($time_slot->timeslot_starttime) 
							&& strtotime($bookoff->bookoff_endtime) == strtotime($time_slot->timeslot_endtime)){
								// bkg start & end = ts start & end (bookoff = timeslot)
								$ts_blocked_ids .= $time_slot->id.",";
							} else if( strtotime($bookoff->bookoff_starttime) == strtotime($time_slot->timeslot_starttime)){
								// bkg starts at ts start
								$ts_blocked_ids .= $time_slot->id.",";
							} else if( strtotime($bookoff->bookoff_endtime) == strtotime($time_slot->timeslot_endtime)){
								// bkg end at ts end
								$ts_blocked_ids .= $time_slot->id.",";
							} else if( strtotime($bookoff->bookoff_starttime) > strtotime($time_slot->timeslot_starttime) 
								&& strtotime($bookoff->bookoff_starttime) < strtotime($time_slot->timeslot_endtime)){
								// bkg start > ts start and < ts end (bookoff starts in a timeslot)
								$ts_blocked_ids .= $time_slot->id.",";
							} else if( strtotime($bookoff->bookoff_endtime) > strtotime($time_slot->timeslot_starttime) 
								&& strtotime($bookoff->bookoff_endtime) < strtotime($time_slot->timeslot_endtime)){
								// bkg end > ts start and < ts end (bookoff ends in a timeslot)
								$ts_blocked_ids .= $time_slot->id.",";
							} else if( strtotime($bookoff->bookoff_starttime) < strtotime($time_slot->timeslot_starttime) 
								&& strtotime($bookoff->bookoff_endtime) > strtotime($time_slot->timeslot_endtime)){
								// bkg start < ts start and bkg end > ts end (bookoff covers a timeslot)
								$ts_blocked_ids .= $time_slot->id.",";
							}
						}						
					}

				// get booked ids
				if($res_detail->max_seats > 0){ // this version not compatible with max_seats > 1
					$ts_booked_ids = "";
					foreach($timeslot_list as $time_slot){
						foreach($bookings_list as $booking){
							if($this->local_fullyBooked($booking, $res_detail, $apptpro_config)){
								if( strtotime($booking->starttime) == strtotime($time_slot->timeslot_starttime) 
									&& strtotime($booking->endtime) == strtotime($time_slot->timeslot_endtime)){
										// bkg start & end = ts start & end (booking = timeslot)
										$ts_booked_ids .= $time_slot->id.",";
									} else if( strtotime($booking->starttime) == strtotime($time_slot->timeslot_starttime)){
										// bkg starts at ts start
										$ts_booked_ids .= $time_slot->id.",";
									} else if( strtotime($booking->endtime) == strtotime($time_slot->timeslot_endtime)){
										// bkg end at ts end
										$ts_booked_ids .= $time_slot->id.",";
									} else if( strtotime($booking->starttime) > strtotime($time_slot->timeslot_starttime) 
										&& strtotime($booking->starttime) < strtotime($time_slot->timeslot_endtime)){
										// bkg start > ts start and < ts end (booking starts in a timeslot)
										$ts_booked_ids .= $time_slot->id.",";
									} else if( strtotime($booking->endtime) > strtotime($time_slot->timeslot_starttime) 
										&& strtotime($booking->endtime) < strtotime($time_slot->timeslot_endtime)){
										// bkg end > ts start and < ts end (booking ends in a timeslot)
										$ts_booked_ids .= $time_slot->id.",";
									} else if( strtotime($booking->starttime) < strtotime($time_slot->timeslot_starttime) 
										&& strtotime($booking->endtime) > strtotime($time_slot->timeslot_endtime)){
										// bkg start < ts start and bkg end > ts end (booking covers a timeslot)
										$ts_booked_ids .= $time_slot->id.",";
									}
								}
							}
						}
				}
//						echo "\ndate = ".$date."\n";
//						echo "bookings count=".sv_count_($bookings_list)."\n";
//						echo "bookoffs count=".sv_count_($bookoff_list)."\n";
//						
//						echo "ts_booked_ids=".$ts_booked_ids."\n";
//						echo "ts_blocked_ids=".$ts_blocked_ids."\n";

				if($ts_booked_ids != "" && $ts_blocked_ids != ""){
					// both booked and blocked
					$booked_exp = " IF(id_timeslots IN(".substr($ts_booked_ids,0,strlen($ts_booked_ids)-1)."),'Booked', IF(id_timeslots IN(".substr($ts_blocked_ids,0,strlen($ts_blocked_ids)-1)."),'Unavailable','Available')) as booked ";
				} else if($ts_booked_ids != "" && $ts_blocked_ids == ""){
					// only booked
					$booked_exp = " IF(id_timeslots IN(".substr($ts_booked_ids,0,strlen($ts_booked_ids)-1)."),'Booked', 'Available') as booked ";
				} else if($ts_booked_ids == "" && $ts_blocked_ids != ""){
					// only blocked
					$booked_exp = " IF(id_timeslots IN(".substr($ts_blocked_ids,0,strlen($ts_blocked_ids)-1)."),'Unavailable', 'Available') as booked ";
				} else {
					// neither
					$booked_exp = "'Available' as booked ";
				}
				$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime, timeslot_description, ";
				if($apptpro_config->timeFormat == '12'){							
					$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%l:%i%p'), ' - ', DATE_FORMAT(timeslot_endtime, '%l:%i%p') ) as startendtime, ".
					"DATE_FORMAT(timeslot_starttime, '%l:%i%p') as display_starttime, ".
					"DATE_FORMAT(timeslot_endtime, '%l:%i%p') as display_endtime, ";
				} else {
					$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%H:%i'), ' - ', DATE_FORMAT(timeslot_endtime, '%H:%i') ) as startendtime, ".
					"DATE_FORMAT(timeslot_starttime, '%H:%i') as display_starttime, ".
					"DATE_FORMAT(timeslot_endtime, '%H:%i') as display_endtime, ";
				}					
//				"CONCAT( DATE_FORMAT(timeslot_starttime, '%H:%i'), ' - ', DATE_FORMAT(timeslot_endtime, '%H:%i') ) as startendtime, ".
//				"DATE_FORMAT(timeslot_starttime, '%H:%i') as display_starttime, ".
//				"DATE_FORMAT(timeslot_endtime, '%H:%i') as display_endtime, ".
				$sql .=	$booked_exp."  from  #__sv_appt".$this->comp_name."timeslots ".
//				" WHERE published = 1 AND day_number = ".$day." AND resource_id = ".$timeslot_resource_id." ORDER BY timeslot_starttime";
				" WHERE published = 1 ";
				if($admin == 'No'){
					$sql .= " AND staff_only = 'No'" ;
				}	
				$sql .= " AND day_number = ".(int)$day. 
				" AND resource_id = ".(int)$timeslot_resource_id.
				" AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$database->escape($ts_date)."') ".
				" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing >= '".$database->escape($ts_date)."')".
				" ORDER BY timeslot_starttime";
				//echo $sql;
				//exit;
			}
		}
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		// The Joomla 4 way
		$retval = "{\n\"data\": \n";
		$retval .= json_encode($result);
		$retval.="\n\n}\n";
		//logIt($retval, "json_x", "", "");
		echo $retval;
		
	}

	function get_services()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$admin = $jinput->getString( 'admin', 'No' );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql = 'SELECT *, id_services as id FROM #__sv_appt'.$this->comp_name.'services where published = 1 ';
		if($admin == 'No'){
			$sql .= ' AND staff_only = "No"' ;
		}	
//		$sql .= ' AND resource_id = '.(int)$res_id.' ORDER BY ordering' ;
		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;
		$sql .= ' AND resource_scope LIKE '.$database->quote( $safe_search_string, false ); 
		$sql .= 'ORDER BY ordering' ;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_udfs()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql = 'SELECT *, id_udfs as id FROM #__sv_appt'.$this->comp_name.'udfs WHERE published=1 '.
			'AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';
		try {	
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_extras()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$adm = $jinput->getInt( 'adm', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql='SELECT * FROM #__sv_appt'.$this->comp_name.'extras WHERE published=1 '.($adm == 0?' AND staff_only="No"':'').' AND (resource_scope = "" OR resource_scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_seats()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$safe_search_string = '%|' . $database->escape( $res_id, true ) . '|%' ;										
		$sql = 'SELECT *, id_seat_types as id FROM #__sv_appt'.$this->comp_name.'seat_types WHERE published=1 AND (scope = "" OR scope LIKE '.$database->quote( $safe_search_string, false ).') ORDER BY ordering';
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_seats_available()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$ts_date = $jinput->getString( 'ts_date', '' );	
		$ts_start = $jinput->getString( 'ts_start', '' );	
		$ts_end = $jinput->getString( 'ts_end', '' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}

		$database = JFactory::getDBO();		
		$sql = 'SELECT max_seats FROM #__sv_appt'.$this->comp_name.'resources WHERE id_resources ='.(int)$res_id;
		try {
			$database->setQuery($sql);
			$max_seats = NULL;
			$max_seats = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		$seats_taken = local_getCurrentSeatCount($ts_date, $ts_start, $ts_end, $res_id, -1);
		if($seats_taken != null){			
			$seats_left = intval($max_seats) - intval($seats_taken);
			$retval = "{ \"data\": [ { \"seats_available\" : \"".$seats_left."\" } ] }";

		} else {
			$retval = "{ \"data\": [ { \"seats_available\" : \"".$max_seats."\" } ] }";
		}
		echo $retval;

	}

	function get_seat_values()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$req_id = $jinput->getInt( 'req_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql = "SELECT seat_type_id, seat_type_label, seat_type_qty FROM ".
			" #__sv_appt".$this->comp_name."seat_counts INNER JOIN #__sv_appt".$this->comp_name."seat_types ".
			"   ON #__sv_appt".$this->comp_name."seat_counts.seat_type_id = #__sv_appt".$this->comp_name."seat_types.id_seat_types ".
			" WHERE #__sv_appt".$this->comp_name."seat_counts.request_id = ".(int)$req_id. " ".
			" ORDER BY ordering ";
		try {	
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_mybookings()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'site_id', '-1' );	
		$booking_ids = $jinput->getString( 'booking_ids', '1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$cancel_codes = $jinput->getString( 'cc', '-1' );	

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		$auth_result = -1;
		if($this->login_required == "Yes" || $username != ""){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
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
			logIt($e->getMessage(), "ctrl_json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
			
		// now get their details and order by startdattime
		$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."requests.name, #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.phone,". 
		"#__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status, #__sv_appt".$this->comp_name."requests.cancellation_id,".
		"#__sv_appt".$this->comp_name."resources.resource_admins, ".
		"#__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."services.name AS ServiceName,  ".
		"CONCAT(#__sv_appt".$this->comp_name."requests.startdate, ' ',#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
		"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e ') as display_startdate, ".
		"CONCAT(DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%H:%i'), '/', DATE_FORMAT(#__sv_appt".$this->comp_name."requests.endtime, '%H:%i')) as display_starttime ".
		"FROM ( ".
		"#__sv_appt".$this->comp_name."requests LEFT JOIN ".
		"#__sv_appt".$this->comp_name."resources ON #__sv_appt".$this->comp_name."requests.resource = ".
		"#__sv_appt".$this->comp_name."resources.id_resources LEFT JOIN ".
		"#__sv_appt".$this->comp_name."services ON #__sv_appt".$this->comp_name."requests.service = ".
		"#__sv_appt".$this->comp_name."services.id_services ) ".
		" WHERE ";
		if($auth_result >0){
			$sql .= " (#__sv_appt".$this->comp_name."requests.id_requests IN (".$database->escape($booking_ids). ") OR #__sv_appt".$this->comp_name."requests.cancellation_id IN (".str_replace("\\","",$cancel_codes).") OR user_id=".$auth_result.") ";
		} else {
			if($cancel_codes != "-1"){
				$sql .= " #__sv_appt".$this->comp_name."requests.cancellation_id IN (".str_replace("\\","",$cancel_codes).") ";
			} else {
				$sql .= " #__sv_appt".$this->comp_name."requests.id_requests IN (".$database->escape($booking_ids). ") ";
			}
		}
		$sql .= " AND #__sv_appt".$this->comp_name."requests.startdate >= CURDATE() ".
		" ORDER BY startdatetime asc"; 
		//echo $sql;
		//exit;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_user_credit()
	{
		$jinput = JFactory::getApplication()->input;	
		// not used, added to get_ues_counts to save a server trip
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'site_id', '-1' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		

		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		// now get their details and order by startdattime
		$database = JFactory::getDBO();
		$sql = "SELECT balance FROM #__sv_appt".$this->comp_name."user_credit".
		" WHERE user_id=".$auth_result;
		
		//echo $sql;
		//exit;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function get_booking_detail()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'site_id', '-1' );	
		$req_id = $jinput->getInt( 'req_id', '' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$cancel_code = $jinput->getString( 'cc', '-1' );	

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
		
//		$config = JFactory::getConfig();
//		$tzoffset = $config->get('offset');  
//		if(intval($tzoffset) < 0){
//			$offsetsign = "-";
//		} else {
//			$offsetsign = "+";
//		}
//		$absoffset = "".abs(intval($tzoffset));
			
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
			logIt($e->getMessage(), "ctrl_json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
			
		// now get their details and order by startdattime
			$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, ".//#__sv_appt".$this->comp_name."requests.*, ".
			" #__sv_appt".$this->comp_name."requests.user_id, #__sv_appt".$this->comp_name."requests.name, 0 as unit_number, #__sv_appt".$this->comp_name."requests.phone, ".
			" #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.sms_phone, #__sv_appt".$this->comp_name."requests.resource, #__sv_appt".$this->comp_name."requests.startdate, ".
			" #__sv_appt".$this->comp_name."requests.starttime, #__sv_appt".$this->comp_name."requests.enddate, #__sv_appt".$this->comp_name."requests.endtime, #__sv_appt".$this->comp_name."requests.comment as not_used, ".
			" #__sv_appt".$this->comp_name."requests.admin_comment as comment, #__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status, #__sv_appt".$this->comp_name."requests.service, ".
			" #__sv_appt".$this->comp_name."requests.booked_seats, #__sv_appt".$this->comp_name."requests.cancellation_id, #__sv_appt".$this->comp_name."requests.booking_due,".
			"   #__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."resources.description as resdesc, ".
			"	#__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."paypal_transactions.id_paypal_transactions AS pp_txnid, ".
			"   IF(#__sv_appt".$this->comp_name."services.name='' OR #__sv_appt".$this->comp_name."services.name IS NULL ,'< not entered >',#__sv_appt".$this->comp_name."services.name) as ServiceName, ". 
			"   #__sv_appt".$this->comp_name."services.service_duration, #__sv_appt".$this->comp_name."services.service_duration_unit, ". 
			"   IF(email='','< not entered >',email) as email, ". 
			"   IF(phone='','< not entered >',phone) as phone, ". 
			"   IF(#__sv_appt".$this->comp_name."requests.sms_phone='','< not entered >',#__sv_appt".$this->comp_name."requests.sms_phone) as sms_phone, ". 
			"  CONCAT(#__sv_appt".$this->comp_name."requests.startdate,#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e') as display_startdate, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %e-%b-%Y') as display_startdate2, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.endtime, '%k:%i') as display_endtime, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%k:%i') as display_starttime, ".
			"  CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ',#__sv_appt".$this->comp_name."requests.starttime) as startdatetimegmt, ".
			"  CONCAT(#__sv_appt".$this->comp_name."requests.enddate,' ',#__sv_appt".$this->comp_name."requests.endtime) as enddatetimegmt ".
//			"  CONVERT_TZ(CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ',#__sv_appt".$this->comp_name."requests.starttime), '".$offsetsign.$absoffset.":00', '+00:00') as startdatetimegmt, ".
//			"  CONVERT_TZ(CONCAT(#__sv_appt".$this->comp_name."requests.enddate,' ',#__sv_appt".$this->comp_name."requests.endtime), '".$offsetsign.$absoffset.":00', '+00:00') as enddatetimegmt ".
			" FROM ".
			"   #__sv_appt".$this->comp_name."requests INNER JOIN #__sv_appt".$this->comp_name."resources  ".
			"   ON #__sv_appt".$this->comp_name."requests.resource = #__sv_appt".$this->comp_name."resources.id_resources LEFT JOIN  ".
			"   #__sv_appt".$this->comp_name."paypal_transactions  ".
			"   ON #__sv_appt".$this->comp_name."requests.txnid = #__sv_appt".$this->comp_name."paypal_transactions.txnid LEFT JOIN  ".
			"   #__sv_appt".$this->comp_name."services ON #__sv_appt".$this->comp_name."requests.service = ".
			"   #__sv_appt".$this->comp_name."services.id_services ";
			if($cancel_code != "-1"){
				$sql .= " WHERE #__sv_appt".$this->comp_name."requests.cancellation_id = ".str_replace("\\","",$cancel_code);				
			} else {
				$sql .= " WHERE #__sv_appt".$this->comp_name."requests.id_requests = ".(int)$req_id;
			}
			//echo $sql;
		try {	
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
	}

	function insertBooking()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );

		$name=$jinput->getString( 'name', '' );
		$user_id=$jinput->getInt( 'user_id', '' );
		$phone=$jinput->getString( 'phone', '' );
		$email=$jinput->getString( 'email', '' );
		$sms_phone=$jinput->getString( 'sms_phone', '' );
		$resource=$jinput->getInt( 'res_id', '' );
		$service_name=$jinput->getString( 'service', '' );
		$startdate=$jinput->getString( 'startdate', '' );
		$starttime=$jinput->getString( 'starttime', '' );
		$enddate=$jinput->getString( 'enddate', '' );
		$endtime=$jinput->getString( 'endtime', '' );
		$request_status=$jinput->getWord( 'request_status', 'new' );
		$booked_seats=$jinput->getString( 'booked_seats', '' );
		$comment=$jinput->getString( 'comment', '(ABPro Mobile booking)' );
		$seat_info=$jinput->getString( 'seat_info', '' );
		$udf_values_info=$jinput->getString( 'udf_values_info', '' );
		$extras_values_info=$jinput->getString( 'extras_values_info', '' );
		$amount_due=$jinput->getString( 'amount_due', '0' );
		$credit_used=$jinput->getString( 'credit_used', '0' );
		$coupon_used = $jinput->getString( 'coupon_used', '' );
		$deposit_amount=$jinput->getString( 'deposit_amount', '0' );

		$category = $jinput->getInt( 'category', '' );
		$from_admin = $jinput->getWord( 'fa', 'No' );
		$use_PayPay = $jinput->getWord( 'use_PayPal', 'No' );
		$ppURL = "";
		
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}
		// if login not required, still check for usier using credit		
		$auth_result = authenticateUser($username, $password);
		if($auth_result != -1 && $user_id == ""){
			// no id passed from mobile but username and password were, so use them
			$user_id = $auth_result;
		}
		
//		if($auth_result >0){
//			$userID = $auth_result;

			require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'functions2.php' );
			require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'sendmail_pro2.php' );

			$database = JFactory::getDBO();

			// get config
			$sql = "SELECT * FROM #__sv_appt".$this->comp_name."config";
			try{
				$database->setQuery($sql);
				$apptpro_config = NULL;
				$apptpro_config = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			// get resource details
			$sql = "SELECT * FROM #__sv_appt".$this->comp_name."resources WHERE id_resources = ".(int)$resource;
			try{
				$database->setQuery($sql);
				$res_detail = NULL;
				$res_detail = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			if($from_admin != "Yes"){
				if($apptpro_config->auto_accept == "Yes"){
					$request_status = "accepted";
				} 
			}
			if($use_PayPay == "Yes"){
					$request_status = "pending";
			}
			
			$last_id = NULL;
			$cancel_code = md5(uniqid(rand(), true));
			$last_id = $this->local_saveToDB($name, $user_id, $phone, $email, ($sms_phone!=""?"Yes":"No"), $sms_phone, 
				$apptpro_config->clickatell_dialing_code, $resource, $category, $service_name, $startdate, $starttime, $enddate, $endtime, 
				$request_status, $cancel_code, $amount_due, $amount_due, $deposit_amount, $coupon_used, $booked_seats, $credit_used, $comment, $comment);		
			if($last_id == NULL || $last_id->last_id == -1){
				echo "{ \"data\": [ { \"Error\" : \"Error on Insert\" }]}";
				return;
			}		

			$str_last_id = strval($last_id->last_id);
			
			// get cancel code to pass to mobile
			$sql = "SELECT cancellation_id FROM #__sv_appt".$this->comp_name."requests WHERE id_requests = ".(int)$str_last_id;
			try{
				$database->setQuery($sql);
				$cc = NULL;
				$cc = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
			
			if($seat_info != ""){
				// add seat values
				$seat_types_array = explode(",", $seat_info);
				
				for($sta=0; $sta<sv_count_($seat_types_array)-1; $sta++){
					$seat_type_info = explode(":", $seat_types_array[$sta]);
					$seat_type_id = $seat_type_info[0];
					$seat_type_qty = $seat_type_info[1];
					if($seat_type_qty > 0){
						$sSql = sprintf("INSERT INTO #__sv_appt".$this->comp_name."seat_counts (seat_type_id, request_id, seat_type_qty) VALUES(%d, %d, '%s')",
								$seat_type_id,
								$last_id->last_id,
								$seat_type_qty);
						try{
							$database->setQuery($sSql);
							$database->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "ctrl_json_x", "", "");
							echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
							return;
						}
					}
				}				
			}
			
			if($udf_values_info != ""){
				// add udf values
				$udf_values_array = explode("~", $udf_values_info);
				
				for($i1=0; $i1<sv_count_($udf_values_array)-1; $i1++){
					$specific_udf_info = explode(";", $udf_values_array[$i1]);
					$udf_id = $specific_udf_info[0];
					$udf_value = $specific_udf_info[1];
					$sSql = sprintf("INSERT INTO #__sv_appt".$this->comp_name."udfvalues (udf_id, request_id, udf_value) VALUES(%d, %d, '%s')",
							$udf_id,
							$last_id->last_id,
							$udf_value);
					try{
						$database->setQuery($sSql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_json_x", "", "");
						echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
						return;
					}
				}				
			}

			if($extras_values_info != ""){
				// add extras values
				$extra_types_array = explode(",", $extras_values_info);
				
				for($i2=0; $i2<sv_count_($extra_types_array)-1; $i2++){
					$specific_extra_info = explode(":", $extra_types_array[$i2]);
					$extra_id = $specific_extra_info[0];
					$extra_qty = $specific_extra_info[1];
					if($extra_qty > 0){
						$sSql = sprintf("INSERT INTO #__sv_appt".$this->comp_name."extras_data (extras_id, request_id, extras_qty) VALUES(%d, %d, '%s')",
								$extra_id,
								$last_id->last_id,
								$extra_qty);
						try{
							$database->setQuery($sSql);
							$database->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "ctrl_json_x", "", "");
							echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
							return;
						}
					}
				}				
			}

			// dev only
			//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 
			
			// If this is a PayPal booking, the emails are sent by the PayPal IPN callback
			if($use_PayPay == "No"){
				// send form		
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
					return false;		
				}
				$message = "";
				$message_admin = "";
			
				if($request_status == "accepted"){
					$message .= buildMessage(strval($last_id->last_id), "confirmation", "No");
					$message_admin .= buildMessage(strval($last_id->last_id), "confirmation_admin", "No");
				} else {
					$message .= buildMessage(strval($last_id->last_id), "in_progress", "No");			
					$message_admin .= buildMessage(strval($last_id->last_id), "in_progress_admin", "No");			
				}
			
				if($apptpro_config->html_email != "Yes"){
					$message = str_replace("<br>", "\r\n", $message);
					$message_admin = str_replace("<br>", "\r\n", $message_admin);
				}

				$array = array($last_id->last_id);
				$ics = buildICSfile($array);

				// email to customer
				if($jinput->getString('email') != ""){
					$to = $jinput->getString('email');

					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}

					if($apptpro_config->attach_ics_customer == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
					}

					try{
						$mailer->addRecipient($to);
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}


				}
			
				// email to admin
				if($apptpro_config->mailTO != ""){
					$to = $apptpro_config->mailTO;

					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}

					if($apptpro_config->attach_ics_admin == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
					}

					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}

					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
				}
			
				// email to resource
				if($res_detail->resource_email != ""){
					$to = $res_detail->resource_email;

					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}

					if($apptpro_config->attach_ics_resource == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
					}

					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
				}

				// SMS to resource
				if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
					$config = JFactory::getConfig();
					$tzoffset = $config->get('offset');
				$tz = new DateTimeZone($tzoffset);
				$offsetdate = new JDate("now", $tz);
					$reminder_log_time_format = "Y-m-d H:i:s";
					if($user_id == ""){
						$bookingUser = -1;
					} else {
						$bookingUser = $user_id;
					}
					$returnCode = "";
	
					if($request_status == "accepted"){
						sv_sendSMS($last_id->last_id, "confirmation", $returnCode, $toResource="Yes");			
						if($apptpro_config->sms_confirmation == "Yes" && $sms_phone != ""){
							sv_sendSMS($last_id->last_id, "confirmation", $returnCode, $toResource="No");			
						}
					} else {
						sv_sendSMS($last_id->last_id, "in_progress", $returnCode, $toResource="Yes");			
					}
					logReminder("New booking (mobile) SMS response: ".$returnCode, $last_id->last_id, $bookingUser, $name, $offsetdate->format($reminder_log_time_format, true, true));
				}
			} else {
				if($deposit_amount != "0"){
					$amount_due = $deposit_amount;
				}				
				// This is a PayPal order, we need to build the URL that will take the customer to PayPal to pay.
				$ppURL = localGoToPayPal($last_id->last_id, $apptpro_config, $amount_due, "", "", "Yes");
			}
			// do calendar stuff is required
			if(!addToCalendar($str_last_id, $apptpro_config, "Yes")){
				echo "{ \"data\": [ { \"Error\" : \"Error adding to calendar, check ABPro Error Log\" }]}";
				return;
			}
	
		$retval = "{ \"data\": [ { \"InsertResult\" : \"OK\" ,\"request_id\" : \"".$last_id->last_id."\",\"cc\" : \"".$cc."\" ";
		if($use_PayPay == "Yes"){
			$retval .= ",\"ppURL\" : \"".$ppURL."\" ";
		}
		
		$retval .= "}]}";
		echo $retval;

		//}
    }

	function checkAccessCode($user_site_access_code)	
	{
		$jinput = JFactory::getApplication()->input;	
		if($this->site_access_code != ""){
			if($user_site_access_code != $this->site_access_code){
				return -1;
			}
		}
	}

	function get_coupon_data()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$resource = $jinput->getInt( 'res_id', '-1' );	
		$coupon_code = $jinput->getString( 'cc', '' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );


		if($coupon_code == ""){
			$result = "{ \"data\": [ { \"Error\" : \"No Coupon Code provided\" } ] }";
			echo $result;
			exit;
		}

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		if($this->login_required == "Yes"){
			$auth_result = authenticateUser($username, $password);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
				echo $result;
				exit;
			}
		}

		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$database = JFactory::getDBO();
			
		$database = JFactory::getDBO(); 
		$sql = "SELECT *, DATE_FORMAT(expiry_date, '%Y-%m-%d') as expiry FROM #__sv_appt".$this->comp_name."coupons where coupon_code = '".$database->escape($coupon_code)."' and published=1";
		try{
			$database->setQuery($sql);
			$coupon_detail = NULL;
			$coupon_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		
		$coupon_refused = false;
		
		// check scope
		if($coupon_detail->scope != ""){
			// one or more resources hae been specified
			if(strpos($coupon_detail->scope, '|'.$resource.'|') === false){
				// coupon not valis for this resource
				//echo JText::_('RS1_INPUT_SCRN_COUPON_INVALID_4_RESOURCE')."|0|";
				$retval = "{ \"data\": [ { \"Description\":\"Coupon Refused\",\"Reason\":\"".JText::_('RS1_INPUT_SCRN_COUPON_INVALID_4_RESOURCE')."\" } ] }";
				$coupon_refused = true;
			}				 			
		}
		if($coupon_detail == NULL){
			//echo JText::_('RS1_INPUT_SCRN_COUPON_INVALID')."|0|";
			$retval = "{ \"data\": [ { \"Description\":\"Coupon Refused\",\"Reason\":\"".JText::_('RS1_INPUT_SCRN_COUPON_INVALID')."\" } ] }";
			$coupon_refused = true;
		} else if(strtotime("now") > strtotime($coupon_detail->expiry)){
			//echo JText::_('RS1_INPUT_SCRN_COUPON_EXPIRED')."|0|";
			$retval = "{ \"data\": [ { \"Description\":\"Coupon Refused\",\"Reason\":\"".JText::_('RS1_INPUT_SCRN_COUPON_EXPIRED')."\" } ] }";
			$coupon_refused = true;
		} else {		
			// Check for Max Total Usage
			if($coupon_detail->max_total_use > 0){
				// get total useage count
				$sql = "SELECT count(*) FROM #__sv_appt".$this->comp_name."requests WHERE coupon_code = '".$database->escape($coupon_code)."' ".
					" AND (".
					"	request_status = 'accepted' ".
					" 	OR request_status = 'attended' ".
					" 	OR request_status = 'completed' ".
					")";
				try{	
					$database->setQuery($sql);
					$coupon_count = NULL;
					$coupon_count = $database -> loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "json_x", "", "");
					echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
					return;
				}		
				if($coupon_count >= $coupon_detail->max_total_use){
					//echo JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."|0|";
					$retval = "{ \"data\": [ { \"Description\":\"Coupon Refused\",\"Reason\":\"".JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."\" } ] }";
					$coupon_refused = true;
				}
			}		

			// Check for Max User Usage
			if($coupon_detail->max_user_use > 0 and $auth_result != -1){
				// get total useage count
				$sql = "SELECT count(*) FROM #__sv_appt".$this->comp_name."requests WHERE coupon_code = '".$database->escape($coupon_code)."' ".
					" AND user_id = ".$auth_result." ".
					" AND (".
					"	request_status = 'accepted' ".
					" 	OR request_status = 'attended' ".
					" 	OR request_status = 'completed' ".
					")";
				try{	
					$database->setQuery($sql);
					$coupon_count = NULL;
					$coupon_count = $database -> loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "json_x", "", "");
					echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
					return;
				}		
				if($coupon_count >= $coupon_detail->max_user_use){
					//echo JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."|0|";
					$retval = "{ \"data\": [ { \"Description\":\"Coupon Refused\",\"Reason\":\"".JText::_('RS1_INPUT_SCRN_COUPON_MAXED_OUT')."\" } ] }";
					$coupon_refused = true;
				}
			}		
			
		}
					
		if($coupon_refused == false){
			$retval = "{ \"data\": [ { \"Description\":\"".JText::_($coupon_detail->description)."\",".
			"\"discount\":\"".$coupon_detail->discount."\",".
			"\"discount_unit\":\"".$coupon_detail->discount_unit."\"".
			"} ] }";
		}

		echo $retval;
		exit;

	}
		
		
	// Admin tasks
	function get_adm_resources()
	{		
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$cat_id = $jinput->getInt( 'cat_id', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql = "SELECT id_resources, name, description, disable_dates_before, disable_dates_before_days, disable_dates_after, disable_dates_after_days, ".
		" id_resources as id FROM #__sv_appt".$this->comp_name."resources ".
		"WHERE published=1 AND resource_admins LIKE '%|".$auth_result."|%' ".
		"ORDER BY ordering;";
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
		
	}

	function get_adm_bookings()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', -1 );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$list_type = $jinput->getWord( 'list_type', '' );	
		$future_days = $jinput->getInt( 'days', '7' );	
		$byname = $jinput->getString( 'byname', '-1' );	
		$future_only = $jinput->getWord( 'fo', 'Yes' );	
		$day_offset = $jinput->getInt( 'offset', '0' );	
		$specific_date = $jinput->getString( 'sd', '' );
		if($res_id == ""){
			$res_id = -1;
		}
		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_bookings.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
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
			logIt($e->getMessage(), "ctrl_json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
		switch($list_type){
			case "recent":
				$id_list = null;
				// need a list of 5 most recent
				$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id FROM (#__sv_appt".$this->comp_name."requests LEFT JOIN #__sv_appt".$this->comp_name."resources ".
					" ON #__sv_appt".$this->comp_name."requests.resource = #__sv_appt".$this->comp_name."resources.id_resources) WHERE #__sv_appt".$this->comp_name."resources.published=1 ";
					if($res_id != -1){
						$sql .= " AND #__sv_appt".$this->comp_name."requests.resource = ".(int)$res_id. " ";
					} else {
						$sql .= " AND #__sv_appt".$this->comp_name."resources.resource_admins LIKE '%|".$auth_result."|%' ";
					}
				$sql .= " ORDER BY created desc limit 5";
				//echo $sql;
				try {
					$database->setQuery($sql);
					$id_list = $database->loadColumn();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "json_x", "", "");
					echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
					return;
				}		

				// now get their details and order by startdattime
				$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."requests.name, #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.phone,". 
				"#__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status,".
				"#__sv_appt".$this->comp_name."resources.resource_admins, ".
				"#__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."services.name AS ServiceName,  ".
				"CONCAT(#__sv_appt".$this->comp_name."requests.startdate, ' ',#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e ') as display_startdate, ".
				"CONCAT(DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%H:%i'), '/', DATE_FORMAT(#__sv_appt".$this->comp_name."requests.endtime, '%H:%i')) as display_starttime ".
				"FROM ( ".
				"#__sv_appt".$this->comp_name."requests LEFT JOIN ".
				"#__sv_appt".$this->comp_name."resources ON #__sv_appt".$this->comp_name."requests.resource = ".
				"#__sv_appt".$this->comp_name."resources.id_resources LEFT JOIN ".
				"#__sv_appt".$this->comp_name."services ON #__sv_appt".$this->comp_name."requests.service = ".
				"#__sv_appt".$this->comp_name."services.id_services ) ".
				" WHERE #__sv_appt".$this->comp_name."requests.id_requests IN (". implode(",", $id_list) . ") ".
				" ORDER BY startdatetime asc"; 
				//echo $sql;
				break;
			
			case "upcoming":
				$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."requests.name, #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.phone,". 
				"#__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status,".
				"#__sv_appt".$this->comp_name."resources.resource_admins, ".
				"#__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."services.name AS ServiceName,  ".
				"CONCAT(#__sv_appt".$this->comp_name."requests.startdate, ' ', #__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e ') as display_startdate, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%H:%i') as display_starttime ".
				"FROM ( ".
				'#__sv_appt'.$this->comp_name.'requests LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'resources ON #__sv_appt'.$this->comp_name.'requests.resource = '.
				'#__sv_appt'.$this->comp_name.'resources.id_resources LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'services ON #__sv_appt'.$this->comp_name.'requests.service = '.
				'#__sv_appt'.$this->comp_name.'services.id_services ) '.
				"WHERE ";
				if($res_id != -1){
					$sql .= " #__sv_appt".$this->comp_name."requests.resource = ".(int)$res_id. " ";
				} else {
					$sql .= " #__sv_appt".$this->comp_name."resources.resource_admins LIKE '%|".$auth_result."|%' ";
				}
				$sql .= " AND CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ', #__sv_appt".$this->comp_name."requests.starttime) >= NOW() ".
				" AND CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ', #__sv_appt".$this->comp_name."requests.starttime) <=  NOW() + INTERVAL ".$future_days." DAY ".
				" ORDER BY startdate, starttime";
				//echo $sql;
				break;
				
			case "byname":
				$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."requests.name, #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.phone,". 
				"#__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status,".
				"#__sv_appt".$this->comp_name."resources.resource_admins, ".
				"#__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."services.name AS ServiceName,  ".
				"CONCAT(#__sv_appt".$this->comp_name."requests.startdate,#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e ') as display_startdate, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%H:%i') as display_starttime ".
				"FROM ( ".
				'#__sv_appt'.$this->comp_name.'requests LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'resources ON #__sv_appt'.$this->comp_name.'requests.resource = '.
				'#__sv_appt'.$this->comp_name.'resources.id_resources LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'services ON #__sv_appt'.$this->comp_name.'requests.service = '.
				'#__sv_appt'.$this->comp_name.'services.id_services ) '.
				"WHERE #__sv_appt".$this->comp_name."requests.name LIKE '%".filter_var(addslashes($byname), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW)."%' ";
				if($res_id != -1){
					$sql .= " AND #__sv_appt".$this->comp_name."requests.resource = ".(int)$res_id. " ";
				} else {
					$sql .= " AND #__sv_appt".$this->comp_name."resources.resource_admins LIKE '%|".$auth_result."|%' ";
				}
				if($future_only === "Yes"){
					$sql .= " AND #__sv_appt".$this->comp_name."requests.startdate > now() ";					
				}
				$sql .= " ORDER BY startdate, starttime";
				//echo $sql;
			
				break;				
			
			case "daily":
				$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."requests.name, #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.phone,". 
				"#__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status,".
				"#__sv_appt".$this->comp_name."resources.resource_admins, ".
				"#__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."services.name AS ServiceName,  ".
				"CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ',#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e ') as display_startdate, ".
				"DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%H:%i') as display_starttime ".
				"FROM ( ".
				'#__sv_appt'.$this->comp_name.'requests LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'resources ON #__sv_appt'.$this->comp_name.'requests.resource = '.
				'#__sv_appt'.$this->comp_name.'resources.id_resources LEFT JOIN '.
				'#__sv_appt'.$this->comp_name.'services ON #__sv_appt'.$this->comp_name.'requests.service = '.
				'#__sv_appt'.$this->comp_name.'services.id_services ) '.
				"WHERE ";
				
				$res_id_clause = " AND #__sv_appt".$this->comp_name."requests.resource = ".(int)$res_id." ";
				if($res_id === -1){
					$res_id_clause = "";
				}
				if($specific_date == ""){
					$sql .= "startdate = DATE_ADD('".date("Y-m-d")."',INTERVAL ".(int)$day_offset." DAY) ".
					$res_id_clause." AND ";
				} else {
					$sql .= "startdate = '".$database->escape($specific_date)."' ".
					$res_id_clause." AND ";
				}
				$sql .= "#__sv_appt".$this->comp_name."resources.resource_admins LIKE '%|".$auth_result."|%' ".
				" ORDER BY startdate, starttime";			
				//echo $sql;
				break;	
		}
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();

		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
		}

	function get_adm_booking_detail()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'site_id', '-1' );	
		$req_id = $jinput->getInt( 'req_id', '' );	
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}

		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_booking_detail.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
		
//		$config = JFactory::getConfig();
//		$tzoffset = $config->get('offset');  
//		if(intval($tzoffset) < 0){
//			$offsetsign = "-";
//		} else {
//			$offsetsign = "+";
//		}
//		$absoffset = "".abs(intval($tzoffset));
			
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
			logIt($e->getMessage(), "ctrl_json_x", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
			
		// now get their details and order by startdattime
			$sql = "SELECT #__sv_appt".$this->comp_name."requests.id_requests as id, ".//#__sv_appt".$this->comp_name."requests.*, ".
			" #__sv_appt".$this->comp_name."requests.user_id, #__sv_appt".$this->comp_name."requests.name, 0 as unit_number, #__sv_appt".$this->comp_name."requests.phone, ".
			" #__sv_appt".$this->comp_name."requests.email, #__sv_appt".$this->comp_name."requests.sms_phone, #__sv_appt".$this->comp_name."requests.resource, #__sv_appt".$this->comp_name."requests.startdate, ".
			" #__sv_appt".$this->comp_name."requests.starttime, #__sv_appt".$this->comp_name."requests.enddate, #__sv_appt".$this->comp_name."requests.endtime, #__sv_appt".$this->comp_name."requests.comment as not_used, ".
			" #__sv_appt".$this->comp_name."requests.admin_comment as comment, #__sv_appt".$this->comp_name."requests.request_status, #__sv_appt".$this->comp_name."requests.payment_status, #__sv_appt".$this->comp_name."requests.service, ".
			" #__sv_appt".$this->comp_name."requests.booked_seats, #__sv_appt".$this->comp_name."requests.cancellation_id, #__sv_appt".$this->comp_name."requests.booking_due, #__sv_appt".$this->comp_name."requests.booking_total,".
			"   #__sv_appt".$this->comp_name."resources.name as resname, #__sv_appt".$this->comp_name."resources.description as resdesc, ".
			"	#__sv_appt".$this->comp_name."requests.id_requests as id, #__sv_appt".$this->comp_name."paypal_transactions.id_paypal_transactions AS pp_txnid, ".
			"   IF(#__sv_appt".$this->comp_name."services.name='' OR #__sv_appt".$this->comp_name."services.name IS NULL ,'< not entered >',#__sv_appt".$this->comp_name."services.name) as ServiceName, ". 
			"   #__sv_appt".$this->comp_name."services.service_duration, #__sv_appt".$this->comp_name."services.service_duration_unit, ". 
			"   IF(email='','< not entered >',email) as email, ". 
			"   IF(phone='','< not entered >',phone) as phone, ". 
			"   IF(#__sv_appt".$this->comp_name."requests.sms_phone='','< not entered >',#__sv_appt".$this->comp_name."requests.sms_phone) as sms_phone, ". 
			"  CONCAT(#__sv_appt".$this->comp_name."requests.startdate,#__sv_appt".$this->comp_name."requests.starttime) as startdatetime, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %b %e') as display_startdate, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.startdate, '%a %e-%b-%Y') as display_startdate2, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.endtime, '%k:%i') as display_endtime, ".
			"  DATE_FORMAT(#__sv_appt".$this->comp_name."requests.starttime, '%k:%i') as display_starttime, ".
			"  CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ',#__sv_appt".$this->comp_name."requests.starttime) as startdatetimegmt, ".
			"  CONCAT(#__sv_appt".$this->comp_name."requests.enddate,' ',#__sv_appt".$this->comp_name."requests.endtime) as enddatetimegmt ".
//			"  CONVERT_TZ(CONCAT(#__sv_appt".$this->comp_name."requests.startdate,' ',#__sv_appt".$this->comp_name."requests.starttime), '".$offsetsign.$absoffset.":00', '+00:00') as startdatetimegmt, ".
//			"  CONVERT_TZ(CONCAT(#__sv_appt".$this->comp_name."requests.enddate,' ',#__sv_appt".$this->comp_name."requests.endtime), '".$offsetsign.$absoffset.":00', '+00:00') as enddatetimegmt ".
			" FROM ".
			"   #__sv_appt".$this->comp_name."requests INNER JOIN #__sv_appt".$this->comp_name."resources  ".
			"   ON #__sv_appt".$this->comp_name."requests.resource = #__sv_appt".$this->comp_name."resources.id_resources LEFT JOIN  ".
			"   #__sv_appt".$this->comp_name."paypal_transactions  ".
			"   ON #__sv_appt".$this->comp_name."requests.txnid = #__sv_appt".$this->comp_name."paypal_transactions.txnid LEFT JOIN  ".
			"   #__sv_appt".$this->comp_name."services ON #__sv_appt".$this->comp_name."requests.service = ".
			"   #__sv_appt".$this->comp_name."services.id_services ".
			" WHERE #__sv_appt".$this->comp_name."requests.id_requests = ".(int)$req_id;
			//echo $sql;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 	
		echo $objJSON->getJSON($result,$num);		
	}

	function get_adm_udf_values()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$req_id = $jinput->getInt( 'req_id', '-1' );	
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$database = JFactory::getDBO();
			
		$sql = "SELECT ".
		"#__sv_appt".$this->comp_name."udfs.udf_label, #__sv_appt".$this->comp_name."udfs.udf_type, ".
		// strip out tabs as they casue parse errors
		"REPLACE(#__sv_appt".$this->comp_name."udfvalues.udf_value, CHAR(9), '  ') as udf_value, ".
//		"#__sv_appt".$this->comp_name."udfvalues.udf_value, ".
		"#__sv_appt".$this->comp_name."udfvalues.request_id ".
		"FROM ".
		"#__sv_appt".$this->comp_name."udfvalues INNER JOIN ".
		"#__sv_appt".$this->comp_name."udfs ON #__sv_appt".$this->comp_name."udfvalues.udf_id = ".
		"#__sv_appt".$this->comp_name."udfs.id_udfs ".
		"WHERE ".
		"#__sv_appt".$this->comp_name."udfvalues.request_id = ".(int)$req_id. " ".
		"ORDER BY ordering ";
		try {
			$database->setQuery($sql);
			$result = $database -> query();;
			$num = $database -> getAffectedRows(); 
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		//echo $sql;
		//exit;

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
	}

	function get_adm_extra_values()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$req_id = $jinput->getInt( 'req_id', '-1' );	
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$database = JFactory::getDBO();
			
		$database = JFactory::getDBO();					
		$sql = "SELECT extras_id as id, extras_label, extras_qty FROM ".
		" #__sv_appt".$this->comp_name."extras_data INNER JOIN #__sv_appt".$this->comp_name."extras ".
		"   ON #__sv_appt".$this->comp_name."extras_data.extras_id = #__sv_appt".$this->comp_name."extras.id_extras ".
		" WHERE #__sv_appt".$this->comp_name."extras_data.request_id = ".$req_id. " ".
		" ORDER BY ordering ";
		try {
			$database->setQuery($sql);
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 
		//echo $sql;
		//exit;

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
	}

	function get_adm_seat_values()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$req_id = $jinput->getInt( 'req_id', '-1' );	
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){

			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		// get this anyway even if login not required as it is needed for user credit
		$auth_result = authenticateUser($username, $password);
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=list_resources.json' );
		}
		 	
		$database = JFactory::getDBO();
			
		$sql = "SELECT seat_type_id, seat_type_label, seat_type_qty FROM ".
		" #__sv_appt".$this->comp_name."seat_counts INNER JOIN #__sv_appt".$this->comp_name."seat_types ".
		"   ON #__sv_appt".$this->comp_name."seat_counts.seat_type_id = #__sv_appt".$this->comp_name."seat_types.id_seat_types ".
		" WHERE #__sv_appt".$this->comp_name."seat_counts.request_id = ".(int)$req_id. " ".
		" ORDER BY ordering ";
		try {
			$database->setQuery($sql);
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 
		//echo $sql;
		//exit;

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
	}

	function adm_update_booking()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$req_id = $jinput->getInt( 'req_id', '-1' );	
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$new_startdate=$jinput->getString( 'startdate', '' );
		$new_starttime=$jinput->getString( 'starttime', '' );
		$new_endtime=$jinput->getString( 'endtime', '' );
		$new_request_status=$jinput->getWord( 'request_status', '' );
		$new_payment_status=$jinput->getWord( 'payment_status', '' );
		$new_booking_total=$jinput->getString( 'booking_total', '0' );
		$new_booking_due=$jinput->getString( 'booking_due', '0' );
		$new_comment=$jinput->getString( 'comment', '' );
		
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		$auth_result = authenticateUser($username, $password);
		if($auth_result != -1){
			$user_id = $auth_result;
		}
		
//		if($auth_result >0){
//			$userID = $auth_result;

			require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'functions2.php' );
			require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'sendmail_pro2.php' );

			$database = JFactory::getDBO();

			// get config
			$sql = "SELECT * FROM #__sv_appt".$this->comp_name."config";
			try{
				$database->setQuery($sql);
				$apptpro_config = NULL;
				$apptpro_config = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			// get resource details, needed for email
			$sql = "SELECT * FROM #__sv_appt".$this->comp_name."resources WHERE id_resources = ".(int)$res_id;
			try{
				$database->setQuery($sql);
				$res_detail = NULL;
				$res_detail = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			// get original request details so we can chcek to see if status is changing from a non-accepted to accepted
			$sql = "SELECT request_status FROM #__sv_appt".$this->comp_name."requests WHERE id_requests = ".(int)$req_id;
			try{
				$database->setQuery($sql);
				$old_request_status = NULL;
				$old_request_status = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			$sql = "UPDATE #__sv_appt".$this->comp_name."requests SET request_status = '".$new_request_status."', ".
				" payment_status = '".$new_payment_status."', ".
				" startdate = '".$new_startdate."', ".
				" starttime = '".$new_starttime."', ".
				" enddate = '".$new_startdate."', ".
				" endtime = '".$new_endtime."', ".
				" comment = '".$new_comment."', ".
				" booking_total = '".$new_booking_total."', ".
				" booking_due = '".$new_booking_due."' ".
			"WHERE id_requests = ".$req_id;
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			// dev only
			//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 
			
			// email a confimration if request went from a non-accepted status to accepted
			$request_status = $new_request_status;
			if($request_status == "accepted" && old_request_status != "accepted" ){
			
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
					return false;		
				}
	
				$message .= buildMessage(strval($req_id), "confirmation", "No");
				$message_admin .= buildMessage(strval($req_id), "confirmation_admin", "No");
				
				if($apptpro_config->html_email != "Yes"){
					$message = str_replace("<br>", "\r\n", $message);
					$message_admin = str_replace("<br>", "\r\n", $message_admin);
				}
	
				$array = array($req_id);
				$ics = buildICSfile($array);

				// email to customer
				if($jinput->getString('email') != ""){
					$to = $jinput->getString('email');
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					if($apptpro_config->attach_ics_customer == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($req_id).".ics");
					}
	
					try{
						$mailer->addRecipient($to);
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
	
				}
				
				// email to admin
				if($apptpro_config->mailTO != ""){
					$to = $apptpro_config->mailTO;
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					if($apptpro_config->attach_ics_admin == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($req_id).".ics");
					}
	
					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
	
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}

				}
				
				// email to resource
				if($res_detail->resource_email != ""){
					$to = $res_detail->resource_email;
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					if($apptpro_config->attach_ics_resource == "Yes" && $request_status == "accepted"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($req_id).".ics");
					}
	
					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
				}
	
				// SMS to resource
				if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
					$config = JFactory::getConfig();
					$tzoffset = $config->get('offset');
				$tz = new DateTimeZone($tzoffset);
				$offsetdate = new JDate("now", $tz);
					$reminder_log_time_format = "Y-m-d H:i:s";
					if($user_id == ""){
						$bookingUser = -1;
					} else {
						$bookingUser = $user_id;
					}
					$returnCode = "";
		
					if($request_status == "accepted"){
						sv_sendSMS($req_id, "confirmation", $returnCode, $toResource="Yes");			
					} else {
						sv_sendSMS($req_id, "in_progress", $returnCode, $toResource="Yes");			
					}
					logReminder("New booking (mobile) SMS response: ".$returnCode, $req_id, $bookingUser, $name, $offsetdate->format($reminder_log_time_format, true, true));
				}
	
			}

			if($request_status == "canceled" && old_request_status != "canceled" ){
			
				$mailer = JFactory::getMailer();
				try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
	
				$message .= buildMessage(strval($req_id), "cancellation", "No");
				$message_admin .= buildMessage(strval($req_id), "cancellation", "No");
				
				if($apptpro_config->html_email != "Yes"){
					$message = str_replace("<br>", "\r\n", $message);
					$message_admin = str_replace("<br>", "\r\n", $message_admin);
				}
	
				// email to customer
				if($jinput->getString('email') != ""){
					$to = $jinput->getString('email');
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					try{
						$mailer->addRecipient($to);
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
	
				}
				
				// email to admin
				if($apptpro_config->mailTO != ""){
					$to = $apptpro_config->mailTO;
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
	
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
				}
				
				// email to resource
				if($res_detail->resource_email != ""){
					$to = $res_detail->resource_email;
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_json", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($message_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo);
					}
				}
	
				// SMS to resource
				if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
					$config = JFactory::getConfig();
					$tzoffset = $config->get('offset');
				$tz = new DateTimeZone($tzoffset);
				$offsetdate = new JDate("now", $tz);
					$reminder_log_time_format = "Y-m-d H:i:s";
					if($user_id == ""){
						$bookingUser = -1;
					} else {
						$bookingUser = $user_id;
					}
					$returnCode = "";
		
					sv_sendSMS($req_id, "sms_cancellation", $returnCode, "Yes");			
					logReminder("Mobile booking cancel SMS response: ".$returnCode, $req_id, $bookingUser, $name, $offsetdate->format($reminder_log_time_format, true, true));
				}
	
			}
			
			// do calendar stuff is required
			if(!addToCalendar($req_id, $apptpro_config, "Yes")){
				echo "{ \"data\": [ { \"Error\" : \"Error adding to calendar, check ABPro Error Log\" }]}";
				return;
			}
			
		$retval = "{ \"data\": [ { \"InsertResult\" : \"OK\" ,\"request_id\" : \"".$req_id."\",\"cc\" : \"".$cc."\" }]}";
		echo $retval;

		//}
    }

	function get_adm_bookoffs()
	{		
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$res_id = $jinput->getInt( 'res_id', '-1' );	
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
		$sql = "SELECT #__sv_appt".$this->comp_name."bookoffs.id_bookoffs as id, #__sv_appt".$this->comp_name."bookoffs.*, DATE_FORMAT(off_date, '%W, %b %e, %Y') as off_date_display, off_date, ".
		"DATE_FORMAT(off_date, '%a, %b %e, %Y') as off_date_display_short, ".
		"#__sv_appt".$this->comp_name."bookoffs.description, ".
		"IF(full_day = 'Yes','Full Day',CONCAT(DATE_FORMAT(bookoff_starttime, '%H:%i'), '-', DATE_FORMAT(bookoff_endtime, '%H:%i'))) as hours, ".
		"IF(published = 1, 'Yes', 'No') as pub ".
		"FROM #__sv_appt".$this->comp_name."bookoffs WHERE resource_ID = ".(int)$res_id." ORDER BY off_date";
		//echo $sql;
		//exit;
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
		
	}

	function get_adm_bookoff_detail()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$bo_id = $jinput->getInt( 'bo_id', '-1' );	


		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_booking_detail.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
		
		$config = JFactory::getConfig();
		$sql = "SELECT #__sv_appt".$this->comp_name."bookoffs.id_bookoffs as id, #__sv_appt".$this->comp_name."bookoffs.*, DATE_FORMAT(off_date, '%W, %b %e, %Y') as off_date_display, off_date, ".
			"#__sv_appt".$this->comp_name."bookoffs.description, ".
			"IF(full_day = 'Yes','Full Day',CONCAT(DATE_FORMAT(bookoff_starttime, '%H:%i'), '-', DATE_FORMAT(bookoff_endtime, '%H:%i'))) as hours, ".
			"IF(published = 1, 'Yes', 'No') as pub ".
			"FROM #__sv_appt".$this->comp_name."bookoffs WHERE id_bookoffs = ".(int)$bo_id;
			//echo $sql;
			//exit;
		try {	
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num=$database -> getAffectedRows();
		
		$objJSON=new mysql2json(); 
		//print(trim($objJSON->getJSON($result,$num))); 		
		echo $objJSON->getJSON($result,$num);		
			
	}

	function adm_save_bookoff()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$id = $jinput->getInt( 'bo_id', '-1' );	
		$resource_id = $jinput->getInt( 'res_id', '-1' );	
		$off_date=$jinput->getString( 'bo_offdate', '' );
		$bookoff_starttime=$jinput->getString( 'bo_starttime', '' );
		$bookoff_endtime=$jinput->getString( 'bo_endtime', '' );
		$full_day=$jinput->getWord( 'bo_fullday', '' );
		$published=$jinput->getInt( 'bo_pub', '0' );
		$description=$jinput->getString( 'description', '' );
		
		
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		$auth_result = authenticateUser($username, $password);
		if($auth_result != -1){
			$user_id = $auth_result;
		}

		require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'functions2.php' );
		require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'sendmail_pro2.php' );

		$database = JFactory::getDBO();
		// get config
		$sql = 'SELECT * FROM #__sv_appt'.$this->comp_name.'config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		

		// check for bookings, no book-off over 'Accepted' bookings
		if($full_day == "Yes"){
			// just check by date
			$mystartdatetime = "STR_TO_DATE('".$off_date ." 00:00:00', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$off_date ." 23:59:00', '%Y-%m-%d %T')- INTERVAL 1 SECOND";				
		} else {
			$mystartdatetime = "STR_TO_DATE('".$off_date ." ". $bookoff_starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$off_date ." ". $bookoff_endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		}
		$sql = "select count(*) from #__sv_appt".$this->comp_name."requests "
		." where (resource = '". (int)$resource_id ."')"
		." and (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"")." )"
		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		if ($overlapcount >0){
			$err = "Book-Off conflicts with existing Accepted booking, please cancel bookings before creating a Book-Off";
			echo "{ \"data\": [ { \"Error\" : \"".$err."\" }]}";
			return;
		}

		if($id < 1){			
			// insert
			$sql = "INSERT INTO #__sv_appt".$this->comp_name."bookoffs (resource_id, description, off_date, full_day, bookoff_starttime, bookoff_endtime, published ) ".
			"VALUES(".$resource_id.",'".$description."', '".$off_date."', '".$full_day."', '".$bookoff_starttime."', '".$bookoff_endtime."', ".$published.")";
		} else {
			// update
			$sql = "UPDATE #__sv_appt".$this->comp_name."bookoffs SET ".
			"description = '".$description."', ".
			"off_date = '".$off_date."', ".
			"full_day = '".$full_day."', ".
			"bookoff_starttime = '".$bookoff_starttime."', ".
			"bookoff_endtime = '".$bookoff_endtime."', ".
			"published = ".$published." ".
			"WHERE id_bookoffs = ".$id;
		}
		try{
			$database->setQuery($sql);
			$result = NULL;
			$result = $database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		
		$retval = "{ \"data\": [ { \"SaveResult\" : \"OK\" }]}";
		echo $retval;
	}
	
	function adm_delete_bookoff()
	{
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$id = $jinput->getInt( 'bo_id', '-1' );	
		
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}

		$auth_result = authenticateUser($username, $password);
		if($auth_result != -1){
			$user_id = $auth_result;
		}

		require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'functions2.php' );
		require_once ( JPATH_SITE .DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rsappt_pro3'.DIRECTORY_SEPARATOR.'sendmail_pro2.php' );

		$database = JFactory::getDBO();

		$sql = "DELETE FROM #__sv_appt".$this->comp_name."bookoffs WHERE id_bookoffs = ".$id;
		try{
			$database->setQuery($sql);
			$result = NULL;
			$result = $database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		
		$retval = "{ \"data\": [ { \"DeleteResult\" : \"OK\" }]}";
		echo $retval;
	}
	
	function get_adm_users(){		
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
			
		$sql = 'SELECT id,name,email,balance FROM #__users'.
			' LEFT JOIN #__sv_apptpro3_user_credit ON '.
			' #__users.id = #__sv_apptpro3_user_credit.user_id '.
			' WHERE block = 0 order by name';
		//$sql = 'SELECT id,name,email FROM #__users WHERE block = 0 order by name';
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
		
	}

	function get_adm_user_search(){		
		$jinput = JFactory::getApplication()->input;	
		$fileout = $jinput->getWord( 'fileout', 'Yes' );
		$username = $jinput->getString( 'usr', '' );
		$password = $jinput->getString( 'pwd', '' );
		$site_access_code = $jinput->getString( 'sac', '' );
		$sc = $jinput->getInt( 'sc', 0 );
		$search_for = $jinput->getString( 'un', '' );

		if($this->site_access_code != "" && $sc==1){
			$auth_result = $this->checkAccessCode($site_access_code);
			if($auth_result == "ABPro not found"){
				$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
				echo $result;
				exit;
			}
			if($auth_result == -1){
				$result = "{ \"data\": [ { \"sac_error\" : \"Site Access Code failed\" } ] }";
				echo $result;
				exit;
			}
		}
	
		$auth_result = authenticateUser($username, $password);
		if($auth_result == "ABPro not found"){
			$result = "{ \"data\": [ { \"Error\" : \"ABPro not found\" } ] }";
			echo $result;
			exit;
		}
		if($auth_result == -1){
			$result = "{ \"data\": [ { \"auth_error\" : \"User Authentication failed\" } ] }";
			echo $result;
			exit;
		}
		
		// Get the document object.
		$document = JFactory::getDocument();
		 
		if($fileout == "Yes"){
			// Set the MIME type for JSON output.
			$document->setMimeEncoding( 'text/javascript' );
			 
			// Change the suggested filename.
			JResponse::setHeader( 'Content-Disposition', 'attachment; filename=get_adm_resources.json' );
		}
		 	
		$num=0; 
		$database = JFactory::getDBO();
			
			
		$sql = 'SELECT id,name,email,balance FROM #__users'.
			' LEFT JOIN #__sv_apptpro3_user_credit ON '.
			' #__users.id = #__sv_apptpro3_user_credit.user_id '.
			' WHERE block = 0 '.
			' AND name LIKE "%'.filter_var(addslashes($search_for), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW).'%"'.
			' order by name';
		//$sql = 'SELECT id,name,email FROM #__users WHERE block = 0 order by name';
		try {
			$database->setQuery($sql);
			$result = NULL;
			$result = $database -> query();;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "json_x", "", "");
			echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
			return;
		}		
		$num = $database -> getAffectedRows(); 

		$objJSON=new mysql2json(); 
		echo $objJSON->getJSON($result,$num);
		
	}


	function local_getCurrentSeatCount($startdate, $starttime, $endtime, $resource, $exclude_request=-1){
		$jinput = JFactory::getApplication()->input;	
	
			$database = JFactory::getDBO();
			// get config
			$sql = 'SELECT * FROM #__sv_appt'.$this->comp_name.'config';
			try{
				$database->setQuery($sql);
				$apptpro_config = NULL;
				$apptpro_config = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
			$sql = "SELECT Sum(booked_seats) FROM #__sv_appt".$this->comp_name."requests ".
				" WHERE ".
				" id_requests != ".(int)$exclude_request." AND ".
				" startdate = '".$database->escape($startdate)."' AND ".
				" starttime = '".$database->escape($starttime)."' AND ".
				" endtime = '".$database->escape($endtime)."' AND ".
				" resource = ".$database->escape($resource)." AND ".
				"(request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") AND ".
				" booked_seats > 0;";
			try{
				$database->setQuery( $sql );
				$currentcount = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return -1;
			}		
			return $currentcount;
		}
	
	function local_saveToDB($name,$user_id,$phone,$email,$sms_reminders,$sms_phone,$sms_dial_code,$resource,$category,
			$service_name,$startdate,$starttime,$enddate,$endtime,$request_status,$cancel_code,$grand_total,$ammount_due,
			$booking_deposit, $coupon_code,$booked_seats,$applied_credit,$comment, $admin_comment=''){
			$lang = JFactory::getLanguage();
	
			$jinput = JFactory::getApplication()->input;	
			
			$manual_payment_collected = 0;
			
			$database = JFactory::getDBO();
	
			if($ammount_due == 0.00){
				$payment_status = "paid";
			} else {
				$payment_status = "pending";
			}
	
			if($applied_credit == ""){
				$applied_credit = 0;
			}
	
			// get config
			$sql = 'SELECT * FROM #__sv_appt'.$this->comp_name.'config';
			try{
				$database->setQuery($sql);
				$apptpro_config = NULL;
				$apptpro_config = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		

			// Check again for no overlap - this was checked in validation so it will only fail if someone 
			// has booked in the time it took the validation to get back to the client and the form submit itself
			//(a second or two?)
			// get resource info for the selected resource
			$sql = "SELECT * FROM #__sv_appt".$this->comp_name."resources where id_resources = ".(int)$resource;
			try {
				$database->setQuery($sql);
				$res_detail = NULL;
				$res_detail = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
	
			$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
			$sql = "select count(*) from #__sv_appt".$this->comp_name."requests "
			." where (resource = '". (int)$resource ."')"
			." and (request_status = 'accepted' or request_status = 'pending'".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"")." )"
			." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
			." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
			." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
			." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
			try{
				$database->setQuery( $sql );
				$overlapcount = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
			if ($overlapcount >= $res_detail->max_seats && $res_detail->max_seats > 0 ){
				// serious problem, bail out
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_INPUT_SCRN_CONFLICT_ERR')."\" }]}";
				exit;
			}
	
			// save to db
			$sSql = "INSERT INTO #__sv_appt".$this->comp_name."requests(".
			"name, ".
			"user_id, ".
			"phone, ".
			"email, ".
			"sms_reminders, ".
			"sms_phone, ".
			"sms_dial_code, ".
			"resource, ".
			"category, ".
			"service, ".
			"startdate, ".
			"starttime, ".
			"enddate, ".
			"endtime, ".
			"request_status, ".
			"payment_status, ".
			"cancellation_id, ".
			"booking_total, ".
			"booking_due, ".
			"booking_deposit, ".
			"credit_used, ".
			"coupon_code, ".
			"booked_seats, ".
			"admin_comment, ".
			"booking_language, ".
			"manual_payment_collected, ".
			"comment ";
			$sSql = $sSql.") VALUES(".
			"'".$database->escape($name)."',".
			"'".$user_id."',".
			"'".$database->escape($phone)."',".
			"'".$database->escape($email)."',".
			"'".$sms_reminders."',".
			"'".$database->escape($sms_phone)."',".
			"'".$sms_dial_code."',".
			"'".$resource."',".
			"'".$category."',".
			"'".$database->escape($service_name)."',".
			"'".$startdate."',".
			"'".$starttime."',".
			"'".$enddate."',".
			"'".$endtime."',".
			"'".$database->escape($request_status)."',".
			"'".$database->escape($payment_status)."',".
			"'".$database->escape($cancel_code)."',".
			$grand_total.",".
			$ammount_due.",".
			$booking_deposit.",".
			$applied_credit.",".
			"'".$coupon_code."',".
			"'".$booked_seats."',".
			"'".$database->escape($admin_comment)."',".
			"'".$lang->getTag()."',".
			"'".$database->escape($manual_payment_collected)."',".
			"'".$database->escape($comment)."'";
			$sSql = $sSql.")";

			$sSql = str_replace("~", $database->escape("'"), $sSql);
			try{
				$database->setQuery($sSql);		
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ctrl_json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}
	
			// need request id to pass through to PayPal (so PP can pass it back with IPN)
			$sSql = "SELECT LAST_INSERT_ID() AS last_id";
			try{
				$database->setQuery($sSql);
				$last_id = NULL;
				$last_id = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
			
			// if credit used..
			if(floatval($applied_credit) > 0.00 && $user_id != -1 && $user_id != ""){
				// adjust credit balance is
				$sql = "UPDATE #__sv_appt".$this->comp_name."user_credit SET balance = balance - ".$applied_credit." WHERE user_id = ".$user_id;
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "json_x", "", "");
					echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
					return;
				}		
				
				// add credit audit
				$sql = "INSERT INTO #__sv_appt".$this->comp_name."user_credit_activity (user_id, request_id, decrease, comment, operator_id, balance) ".
				"VALUES (".$user_id.",".
				$last_id->last_id.",".
				$applied_credit.",".
				"'".JText::_('RS1_ADMIN_CREDIT_ACTIVITY_CREDIT_USED')."',".
				$user_id.",".
				"(SELECT balance from #__sv_appt".$this->comp_name."user_credit WHERE user_id = ".(int)$user_id."))";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "json_x", "", "");
					echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
					return;
				}		
				
				// if paid in full by credit, set paystatus to paid
				if(floatval($ammount_due)==0.00){
					$sql = "UPDATE #__sv_appt".$this->comp_name."requests SET payment_status = 'paid' WHERE id_requests = ".$last_id->last_id;
					try{
						$database->setQuery($sql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "json_x", "", "");
						echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
						return;
					}		
				}
			}
			
			return $last_id;		
	}

	function local_fullyBooked($booking, $res_row, $apptpro_config){
		$jinput = JFactory::getApplication()->input;	
			// max_seats = 0 = no limit
			if($res_row->max_seats == 0){
				return false;
			}	
			// now check to see if there are other bookings and if so how many total seats are booked.
			$adjusted_max_seats = getSeatAdjustments($booking->startdate, $booking->starttime, $booking->endtime, $booking->resource);
			$currentcount = $this->local_getCurrentSeatCount($booking->startdate, $booking->starttime, $booking->endtime, $booking->resource);
			if ($currentcount >= ($res_row->max_seats + $adjusted_max_seats)){
				return true;
			}
		}
	
	
	function local_display_this_resource($res_detail, $userid){
		$jinput = JFactory::getApplication()->input;	
		$display_this_resource = true;
		if($res_detail->name == JText::_('RS1_GAD_SCRN_RESOURCE_DROPDOWN')){
			return true;
		}
		// is this resource restricted to a specific group?
		if($res_detail->access == 'everyone' || stripos($res_detail->access, "|1|") > -1){
			$display_this_resource = true;							
		} else {
			// yes further checking is reqiuired..
			// access is not everyone and not public_only so we need to see if the user is a member of the group specified
			$groups = str_replace("||", ",", $res_detail->access);
			$groups = str_replace("|", "", $groups);
	
			$database = JFactory::getDBO();
			$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE group_id IN (".$database->escape($groups).") AND user_id = ".(int)$userid;
			//echo $sql;
			try {
				$database->setQuery($sql);
				$match = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "json_x", "", "");
				echo "{ \"data\": [ { \"Error\" : \"".JText::_('RS1_SQL_ERROR')."\" }]}";
				return;
			}		
			if($match < 1){
				$display_this_resource = false;
			}
			
		}
		return $display_this_resource;
	}
	


} // end of class
//---------------------------------------------------------------------------------------------------------

function authenticateUser($username, $password)	
	{
		$userId = -1;

		// Get the global JAuthentication object
		jimport('joomla.user.authentication');
		JPluginHelper::importPlugin('system');

		jimport('joomla.application.component.helper');
		if(!JComponentHelper::isEnabled('com_rsappt_pro3', true)){
			return "ABPro not found";
		}		

		$auth = JAuthentication::getInstance();
		$credentials = array('username' => $username, 'password' => $password);
		$options = array();
		$response = $auth->authenticate($credentials, $options);

		if($response->status === JAuthentication::STATUS_SUCCESS)
		{
			$userId = JUserHelper::getUserId($username);
			$user = JUser::getInstance($userId);
		} else {
			return -1;
		}

		return $userId;
	}


function localGoToPayPal($request_id, $apptpro_config, $grand_total, $from_screen, $from_screen_itemid, $mobile_order = "Yes"){

	$jinput = JFactory::getApplication()->input;	
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_paypal_settings;';
	try{
		$database->setQuery($sql);
		$paypal_settings = NULL;
		$paypal_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_goto", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	if($paypal_settings->paypal_use_sandbox == "Yes"){
		$paypal_url = $paypal_settings->paypal_sandbox_url; 
	} else {
		$paypal_url = $paypal_settings->paypal_production_url; 
	}
	$mobile_url = "";

	// check for request specific PayPal account 
	$sql = "SELECT #__sv_apptpro3_resources.paypal_account FROM #__sv_apptpro3_requests ".
	"  INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
	" WHERE #__sv_apptpro3_requests.id_requests = ".(int)$request_id;
	//echo $sql;
	//exit;
	try{
		$database->setQuery($sql);
		$res_paypal_account = $database->loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}				
	if($res_paypal_account == ""){
		$paypal_account = $paypal_settings->paypal_account;
	} else {
		$paypal_account = $res_paypal_account;
	}
	
	$paypal_url = $paypal_url.'?cmd=_xclick&currency_code='.$paypal_settings->paypal_currency_code.
	"&business=" .$paypal_account;
	$mobile_url = $paypal_url;

	$paypal_url .= "&return=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$from_screen_itemid."&task=pp_return&req_id=".$request_id);
	
	$paypal_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=admin&task=ipn"). 
	"&charset=UTF-8";
	
	// mobile does not need 'return' as that comes from the mobile app.
	$mobile_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=admin&task=ipn"). 
	"&charset=UTF-8";


	//PayPal will display in the language you have your account set to. 
	//If you want to switch PayPal language in the call you can un-comment the following lines and set the langauge appropriately.
	//The follwon show changing to Japanese
	//$paypal_url .= "&locale.x=ja_JP";
	//$paypal_url .= "&lc=JP";
	if($paypal_settings->paypal_itemname ==""){
		$paypal_url .= "&item_name=".JText::_($res_detail->description).": ".$startdate." ".$starttime;
		$mobile_url .= "&item_name=".JText::_($res_detail->description).": ".$startdate." ".$starttime;
	} else {
		$itemname = processTokens($request_id, JText::_($paypal_settings->paypal_itemname));
		$paypal_url .= "&item_name=".$itemname;
		$mobile_url .= "&item_name=".$itemname;
	}
	if($paypal_settings->paypal_on0 !="" && $paypal_settings->paypal_os0 !=""){
		$on0 = processTokens($request_id, JText::_($paypal_settings->paypal_on0));
		$os0 = processTokens($request_id, JText::_($paypal_settings->paypal_os0));
		$paypal_url .= "&on0=".$on0.
		"&os0=".$os0;
	}
	if($paypal_settings->paypal_on1 !="" && $paypal_settings->paypal_os1 !=""){
		$on1 = processTokens($request_id, JText::_($paypal_settings->paypal_on1));
		$os1 = processTokens($request_id, JText::_($paypal_settings->paypal_os1));
		$paypal_url .= "&on1=".$on1.
		"&os1=".$os1;
	}
	if($paypal_settings->paypal_on2 !="" && $paypal_settings->paypal_os2 !=""){
		$on2 = processTokens($request_id, JText::_($paypal_settings->paypal_on2));
		$os2 = processTokens($request_id, JText::_($paypal_settings->paypal_os2));
		$paypal_url .= "&on2=".$on2.
		"&os2=".$os2;
	}
	if($paypal_settings->paypal_on3 !="" && $paypal_settings->paypal_os3 !=""){
		$on3 = processTokens($request_id, JText::_($paypal_settings->paypal_on3));
		$os3 = processTokens($request_id, JText::_($paypal_settings->paypal_os3));
		$paypal_url .= "&on3=".$on3.
		"&os3=".$os3;
	}
	$paypal_url .= "&amount=".$grand_total.
	"&custom=".strval($request_id);

	$mobile_url .= "&amount=".$grand_total.
	"&custom=".strval($request_id);

	/* The locale of the login or sign-up page, which may have the specific country's language available, depending on localization. 
	If unspecified, PayPal determines the locale by using a cookie in the subscriber's browser. 
	If there is no PayPal cookie, the default locale is US. */
	//$paypal_url .= "&lc=US”
	//$mobile_url .= "&lc=US”
	
	if($paypal_settings->paypal_logo_url != ""){
		$paypal_url .= "&image_url=".$paypal_settings->paypal_logo_url;
	}
	
	//echo $paypal_url;
	//exit;		

	return $mobile_url;

}
?>
