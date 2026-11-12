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
 
class requestsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
		$this->registerTask( 'export', 'export_to_csv' );
		$this->registerTask( 'export_ics', 'export_to_ics' );
		$this->registerTask( 'reminders', 'send_reminders' );
		$this->registerTask( 'reminders_sms', 'send_sms_reminders' );
		$this->registerTask( 'thankyou', 'send_post_booking' );
		
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
		rsappt_pro3Helper::addSubmenu('requests');
		
	}
	
	
	function export_to_csv(){
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

		$jinput = JFactory::getApplication()->input;
		$filter_order = $jinput->get( 'filter_order', 'filter_order' );		
		$filter_order_dir = $jinput->get( 'filter_order_Dir', 'filter_order_Dir' );				

		$uid = $jinput->get( 'cid', array(0), 'post', 'array' );

		$database = JFactory::getDBO();

		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// Note: this is back end so the language code found will be the Admin language not the Site langugage.
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
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		
		
		// get export settings
		// First core
		$sql = "SELECT * FROM #__sv_apptpro3_export_columns WHERE export_column_type = 'core' ORDER BY export_order";
		try{
			$database->setQuery($sql);
			$export_columns = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$sql = ' SELECT ';
			foreach($export_columns as $export_column) {	
				if($export_column->export_field == "startdate"){
//					$sql .= " DATE_FORMAT(#__".$export_column->export_table.".".$export_column->export_field.", '".$export_column->export_format."')";
					$sql .= " DATE_FORMAT(#__".$export_column->export_table.".".$export_column->export_field.", '".php_date_string_to_sql($apptpro_config->long_date_format, "SQL")."')";
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}
				} else if($export_column->export_field == "starttime" || $export_column->export_field == "endtime"){
					$sql .= " DATE_FORMAT(#__".$export_column->export_table.".".$export_column->export_field.", '".$export_column->export_format."')";
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}					 
				} else {
					$sql .= "#__".$export_column->export_table.".".$export_column->export_field;
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}
				}
				$sql .= ",";
			}

		$sql .= '#__sv_apptpro3_requests.id_requests,'. // needed for table JOINS
				" CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) as startdatetime "; // needed for ordering
		$sql = rtrim($sql, ',');
		$sql .= ' FROM ( #__sv_apptpro3_requests '.
				' LEFT JOIN #__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = #__sv_apptpro3_categories.id_categories '.
				' LEFT JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources  '.
				' LEFT JOIN #__sv_apptpro3_services ON #__sv_apptpro3_requests.service = #__sv_apptpro3_services.id_services )  '.
				" WHERE #__sv_apptpro3_requests.id_requests IN (".implode(",", $uid).")";
		if($filter_order != ""){
			$sql .= " ORDER BY ".$filter_order;
			if($filter_order_dir != ""){
				$sql .= " ".$filter_order_dir;
			}
		}
		//echo $sql;
		//exit;
	
		$database = JFactory::getDBO();
		try{
			$database->setQuery($sql);
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		//print_r($rows);
		//exit;		


		// get udfs
		$sql = "SELECT * FROM #__sv_apptpro3_export_columns WHERE export_column_type = 'udf' ORDER BY export_order";
		try{
			$database->setQuery($sql);
			$export_columns = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$sql2 = " SELECT #__sv_apptpro3_udfs.udf_label, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_udfs ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_udfs.id_udfs ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'udf' )".
			"WHERE #__sv_apptpro3_udfs.published = 1";
		$sql2 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql2);
			$udf_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get extras
		$sql3 = " SELECT #__sv_apptpro3_extras.*, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_extras ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_extras.id_extras ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'extra' )".
			"WHERE #__sv_apptpro3_extras.published = 1";
		$sql3 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql3);
			$extra_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get seat types
		$sql3 = " SELECT #__sv_apptpro3_seat_types.*, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_seat_types ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_seat_types.id_seat_types ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'seat' )".
			"WHERE #__sv_apptpro3_seat_types.published = 1";
		$sql3 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql3);
			$seat_type_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}	

		$file_name = 'export_sv_apptpro3_requests.csv';

		ob_end_clean();		
			
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename='.basename($file_name).';');
		header('Content-Type: text/plain; '.'_ISO');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Pragma: no-cache');

		
		$csv_save = '';
		if (!empty($rows)) {
				$comma = ',';
				$CR = "\r";
				// Make csv rows for field name
				$i=0;
				$fields = $rows[0];
				$cnt_fields = sv_count_($fields);
				$csv_fields = '';
				foreach($fields as $name=>$val) {
						$i++;
						// don't add rows added only for processing
						if($name != "id_requests" && $name != "startdatetime"){
							$csv_fields .= $name.$comma;
						}
				}
				// add columns for udfs
				foreach($udf_rows as $udf_row) {
					if($udf_row->export_header != ""){
						$csv_fields .= $udf_row->export_header.$comma;
					} else {
						$csv_fields .= $udf_row->udf_label.$comma;
					}
				}
				// add columns for extras
				foreach($extra_rows as $extra_row) {
					if($extra_row->export_header != ""){
						//$csv_fields .= $extra_row->export_header." (".$extra_row->extras_cost."/".$extra_row->cost_unit.")".$comma;
						$csv_fields .= $extra_row->export_header.$comma;
					} else {
						//$csv_fields .= $extra_row->extras_label." (".$extra_row->extras_cost."/".$extra_row->cost_unit.")".$comma;
						$csv_fields .= $extra_row->extras_label.$comma;
					}
				}
				// add columns for seat types
				foreach($seat_type_rows as $seat_type_row) {
					if($seat_type_row->export_header != ""){
						$csv_fields .= $seat_type_row->export_header.$comma;
					} else {
						$csv_fields .= $seat_type_row->seat_type_label.$comma;
					}
				}
				
				// Make csv rows for data
				$csv_values = '';
				foreach($rows as $row) {
						$i=0;
						$comma = ',';
						foreach($row as $name=>$val) {
								$i++;
								// don't add rows added only for processing
								if($name != "id_requests" && $name != "startdatetime"){
									$csv_values .= '"'.$val.'"'.$comma;
								}
						}
						// add udf columns data
						// get udf values for this request						
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'udf' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$udf_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "be_ctrl_requests", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $udf_rows_IN); // IN values as string
						if($strIN != ""){
							$sql2 = "SELECT #__sv_apptpro3_udfvalues.* FROM ".
								" #__sv_apptpro3_udfs LEFT JOIN #__sv_apptpro3_udfvalues ".
								" ON #__sv_apptpro3_udfs.id_udfs = #__sv_apptpro3_udfvalues.udf_id ".
								" AND #__sv_apptpro3_udfvalues.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_udfs.id_udfs IN (".$strIN.") ".
								" AND #__sv_apptpro3_udfs.published=1 ".
								" ORDER BY FIELD(id_udfs, $strIN)";
								
							try{
								$database->setQuery($sql2);
								$udf_value_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "be_ctrl_requests", "", "");
								echo JText::_('RS1_SQL_ERROR').$e->getMessage();
								exit;
							}		
							foreach($udf_value_rows as $udf_value_row) {
								$csv_values .= '"'.$udf_value_row->udf_value.'"'.$comma;
							}
						}
						// add extras columns data
						// get extras values for this request						
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'extra' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$extra_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "be_ctrl_requests", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $extra_rows_IN); // IN values as string
						if($strIN != ""){
							$sql3 = "SELECT #__sv_apptpro3_extras_data.*,#__sv_apptpro3_extras.* FROM ".
								" #__sv_apptpro3_extras LEFT JOIN #__sv_apptpro3_extras_data ".
								" ON #__sv_apptpro3_extras.id_extras = #__sv_apptpro3_extras_data.extras_id ".
								" AND #__sv_apptpro3_extras_data.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_extras.id_extras IN (".$strIN.") ".
								" AND #__sv_apptpro3_extras.published=1 ".
								" ORDER BY FIELD(id_extras, $strIN)";
								
							try{
								$database->setQuery($sql3);
								$extras_value_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "be_ctrl_requests", "", "");
								echo JText::_('RS1_SQL_ERROR').$e->getMessage();
								exit;
							}		
							foreach($extras_value_rows as $extras_value_row) {
								$csv_values .= '"'.$extras_value_row->extras_qty.'"'.$comma;
							}
						}
						
						// add seat type columns data
						// get seat type values for this request	
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'seat' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$seat_counts_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "be_ctrl_requests", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $seat_counts_rows_IN); // IN values as string
						if($strIN != ""){
							$sql2 = "SELECT #__sv_apptpro3_seat_counts.* FROM ".
								" #__sv_apptpro3_seat_types LEFT JOIN #__sv_apptpro3_seat_counts ".
								" ON #__sv_apptpro3_seat_types.id_seat_types = #__sv_apptpro3_seat_counts.seat_type_id ".
								" AND #__sv_apptpro3_seat_counts.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_seat_types.id_seat_types IN (".$strIN.") ".
								" AND #__sv_apptpro3_seat_types.published=1 ".
								" ORDER BY FIELD(id_seat_types, $strIN)";
						//http://stackoverflow.com/questions/396748/ordering-by-the-order-of-values-in-a-sql-in-clause					
							try{	
								$database->setQuery($sql2);
								$seat_counts_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "be_ctrl_requests", "", "");
								echo JText::_('RS1_SQL_ERROR');
								return false;
							}		
							foreach($seat_counts_rows as $seat_counts_row) {
								$csv_values .= '"'.$seat_counts_row->seat_type_qty.'"'.$comma;
							}
						}
						$csv_values .= $CR;
				}
				$csv_save = $csv_fields.$CR.$csv_values;
		}

		echo $csv_save;
		die();  // no need to send anything else
	}

	function export_to_ics(){
		$jinput = JFactory::getApplication()->input;
		$uid = $jinput->get( 'cid', array(0), 'post', 'array' );

		$body = buildICSfile($uid);
	
		ob_end_clean();
			
		$file_name = 'export_sv_apptpro3_requests.ics';
	
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename='.basename($file_name).';');
		header('Content-Type: text/x-vCalendar; '.'_ISO');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Pragma: no-cache');
			
		echo $body;
		die();  // no need to send anything else
		
	}

	function send_reminders($sms="No"){
		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
			
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		if (!is_array($cid) || sv_count_($cid) < 1) {
			echo "<script> alert('Select an item for reminder'); window.history.go(-1);</script>\n";
			exit();
		}
	
		// Note: this is back end so the language code found will be the Admin language not the Site langugage.
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
			logIt($e->getMessage(), "send_reminders", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
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
				logIt($e->getMessage(), "be_ctrl_requests", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
		// they changed JDate yet again
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);
			date_default_timezone_set ($tzoffset );
			
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
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT').stripslashes($request->name)." ".stripslashes($request->phone).", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate."";
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
//							if($apptpro_config->clickatell_what_to_send == "Reminders" || $apptpro_config->clickatell_what_to_send == "All"){
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
//							}			
						} else {
							logReminder(JText::_('RS1_SMS_MSG_DISABLED'), $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status = JText::_('RS1_SMS_MSG_DISABLED');
						}				
					}
				}
			}
		}
		
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'requests_reminders' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'results', $status);

		parent::display();
		
	}

	function send_sms_reminders(){
		$this->send_reminders("Yes");
	}

	function send_post_booking(){
		$jinput = JFactory::getApplication()->input;
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
			
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		if (!is_array($cid) || sv_count_($cid) < 1) {
			echo "<script> alert('Select an item for reminder'); window.history.go(-1);</script>\n";
			exit();
		}
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_requests", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
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
				logIt($e->getMessage(), "be_ctrl_requests", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
		// they changed JDate yet again
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);

			$status = '';
			$subject = JText::_('RS1_ADMIN_CONFIG_MSG_THANKYOU_SUBJECT');
			
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
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT').stripslashes($request->name)." ".stripslashes($request->phone).", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate."";
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

		parent::display();
		
	}
	
}	
?>

