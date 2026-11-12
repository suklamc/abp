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

	$credit_type = $this->credit_type;

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_uc_detail_tmpl_form", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
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
				logIt($e->getMessage(), "be_uc_detail_tmpl_form", "", "");
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
				logIt($e->getMessage(), "be_uc_detail_tmpl_form", "", "");
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
			logIt($e->getMessage(), "be_uc_detail_tmpl_form", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
	}

	$search_link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=user_credit_detail&task=user_search&frompage=user_credit_detail&Itemid=-1'). " class=\"modal\" rel=\"{handler: 'iframe', size: {x: 500, y: 350}, onClose: function() {}}\" ";

?>
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
<script language="javascript">
$(document).ready(function(){
  $("form").submit(function(){
	pressbutton = document.adminForm.task.value;  
   	if (pressbutton == 'save' || pressbutton == 'save2new'){
		<?php if($credit_type == "uc"){ ?>
			if(document.getElementById("user_id").value == "-1"){
				alert("<?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_USER_REQ');?>");
				return false;
			} else {
				Joomla.submitform(pressbutton);
			}
		<?php } else { ?>
			if(document.getElementById("gift_cert").value == ""){
				alert("<?php echo JText::_('RS1_ADMIN_GIFT_CERT_REQ');?>");
				return false;
			}
			if(document.getElementById("balance").value == "" || document.getElementById("balance").value == "0.00"){
				alert("<?php echo JText::_('RS1_ADMIN_GIFT_CERT_BAL_REQ');?>");
				return false;
			}
			Joomla.submitform(pressbutton);
		<?php } ?>
		
	} else {
		Joomla.submitform(pressbutton);
	}		
  });
});

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

</script>
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<?php 
	if($credit_type == "uc"){
		echo JText::_('RS1_ADMIN_SCRN_CREDIT_DETAIL_INTRO')."<br />".JText::_('RS1_ADMIN_CREDIT_INTRO');
    } else {
		echo JText::_('RS1_ADMIN_GC_INTRO');
    }  
