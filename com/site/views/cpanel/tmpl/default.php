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

//DEVNOTE: import html tooltips


	$showform = true;
	$user = JFactory::getUser();

	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_cpanel_view", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else{
		$sql = "SELECT id_resources FROM #__sv_apptpro3_resources ".
		"WHERE resource_admins LIKE '%|".$user->id."|%' ".
		"ORDER BY ordering;";
		try{
			$database->setQuery($sql);
			$res_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_cpanel_view", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}	
		$new_resource = false;	
		if($apptpro_config->enable_auto_resource == "Yes"){
			if(sv_count_($res_rows) == 0 && $apptpro_config->enable_auto_resource == "Yes"){
				// enable_auto_resource = Yes so we will create a resource for this user and make them admin
				if(!auto_resource($user, $apptpro_config->auto_resource_groups, $apptpro_config->auto_resource_category )){
					logIt("Auto User failed on ".$user->name, "fe_cpanel_view", "", "");
				} else {
					echo JText::_('RS1_NEW_AUTO_RESOURCE_CREATED').$user->name;
					echo "<br/>".JText::_('RS1_NEW_AUTO_RESOURCE_INTRO');					
					echo "<br/>".JText::_('RS1_NEW_AUTO_RESOURCE_INTRO2');					
					$showform = true;
					$new_resource = true; 
				}
			}
		}
		if(sv_count_($res_rows) == 0 && $new_resource == false){
				echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
				$showform = false;
		}
		
	}
	
?>

<form action="index2.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<?php if($showform){?>
    <table width="95%" border="0" cellspacing="0" cellpadding="5" align="center" >
        <tr>
          <td colspan="2" style="vertical-align:top" ><h3><?php echo JText::_('RS1_CPANEL_TITLE');?></h3></td>
        </tr>
    <!--	<tr>
            <td valign="bottom" width="16%" style="border: solid 1px #ECECEC" align="center"><a href="index.php?option=com_rsappt_pro3&amp;view=admin"><img src="<?php echo JURI::base()."/administrator/components/com_rsappt_pro3/images/bookings.png"?>" /></a><br /><a href="index.php?option=com_rsappt_pro3&amp;view=admin"><?php echo JText::_('RS1_CPANEL_FRONT_DESK');?></a></td>
            <td><?php echo JText::_('RS1_CPANEL_FRONT_DESK_HELP');?></td>
        </tr>
    -->	<tr>
            <td width="16%" style="border: solid 1px #ECECEC" align="center"><a href="index.php?option=com_rsappt_pro3&amp;view=advadmin"><img src="<?php echo JURI::base()."/administrator/components/com_rsappt_pro3/images/configure.jpg"?>" /></a><br /><a href="index.php?option=com_rsappt_pro3&amp;view=advadmin"><?php echo JText::_('RS1_CPANEL_ADV_ADMIN');?></a></td>
            <td valign="top"><?php echo JText::_('RS1_CPANEL_ADV_ADMIN_HELP');?></td>
        </tr>
        <tr>
            <td style="border: solid 1px #ECECEC" align="center"><a href="index.php?option=com_rsappt_pro3&amp;controller=mail"><img src="<?php echo JURI::base()."/administrator/components/com_rsappt_pro3/images/email.png"?>" /></a><br /><a href="index.php?option=com_rsappt_pro3&amp;controller=mail"><?php echo JText::_('RS1_CPANEL_MAIL');?></a></td>
            <td valign="top"><?php echo JText::_('RS1_CPANEL_MAIL_HELP');?></td>
        </tr>
    </table>
<?php 
} // end of if showform
?>
<hr>
  <br />

  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
</form>
