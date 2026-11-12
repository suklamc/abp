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

function backupnow() {

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
-->
}
</style>
<div style="overflow:scroll; width:100%">
<?php
$err = "";
	// -------------------------------------------------------------------------
	//  sv_apptpro3_requests
	// -------------------------------------------------------------------------
	$database = JFactory::getDBO();
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_requests; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";

	}		
	if($rowCount->count == 0){		echo "<br>No Appontments found for backup.<br>";	
		echo("<br>");
	} else {
		echo "Dropping old Appontments backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_requests_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		

		echo "Create new Appontments backup table.. <br>";
		$sql = "create table #__sv_apptpro3_requests_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_requests; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");

	}
	
	// -------------------------------------------------------------------------
	//  sv_apptpro3_config
	// -------------------------------------------------------------------------
	echo "<br>Dropping old Configuration backup table..<br>";
	$sql = "drop table IF EXISTS #__sv_apptpro3_config_backup; ";
	try{
		$database->setQuery($sql);
		$database ->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		

	echo "Create new Configuration backup table.. <br>";
	$sql = "create table #__sv_apptpro3_config_backup engine InnoDB as SELECT * FROM ". 
		"#__sv_apptpro3_config;";
	try{
		$database->setQuery($sql);
		$database ->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	echo("Affected Rows: ".$database->getAffectedRows());
	echo("<br>");

	// -------------------------------------------------------------------------
	//  sv_apptpro3_resources
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_resources; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Resources found for backup.<br>";	
	} else {
		echo "<br>Dropping old Resources backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_resources_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Resources backup table.. <br>";
		$sql = "create table #__sv_apptpro3_resources_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_resources; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_timeslots
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_timeslots; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Timeslots found for backup.<br>";	
	} else {
		echo "<br>Dropping old Timeslots backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_timeslots_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Timeslots backup table.. <br>";
		$sql = "create table #__sv_apptpro3_timeslots_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_timeslots; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		

	// -------------------------------------------------------------------------
	//  sv_apptpro3_bookoffs
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_bookoffs; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No BookOffs found for backup.<br>";	
	} else {
		echo "<br>Dropping old BookOffs backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_bookoffs_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new BookOffs backup table.. <br>";
		$sql = "create table #__sv_apptpro3_bookoffs_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_bookoffs; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_book_dates
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_book_dates; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Book Dates found for backup.<br>";	
	} else {
		echo "<br>Dropping old Book Dates backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_book_dates_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Book Dates backup table.. <br>";
		$sql = "create table #__sv_apptpro3_book_dates_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_book_dates; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_categories
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_categories; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Categories found for backup.<br>";	
	} else {
		echo "<br>Dropping old Categories backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_categories_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Categories backup table.. <br>";
		$sql = "create table #__sv_apptpro3_categories_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_categories; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_services
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_services; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Services found for backup.<br>";	
	} else {
		echo "<br>Dropping old Services backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_services_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Services backup table.. <br>";
		$sql = "create table #__sv_apptpro3_services_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_services; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_udfs
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_udfs; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No UDFs found for backup.<br>";	
	} else {
		echo "<br>Dropping old UDFs backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_udfs_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new UDFs backup table.. <br>";
		$sql = "create table #__sv_apptpro3_udfs_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_udfs; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_udfvalues
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_udfvalues; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No UDF Values found for backup.<br>";	
	} else {
		echo "<br>Dropping old UDF Values backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_udfvalues_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new UDF Values backup table.. <br>";
		$sql = "create table #__sv_apptpro3_udfvalues_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_udfvalues; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		

	// -------------------------------------------------------------------------
	//  sv_apptpro3_coupons
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_coupons; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Coupons found for backup.<br>";	
	} else {
		echo "<br>Dropping old Coupons backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_coupons_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Coupons backup table.. <br>";
		$sql = "create table #__sv_apptpro3_coupons_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_coupons; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		

	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_types
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_seat_types; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Seat Types found for backup.<br>";	
	} else {
		echo "<br>Dropping old Seat Types backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_seat_types_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Seat Types backup table.. <br>";
		$sql = "create table #__sv_apptpro3_seat_types_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_seat_types; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_counts
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_seat_counts; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Seat Counts found for backup.<br>";	
	} else {
		echo "<br>Dropping old Seat Counts backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_seat_counts_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Seat Counts backup table.. <br>";
		$sql = "create table #__sv_apptpro3_seat_counts_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_seat_counts; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_extras
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_extras; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Extras found for backup.<br>";	
	} else {
		echo "<br>Dropping old Extras backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_extras_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Extras backup table.. <br>";
		$sql = "create table #__sv_apptpro3_extras_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_extras; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_extras_data
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_extras_data; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Extras Data found for backup.<br>";	
	} else {
		echo "<br>Dropping old Extras Data backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_extras_data_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Extras Data backup table.. <br>";
		$sql = "create table #__sv_apptpro3_extras_data_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_extras_data; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_user_credit
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_user_credit";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No User Credit date found for backup.<br>";	
	} else {
		echo "<br>Dropping old User Credit backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_user_credit_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new User Credit backup table.. <br>";
		$sql = "create table #__sv_apptpro3_user_credit_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_user_credit; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}
		
	// -------------------------------------------------------------------------
	//  sv_apptpro3_user_credit_activity
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_user_credit_activity; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No User Credit Activity found for backup.<br>";	
	} else {
		echo "<br>Dropping old User Credit Activity backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_user_credit_activity_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new User Credit Activity backup table.. <br>";
		$sql = "create table #__sv_apptpro3_user_credit_activity_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_user_credit_activity; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_rate_overrides
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_rate_overrides; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Rate Overrides found for backup.<br>";	
	} else {
		echo "<br>Dropping old Rate Overrides backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_rate_overrides_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Rate Overrides backup table.. <br>";
		$sql = "create table #__sv_apptpro3_rate_overrides_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_rate_overrides; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_rate_adjustments
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_rate_adjustments; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Rate Adjustments found for backup.<br>";	
	} else {
		echo "<br>Dropping old Rate Adjustments backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_rate_adjustments_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Rate Adjustments backup table.. <br>";
		$sql = "create table #__sv_apptpro3_rate_adjustments_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_rate_adjustments; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_seat_adjustments
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_seat_adjustments; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Seat Adjustments found for backup.<br>";	
	} else {
		echo "<br>Dropping old Seat Adjustments backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_seat_adjustments_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Seat Adjustments backup table.. <br>";
		$sql = "create table #__sv_apptpro3_seat_adjustments_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_seat_adjustments; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_email_marketing
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_email_marketing; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Email Marketing data found for backup.<br>";	
	} else {
		echo "<br>Dropping old Email Marketing data backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_email_marketing_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Email Marketing data backup table.. <br>";
		$sql = "create table #__sv_apptpro3_email_marketing_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_email_marketing; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

				
	// -------------------------------------------------------------------------
	//  sv_apptpro3_errorlog
	// -------------------------------------------------------------------------
	$jinput = JFactory::getApplication()->input;

	if($jinput->getString('chkBackupErrorLog')=='on'){
		$sql = "Select Count(*) as count FROM #__sv_apptpro3_errorlog; ";
		try{
			$database->setQuery($sql);
			$rowCount = Null;
			$rowCount = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		if($rowCount->count == 0){		echo "<br>No Error Log Entries found for backup.<br>";	
		} else {
			echo "<br>Dropping old Error Log backup table..<br>";
			$sql = "drop table IF EXISTS #__sv_apptpro3_errorlog_backup; ";
			try{
				$database->setQuery($sql);
				$database ->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_backup", "", "");
				
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
		
			echo "Create new Error Log backup table.. <br>";
			$sql = "create table #__sv_apptpro3_errorlog_backup engine InnoDB as SELECT * FROM ".
				"#__sv_apptpro3_errorlog; ";
			try{
				$database->setQuery($sql);
				$database ->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_backup", "", "");
				
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
		}
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_reminderlog
	// -------------------------------------------------------------------------
	if($jinput->getString('chkBackupReminderLog')=='on'){
		$sql = "Select Count(*) as count FROM #__sv_apptpro3_reminderlog; ";
		try{
			$database->setQuery($sql);
			$rowCount = Null;
			$rowCount = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		if($rowCount->count == 0){		echo "<br>No Reminder Log Entries found for backup.<br>";	
		} else {
			echo "<br>Dropping old Reminder Log backup table..<br>";
			$sql = "drop table IF EXISTS #__sv_apptpro3_reminderlog_backup; ";
			try{
				$database->setQuery($sql);
				$database ->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_backup", "", "");
				
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
		
			echo "Create new Reminder Log backup table.. <br>";
			$sql = "create table #__sv_apptpro3_reminderlog_backup engine InnoDB as SELECT * FROM ".
				"#__sv_apptpro3_reminderlog; ";
			try{
				$database->setQuery($sql);
				$database ->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_backup", "", "");
				
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
		}
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_mail
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_mail; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Messages Entries found for backup.<br>";	
	} else {
		echo "<br>Dropping old Messages data backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_mail_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Messages data backup table.. <br>";
		$sql = "create table #__sv_apptpro3_mail_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_mail; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_payment_processors
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_payment_processors; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Payment Processors found for backup.<br>";	
	} else {
		echo "<br>Dropping old Payment Processors data backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_payment_processors_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Payment Processors data backup table.. <br>";
		$sql = "create table #__sv_apptpro3_payment_processors_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_payment_processors; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  installed payment processors settings
	// -------------------------------------------------------------------------
	// get payment processors
	$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors;';
	try{
		$database->setQuery($sql);
		$pay_procs = NULL;
		$pay_procs = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_pay_procs_tmpl_form", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	foreach($pay_procs as $pay_proc){ 
		// -------------------------------------------------------------------------
		//  installed payment processors settings
		// -------------------------------------------------------------------------
	
        $sql = "Select Count(*) as count FROM #__sv_apptpro3_".$pay_proc->prefix."_settings";
        try{
            $database->setQuery($sql);
            $rowCount = Null;
            $rowCount = $database -> loadObject();
        } catch (RuntimeException $e) {
            logIt($e->getMessage(), "be_backup", "", "");
            
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
        }		
        if($rowCount->count == 0){		echo "<br>No records found for backup: #__sv_apptpro3_".$pay_proc->prefix."_settings <br>";	
        } else {
            echo "<br>Dropping old backup table..<br>";
            $sql = "drop table IF EXISTS #__sv_apptpro3_".$pay_proc->prefix."_settings_backup; ";
            try{
                $database->setQuery($sql);
                $database ->execute();
            } catch (RuntimeException $e) {
                logIt($e->getMessage(), "be_backup", "", "");
                
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
            }		
        
            echo "Create new backup table.. <br>";
            $sql = "create table #__sv_apptpro3_".$pay_proc->prefix."_settings_backup engine InnoDB as SELECT * FROM ".
                "#__sv_apptpro3_".$pay_proc->prefix."_settings; ";
            try{
                $database->setQuery($sql);
                $database ->execute();
            } catch (RuntimeException $e) {
                logIt($e->getMessage(), "be_backup", "", "");
                
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
            }		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
        }

		// -------------------------------------------------------------------------
		//  installed payment processors transactions
		// -------------------------------------------------------------------------
		if($pay_proc->prefix!='payage'){
			$sql = "Select Count(*) as count FROM #__sv_apptpro3_".$pay_proc->prefix."_transactions;";
			try{
				$database->setQuery($sql);
				$rowCount = Null;
				$rowCount = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_backup", "", "");
				
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
			}		
			if($rowCount->count == 0){		echo "<br>No records found for backup: #__sv_apptpro3_".$pay_proc->prefix."_transactions <br>";	
			} else {
				echo "<br>Dropping old backup table..<br>";
				$sql = "drop table IF EXISTS #__sv_apptpro3_".$pay_proc->prefix."_transactions_backup; ";
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_backup", "", "");
					
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
			
				echo "Create new backup table.. <br>";
				$sql = "create table #__sv_apptpro3_".$pay_proc->prefix."_transactions_backup engine InnoDB as SELECT * FROM ".
					"#__sv_apptpro3_".$pay_proc->prefix."_transactions; ";
				try{
					$database->setQuery($sql);
					$database ->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_backup", "", "");
					
			$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
				}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
			}
		}
	}

	// -------------------------------------------------------------------------
	//  sv_apptpro3_products
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_products; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Products found for backup.<br>";	
	} else {
		echo "<br>Dropping old Products backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_products_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Products backup table.. <br>";
		$sql = "create table #__sv_apptpro3_products_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_products; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}


	// -------------------------------------------------------------------------
	//  sv_apptpro3_notification_list
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_notification_list; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Notifications found for backup.<br>";	
	} else {
		echo "<br>Dropping old Notifications backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_notification_list_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Notifications backup table.. <br>";
		$sql = "create table #__sv_apptpro3_notification_list_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_notification_list; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}



	// -------------------------------------------------------------------------
	//  sv_apptpro3_export_columns
	// -------------------------------------------------------------------------
	$sql = "Select Count(*) as count FROM #__sv_apptpro3_export_columns; ";
	try{
		$database->setQuery($sql);
		$rowCount = Null;
		$rowCount = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_backup", "", "");
		
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
	}		
	if($rowCount->count == 0){		echo "<br>No Export Columns found for backup.<br>";	
	} else {
		echo "<br>Dropping old Export Columns backup table..<br>";
		$sql = "drop table IF EXISTS #__sv_apptpro3_export_columns_backup; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
	
		echo "Create new Export Columns backup table.. <br>";
		$sql = "create table #__sv_apptpro3_export_columns_backup engine InnoDB as SELECT * FROM ".
			"#__sv_apptpro3_export_columns; ";
		try{
			$database->setQuery($sql);
			$database ->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_backup", "", "");
			
		$err .= JText::_('RS1_SQL_ERROR').$e->getMessage()."<hr>";
		}		
		echo("Affected Rows: ".$database->getAffectedRows());
		echo("<br>");
	}

	// -------------------------------------------------------------------------
	//  language file
	// -------------------------------------------------------------------------
	if($jinput->getString('chkBackupLangFile')=='on'){
		$file = JPATH_SITE."/language/en-GB/en-GB.com_rsappt_pro3.ini";
		$newfile = JPATH_SITE."/language/en-GB/en-GB.com_rsappt_pro3.ini_bac";

		if (!copy($file, $newfile)) {
		    echo "Failed to backed up ". $file;
		} else {
			echo "<br>Language file backed up.<br>";
		}
	}

	// -------------------------------------------------------------------------
	//  css file
	// -------------------------------------------------------------------------
	$file = JPATH_SITE."/components/com_rsappt_pro3/sv_apptpro.css";
	$newfile = JFactory::getApplication()->getCfg('tmp_path')."/sv_apptpro.css_bac";
	if (!copy($file, $newfile)) {
		$err .="Failed to backed up CSS file: ". $file;
        logIt("Failed to backed up CSS file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.", "be_backup", "", "");
		echo "Failed to backed up CSS file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.<br>";
	} else {
		echo "<br>CSS file backed up.<br>";
	}

	// -------------------------------------------------------------------------
	//  google calendar api p12 file
	// -------------------------------------------------------------------------
	foreach (glob(JPATH_SITE."/components/com_rsappt_pro3/*.p12") as $filename) {
		$path_parts = pathinfo($filename);
		$file = JPATH_SITE."/components/com_rsappt_pro3/".$path_parts['basename'];		
		$newfile = JFactory::getApplication()->getCfg('tmp_path').DIRECTORY_SEPARATOR.$path_parts['basename']."_bac";
		//echo $file."<br>";
		//echo $newfile."<br>";
		if (!copy($file, $newfile)) {
			$err .="Failed to backed up Google Calendar p12 file: ". $file;
			logIt("Failed to backed up Google Calendar p12 file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.", "be_backup", "", "");
			echo "Failed to backed up Google Calendar p12 file:  ". $file." - Check your Joomla `Path to Temp Folder` path is set correctly.<br>";
		} else {
			echo "<br>Google Calendar p12 file backed up.<br>";
		}
	}


	// -------------------------------------------------------------------------
	//  google calendar api folder
	// -------------------------------------------------------------------------
	$folder = JPATH_SITE."/components/com_rsappt_pro3/google-api-php-client-master";
	if ( !file_exists( $folder ) && !is_dir( $folder ) ) {
		$newfolder = JFactory::getApplication()->getCfg('tmp_path')."/google-api-php-client-master_bac";
		if (!recurse_copy($folder, $newfolder)) {
			$err .="Failed to backed up Google Calendar API folder: ". $folder;
			logIt("Failed to backed up Google Calendar API folder:  ". $folder." - Check your Joomla `Path to Temp Folder` path is set correctly. This error can be ignored if you previously used ABPro Backup, the temp folder is already there and cannot be overwritten.", "be_backup", "", "");
			echo "Failed to backed up Google Calendar API folder:  ". $folder." - Check your Joomla `Path to Temp Folder` path is set correctly.<br>";
		} else {
			echo "<br>Google Calendar API folder backed up.<br>";
		}
	} else {
		$results = "No Google Calendar API folder found";
	}

	if ($err != ""){
		$results = "Errors were encountered. \\nIf the error(s) are data not found on a feature you do not use they can be ignored. \\nCheck the Error Log for details.";
	} else {
		$results = "Backup Complete";
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
