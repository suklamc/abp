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

function buildInsert($table, $import_from = ""){
	// build insert based on number of columns in $table and $table_backup
	
	if($import_from != "" ){
		$table_backup = $import_from;
	} else {
		$table_backup = $table."_backup";
	}
	$return = "";
	$destColumns = null;
	
	$database = JFactory::getDBO();
	// There may be less columns in the _backup than in the destination so we use columns 
	// from _backup to create the insert. Other columns will default.
	
	// get columns for destination
	$sql = "show columns from ".$table_backup;
	try{
		$database->setQuery($sql);
		$destColumns = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		//
	}		
	$return="insert into ".$table."(";
	for($i=0;$i<sv_count_($destColumns);$i++){
		$fields1 = $destColumns[$i];
		$return = $return.$fields1->Field;
		if($i<(sv_count_($destColumns))-1){
			$return = $return.", ";
		}
	}
	$return = $return.") ";

	$return = $return."select * from ".$table_backup;

	return $return;	
}

function restorenow() {
	
	$config = JFactory::getConfig();
	$dbtype = $config->get('dbtype');
	
	if($dbtype == "mysqli"){
	} else if($dbtype == "mysql"){
	} else {
		echo "Database type not supported by ABPro Backup/Restore.";
		exit;
	}
	
?>
<style type="text/css">
<!--
.row0 { border:solid thin #999  }
.row1 { border:solid thin #999  }
.restorelist { border:solid thin #999 }
-->
}
</style>
<div style="overflow:scroll; width:100%">
<?php
	// -------------------------------------------------------------------------
	//  sv_apptpro3_config
	// -------------------------------------------------------------------------
	$database = JFactory::getDBO();
	$err = "";
	$jinput = JFactory::getApplication()->input;
	
	$abp_ver = 3;
	if($jinput->getString('chkFromV2')=='on'){
		$abp_ver = 2;
	}

	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_config_backup;";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No configuration information found in backup file.<br>";	
	} else {
		echo "Remove old configuration information...<br>";
		try{
			$sql = "DELETE FROM #__sv_apptpro3_config; ";
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Load configuration from backup table... <br>";
		$sql = buildInsert("#__sv_apptpro3_config", "#__sv_apptpro".$abp_ver."_config_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		//
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}		
		
	}
	
	echo "Adjusting date picker format string as required... <br>";
	// in ABPro 4.0.5, the field date_picker_format was changed to represent the actual php format string
	// DD-MM-YYYY becomes dd-mm-yy
	// MM-DD-YYYY becomes mm-dd-yy
	// YYYY-MM-DD becomes yy-mm-dd
	// Here we update old style to new if required
	$sql = "UPDATE ".$database->getPrefix()."sv_apptpro3_config SET date_picker_format = \"dd-mm-yy\" WHERE date_picker_format = \"DD-MM-YYYY\"";
	try{
		$database->setQuery($sql);
		$database ->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	$sql = "UPDATE ".$database->getPrefix()."sv_apptpro3_config SET date_picker_format = \"mm-dd-yy\" WHERE date_picker_format = \"MM-DD-YYYY\"";
	try{
		$database->setQuery($sql);
		$database ->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	$sql = "UPDATE ".$database->getPrefix()."sv_apptpro3_config SET date_picker_format = \"yy-mm-dd\" WHERE date_picker_format = \"YYYY-MM-DD\"";
	try{
		$database->setQuery($sql);
		$database ->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	
	// -------------------------------------------------------------------------
	//  sv_apptpro3_resources
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_resources_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";		
	}		
		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Resources found in backup file. No Resources restored.<br>";	
	} else {

		$sql = "DELETE FROM #__sv_apptpro3_resources; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}		
	
		echo "<br>Restore Resources from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_resources", "#__sv_apptpro".$abp_ver."_resources_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}		
				
	}
	
	// -------------------------------------------------------------------------
	//  sv_apptpro3_requests
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_requests_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");	
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";		
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Appontments found in backup file. No Appontments restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_requests; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Appontments from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_requests", "#__sv_apptpro".$abp_ver."_requests_backup");
		try{	
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");		
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";	
		}		

	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_timeslots
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_timeslots_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Timeslots found in backup file. No Timeslots restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_timeslots; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Timeslots from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_timeslots", "#__sv_apptpro".$abp_ver."_timeslots_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}		
		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_bookoffs
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_bookoffs_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		

	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No BookOffs found in backup file. No BookOffs restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_bookoffs; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore BookOffs from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_bookoffs", "#__sv_apptpro".$abp_ver."_bookoffs_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_book_dates
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_book_dates_backup; ";
	try{
		$database->setQuery($sql);		
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Book Dates found in backup file. No Book Dates restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_book_dates; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Book Dates from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_book_dates", "#__sv_apptpro".$abp_ver."_book_dates_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_categories
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_categories_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Categories found in backup file. No Categories restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_categories; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Categories from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_categories", "#__sv_apptpro".$abp_ver."_categories_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_services
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_services_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Services found in backup file. No Services restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_services; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Services from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_services", "#__sv_apptpro".$abp_ver."_services_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";	
		}		

		echo "Migrate Service resource to resource_scope";
		$sql = "UPDATE #__sv_apptpro3_services ".
			"SET resource_scope = CONCAT('|', resource_id, '|'), ".
			"resource_id = NULL ".
			"WHERE ".
			"resource_scope IS NULL ".
			"AND resource_id IS NOT NULL";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";	
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_udfs
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_udfs_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No UDFs found in backup file. No UDFs restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_udfs; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore UDFs from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_udfs", "#__sv_apptpro".$abp_ver."_udfs_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_udfvalues
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_udfvalues_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No UDF Values found in backup file. No UDF Values restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_udfvalues; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore UDF Values from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_udfvalues", "#__sv_apptpro".$abp_ver."_udfvalues_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_coupons
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_coupons_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Coupons found in backup file. No Coupons restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_coupons; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Coupons from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_coupons", "#__sv_apptpro".$abp_ver."_coupons_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_types
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_seat_types_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Seat Types found in backup file. No Seat Types restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_seat_types; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Seat Types from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_seat_types", "#__sv_apptpro".$abp_ver."_seat_types_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_counts
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_seat_counts_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Seat Counts found in backup file. No Seat Counts restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_seat_counts; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Seat Counts from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_seat_counts", "#__sv_apptpro".$abp_ver."_seat_counts_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_extras
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_extras_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Extras found in backup file. No Extras restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_extras; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Extras from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_extras", "#__sv_apptpro".$abp_ver."_extras_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_extras_data
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_extras_data_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Extras Data found in backup file. No Extras Data restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_extras_data; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Extras Data from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_extras_data", "#__sv_apptpro".$abp_ver."_extras_data_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_paypal_transactions
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_paypal_transactions_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No PayPal Tranasctions found in backup file. No PayPal Values restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_paypal_transactions; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore PayPal Tranasctions from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_paypal_transactions", "#__sv_apptpro".$abp_ver."_paypal_transactions_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_authnet_transactions
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_authnet_transactions_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Authorize.net Tranasctions found in backup file. No Authorize.net Values restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_authnet_transactions; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Authorize.net Tranasctions from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_authnet_transactions", "#__sv_apptpro".$abp_ver."_authnet_transactions_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3__2co_transactions
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."__2co_transactions_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No 2CheckOut Tranasctions found in backup file. No 2CheckOut Values restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3__2co_transactions; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore 2CheckOut Tranasctions from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3__2co_transactions", "#__sv_apptpro".$abp_ver."__2co_transactions_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_user_credit
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_user_credit_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No User Credit found in backup file. No User Credit restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_user_credit; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore User Credit from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_user_credit", "#__sv_apptpro".$abp_ver."_user_credit_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_user_credit_activity
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_user_credit_activity_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";		
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No User Credit Activity found in backup file. No User Credit Activity restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_user_credit_activity; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore User Credit Activity from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_user_credit_activity", "#__sv_apptpro".$abp_ver."_user_credit_activity_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_rate_overrides
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_rate_overrides_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Rate Overrides found in backup file. No Rate Overrides restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_rate_overrides; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Rate Overrides from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_rate_overrides", "#__sv_apptpro".$abp_ver."_rate_overrides_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_rate_adjustments
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_rate_adjustments_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Rate Adjustments found in backup file. No Rate Adjustments restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_rate_adjustments; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Rate Adjustments from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_rate_adjustments", "#__sv_apptpro".$abp_ver."_rate_adjustments_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_adjustments
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_seat_adjustments_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Seat Adjustments found in backup file. No Seat Adjustments restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_seat_adjustments; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Seat Adjustments from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_seat_adjustments", "#__sv_apptpro".$abp_ver."_seat_adjustments_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_email_marketing
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_email_marketing_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Email Marketing data found in backup file. No Email Marketing data restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_email_marketing; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Email Marketing data from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_email_marketing", "#__sv_apptpro".$abp_ver."_email_marketing_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_mail
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_mail_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}	
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Messages data found in backup file. No Messages data restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_mail; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Messages data from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_mail", "#__sv_apptpro".$abp_ver."_mail_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	}
	
	// if backup contained messages data in config, move to Global in message center and remove from config
	
	$sql = "Select booking_succeeded,booking_succeeded_admin, booking_succeeded_sms, booking_in_progress, ".
		" booking_in_progress_admin, booking_in_progress_sms, booking_cancel, booking_cancel_sms, booking_too_close_to_cancel, ".
		" booking_reminder, booking_reminder_sms FROM #__sv_apptpro".$abp_ver."_config_backup; ";
	try{
		$database->setQuery($sql);
		$temp = Null;
		$temp = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";		
	}	
	//print_r($temp);
	if($temp->booking_succeeded != ""){
		// old messages data found, put it inoto the Global row of message center
		$sql = "UPDATE #__sv_apptpro".$abp_ver."_mail SET ".
		" booking_succeeded = '".$temp->booking_succeeded."',".
		" booking_succeeded_admin = '".$temp->booking_succeeded_admin."',".
		" booking_succeeded_sms = '".$temp->booking_succeeded_sms."',".
		" booking_in_progress = '".$temp->booking_in_progress."',".
		" booking_in_progress_admin = '".$temp->booking_in_progress_admin."',".
		" booking_in_progress_sms = '".$temp->booking_in_progress_sms."',". 
		" booking_cancel = '".$temp->booking_cancel."',". 
		" booking_cancel_sms = '".$temp->booking_cancel_sms."',". 
		" booking_too_close_to_cancel = '".$temp->booking_too_close_to_cancel."',".
		" booking_reminder = '".$temp->booking_reminder."',". 
		" booking_reminder_sms = '".$temp->booking_reminder_sms."'".
		" WHERE id_mail = 1";
	}
	$update_ok = true;
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$update_ok = false;
	}	
	if($update_ok == true){
		// ok to remove from config, this is done so next time you backup/restore it will not overwrite your Glocal with old config data
		$sql = "UPDATE #__sv_apptpro".$abp_ver."_config SET ".
		" booking_succeeded = '',".
		" booking_succeeded_admin = '',".
		" booking_succeeded_sms = '',".
		" booking_in_progress = '',".
		" booking_in_progress_admin = '',".
		" booking_in_progress_sms = '',".
		" booking_cancel = '',".
		" booking_cancel_sms =  '',".
		" booking_too_close_to_cancel = '',".
		" booking_reminder = '',". 
		" booking_reminder_sms = ''".
		" WHERE id_config = 1";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}	
		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_payment_processors
	// -------------------------------------------------------------------------
	// No need to restore sv_apptpro3_payment_processors as it is strictly internal with no user set data
	
	// If the backup contained PayPal, Authnet or 2Checkout data in config, move to _settings tables and remove from config

	// PayPal settings
		// First check to see if paypal_settings_backup table exists, if so just restore it, if not this must be an
		// upgrade from an older version that needs paypal stuff moved from config file.
		$config = JFactory::getConfig();
		$db_prefix = $config->get( 'dbprefix' );	
		$sql = "SHOW TABLES LIKE '".$db_prefix."sv_apptpro3_paypal_settings_backup'";
		try{
			$database->setQuery($sql);
			$temp = "";
			$temp = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			
		}		
		if($temp == ""){
			// no paypal_settings_backup, get data from config		
			$pptemp = null;
			$sql = "Select enable_paypal, paypal_button_url, paypal_logo_url, paypal_currency_code, paypal_account, paypal_sandbox_url, ".
				"paypal_use_sandbox, paypal_production_url, paypal_itemname, paypal_on0, paypal_os0, paypal_on1, paypal_os1, paypal_on2, ".
				"paypal_os2, paypal_on3, paypal_os3 FROM #__sv_apptpro".$abp_ver."_config_backup; ";
			try{
				$database->setQuery($sql);
				$pptemp = Null;
				$pptemp = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}	
			//print_r($pptemp);
			if($pptemp->paypal_account != ""){
				// old payapl data found, put it into the paypal_settings table
				$sql = "UPDATE #__sv_apptpro".$abp_ver."_paypal_settings SET ".
				" paypal_enable = '".$pptemp->enable_paypal."',".
				" paypal_button_url = '".$pptemp->paypal_button_url."',".
				" paypal_logo_url = '".$pptemp->paypal_logo_url."',".
				" paypal_currency_code = '".$pptemp->paypal_currency_code."',".
				" paypal_account = '".$pptemp->paypal_account."',".
				" paypal_sandbox_url = '".$pptemp->paypal_sandbox_url."',". 
				" paypal_use_sandbox = '".$pptemp->paypal_use_sandbox."',". 
				" paypal_production_url = '".$pptemp->paypal_production_url."',". 
				" paypal_itemname = '".$pptemp->paypal_itemname."',".
				" paypal_on0 = '".$pptemp->paypal_on0."',". 
				" paypal_os0 = '".$pptemp->paypal_os0."',".
				" paypal_on1 = '".$pptemp->paypal_on1."',". 
				" paypal_os1 = '".$pptemp->paypal_os1."',".
				" paypal_on2 = '".$pptemp->paypal_on2."',". 
				" paypal_os2 = '".$pptemp->paypal_os2."',".
				" paypal_on3 = '".$pptemp->paypal_on3."',". 
				" paypal_os3 = '".$pptemp->paypal_os3."'".
				" WHERE id_paypal_settings = 1";
			}
			$update_ok = true;
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				$update_ok = false;
			}	
			if($update_ok == true){
				// ok to remove from config, this is done so next time you backup/restore it will not overwrite old config data
				$sql = "UPDATE #__sv_apptpro".$abp_ver."_config SET ".
				" enable_paypal = '',".
				" paypal_button_url = '',".
				" paypal_logo_url = '',".
				" paypal_currency_code = '',".
				" paypal_account = '',".
				" paypal_sandbox_url = '',".
				" paypal_use_sandbox = '',".
				" paypal_production_url =  '',".
				" paypal_itemname = '',".
				" paypal_on0 = '',". 
				" paypal_os0 = '',".
				" paypal_on1 = '',". 
				" paypal_os1 = '',".
				" paypal_on2 = '',". 
				" paypal_os2 = '',".
				" paypal_on3 = '',". 
				" paypal_os3 = ''".
				" WHERE id_config = 1";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}			
			}
		} else {
			//sv_apptpro3_paypal_settings_backup exists, restore it
			$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_paypal_settings_backup; ";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
			}		
			if($rowCount == null || $rowCount->count == 0){
				echo "<br>No PayPal settings found in backup file. No PayPal settings restored.<br>";	
			} else {
				$sql = "DELETE FROM #__sv_apptpro3_paypal_settings; "; 
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}		
			
				echo "<br>Restore PayPal settings from backup table.. <br>";
				$sql = buildInsert("#__sv_apptpro3_paypal_settings", "#__sv_apptpro".$abp_ver."_paypal_settings_backup");
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}									
			}
		}

	// Authnet settings
		// First check to see if authnet_settings_backup table exists, if so just restore it, if not this must be an
		// upgrade from an older version that needs authnet stuff moved from config file.
		$sql = "SHOW TABLES LIKE '".$db_prefix."sv_apptpro3_authnet_settings_backup'";
		try{
			$database->setQuery($sql);
			$temp = "";
			$temp = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			
		}		
		if($temp == ""){
			// no authnet_settings_backup, get data from config		
			$antemp = null;
		$sql = "Select authnet_enable, authnet_api_login_id, authnet_transaction_key, authnet_header_text, ".
			"authnet_footer_text, authnet_button_url FROM #__sv_apptpro".$abp_ver."_config_backup; ";
		try{
			$database->setQuery($sql);
			$antemp = Null;
			$antemp = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}	
		//print_r($antemp);
		if($antemp->authnet_api_login_id != ""){
			// old authnet data found, put it into the authnet_settings table
			$sql = "UPDATE #__sv_apptpro".$abp_ver."_authnet_settings SET ".
			" authnet_enable = '".$antemp->authnet_enable."',".
			" authnet_api_login_id = '".$antemp->authnet_api_login_id."',".
			" authnet_transaction_key = '".$antemp->authnet_transaction_key."',".
			" authnet_header_text = '".$antemp->authnet_header_text."',".
			" authnet_footer_text = '".$antemp->authnet_footer_text."',".
			" authnet_button_url = '".$antemp->authnet_button_url."'". 
			" WHERE id_authnet_settings = 1";
		}
		$update_ok = true;
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			$update_ok = false;
		}	
		if($update_ok == true){
			// ok to remove from config, this is done so next time you backup/restore it will not overwrite with old config data
			$sql = "UPDATE #__sv_apptpro".$abp_ver."_config SET ".
			" authnet_enable = '',".
			" authnet_api_login_id = '',".
			" authnet_transaction_key = '',".
			" authnet_header_text = '',".
			" authnet_footer_text = '',".
			" authnet_button_url = ''".
			" WHERE id_config = 1";
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
			
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}			
		}
		} else {
			//sv_apptpro3_authnet_settings_backup exists, restore it
			$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_authnet_settings_backup; ";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
			}		
			if($rowCount == null || $rowCount->count == 0){
				echo "<br>No Authorize.net settings found in backup file. No Authroize.net settings restored.<br>";	
			} else {
				$sql = "DELETE FROM #__sv_apptpro3_authnet_settings; "; 
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}		
			
				echo "<br>Restore Authorize.net settings from backup table.. <br>";
				$sql = buildInsert("#__sv_apptpro3_authnet_settings", "#__sv_apptpro".$abp_ver."_authnet_settings_backup");
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}		
			}
		}

	// 2Co settings
		// First check to see if _2co_settings_backup table exists, if so just restore it, if not this must be an
		// upgrade from an older version that needs 2co stuff moved from config file.
		$sql = "SHOW TABLES LIKE '".$db_prefix."sv_apptpro3__2co_settings_backup'";
		try{
			$database->setQuery($sql);
			$temp = "";
			$temp = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			
		}		
		if($temp == ""){
			// no _2co_settings_backup, get data from config	
			$_2cotemp = null;
		
			$sql = "Select _2co_enable, _2co_account, _2co_demo, _2co_button_url, ".
				"_2co_item_name FROM #__sv_apptpro".$abp_ver."_config_backup; ";
			try{
				$database->setQuery($sql);
				$_2cotemp = Null;
				$_2cotemp = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}	
			//print_r($antemp);
			if($_2cotemp->_2co_account != ""){
				// old 2checkout data found, put it into the _2co_settings table
				$sql = "UPDATE #__sv_apptpro".$abp_ver."__2co_settings SET ".
				" _2co_enable = '".$_2cotemp->_2co_enable."',".
				" _2co_account = '".$_2cotemp->_2co_account."',".
				" _2co_demo = '".$_2cotemp->_2co_demo."',".
				" _2co_button_url = '".$_2cotemp->_2co_button_url."',".
				" _2co_item_name = '".$_2cotemp->_2co_item_name."'".
				" WHERE id_2co_settings = 1";
			}
			$update_ok = true;
			try{
				$database->setQuery($sql);
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				$update_ok = false;
			}	
			if($update_ok == true){
				// ok to remove from config, this is done so next time you backup/restore it will not overwrite with old config data
				$sql = "UPDATE #__sv_apptpro".$abp_ver."_config SET ".
				" _2co_enable = '',".
				" _2co_account = '',".
				" _2co_demo = '',".
				" _2co_button_url = '',".
				" _2co_item_name = ''".
				" WHERE id_config = 1";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}			
			}
		} else {
			//sv_apptpro3__2co_settings_backup exists, restore it
			$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."__2co_settings_backup; ";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
			}		
			if($rowCount == null || $rowCount->count == 0){
				echo "<br>No 2checkout settings found in backup file. No 2checkout settings restored.<br>";	
			} else {
				$sql = "DELETE FROM #__sv_apptpro3__2co_settings; "; 
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}		
			
				echo "<br>Restore 2checkout settings from backup table.. <br>";
				$sql = buildInsert("#__sv_apptpro3__2co_settings", "#__sv_apptpro".$abp_ver."__2co_settings_backup");
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			
				}						
			}
		}

	// These ones do not have to be purged from config as they never existed there..
	// Authnet AIM settings
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_authnet_aim_settings_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Authorize.net AIM settings found in backup file. No Authorize.net AIM settings restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_authnet_aim_settings; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}		
	
		echo "<br>Restore Authorize.net AIM settings from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_authnet_aim_settings", "#__sv_apptpro".$abp_ver."_authnet_aim_settings_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	
		}			
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_errorlog
	// -------------------------------------------------------------------------
	if($jinput->getString('chkRestoreErrorLog')=='on'){
		$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_errorlog_backup; ";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
			
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
			if($rowCount == null || $rowCount->count == 0){
				echo "<br>No Error Log found in backup file. No Error Log restored.<br>";	
			} else {
				$sql = "DELETE FROM #__sv_apptpro3_errorlog; "; 
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
			
				echo "<br>Restore Error Log from backup table.. <br>";
				$sql = buildInsert("#__sv_apptpro3_errorlog", "#__sv_apptpro".$abp_ver."_errorlog_backup");
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
				
			}
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_reminderlog
	// -------------------------------------------------------------------------
	if($jinput->getString('chkRestoreReminderLog')=='on'){
		$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_reminderlog_backup; ";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_restore", "", "");
			
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
			if($rowCount == null || $rowCount->count == 0){
				echo "<br>No Reminder Log found in backup file. No Reminder Log restored.<br>";	
			} else {
				$sql = "DELETE FROM #__sv_apptpro3_reminderlog; "; 
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
			
				echo "<br>Restore Reminder Log from backup table.. <br>";
				$sql = buildInsert("#__sv_apptpro3_reminderlog", "#__sv_apptpro".$abp_ver."_reminderlog_backup");
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_restore", "", "");
				
				$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
			}
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_products
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_products_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Products found in backup file. No Products restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_products; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Products from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_products", "#__sv_apptpro".$abp_ver."_products_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		
		
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_notification_list
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_notification_list_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Notifications found in backup file. No Notifications restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_notification_list; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Notifications from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_notification_list", "#__sv_apptpro".$abp_ver."_notification_list_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_export_columns
	// -------------------------------------------------------------------------
	$rowCount = Null;
	$sql = "Select Count(*) as count FROM #__sv_apptpro".$abp_ver."_export_columns_backup; ";
	try{
		$database->setQuery($sql);
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_restore", "", "");
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";				
	}		
	
	if($rowCount == null || $rowCount->count == 0){
		echo "<br>No Export Columns found in backup file. No Export Columns restored.<br>";	
	} else {
		$sql = "DELETE FROM #__sv_apptpro3_export_columns; "; 
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "<br>Restore Export Columns from backup table.. <br>";
		$sql = buildInsert("#__sv_apptpro3_export_columns", "#__sv_apptpro".$abp_ver."_export_columns_backup");
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_restore", "", "");
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		
	}



	// -------------------------------------------------------------------------
	//  language file
	// -------------------------------------------------------------------------
	if($jinput->getString('chkRestoreLangFile')=='on'){
		$file = JPATH_SITE."/language/en-GB/en-GB.com_rsappt_pro3.ini_bac";
		$newfile = JPATH_SITE."/language/en-GB/en-GB.com_rsappt_pro3.ini";
		if(file_exists($file)){ 
			if (!copy($file, $newfile)) {
				echo "Failed to restore up ". $file;
			} else {
				echo "<br>Language file restored.<br>";
			}
		} else {
				echo "<br>No backup Language file found, Language file NOT restored.<br>";
		}
	}

	// -------------------------------------------------------------------------
	//  backfill categories
	// -------------------------------------------------------------------------
	if($jinput->getString('chkBackfillCats')=='on'){
		$rowCount = 0;
		$sql = "update #__sv_apptpro3_requests ".
			"set #__sv_apptpro3_requests.category = ".
			"(select category_id from #__sv_apptpro3_resources  ".
			"where id_resources = #__sv_apptpro3_requests.resource and #__sv_apptpro3_resources.category_id IS NOT NULL) ".
			"where (#__sv_apptpro3_requests.category IS NULL OR #__sv_apptpro3_requests.category = '')";
			$database->setQuery($sql);
			if( !$result = $database->execute() ) {
				die( $database->stderr( true ) );
			} else {
				$rowCount = $database->getAffectedRows($result);
			}			
			if($rowCount == 0){
				echo "<br>No booking Category data found to adjust.<br>";	
			} else {
				echo "Booking Category backfilled on ".$rowCount." bookings.<br>";	
			}

	}
	
	// -------------------------------------------------------------------------
	//  Google Calendar p12 key files
	// -------------------------------------------------------------------------
	foreach (glob(JFactory::getApplication()->getCfg('tmp_path')."/*.p12_bac") as $filename) {
		$path_parts = pathinfo($filename);
		$file = JFactory::getApplication()->getCfg('tmp_path').DIRECTORY_SEPARATOR.$path_parts['basename'];
		$newfile = JPATH_SITE."/components/com_rsappt_pro3/".(str_replace("p12_bac", "p12", $path_parts['basename']));		
		//echo $file."<br>";
		//echo $newfile."<br>";
		if(file_exists($file)){ 
			if (!copy($file, $newfile)) {
				$err .="Failed to restore up Google Calendar p12 file: ". $file;
				logIt("Failed to restore up Google Calendar p12 file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.", "be_restore", "", "");
				echo "Failed to restore up Google Calendar p12 file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.<br>";
			} else {
				echo "<br>Google Calendar p12 file restored: ".str_replace("p12_bac", "p12", $path_parts['basename'])."<br>";
			}
		}
	}
	
	// -------------------------------------------------------------------------
	//  google calendar api folder
	// -------------------------------------------------------------------------
	$folder = JFactory::getApplication()->getCfg('tmp_path')."/google-api-php-client-master_bac";
	$newfolder = JPATH_SITE."/components/com_rsappt_pro3/google-api-php-client-master";
	if (!recurse_copy($folder, $newfolder)) {
		$err .="Failed to restore up Google Calendar API folder: ". $folder;
        logIt("Failed to restore up Google Calendar API folder:  ". $folder." - Ignore this message is you are not using Google Calendar.", "be_restore", "", "");
		echo "Failed to restore up Google Calendar API folder:  ". $folder." - Ignore this message is you are not using Google Calendar.<br>";
	} else {
		echo "<br>Google Calendar API folder restored.<br>";
	}

	

	// -------------------------------------------------------------------------
	//  css file
	// -------------------------------------------------------------------------
	if($jinput->getString('chkRestoreCSS')=='on'){
		$file = JFactory::getApplication()->getCfg('tmp_path')."/sv_apptpro.css_bac";	
		$newfile = JPATH_SITE."/components/com_rsappt_pro3/sv_apptpro.css";
		if(file_exists($file)){ 
			if (!copy($file, $newfile)) {
				$err .= "Failed to restore CSS file: ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.";
				echo "Failed to restore CSS file: ". $file;
				logIt("Failed to restore CSS file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.", "be_restore", "", "");
			} else {
				echo "<br>CSS file restored.<br>";
			}
		} else {
				$err .= "No CSS backup file found. CSS not restored. ". $file;
				logIt("No CSS backup file found. CSS not restored. ". $file, "be_restore", "", "");
				echo "<br>No CSS backup file found. CSS not restored. ". $file."<br>";
		}
	}
	echo "<p><span style='font-size:12px'><a href='index.php?option=com_rsappt_pro3&act=backup'>Continue...</a></span></p><br>&nbsp;";


	if ($err != ""){
		$results = "Errors were encountered. \\nIf the error(s) are data not found on a feature you do not use they can be ignored. \\nCheck the Error Log for details.";
	} else {
		$results = "Restore Complete";
	}
		

?>
</div>
    <script>
		document.body.style.cursor = "default"; 
		alert('<?php echo $results; ?>');
	</script>

<?php
}
?>
