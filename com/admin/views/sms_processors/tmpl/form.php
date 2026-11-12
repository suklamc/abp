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

$editor = JEditor::getInstance();
				 
	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$div_cal = "";
	if($apptpro_config->use_div_calendar == "Yes"){
		$div_cal = "'testdiv1'";
	}

	// get dialing codes
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_dialing_codes ORDER BY country" );
		$dial_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$tables = JFactory::getDbo()->getTableList();
	// get cb profile columns
	if(in_array($database->replacePrefix('#__comprofiler_fields'), $tables)){
		try{
			$database->setQuery("SELECT * FROM #__comprofiler_fields WHERE #__comprofiler_fields.table = '#__comprofiler' and (type='text' or type='predefined') ORDER BY name" );
			$cb_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}
	
	// get joomla profile columns
	// note bug in J3.1.1
	//	http://forum.joomla.org/viewtopic.php?f=706&t=802997
//	if(in_array($database->replacePrefix('#__user_profiles'), $tables)){
		try{
			$database->setQuery("SELECT DISTINCT profile_key FROM #__user_profiles ORDER BY ordering" );
			$profile_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
//	}
	
	// get js profile columns
	if(in_array($database->replacePrefix('#__community_fields'), $tables)){
		try{
			$database->setQuery("SELECT * FROM #__community_fields WHERE type!='group' ORDER BY name" );
			$js_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
	}

	?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white; z-index:99999"> </div>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">

<script language="JavaScript" src="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript">
	var now = new Date();
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setCssPrefix("TEST");
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
	
	
	function sms_conflict_check(){
		if(document.getElementById("sms_confirmation").value == "Yes"){		
			if(document.getElementById("sms_to_resource_only").value == "Yes"){
				alert("<?php echo JText::_('RS1_SMS_SETTING_CONFLICT');?>");
				document.getElementById("sms_confirmation").value = "No"
			}
		}
		if(document.getElementById("sms_to_resource_only").value == "Yes"){		
			if(document.getElementById("sms_confirmation").value == "Yes"){
				alert("<?php echo JText::_('RS1_SMS_SETTING_CONFLICT');?>");
				document.getElementById("sms_to_resource_only").value = "No"
			}
		}
	}
</script>
<?php echo JText::_('RS1_ADMIN_SMS_PROCESSORS_INTRO');?>
<hr />
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">

  <?php 
  
 	// get data for dropdowns
	$database = JFactory::getDBO();
	try{
		$database->setQuery("SELECT * FROM #__sv_apptpro3_pp_currency ORDER BY description" );
		$currency_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sms_proc_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	?>
    
    <ul class="nav nav-tabs">
        <li class="active"><a class="nav-link active" href="#panel1" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SMS_PROCESSORS_GENERAL_TAB');?></a></li>
        <li><a class="nav-link" href="#panel2" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SMS_PROCESSORS_CLICKATELL_TAB');?></a></li>
        <li><a class="nav-link" href="#panel3" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SMS_PROCESSORS_EXTEXTING_TAB');?></a></li>
        <li><a class="nav-link" href="#panel4" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_SMS_PROCESSORS_TWILIO_TAB');?></a></li>
    </ul>

	<div class="tab-content">
		<div id="panel1" class="tab-pane active">
        <table class="table table-striped" >
        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_SMS_CONFIRMATION');?>: </td>
          <td><select name="sms_confirmation" id="sms_confirmation" onchange="sms_conflict_check();">
              <option value="Yes" <?php if($this->detail->sms_confirmation == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($this->detail->sms_confirmation == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            </select></td> 
         <td width="50%"><?php echo JText::_('RS1_ADMIN_SMS_CONFIRMATION_HELP');?></td>         
        </tr>

        <tr >
          <td valign="top"><?php echo JText::_('RS1_ADMIN_SCRN_SMS_PHONE');?>: </td>
          <td valign="top"><div><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_PROFILE');?></div>
            <select name="sms_phone_profile_mapping" id="sms_phone_profile_mapping" >
              <option value=""><?php echo JText::_('RS1_ADMIN_SELECT_PROFILE_VALUE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($profile_rows ); $i++) {
				$profile_row = $profile_rows[$i];
				?>
              <option value="<?php echo $profile_row->profile_key; ?>" <?php if($this->detail->sms_phone_profile_mapping == $profile_row->profile_key){echo " selected='selected' ";} ?>><?php echo stripslashes($profile_row->profile_key); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select></td>
          <td valign="top"><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_PROFILE_HELP');?></td>
        </tr>
        <tr >
          <td valign="top"></td>
          <td valign="top"><div><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_CB');?></div>
            <select name="sms_phone_cb_mapping" id="sms_phone_cb_mapping" >
              <option value=""><?php echo JText::_('RS1_ADMIN_SELECT_CB_VALUE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($cb_rows ); $i++) {
				$cb_row = $cb_rows[$i];
				?>
              <option value="<?php echo $cb_row->name; ?>" <?php if($this->detail->sms_phone_cb_mapping == $cb_row->name){echo " selected='selected' ";} ?>><?php echo stripslashes($cb_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select></td>
          <td valign="top"><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_CB_HELP');?></td>
        </tr>
        <tr >
          <td valign="top"></td>
          <td valign="top"><div><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_JS');?></div>
            <select name="sms_phone_js_mapping" id="sms_phone_js_mapping" >
              <option value=""><?php echo JText::_('RS1_ADMIN_SELECT_JS_VALUE');?></option>
              <?php
				$k = 0;
				for($i=0; $i < sv_count_($js_rows ); $i++) {
				$js_row = $js_rows[$i];
				?>
              <option value="<?php echo $js_row->fieldcode; ?>" <?php if($this->detail->sms_phone_js_mapping == $js_row->fieldcode){echo " selected='selected' ";} ?>><?php echo stripslashes($js_row->name); ?></option>
              <?php $k = 1 - $k; 
				} ?>
            </select></td>
          <td valign="top"><?php echo JText::_('RS1_ADMIN_CONFIG_PHONE_JS_HELP');?></td>
        </tr>


        <tr >
          <td width="15%"><?php echo JText::_('RS1_ADMIN_CONFIG_SMS_RES_ONLY');?>: </td>
          <td><select name="sms_to_resource_only" id="sms_to_resource_only" onchange="sms_conflict_check();">
              <option value="Yes" <?php if($this->detail->sms_to_resource_only == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
              <option value="No" <?php if($this->detail->sms_to_resource_only == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            </select></td> 
         <td width="50%"><?php echo JText::_('RS1_ADMIN_CONFIG_SMS_RES_ONLY_HELP');?></td>         
        </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_DIAL_CODE');?>:</td>
                <td><select name="clickatell_dialing_code" >
                    <?php
                        $k = 0;
                        for($i=0; $i < sv_count_($dial_rows ); $i++) {
                        $dial_row = $dial_rows[$i];
                        ?>
                    <option value="<?php echo $dial_row->dial_code; ?>"  <?php if($this->detail->clickatell_dialing_code == $dial_row->dial_code){echo " selected='selected' ";} ?>><?php echo $dial_row->country." - ".$dial_row->dial_code ?></option>
                    <?php $k = 1 - $k; 
                        } ?>
                  </select>
                  &nbsp;</td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_DIAL_CODE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_SHOW_CODE');?>:</td>
                <td><select name="clickatell_show_code">
                    <option value="Yes" <?php if($this->detail->clickatell_show_code == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->clickatell_show_code == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp;</td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_SHOW_CODE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_SMS_PHONE_SIZE');?>: </td>
                <td><input type="number" size="20" maxsize="50" name="sms_phone_size" value="<?php echo trim($this->detail->sms_phone_size);?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_SMS_PHONE_SIZE_HELP');?></td>
              </tr>
      </table>
        </div>
        <div id="panel2" class="tab-pane">
        	<?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_INTRO');?>
            <table class="table table-striped" >
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_ENABLE');?>: </td>
                <td><select name="enable_clickatell">
                    <option value="Yes" <?php if($this->detail->enable_clickatell == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->enable_clickatell == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select></td>           
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_ENABLE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_USER');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="clickatell_user" value="<?php echo $this->detail->clickatell_user; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_USER_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_PWD');?>: </td>
                <td><input type="password" size="20" maxsize="50" name="clickatell_password" value="<?php echo trim(encrypt_decrypt('decrypt', $this->detail->clickatell_password)); ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_PWD_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_API_ID');?>: </td>
                <td><input type="text" size="15" maxsize="50" name="clickatell_api_id" value="<?php echo $this->detail->clickatell_api_id; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_API_ID_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_SENDER_ID');?>: </td>
                <td><input type="text" size="15" maxsize="50" name="clickatell_sender_id" value="<?php echo $this->detail->clickatell_sender_id; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_SENDER_ID_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_ENABLE_UNICODE');?>:</td>
                <td><select name="clickatell_enable_unicode">
                    <option value="Yes" <?php if($this->detail->clickatell_enable_unicode == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->clickatell_enable_unicode == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp;</td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_ENABLE_UNICODE_HELP');?></td>
              </tr>
              <tr >
                <td colspan="3">
                  <?php echo JText::_('RS1_ADMIN_CONFIG_CLICKATELL_FOOTER');?></td>
              </tr>
            </table>

        </div>
        <div id="panel3" class="tab-pane">
        	<?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_INTRO');?>
            <table class="table table-striped" >
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_ENABLE');?>: </td>
                <td><select name="enable_eztexting">
                    <option value="Yes" <?php if($this->detail->enable_eztexting == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->enable_eztexting == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp; 
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_ENABLE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_USER');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="eztexting_user" value="<?php echo $this->detail->eztexting_user; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_USER_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_PWD');?>: </td>
                <td><input type="password" size="20" maxsize="50" name="eztexting_password" value="<?php echo trim(encrypt_decrypt('decrypt', $this->detail->eztexting_password)); ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_PWD_HELP');?></td>
              </tr>
              <tr >
                <td colspan="3">
                  <?php echo JText::_('RS1_ADMIN_CONFIG_EZTEXTING_FOOTER');?></td>
              </tr>
            </table>

        </div>
        <div id="panel4" class="tab-pane">
            <?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_INTRO');?>
            <table class="table table-striped" >
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_ENABLE');?>: </td>
                <td><select name="enable_twilio">
                    <option value="Yes" <?php if($this->detail->enable_twilio == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->enable_twilio == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp; 
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_ENABLE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_SID');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="twilio_sid" value="<?php echo $this->detail->twilio_sid; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_SID_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_TOKEN');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="twilio_token" value="<?php echo $this->detail->twilio_token; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_TOKEN_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_PHONE');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="twilio_phone" value="<?php echo $this->detail->twilio_phone; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_TWILIO_PHONE_HELP');?></td>
              </tr>
            </table>

        </div>
	</div>

  <input type="hidden" name="id_config" value="<?php echo $this->detail->id_config; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="sms_processors" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
