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


//Ordering allowed ?
$ordering = ($this->lists['order'] == 'ordering');
	$user = JFactory::getUser();
	 
	if(!$user->guest){

		$database = JFactory::getDBO();
		
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// check to see id user is an admin		
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE '%|".$user->id."|%';";
		try{	
			$database->setQuery($sql);
			$check = NULL;
			$check = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_tmpl_default_prt", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($check->count == 0){
			echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NOT_ADMIN')."</font>";
		}	

	} else{
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
	}

?>
<link href="<?php echo $this->baseurl;?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<div id="sv_apptpro_print">
     <table width="100%">
        <tr>
          <td colspan="2" align="left"> <h3><?php echo JText::_('RS1_ADMIN_SCRN_TITLE');?></h3></td>
          <td width="20%" align="right"><?php echo $user->name ?></td>
        </tr>
    </table> 
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
    <tr bgcolor="#F4F4F4" >
      <th class="sv_title" width="3%">&nbsp;</th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_NAME_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_EMAIL_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_RESID_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_FROM_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_SERVICE_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_PAYMENT_COL_HEAD'); ?></th>
    </tr> 
	<?php
	$k = 0;
	for($i=0; $i < sv_count_($this->items ); $i++) {
	$row = $this->items[$i];

	?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" /></td>
      <td align="center"><?php echo $row->id_requests; ?></td>
      <td><?php echo stripslashes($row->name); ?></td>
      <td align="left"><?php echo $row->email; ?></td>
      <td align="left"><?php echo JText::_(stripslashes($row->ResourceName)); ?>&nbsp;</td>
      <td align="left"><?php echo $row->display_startdate; ?>&nbsp;<?php echo $row->display_starttime; ?> </td>
      <td align="left"><?php echo JText::_(stripslashes($row->ServiceName)); ?> </td>
      <td align="center"><?php echo translated_status($row->request_status); ?></td>
      <td align="center"><?php echo translated_status($row->payment_status).($row->invoice_number != ""?"<br/>(".$row->invoice_number.")":"") ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
	</table>

</div>
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
<?php echo JHtml::_('form.token'); ?>
</form>
<hr />
<p align="center">
  <input type="button"  onClick="window.print()"  value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_PRINT'); ?>"/>&nbsp;
  <input type="button"  onClick="window.close()"  value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE'); ?>"/>
  </p>
