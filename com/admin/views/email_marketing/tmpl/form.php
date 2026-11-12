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

	// get MailChimp lists
	$aryLists = null;
	if($this->detail->mailchimp_api_key != ""){
		include_once( JPATH_SITE."/components/com_rsappt_pro3/inc/MailChimp.php" );
		$MailChimp = new \Drewm\MailChimp($this->detail->mailchimp_api_key);
		$params = array(
      		'sort_field' => 'name',
            'sort_dir' => 'asc');				
		$aryLists = $MailChimp->call('lists/list', $params);
		//print_r($aryLists);		
	} else {
		$aryLists = array("total"=>1, "data"=>array(array("id"=>"-1","name"=>"None Loaded")));
	}

	// get AcyMailing lists	
	$acyLists = null;
	if(file_exists(JPATH_ADMINISTRATOR . '/components/com_acymailing/acymailing.php') && JComponentHelper::isEnabled('com_acymailing', true)){
		if(include_once(rtrim(JPATH_ADMINISTRATOR,DS).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
			$listClass = acymailing_get('class.list');
			$acyLists = $listClass->getLists();	
			//print_r($acyLists);
		 }
    }	
	
	?>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">

    <ul class="nav nav-tabs">
        <li class="active"><a class="nav-link active" href="#panel1" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_EMAIL_MARKETING_GENERAL_TAB');?></a></li>
        <li><a class="nav-link" href="#panel2" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_EMAIL_MARKETING_MAILCHIMP_TAB');?></a></li>
        <li><a class="nav-link" href="#panel3" data-toggle="tab"><?php echo JText::_('RS1_ADMIN_EMAIL_MARKETING_ACYMAILING_TAB');?></a></li>
    </ul>

	<div class="tab-content">
		<div id="panel1" class="tab-pane active" style="min-height:300px">
        <?php echo JText::_('RS1_ADMIN_EMAIL_MARKETING_INTRO');?>
        </div>
        <div id="panel2" class="tab-pane">
        	<p><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_INTRO');?></p>
            
            <table class="table table-striped" >
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_ENABLE');?>: </td>
                <td><select name="mailchimp_enable">
                    <option value="Yes" <?php if($this->detail->mailchimp_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->mailchimp_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select></td>           
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_ENABLE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_API_KEY');?>: </td>
                <td><input type="text" size="20" maxsize="50" name="mailchimp_api_key" value="<?php echo $this->detail->mailchimp_api_key; ?>" /></td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_API_KEY_HELP');?></td>
              </tr>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_SPLIT_NAME');?>: </td>
                <td><select name="mailchimp_split_name">
                    <option value="Yes" <?php if($this->detail->mailchimp_split_name == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->mailchimp_split_name == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select></td>           
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_SPLIT_NAME_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_DEFAULT_LIST');?>: </td>
                <td><select name="mailchimp_default_list_id">
                <?php 
				foreach($aryLists["data"] as $List){ ?>			
					<option value="<?php echo $List["id"];?>"<?php if($this->detail->mailchimp_default_list_id == $List["id"]){echo " selected='selected' ";} ?>><?php echo $List["name"];?></option>
                <?php } ?>          
                  </select></td>   
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_DEFAULT_LIST_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_SEND_WELCOME');?>:</td>
                <td><select name="mailchimp_send_welcome">
                    <option value=1 <?php if($this->detail->mailchimp_send_welcome == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value=0 <?php if($this->detail->mailchimp_send_welcome == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp;</td>
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_MAILCHIMP_SEND_WELCOME_HELP');?></td>
              </tr>
            </table>

        </div>
        <div id="panel3" class="tab-pane">
        	<p><?php echo JText::_('RS1_ADMIN_CONFIG_ACYMAILING_INTRO');?></p>
            
            <table class="table table-striped" >
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_ACYMAILING_ENABLE');?>: </td>
                <td><select name="acymailing_enable">
                    <option value="Yes" <?php if($this->detail->acymailing_enable == "Yes"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
                    <option value="No" <?php if($this->detail->acymailing_enable == "No"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
                  </select>
                  &nbsp;&nbsp; 
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_ACYMAILING_ENABLE_HELP');?></td>
              </tr>
              <tr >
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_ACYMAILING_DEFAULT_LIST');?>: </td>
                <td><select name="acymailing_default_list_id">
                <?php 
					foreach($acyLists as $List){ ?>			
						<option value="<?php echo $List->listid;?>"<?php if($this->detail->acymailing_default_list_id == $List->listid){echo " selected='selected' ";} ?>><?php echo $List->name;?></option>
                <?php } ?>          
                  </select></td>   
                <td><?php echo JText::_('RS1_ADMIN_CONFIG_ACYMAILING_DEFAULT_LIST_HELP');?></td>
              </tr>
            </table>

        </div>
        
	</div>

  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="email_marketing" />
  <input type="hidden" name="email_marketing_id" value="1" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
