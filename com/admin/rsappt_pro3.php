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

JHTML::_('behavior.keepalive');

/* Joomla 4 (beta) admin template does not render limit boxes correcly
   - this CSS may be able to be removed if they fix the admin tempalte */
JHtml::_('stylesheet', JUri::root() . '/administrator/components/com_rsappt_pro3/abpro_admin.css');  


defined('DS')?  null :define('DS',DIRECTORY_SEPARATOR);

$lang = JFactory::getLanguage();
$langTag =  $lang->getTag();
if($langTag == ""){
	define('PICKER_LANG',"");
} else {
	define('PICKER_LANG',substr($langTag,0,2));
}

// Access check: is this user allowed to access the backend of this component?
if (!JFactory::getUser()->authorise('core.manage', 'com_rsappt_pro3')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('rsappt_pro3Helper', JPATH_COMPONENT . '/helpers/rsappt_pro3.php');


//if no controller then default controller = 'cpanel'
$jinput = JFactory::getApplication()->input;
$controller = $jinput->getString('controller','cpanel' ); 

//set the controller page  
require_once (JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php');

// Create the controller sv_sebController 
$classname  = $controller.'controller';

//create a new class of classname and set the default task:display
$controller = new $classname( array('default_task' => 'display') );

// Perform the Request task
$controller->execute( $jinput->getString('task' ));


// Redirect if set by the controller
$controller->redirect(); 

?>