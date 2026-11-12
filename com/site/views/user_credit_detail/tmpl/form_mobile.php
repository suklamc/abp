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




	$showform= true;
	$jinput = JFactory::getApplication()->input;
	$listpage = $jinput->getString('listpage', 'list');

	$id = $jinput->getString( 'id', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$option = $jinput->getString( 'option', '' );

	$credit_type = "uc";
//	$credit_type = $this->credit_type;
	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";

		// get config stuff
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_uc_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
	}
	
	if($credit_type == "uc"){
		if($this->detail->id_user_credit == ""){
			// get users 
			$sql = "SELECT id, name, username FROM #__users WHERE ".
			" id NOT IN (select user_id from #__sv_apptpro3_user_credit where credit_type = 'uc')";
			try{
				$database->setQuery($sql);
				$users_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "fe_uc_detail_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}
		} else {
			// get users 
			$sql = "SELECT id, name FROM #__users WHERE id = ".$this->detail->user_id;
			try{
				$database->setQuery($sql);
				$users_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "fe_uc_detail_tmpl_form", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}
		}
	}
	
	// get activity
	$activity_rows = null;
	if($this->detail->user_id != "" || $this->detail->gift_cert != ""){
		$sql = "SELECT #__sv_apptpro3_user_credit_activity.*, #__users.name as operator, #__sv_apptpro3_requests.startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ".
			"#__sv_apptpro3_resources.description as resource ".
			"FROM #__sv_apptpro3_user_credit_activity ".
			"  LEFT OUTER JOIN #__users ON #__sv_apptpro3_user_credit_activity.operator_id = #__users.id ".
			"  LEFT OUTER JOIN #__sv_apptpro3_requests ON #__sv_apptpro3_user_credit_activity.request_id = #__sv_apptpro3_requests.id_requests ".
			"  LEFT OUTER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			"WHERE ";
			if($credit_type == "uc"){
				$sql .= " #__sv_apptpro3_user_credit_activity.user_id = ".$this->detail->user_id.
				" AND (#__sv_apptpro3_user_credit_activity.gift_cert IS NULL OR #__sv_apptpro3_user_credit_activity.gift_cert = '')";
			} else {
				$sql .= " #__sv_apptpro3_user_credit_activity.gift_cert = '".$this->detail->gift_cert."'";
			}
			$sql .= " ORDER BY stamp desc";
		try{
			$database->setQuery($sql);
			$activity_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_uc_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
	}

	$search_link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=user_credit_detail&task=user_search&frompage=user_credit_detail&Itemid=-1'). " class=\"modal\" rel=\"{handler: 'iframe', size: {x: 500, y: 350}, onClose: function() {}}\" ";

?>
<?php if($showform){?>
<script language="javascript">

function setReqID(id){
	document.getElementById("xid").value = id;
	Joomla.submitbutton('edit_direct_from_credit');
	return false;
}

function search_postback(theuser){
	selected_user = theuser.split("|");
	document.getElementById("user_id").value = selected_user[0];
	if(document.getElementById("sel_user") != null){
		document.getElementById("sel_user").innerHTML = selected_user[1];	
	}
}

function doCancel(){
	Joomla.submitform("uc_cancel");
}		

function doClose(){
	Joomla.submitform("uc_close");
}		

function doSave(){
	if(document.getElementById('balance').value == ""){
		alert('<?php echo JText::_('RS1_ADMIN_SCRN_BALANCE_ERR');?>');
		return(false);
	}
	Joomla.submitform("save_user_credit_detail");
}

</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" class="sv_adminForm">
  <div id="sv_apptpro_fe_user_credit_detail">
    <?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
    <h3><?php echo JText::_('RS1_ADMIN_SCRN_USER_CREDIT_TITLE_MOBILE');?></h3>
    <table class="table table-striped" width="100%" >
      <tr>
        <td class="fe_header_bar">
        <div class="controls sv_yellow_bar" align="center">
            <?php if($this->lock_msg != ""){?>
            <?php echo $this->lock_msg?>
            <input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
            <?php } else { ?>
            <input type="button" id="saveLink" onclick="doSave();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_SAVE');?>">
            <input type="button" id="closeLink" onclick="doCancel();return(false);" value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CANCEL');?>">
            <?php } ?>
          </div></td>
      </tr>
    </table>
    <?php 
	if($credit_type == "uc"){
		echo JText::_('RS1_ADMIN_SCRN_CREDIT_DETAIL_INTRO')."<br />".JText::_('RS1_ADMIN_CREDIT_INTRO');
    } else {
		echo JText::_('RS1_ADMIN_GC_INTRO');
    }  
?>
    <table class="table table-striped" >
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_ID');?>: <?php echo $this->detail->user_id ?></div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_USER_NAME');?>:
            <?php if($this->detail->id_user_credit ==""){?>
            </div>
	          <div class="controls">
            <select name="user_id" id="user_id">
              <option value="-1"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_SEL_USER');?></option>
              <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($users_rows ); $i++) {
                        $users_row = $users_rows[$i];
                        ?>
              <option value="<?php echo $users_row->id; ?>"<?php if( $this->detail->user_id == $users_row->id){echo " selected='selected' ";} ?>><?php echo stripslashes($users_row->name)." (".$users_row->username.")"; ?></option>
              <?php $k = 1 - $k; 
                        } ?>
            </select>
            <?php } else { ?>
            <input type="hidden" id="user_id" name="user_id" value="<?php echo $this->detail->user_id; ?>" />
            &nbsp;&nbsp;<b><?php echo stripslashes($users_rows[0]->name); ?></b>
            <?php } ?>
          </div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo ($credit_type == "uc"?JText::_('RS1_ADMIN_SCRN_CREDIT_BALANCE') : JText::_('RS1_ADMIN_GIFT_CERT_AMOUNT'))?>:</div>
          <div class="controls">
            <div style="display: table-cell;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
            <div style="display: table-cell; padding-left:10px;">
              <input style="width:60px; text-align: center" type="text" size="8" maxsize="10" name="balance" id="balance" value="<?php echo stripslashes($this->detail->balance); ?>" />
            </div>
          </div></td>
      </tr>
      <tr>
        <td><div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_COMMENT');?>:</div>
          <div class="controls">
            <input type="text" maxsize="255" name="comment" id="comment" value="" />
          </div></td>
      </tr>
    </table>
    <input type="hidden" name="id_user_credit" value="<?php echo $this->detail->id_user_credit; ?>" />
    <input type="hidden" name="credit_type" value="uc" />
    <input type="hidden" name="option" value="<?php echo $option; ?>" />
    <input type="hidden" name="controller" value="admin_detail" />
    <input type="hidden" name="id" value="<?php echo $this->user_id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="user" id="user" value="<?php echo $user->id; ?>" />
    <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
    <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
    <input type="hidden" name="fromtab" value="<?php echo $this->fromtab ?>" />
    <br />
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span> </div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>
