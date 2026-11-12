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
 * rsappt_pro2  Controller
 */
 
class ajaxController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		$this->registerTask( 'ajax_user_search', 'ajax_user_search' );
		$this->registerTask( 'ajax_set_rate_override_enable', 'ajax_set_rate_override_enable' );
		$this->registerTask( 'ajax_set_gift_cert_enable', 'ajax_set_gift_cert_enable' );
		$this->registerTask( 'ajax_set_book_dates_enable', 'ajax_set_book_dates_enable' );

		$this->registerTask( 'ajax_get_purge_count', 'ajax_get_purge_count' );	
		$this->registerTask( 'ajax_do_purge', 'ajax_do_purge' );	

		$this->registerTask( 'ajax_get_table_columns', 'ajax_get_table_columns' );	

		$this->registerTask( 'ajax_export_table_update', 'ajax_export_table_update' );
	

	}


	function ajax_user_search()
	{
		include_once(JPATH_SITE.'/administrator/components/com_rsappt_pro3/ajax/user_search.php');
	}


	function ajax_set_rate_override_enable()
	{
		$jinput = JFactory::getApplication()->input;
		$new_value = $jinput->getWord( 'nv', 'Np' );
		
		$database =JFactory::getDBO(); 
		$sql = 'UPDATE #__sv_apptpro3_config SET enable_overrides = "'.$new_value.'"';
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		
		
		echo json_encode(JText::_('RS1_ADMIN_RATE_OVERRIDES_ENABLE_CHANGE'));
		jExit();
	}


	function ajax_set_gift_cert_enable()
	{
		$jinput = JFactory::getApplication()->input;
		$new_value = $jinput->getWord( 'nv', 'Np' );
		
		$database =JFactory::getDBO(); 
		$sql = 'UPDATE #__sv_apptpro3_config SET enable_gift_cert = "'.$new_value.'"';
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echojson_encode( JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		
		
		echo json_encode(JText::_('RS1_ADMIN_GIFT_CERT_ENABLE_CHANGE'));
		jExit();
	}


	function ajax_set_book_dates_enable()
	{
		$jinput = JFactory::getApplication()->input;
		$new_value = $jinput->getWord( 'nv', 'Np' );
		$resource = $jinput->getInt( 'res', 0 );
		
		$database =JFactory::getDBO(); 
		$sql = 'UPDATE #__sv_apptpro3_resources SET date_specific_booking = "'.$new_value.'" WHERE id_resources = '.$resource;
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		
		
		echo json_encode(JText::_('RS1_ADMIN_BOOK_DATES_ENABLE_CHANGE'));
		jExit();
	}

	function ajax_get_purge_count(){
		
		$jinput = JFactory::getApplication()->input;
		$purge_unit = $jinput->getWord( 'punit', '' );
		$purge_unit_count = $jinput->getInt( 'pcount', 1 );
		$result = null;
		
		$database =JFactory::getDBO(); 
		$sql = "SELECT count(*) FROM #__sv_apptpro3_requests where startdate < DATE_SUB(CURDATE(), INTERVAL ".$purge_unit_count." ".$purge_unit.")";
		try{
			$database->setQuery($sql);
			$result = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		

		echo json_encode($result);
		jExit();
	}

	function ajax_do_purge(){
		
		$jinput = JFactory::getApplication()->input;
		$purge_unit = $jinput->getWord( 'punit', '' );
		$purge_unit_count = $jinput->getInt( 'pcount', 1 );
		$result = null;
		
		$database =JFactory::getDBO(); 
		$sql = "SELECT id_requests FROM #__sv_apptpro3_requests WHERE startdate < DATE_SUB(CURDATE(), INTERVAL ".$purge_unit_count." ".$purge_unit.") ORDER BY id_requests";
		try{
			$database->setQuery($sql);
			$ids_to_purge = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_ajax", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR').$e->getMessage());
			jExit();
		}		
		
		// convert to array of ids
		$str_to_purge = "";
		foreach($ids_to_purge as $id_to_purge){
			$str_to_purge.=$id_to_purge->id_requests.",";		
		}
		$ary_to_purge = explode(",", rtrim($str_to_purge, ','));
				
		if(sv_count_($ary_to_purge) > 0){
			try{
				require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
				$model = new requests_detailModelrequests_detail;
				if($model == null){
					logIt("model = null", "be_ctrl_ajax", "", "");
					$result =  "model = null";
					echo json_encode($result);
					jExit();
				}
				if(!$model->delete($ary_to_purge)) {
					logIt($e->getMessage(), "be_ctrl_ajax", "", "");
					$result = $model->getError();
				} else {
					$result = JText::_('RS1_ADMIN_PURGE_COMPLETE');
				}
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_ctrl_ajax", "", "");
				echo json_encode($e->getMessage());
				jExit();
			}		
		}

		echo json_encode($result);
		jExit();
	}

	function ajax_get_table_columns(){
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		$jinput = JFactory::getApplication()->input;
		$tbl = $jinput->getString( 'tbl', '' );
		
		$database =JFactory::getDBO(); 
		try{
			$tablename = $database->getPrefix().$tbl;
			$database->setQuery("SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$tablename."'");
			$columns = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_controllers/ajax", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			jExit();
		}	
		$columnArrayString = "";
		foreach($columns as $column){
			$columnArrayString .= $column->COLUMN_NAME.",";		
		}		
		echo json_encode($columnArrayString);
		jExit();		
	}

	function ajax_export_table_update($cachable=false, $urlparams=false) {
		
		$jinput = JFactory::getApplication()->input;
		$type = $jinput->getString('type', "");
		$rowdata = $jinput->getString('rowdata', "");
		$aryRows = explode("|", $rowdata);
		
		$database = JFactory::getDBO(); 
		
		// CORE
		
		// out with old
		$sql = "DELETE FROM #__sv_apptpro3_export_columns WHERE export_column_type='core'";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_export_table_update", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		

		// in with new
		$export_order = 1;
		foreach($aryRows as $aryRow){
			$aryRowData = explode("~",$aryRow); // theTable+"~"+theField+"~"+theHeader;
			$sql = "INSERT INTO #__sv_apptpro3_export_columns (export_column_type, export_table, export_field, export_format, export_header, export_order) ".
			"VALUES(".
			"'core',".
			"'".$aryRowData[0]."',".
			"'".$aryRowData[1]."',";
			if($aryRowData[1] == "startdate"){
				$sql .= "'%c-%b-%Y',";
			}else if($aryRowData[1] == "starttime" || $aryRowData[1] == "endtime"){
				$sql .= "'%I:%i %p',";
			}else {
				$sql .= "'',";}
			$sql .= "'".$aryRowData[2]."',".
			$export_order.
			")";
			$export_order ++;
			try{
				$database->setQuery( $sql );
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ajax_export_table_update", "", "");
				echo json_encode(JText::_('RS1_SQL_ERROR'));
				jExit();
			}		
				
		}
		
		// UDFs
		$rowdata = $jinput->getString('udf_rowdata', "");
		$aryRows = explode("|", $rowdata);
				
		// out with old
		$sql = "DELETE FROM #__sv_apptpro3_export_columns WHERE export_column_type='udf'";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_export_table_update", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		

		if($rowdata != ""){
			// in with new
			$export_order = 1;
			foreach($aryRows as $aryRow){
				$aryRowData = explode("~",$aryRow); // theKey~theHeader;
				$sql = "INSERT INTO #__sv_apptpro3_export_columns (export_column_type, export_table, export_foreign_key, export_header, export_order) ".
				"VALUES(".
				"'udf',".
				"'sv_apptpro3_udfs',".
				$aryRowData[0].",".
				"'".$aryRowData[1]."',".
				$export_order.
				")";
				$export_order ++;
	
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($sql, "ajax_export_table_update", "", "");
					logIt($e->getMessage(), "ajax_export_table_update", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR'));
					jExit();
				}						
			}
		}
		// Extras
		$rowdata = $jinput->getString('extra_rowdata', "");
		$aryRows = explode("|", $rowdata);
				
		// out with old
		$sql = "DELETE FROM #__sv_apptpro3_export_columns WHERE export_column_type='extra'";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_export_table_update", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		

		if($rowdata != ""){
			// in with new
			$export_order = 1;
			foreach($aryRows as $aryRow){
				$aryRowData = explode("~",$aryRow); // theKey~theHeader;
				$sql = "INSERT INTO #__sv_apptpro3_export_columns (export_column_type, export_table, export_foreign_key, export_header, export_order) ".
				"VALUES(".
				"'extra',".
				"'sv_apptpro3_extras',".
				$aryRowData[0].",".
				"'".$aryRowData[1]."',".
				$export_order.
				")";
				$export_order ++;
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ajax_export_table_update", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR'));
					jExit();
				}						
			}
		}
		
		// Seats
		$rowdata = $jinput->getString('seat_rowdata', "");
		$aryRows = explode("|", $rowdata);
				
		// out with old
		$sql = "DELETE FROM #__sv_apptpro3_export_columns WHERE export_column_type='seat'";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ajax_export_table_update", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		

		if($rowdata != ""){
			// in with new
			$export_order = 1;
			foreach($aryRows as $aryRow){
				$aryRowData = explode("~",$aryRow); // theKey~theHeader;
				$sql = "INSERT INTO #__sv_apptpro3_export_columns (export_column_type, export_table, export_foreign_key, export_header, export_order) ".
				"VALUES(".
				"'seat',".
				"'sv_apptpro3_seats',".
				$aryRowData[0].",".
				"'".$aryRowData[1]."',".
				$export_order.
				")";
				$export_order ++;
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ajax_export_table_update", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR'));
					jExit();
				}						
			}
		}
		
		echo json_encode(JText::_('RS1_ADMIN_CONFIG_EXPORT_SAVE_OK'));
		jExit();
	}

}

?>

