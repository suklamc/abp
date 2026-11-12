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

require_once ( JPATH_SITE."/administrator/components/com_rsappt_pro3/functions_pro2.php" );
require_once ( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );
require_once ( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );  // needed everywhere for sv_count_

defined('DS')?  null :define('DS',DIRECTORY_SEPARATOR);

if (substr(PHP_OS, 0, 3) == 'WIN') {
   define("WINDOWS", true);
} else {
   define("WINDOWS", false);
}

$lang = JFactory::getLanguage();
$langTag =  $lang->getTag();
if($langTag == ""){
	define('PICKER_LANG',"");
} else {
	define('PICKER_LANG',substr($langTag,0,2));
}

// in J!4 alpha5 option com_content sent here ??? 
$option = JFactory::getApplication()->input->get('option', '');
if($option != 'com_rsappt_pro3'){
	return;
}

//$jinput = JFactory::getApplication()->input;
//logIt(print_r($jinput), "jinput - rsappt_pro3.php", "", "");
 
$view = JFactory::getApplication()->input->get('view', '');
//logIt($view, "view - rsappt_pro3.php", "", "");

if($view != "" && $view != "featured"){ // J4 alpha 5 puts view a featured
	if($view == "advadmin"){
		$controller = "admin";
	} else if($view == "month" || $view == "day" || $view == "week" || $view == "category"){
		// term 'view' used by front_desk calendar (day/month/week)
		$controller = "ajax";
	} else if($view == "front_desk"){
		$controller = "front_desk";
	} else {
		$controller = $view; 
	}
} else {
	$controller = JFactory::getApplication()->input->get('controller', '');
}

//logIt($controller, "controller - rsappt_pro3.php", "", "");

if($controller == "" | $controller == "article"){
	$controller = $jinput->getWord('controller','' ); 
}

//set the controller page  
if (!file_exists(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php' )){
	echo "Invalid ABPro controller specified - ".$controller;
	exit;
}

require_once (JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php');

// Create the controller sv_sebController 
$classname  = $controller.'controller';

//create a new class of classname and set the default task:display
$controller = new $classname( array('default_task' => 'display') );

// Perform the Request task
$controller->execute( JFactory::getApplication()->input->get('task', ''));

// Redirect if set by the controller
$controller->redirect(); 

?>