?>	
  <table class="table table-striped" >
    <tr>
      <td width="15%"><?php echo JText::_('ID');?>:</td>
      <td><?php echo $this->detail->id_user_credit ?></td>
      <td width="50%">&nbsp;</td>
    </tr>
    <?php if($credit_type == "uc"){ ?>
        <tr>
          <td><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_ID');?>:</td>
          <td align="left"><?php echo $this->detail->user_id ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="left"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_USER_NAME');?>:</td>
          <?php if($this->detail->id_user_credit ==""){?>
          <td valign="top" align="left">
            <?php if(sv_count_($users_rows) < 100){ ?>
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
                    <label id="sel_user"></label><input type="hidden" id="user_id" name="user_id"/>
              <?php } ?>  
                &nbsp;<a href=<?php echo $search_link?>><?php echo JText::_('RS1_INPUT_SCRN_NAME_SEARCH');?></a><label id="user_fetch"  class="sv_apptpro_errors">&nbsp;</label> 
          </td>
            <td><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_SEL_USER_HELP');?>&nbsp;</td>
           <?php } else { ?>
            <td colspan="2">
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $this->detail->user_id; ?>" /><?php echo stripslashes($users_rows[0]->name); ?>
            </td>
           <?php } ?>
        </tr>
	<?php } else { ?>
        <tr>
          <td valign="top" align="left"><?php echo JText::_('RS1_ADMIN_GIFT_CERT');?>:</td>
          <td valign="top"><input type="text" maxsize="255" name="gift_cert" id="gift_cert" value="<?php echo $this->detail->gift_cert; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GIFT_CERT_HELP');?>&nbsp;</td>
        </tr>
        <tr>
          <td valign="top" align="left"><?php echo JText::_('RS1_ADMIN_GIFT_CERT_NAME');?>:</td>
          <td valign="top"><input type="text" maxsize="255" name="gift_cert_name" id="gift_cert_name" value="<?php echo $this->detail->gift_cert_name; ?>" /></td>
          <td><?php echo JText::_('RS1_ADMIN_GIFT_CERT_NAME_HELP');?>&nbsp;</td>
        </tr>
    <?php } ?>	
    <tr>
      <td valign="top" align="left"><?php echo ($credit_type == "uc"?JText::_('RS1_ADMIN_SCRN_CREDIT_BALANCE') : JText::_('RS1_ADMIN_GIFT_CERT_AMOUNT'))?>:</td>
      <td valign="top"><div style="display: table-cell;"><?php echo JText::_('RS1_INPUT_SCRN_CURRENCY_SYMBOL');?></div>
      <div style="display: table-cell; padding-left:10px;"><input style="width:60px; text-align: center" type="text" size="8" maxsize="10" name="balance" id="balance" value="<?php echo stripslashes($this->detail->balance); ?>" /></div></td>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_BALANCE_HELP');?>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top" align="left"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_COMMENT');?>:</td>
      <td valign="top"><input type="text" maxsize="255" name="comment" id="comment" value="" /></td>
      <td>&nbsp;</td>
    </tr>
	<?php if($credit_type == "gc"){ ?>
<!--    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_TIMESLOT_DETAIL_PUBLISHED');?></td>
        <td>
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
        <td>&nbsp;</td>
    </tr>
-->    <?php } ?>
  </table><br /><br />
  <hr />
  <?php if($credit_type == "uc"){ 
		echo JText::_('RS1_ADMIN_SCRN_CREDIT_ACTIVITY_INTRO')."<br />";
  } else {
		echo JText::_('RS1_ADMIN_GIFT_CERT_ACTIVITY_INTRO')."<br />";
  }?>
  <table class="table table-striped" >
	<thead>
    <tr>
      <th width="5%" class="title" align="center"><?php echo JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'); ?></th>
      <th class="title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_COMMENT_COL_HEAD'); ?></th>
      <th class="title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BOOKING_COL_HEAD'); ?></th>
      <th class="title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_INCREASE_COL_HEAD'); ?></th>
      <th class="title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_DECREASE_COL_HEAD'); ?></th>
      <th class="title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BALANCE_COL_HEAD'); ?></th>
      <th width="5%" class="title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_OPERATOR_COL_HEAD'); ?></th>
      <th class="title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_TIMESTAMP_COL_HEAD'); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($activity_rows ); $i++) {
		$activity_row = $activity_rows[$i];
		if($activity_row->display_startdate == ""){
			$link = "\"javascript: alert('".JText::_('RS1_ADMIN_SCRN_CREDIT_BOOKING_DELETED')."')\"";
		} else {
			$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=requests_detail&task=edit&cid[]='. $activity_row->request_id."&frompage=UC&frompage_item=".$this->detail->id_user_credit."&type=".$credit_type );
		}
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="center"><?php echo $activity_row->id; ?>&nbsp;</td>
      <td align="left"><?php echo stripslashes($activity_row->comment); ?>&nbsp;</td>
      <?php if($activity_row->request_id != ""){ ?>
<!--      <td align="center">(<?php echo $activity_row->request_id; ?>)&nbsp;-->
      <td align="center">(<a href=<?php echo $link; ?>><?php echo $activity_row->request_id; ?>)</a>&nbsp;
	  <?php echo $activity_row->display_startdate."/".$activity_row->display_starttime; ?>&nbsp;- <?php echo stripslashes($activity_row->resource); ?></td>
      <?php } else { ?>
      <td align="center">&nbsp;</td>
      <?php } ?>
      <td align="center"><?php echo $activity_row->increase; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->decrease; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->balance; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->operator; ?>&nbsp;</td>
      <td align="center"><?php echo $activity_row->stamp; ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 
?>
  </table>

</fieldset>
  <input type="hidden" name="id_user_credit" value="<?php echo $this->detail->id_user_credit; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="credit_type" value="<?php echo $credit_type; ?>" />
  <input type="hidden" name="controller" value="user_credit_detail" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
