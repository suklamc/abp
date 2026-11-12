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

defined('_JEXEC') or die('Restricted access');

## initialize the editor



$editor = JFactory::getEditor();
$edit_params = array( 'html_height'=> '200' );

	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "advadmin_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// Get resources for dropdown list
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_resources WHERE timeslots != 'Global' ORDER BY name" );
		$res_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	

?>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<script>
	function doCancel(){
		Joomla.submitform("cancel");
	}		

	function doSave(){	
		Joomla.submitform("save");
	}
</script>
<?php echo JText::_('RS1_ADMIN_CONFIG_MSG_INTRO');?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" >
  <div id="sv_apptpro_mybookings">
    <table class="table table-striped" >
      <tr>
        <td style="text-align:right" height="40px"  class="fe_header_bar"><a href="#" onclick="doSave();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?></a> &nbsp;|&nbsp;&nbsp;<a href="#" onclick="doCancel();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?></a>&nbsp;&nbsp;</td>
      </tr>
    </table>
    <table class="table table-striped" >
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_MAIL_ID');?>:</div>
          <div class="controls"><?php echo $this->detail->id_mail ?> </div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MAIL_LABEL');?>:</div>
          <div class="controls"><?php echo $this->detail->mail_label ?> <?php echo JText::_('RS1_ADMIN_CONFIG_MAIL_LABEL_HELP');?> </div></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_COMPLETE');?>:</div>
          <div class="controls"><?php echo $editor->display( 'booking_succeeded',  $this->detail->booking_succeeded , '100%', '250', '75', '20', false , null, null, null, $edit_params) ;?> 
		  <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_COMPLETE_HELP');?> </div></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_COMPLETE_ADMIN');?>:</div>
        <div class="controls"><?php echo $editor->display( 'booking_succeeded_admin',  $this->detail->booking_succeeded_admin , '100%', '250', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_COMPLETE_ADMIN_HELP');?></div></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_CONF_ATTACHMENT');?>:</div>
        <div class="controls"><input type="text" style="width:95%" name="confirmation_attachment" value="<?php echo stripslashes($this->detail->confirmation_attachment); ?>"/>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_CONF_ATTACHMENT_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_BOOKING_COMPLETE');?>:</div>
        <div class="controls"><textarea style="width:95%" name="booking_succeeded_sms" rows="3" cols="70"><?php echo stripslashes($this->detail->booking_succeeded_sms); ?></textarea>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_BOOKING_COMPLETE_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_INPROGRESS');?>:</div>
        <div class="controls"><?php echo $editor->display( 'booking_in_progress',  $this->detail->booking_in_progress , '100%', '250', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_INPROGRESS_HELP');?></div></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_INPROGRESS_ADMIN');?>:</div>
        <div class="controls"><?php echo $editor->display( 'booking_in_progress_admin',  $this->detail->booking_in_progress_admin , '100%', '250', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BOOKING_INPROGRESS_ADMIN_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_BOOKING_INPROGRESS');?>:</div>
        <div class="controls"><textarea style="width:95%" name="booking_in_progress_sms" rows="3" cols="70"><?php echo stripslashes($this->detail->booking_in_progress_sms); ?></textarea>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_BOOKING_INPROGRESS_HELP');?></div></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_CANCELLATION');?>: </div>
        <div class="controls"><?php echo $editor->display( 'booking_cancel',  $this->detail->booking_cancel , '100%', '250', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_CANCELLATION_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_CANCELLATION');?>:</div>
        <div class="controls"><textarea style="width:95%" name="booking_cancel_sms" rows="3" cols="70"><?php echo stripslashes($this->detail->booking_cancel_sms); ?></textarea>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_CANCELLATION_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOO_LATE');?>:</div>
        <div class="controls"><?php echo $editor->display( 'booking_too_close_to_cancel',  $this->detail->booking_too_close_to_cancel , '100%', '150', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOO_LATE_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_REMINDER');?>:</div>
        <div class="controls"><?php echo $editor->display( 'booking_reminder',  $this->detail->booking_reminder , '100%', '250', '75', '20', false ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_REMINDER_HELP');?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_REMINDER');?>:</div>
        <div class="controls"><textarea style="width:95%" name="booking_reminder_sms" rows="3" cols="70"><?php echo stripslashes($this->detail->booking_reminder_sms); ?></textarea>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_SMS_REMINDER_HELP');?></div></td>
      </tr>
      <?php if($this->detail->mail_label == "Global"){ // birthday messasges are not resource related so only set it in Global ?>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BIRTHDAY');?>:</div>
        <div class="controls"><?php echo $editor->display( 'birthday_msg',  $this->detail->birthday_msg , '100%', '250', '75', '20' ) ;?>
        <?php echo JText::_('RS1_ADMIN_CONFIG_MSG_BIRTHDAY_HELP');?></div></td>
      </tr>
      <?php } ?>
      <tr>
        <td><hr /></td>
      </tr>
      <tr >
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_INTRO');?></div>
        <table  border="0" cellpadding="4">
            <tr>
              <td><strong><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN');?></strong></td>
              <td><strong><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_REPLACE');?></strong></td>
            </tr>
            <tr>
              <td>[resource]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_1');?></td>
              </tr><tr>
              <td>[resource_category]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_2');?></td>
            </tr>
            <tr>
              <td>[requester name]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_3');?></td>
              </tr><tr>
              <td>[resource_service]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_4');?></td>
            </tr>
            <tr>
              <td>[startdate]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_5');?></td>
              </tr><tr>
              <td>[phone]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_6');?></td>
            </tr>
            <tr>
              <td>[starttime]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_7');?></td>
              </tr><tr>
              <td>[email]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_8');?></td>
            </tr>
            <tr>
              <td>[enddate]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_9');?></td>
              </tr><tr>
              <td>[cancellation_id]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_10');?></td>
            </tr>
            <tr>
              <td>[endtime]</td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_11');?></td>
              </tr><tr>
              <td>[booking_total]</td><td></td>
            </tr>
            <tr>
              <td>[booked_seats]</td><td></td>
              </tr><tr>
              <td>[booking_due]</td><td></td>
            </tr>
            <tr>
              <td>[coupon]</td><td></td>
              </tr><tr>
              <td>[booking_id]</td><td></td>
            </tr>
            <tr>
              <td>[today]</td><td></td>
              </tr><tr>
              <td>[booking_deposit]</td><td></td>
            </tr>
            <tr>
              <td>[admin_comment]</td><td></td>
            </tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_1');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_2');?></td>
            </tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_3');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_4');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_5');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_UDF_6');?></td>
            </tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_1');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_2');?></td>
            </tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_3');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_4');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_5');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_EXTRAS_6');?></td>
            </tr>
            <tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_1');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_2');?></td>
            </tr>
            <tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_3');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_4');?></td>
              </tr><tr>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_5');?></td>
              <td><?php echo JText::_('RS1_ADMIN_CONFIG_MSG_TOKEN_SEATS_6');?></td>
            </tr>
          </table>
         </td>
      </tr>
    </table>
  </div>
  <input type = "hidden" name="id_mail" value="<?php echo $this->detail->id_mail; ?>" />
  <input type = "hidden" name="task" value="" />
  <input type = "hidden" name="controller" value="mail_detail" />
  <input type = "hidden" name="published" value="<?php echo $this->detail->published; ?>" />
  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
