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

	if($single_category_mode){
		$andClause .= " AND id_categories = ". (int)$single_category_id;
	} else {
		$andClause .= " AND (parent_category IS NULL OR parent_category = '') ";
		if(!$user->guest){
			// logged in user, show categories based on groups	
			$sql = "SELECT group_id FROM #__user_usergroup_map WHERE ".
				"user_id=".$user->id;	
			try{		
				$database->setQuery($sql);
				$ary_my_groups = $database -> loadColumn();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "gad_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}	
			$andClause .= " AND (";
			for ($x=0; $x<sv_count_($ary_my_groups); $x++){
				$safe_search_string = '%|' . $database->escape( $ary_my_groups[$x], true ) . '|%' ;
				$andClause .= ' group_scope LIKE '.$database->quote( $safe_search_string, false );
				if($x < sv_count_($ary_my_groups)-1){
					$andClause .= " OR ";
				}
			}
			$andClause .= ")";	
		} else {
			// not logged in, show only public categories
			$andClause .= " AND  group_scope LIKE '%|1|%' ";
		}
		
	}	
	$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE published = 1 '.$andClause.'  order by ordering';
	//echo $sql;		
	try{
		$database->setQuery($sql);
		$res_cats = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// check for sub-categories
	$sql = 'SELECT count(*) as count FROM #__sv_apptpro3_categories WHERE published = 1 AND (parent_category IS NOT NULL AND parent_category != "") ';
	try{
		$database->setQuery($sql);
		$sub_cat_count = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gad_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		



?>
