<?php
/*
 ****************************************************************
 Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 * @license	GNU/GPL, see http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
 

// No direct access.
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );



class plgContentAbpro_content_link extends JPlugin {

	function onContentPrepare( $context, &$article, &$params, $limitstart ){
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		// simple performance check to determine whether bot should process further
		if ( strpos( $article->text, 'abpro_link' ) === false ) {
				return true;
		}
		$abpro_version = "3";
		$link_text = $this->params->get('default_text', '');
		$view = $this->params->get('which_screen', '');
		$mode = $this->params->get('mode', '');
		$sing_id = $this->params->get('sing_id', '');
		$startdate = $this->params->get('startdate', '');
		$starttime = $this->params->get('starttime', '');
		$endtime = $this->params->get('endtime', '');
		$popup = $this->params->get('popup', 'No');
		$popup_x = $this->params->get('popup_x', '750');
		$popup_y = $this->params->get('popup_y', '650');
		$link_class = $this->params->get('link_class', '');
		$link_image = $this->params->get('link_image', '');

		$layout = $this->params->get('layout', '');
		$ac_1 = $this->params->get('accord_1');
		$ac_2 = $this->params->get('accord_2');
		$ac_3 = $this->params->get('accord_3');
		$ac_4 = $this->params->get('accord_4');
		$ac_5 = $this->params->get('accord_5');
		$ac_6 = $this->params->get('accord_6');
		$ac_7 = $this->params->get('accord_7');
		$ac_1w = $this->params->get('accord_1w');
		$ac_2w = $this->params->get('accord_2w');
		$ac_3w = $this->params->get('accord_3w');
		$ac_4w = $this->params->get('accord_4w');

		// Some of the above can be overridden at runtime with parameters
		// link_text = [your text here]
		// view = simple | gad | wizard
		// mode = single_resource | single_category | single_service
		// id = id
		// date = YYYY-MM-DD
		// starttime = HH:MM  // 24 hr only
		// endtime = HH:MM  // 24 hr only
		// popup = Yes | No
		// popup_x = number
		// popup_y = number
		 
		$query_string = ""; 
		// if no params skip param parse
		if ( strpos( $article->text, '{abpro_link' ) != false ) {
			// tag present 
			// first get all the abpro_links on the page
			$regex = '{abpro_link.*}';
			preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
			//print_r($matches);
			if ($matches) {
				foreach ($matches as $match) {									
					// reset to plug-in params
					$link_text = $this->params->get('default_text', '');
					$view = $this->params->get('which_screen', '');
					$mode = $this->params->get('mode', '');
					$sing_id = $this->params->get('sing_id', '');
					$startdate = $this->params->get('startdate', '');
					$starttime = $this->params->get('starttime', '');
					$endtime = $this->params->get('endtime', '');
					$popup = $this->params->get('popup', 'No');
					$popup_x = $this->params->get('popup_x', '750');
					$popup_y = $this->params->get('popup_y', '650');
					$link_class = $this->params->get('link_class', '');
					$link_image = $this->params->get('link_image', '');

					// safer, look for specific name value pairs
					$regex2 = '/link_text\s?=\s?".*?"|link_class\s?=\s?".*?"|link_image\s?=\s?".*?"'.
					'|date\s?=\s?"\d\d\d\d-\d\d-\d\d"|view\s?=\s?".*?"|mode\s?=\s?".*?"'.
					'|id\s?=\s?".*?"|starttime\s?=\s?"\d\d:\d\d"|endtime\s?=\s?".*?"'.
					'|popup\s?=\s?"Yes"|popup\s?=\s?"No"|popup_x\s?=\s?"\d*?"|popup_y\s?=\s?"\d*?"/';
					preg_match_all($regex2, $match[0], $namevaluepairs);
					foreach ($namevaluepairs as $namevaluepair) {
						if(count($namevaluepair) > 0){
							for($i=0; $i<count($namevaluepair); $i++){
								//echo $namevaluepair[$i];
								$parts = explode("=", $namevaluepair[$i]);
								//echo trim($parts[0])." - ".trim($parts[1]);				
								switch(trim($parts[0])){
									case "link_text":
										$link_text = str_replace("\"","",$parts[1]);
										//echo "link_text=".$link_text;
										break;
									case "view":
										switch(str_replace("\"","",$parts[1])){
											case "gad":
												$view = "booking_screen_gad";
												break;
											case "simple":
												$view = "booking_screen_simple";
												break;
											case "wizard":
												$view = "bookingscreengadwiz";
												break;	
										}
										break;
									case "mode":
										$temp = str_replace("\"","",$parts[1]);
										switch($temp){
											case "single_resource":
												$mode = "sing_res";
												break;
											case "single_category":
												$mode = "sing_cat";
												break;
											case "single_service":
												$mode = "sing_srv";
												break;
										}
										break;
									case "id":
										$sing_id = str_replace("\"","",$parts[1]);
										break;
									case "date":
										$startdate = str_replace("\"","",$parts[1]);
										break;
									case "starttime":
										$starttime = str_replace("\"","",$parts[1]);
										break;
									case "endtime":
										$endtime = str_replace("\"","",$parts[1]);
										break;
									case "popup":
										$popup = strtolower(str_replace("\"","",$parts[1]));
										break;
									case "popup_x":
										$popup_x = str_replace("\"","",$parts[1]);
										break;
									case "popup_y":
										$popup_y = str_replace("\"","",$parts[1]);
										break;
									case "link_class":
										$link_class = str_replace("\"","",$parts[1]);
										break;
									case "link_image":
										$link_image = str_replace("\"","",$parts[1]);
										break;									 
								}
							}
						}
					}			

					$menu = JFactory::getApplication()->getMenu(); 
					$active = $menu->getActive(); 
					$menu_id = $active->id;
					
					$query_string = "<a ";
					if($link_class != ""){
						$query_string .= "class='".trim($link_class)."' ";
					}
					$query_string .= " href='".JURI::base()."index.php?option=com_rsappt_pro".$abpro_version."&view=".$view."&Itemid=".$menu_id;
//					$query_string .= " href='index.php?option=com_rsappt_pro".$abpro_version."&view=".$view."&Itemid=".$menu_id;
					switch ($mode) {
						case "sing_res":
							if($sing_id != ""){
								$query_string .= "&res=".$sing_id;
							}
							break;
						case "sing_cat":
							if($sing_id != ""){
								$query_string .= "&cat=".$sing_id;
							}
							break;
						case "sing_srv":
							if($sing_id != ""){
								$query_string .= "&srv=".$sing_id;
							}
							break;
					}			
					if($startdate != ""){
						$query_string .= "&mystartdate=".$startdate;
					}
					if($starttime != ""){
						// remove leading 0 if there
						if (substr($starttime,0,1)=="0") $starttime = substr($starttime,1);
						$query_string .= "&mygridstarttime=".$starttime;
					}
					$query_string .= "&layout=".$layout;
					if($layout != 0 ){ 
							$query_string .= "&ac_1=".$ac_1;
							$query_string .= "&ac_2=".$ac_2;
							$query_string .= "&ac_3=".$ac_3;
							$query_string .= "&ac_4=".$ac_4;;
							$query_string .= "&ac_5=".$ac_5;
							$query_string .= "&ac_6=".$ac_6;
							$query_string .= "&ac_7=".$ac_7;
							$query_string .= "&ac_1w=".$ac_1w;
							$query_string .= "&ac_2w=".$ac_2w;
							$query_string .= "&ac_3w=".$ac_3w;
							$query_string .= "&ac_4w=".$ac_4w;
					 } 
					
					if($endtime != ""){
						if (substr($endtime,0,1)=="0") $endtime = substr($endtime,1);
						$query_string .= "&mygridendtime=".$endtime;
					}
// popup not supported in Joomla 4, needs to be re-writted to use jQuery					
//					if($popup == "yes"){
//						$appWeb = JFactory::getApplication();
//						if($appWeb->client->mobile){
//							$userAgent = $appWeb->client->userAgent;							
//							if(strpos($userAgent, "iPad") !== false){
//								$popup_x = 600;
//								$popup_y = 700;		
//							} else {
//								$popup_x = 300;
//								$popup_y = 400;	
//							}						
//						}
//						$query_string .= "&tmpl=component' rel='{handler: \"iframe\", size: {x: ".$popup_x.", y: ".$popup_y."}, onClose: function() {}}' class='modal";
//					}
				
					$query_string .= "'>";			
					if($link_image != ""){
						$query_string .= "<img src='".$link_image."'>";			
					} else {
						$query_string .= $link_text;			
					}
					$query_string .= "</a>";

					$link = str_replace("&amp;", "&", $query_string);
					//echo "to match: {".$match[0];
					//echo $link;
					$article->text = str_replace("{".$match[0], $link, $article->text);
					
				}
			}					
		
		}
		
	}
}
