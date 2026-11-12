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


/* Modified to work with Joomla and MySQLi */

class mysql2json{


 function getJSON($resultSet,$affectedRecords){
 $numberRows=0;
 $arrfieldName=array();
 $i=0;
 $json="";


	while ($i <$resultSet->field_count)  {
 		$meta = $resultSet->fetch_field();
		$arrfieldName[$i]=$meta->name;
		$i++;
 	}
	 $i=0;
	  $json="{\n\"data\": [\n";
	while ($i <$resultSet->num_rows)  {
		$row = $resultSet->fetch_row();	
		$i++;
		//print("Ind ".$i."-$affectedRecords<br>");
		$json.="{\n";
		for($r=0;$r < sv_count_($arrfieldName);$r++) {
			$json.="\"$arrfieldName[$r]\" :	\"$row[$r]\"";
			if($r < sv_count_($arrfieldName)-1){
				$json.=",\n";
			}else{
				$json.="\n";
			}
		}
		
		 if($i!=$affectedRecords){
		 	$json.="\n},\n";
		 }else{
		 	$json.="\n}\n";
		 }
	}
	$json.="]\n}";
	
	return $json;
 }

}
?>
