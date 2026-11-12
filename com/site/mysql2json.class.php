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


class mysql2json{

 function getJSON($resultSet,$affectedRecords){
 $numberRows=0;
 $arrfieldName=array();
 $i=0;
 $json="";
	//print("Test");
 	while ($i < mysql_num_fields($resultSet))  {
 		$meta = mysql_fetch_field($resultSet, $i);
		if (!$meta) {
		}else{
		$arrfieldName[$i]=$meta->name;
		}
		$i++;
 	}
	 $i=0;
	  $json="{\n\"data\": [\n";
	while($row=mysql_fetch_array($resultSet, MYSQL_NUM)) {
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

