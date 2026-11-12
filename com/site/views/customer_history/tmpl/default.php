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

	// require the html view class
	jimport( 'joomla.application.helper' );

	JHtml::_('jquery.framework');


	$jinput = JFactory::getApplication()->input;
	$option = $jinput->getString( 'option', '' );
	$itemid = $jinput->getString( 'Itemid', '' );
	$listpage = $jinput->getString('listpage', 'list');

	$user = JFactory::getUser();
	if($user->guest){
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	} else {

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";
	}
	
	$session = JSession::getInstance($handler=null, $options=null);
	if($session->get("status_filter") != "" ){
		$filter = $session->get("status_filter");
		$session->set("status_filter", "");
	} else {
		$filter = $jinput->getString( 'status_filter', '' );
	}
	
	if($session->get("startdateFilter") != "" ){
		$startdateFilter = $session->get("startdateFilter");
		$session->set("startdateFilter", "");
	} else {
		$startdateFilter = $jinput->getString( 'startdateFilter', date("Y-m-d"));
	}

	if($session->get("enddateFilter") != "" ){
		$enddateFilter = $session->get("enddateFilter");
		$session->set("enddateFilter", "");
	} else {
		$enddateFilter = $jinput->getString( 'enddateFilter', '' );
	}

	if($session->get("filter_user") != "" ){
		$filter_user = $session->get("filter_user");
		$session->set("filter_user", "");
	} else {
		$filter_user = $jinput->getString( 'filter_user', '-1' );
	}

	if($session->get("user_email") != "" ){
		$user_email = $session->get("user_email");
		$session->set("user_email", "");
	} else {
		$user_email = $jinput->getString( 'user_email', '' );
	}

	

// Load configuration data
	//include( JPATH_SITE . "/administrator/components/com_rsappt_pro3/config.rsappt_pro.php" );
	//include( JPATH_SITE."/administrator/components/com_rsappt_pro3/config.rsappt_pro3.php" );
//	require_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	//global $my;  
	$user = JFactory::getUser();

	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get users
//	$sql = 'SELECT id,name FROM #__users order by name';
	$sql = "SELECT DISTINCT user_id, #__users.* FROM #__sv_apptpro3_requests ".
		" JOIN #__users on #__users.id = #__sv_apptpro3_requests.user_id ".
		" WHERE resource IN( SELECT id_resources FROM #__sv_apptpro3_resources WHERE resource_admins LIKE '%|".$user->id."|%') ".
		" AND user_id != 0 ".
		" ORDER BY #__users.name;";
	try{
		$database->setQuery($sql);
		$user_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if($session->get("filter_order") != "" ){
		$filter_order = $session->get("filter_order");
		$session->set("filter_order", "");
	} else {
		$filter_order = $jinput->getString( 'filter_order', 'startdatetime' );
	}
	$ordering = $filter_order;

	if($session->get("filter_order_Dir") != "" ){
		$filter_order_Dir = $session->get("filter_order_Dir");
		$session->set("filter_order_Dir", "");
	} else {
		$filter_order_Dir = $jinput->getString( 'filter_order_Dir', 'asc' );
	}
	$direction = $filter_order_Dir;

	 
	if(!$user->guest){

		$database = JFactory::getDBO();
		
	$lang = JFactory::getLanguage();
	$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";	
	try{	
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

		// find requests
		$sql = "SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_admins, ".
			"#__sv_apptpro3_resources.name as resname, ".
			//"#__sv_apptpro3_services.name as srvname, ".
			"CONCAT(#__sv_apptpro3_requests.startdate,#__sv_apptpro3_requests.starttime) as startdatetime, ".
			" IF(CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) > Now(),'no','yes') as expired, ";
			if($apptpro_config->timeFormat == "12"){
				$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%l:%i %p') as display_starttime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%l:%i %p') as display_endtime ";
			} else {
				$sql = $sql." DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%a %b %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%k:%i') as display_starttime, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.enddate, '%b %e, %Y') as display_enddate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.endtime, '%k:%i') as display_endtime ";
			}
			$sql = $sql." FROM #__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				"ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			//" INNER JOIN #__sv_apptpro3_services ".
			//" ON #__sv_apptpro3_requests.service = #__sv_apptpro3_services.id_services ".	
			"WHERE request_status!='deleted' AND ";
			$sql = $sql."#__sv_apptpro3_resources.resource_admins LIKE '%|".$user->id."|%' AND ";
			if($filter != ""){
				$sql = $sql." request_status='".$database->escape($filter)."' AND ";
			}
			if($startdateFilter != ""){
				$sql = $sql." startdate>='".$database->escape($startdateFilter)."' AND ";
			}
			if($enddateFilter != ""){
				$sql = $sql." enddate<='".$database->escape($enddateFilter)."' AND ";
			}
			$sql = $sql."(";
			if($user_email != ""){
				$sql = $sql."#__sv_apptpro3_requests.email = '".$database->escape($user_email)."' ";
			}
			if($filter_user != ""){
				if($user_email != ""){
					$sql = $sql." OR ";
				}
				$sql = $sql."#__sv_apptpro3_requests.user_id = ".(int)$filter_user;
			}
			$sql = $sql.")";
//			" AND CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) >= NOW() ".
		$sql = $sql." ORDER BY ".$database->escape($ordering).' '.$database->escape($direction);
		try{	
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// check for credit activity
		if($user->id != ""){
			$sql = "SELECT #__sv_apptpro3_user_credit_activity.*, #__users.name as operator, #__sv_apptpro3_requests.startdate, ";
			if($apptpro_config->timeFormat == "12"){
				$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%l:%i %p') as display_starttime, ";
			} else {
				$sql .= "DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ";				
			}
			$sql .= "#__sv_apptpro3_resources.description as resource ".
				"FROM #__sv_apptpro3_user_credit_activity ".
				"  INNER JOIN #__users ON #__sv_apptpro3_user_credit_activity.operator_id = #__users.id ".
				"  LEFT OUTER JOIN #__sv_apptpro3_requests ON #__sv_apptpro3_user_credit_activity.request_id = #__sv_apptpro3_requests.id_requests ".
				"  LEFT OUTER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
				"WHERE #__sv_apptpro3_user_credit_activity.user_id = ".$filter_user." ORDER BY id desc ";
			try{
				$database->setQuery($sql);
				$activity_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
		}
		
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		$div_cal = "";
		if($apptpro_config->use_div_calendar == "Yes"){
			$div_cal = "'testdiv1'";
		}

		// get statuses
		$sql = "SELECT * FROM #__sv_apptpro3_status WHERE internal_value!='deleted' ORDER BY ordering ";
		try{
			$database->setQuery($sql);
			$statuses = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "cust_hist_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		// was planning to use pdf but Joomla pdf is useless with tables
		$pdflink = JRoute::_( 'index.php?option=com_rsappt_pro3&view=front_desk')."&task=customer_history&frompage=front_desk".
		"&filter_user=".$filter_user.
		"&startdateFilter=".$startdateFilter.
		"&enddateFilter=".$enddateFilter.
		"&statusFilter=".$filter.
		"&user_email=".$user_email.
		"&filter_order=".$filter_order.
		"&filter_order_Dir=".$filter_order_Dir.
		"&format=prt";


		$display_name = "";
		if($filter_user != "-1"){
			$history_user = JFactory::getUser( $filter_user );
			$display_name =  $history_user->name;
		}
		if($user_email !="") {			
			$display_name .= " (".$user_email.")";
		}

	} else{
		echo "<font color='red'>".JText::_('RS1_MYBOOKINGS_SCRN_NO_LOGIN')."</font>";
	}
?>
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<div id="testdiv1" style="VISIBILITY: hidden; POSITION: absolute; BACKGROUND-COLOR: white; layer-background-color: white"> </div>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>
<link href="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/calStyles.css" rel="stylesheet">
<script language="JavaScript" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/CalendarPopup.js"></script>
<script language="JavaScript">
	var cal = new CalendarPopup( <?php echo $div_cal ?>);
	cal.setWeekStartDay(<?php echo $apptpro_config->popup_week_start_day ?>);
	// to set css for popup calendar uncomment next line and change calStyles.css
	cal.setCssPrefix("TEST");
	
</script>
<script language="javascript">
	function doFilter(){
		document.adminForm.submit();
	}
	
	function doCancel(){
		document.getElementById("task").value="cancel"
		document.adminForm.submit();		
	}		


	function cleardate(){
		document.getElementById("startdateFilter").value="";
		document.getElementById("enddateFilter").value="";
		reSubmit();
		return false;		
	}
	
	function reSubmit(){
		document.getElementById("task").value="customer_history"
		document.getElementById("filter_user").value=document.getElementById("users").value;
		document.adminForm.submit();
		return false;		
	}
	
</script>

<form action="<?php echo $this->request_url ?>" method="post" name="adminForm" id="adminForm">
<div id="sv_apptpro_request_gad">
    <table width="97%" align="center">
        <tr>
          <td align="left" colspan="3"> <h3><?php echo JText::_('RS1_FRONTDESK_HISTORY_SCRN_TITLE');?>  - <?php echo $display_name;?></h3></td>
          <td align="right"><?php echo $user->name ?></td>
        </tr>
        <tr>
        <td colspan="3" class="sv_apptpro_request_select_user_label">
	        <table >
            <tr><td colspan="2"><?php echo JText::_('RS1_INPUT_SCRN_SELECT_CUSTOMER');?> </td></tr>
            <tr>
          	  <td><?php echo JText::_('RS1_INPUT_SCRN_SELECT_CUSTOMER_BY_USERID');?></td><td><select name="users" id="users" class="sv_apptpro_request_dropdown" onchange="reSubmit();">
                <option value="-1"><?php echo JText::_('RS1_INPUT_SCRN_SELECT_CUSTOMER_BY_USERID_DROPDOWN');?></option>
                <?php
                $k = 0;
                for($i=0; $i < sv_count_($user_rows ); $i++) {
                $user_row = $user_rows[$i];
                ?>
                    <option value="<?php echo $user_row->id; ?>"<?php echo ($user_row->id == $filter_user?' selected ':'')?> ><?php echo $user_row->name; ?></option>
                    <?php $k = 1 - $k; 
                } ?>
                  </select></td></tr>
			<tr><td><?php echo JText::_('RS1_INPUT_SCRN_SELECT_CUSTOMER_BY_EMAIL');?></td><td><input id="user_email" name="user_email" size="40" value="<?php echo $user_email; ?>"/>
            &nbsp;<input type="button" onclick="reSubmit();" class="sv_apptpro3_request_text" value="<?php echo JText::_('RS1_FRONTDESK_SCRN_SEARCH');?>" /></td>
            </tr>
            <tr><td colspan="2"><?php echo JText::_('RS1_FRONTDESK_HISTORY_SCRN_INFO');?></td></tr>
            <tr><td></td><td><label id="user_fetch"  class="sv_apptpro_errors"></label></td></tr>
            </table>
          </td>
          <td align="right" class="sv_apptpro_request_select_user_label"> <input type="button" class="btncancel"  name="cancel" id="btncancel" onclick="return doCancel();" 
            value="<?php echo JText::_('RS1_ADMIN_SCRN_BTN_CLOSE');?>" /> <p style="padding-top:20px"><a href="<?php echo $pdflink; ?>" target="_blank" title="<?php echo JText::_('RS1_FRONTDESK_HISTORY_PRINT_TIP');?>"><?php echo JText::_('RS1_FRONTDESK_HISTORY_PRINT');?></a></p>
            </td>
        </tr>
    <tr><td colspan="4"><hr /></td></tr>
        <tr>
          <td align="left" colspan="2"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER');?>&nbsp;
			<input type="text" class="sv_date_box" size="10" maxsize="10" name="startdateFilter" id="startdateFilter" readonly="readonly" style="font-size:11px"
            value="<?php echo $startdateFilter; ?>" onchange="reSubmit();"/>
            <a href="#" id="anchor1" onclick="cal.select(document.forms['adminForm'].startdateFilter,'anchor1','yyyy-MM-dd'); return false;"
                         name="anchor1"><img height="15" hspace="2" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
			&nbsp;
			<input type="text" class="sv_date_box" size="10" maxsize="10" name="enddateFilter" id="enddateFilter" readonly="readonly" style="font-size:11px"
            value="<?php echo $enddateFilter; ?>" onchange="reSubmit();"/>
            <a href="#" id="anchor2" onclick="cal.select(document.forms['adminForm'].enddateFilter,'anchor2','yyyy-MM-dd'); return false;"
                         name="anchor2"><img height="15" hspace="2" src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal.gif" width="16" border="0"></a>
            <a href="#" onclick="cleardate();return false;"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER_CLEAR');?></a>&nbsp;&nbsp;
            </td>
          	<td align="right" colspan="2"><select name="status_filter" onchange="doFilter()" style="font-size:11px">
            <option value=""><?php echo JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NONE');?></option>
			<?php foreach($statuses as $status_row){ ?>
                <option value="<?php echo $status_row->internal_value ?>" <?php if($filter == $status_row->internal_value){echo " selected='selected' ";} ?>><?php echo JText::_($status_row->status);?></option>        
            <?php } ?>
            </select>
          </td>
        </tr>
        <tr>
          <td align="left"><div id="cancel_results"></div></td>
        </tr>
    </table>
  <table cellpadding="4" cellspacing="0" border="0" width="97%" align="center" class="adminlist">
    <tr class="adminheading"  bgcolor="#F4F4F4">
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_ID_COL_HEAD'), 'id_requests', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_NAME_COL_HEAD'), 'name', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_EMAIL_COL_HEAD'), 'email', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_RESID_COL_HEAD'), 'resname', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_DATE_COL_HEAD'), 'startdatetime', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_TIME_COL_HEAD'), 'starttime', $direction, $ordering); ?></th>
      <th class="sv_title" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_HISTORY_SCRN_LOGGEDIN_COL_HEAD'), 'endtime', $direction, $ordering); ?></th>
      <th class="sv_title" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_STATUS_COL_HEAD'), 'request_status', $direction, $ordering); ?></th>
    </tr>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($rows ); $i++) {
	$row = $rows[$i];
	// return too difficult
	//$link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=edit&cid[]='. $row->id_requests.'&frompage=frontdesk');
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="left"><?php echo $row->id_requests; ?></td>
      <td align="left"><?php echo $row->name; ?></td>
      <td align="left"><?php echo $row->email; ?></td>
      <td align="left"><?php echo JText::_(stripslashes($row->resname)); ?>&nbsp;</td>
      <td align="left"><?php echo $row->display_startdate; ?></td>
      <td align="left"><?php echo $row->display_starttime; ?>&nbsp;-&nbsp;<?php echo $row->display_endtime; ?></td>
      <td align="center"><?php echo ($row->user_id != "0"?"Yes":""); ?> </td>
      <td align="center"><?php echo translated_status($row->request_status); ?></td>
      <?php $k = 1 - $k; ?>
    </tr>
    <?php } 

?>
  </table>

<?php if(sv_count_($activity_rows)>0){ ?>

<br /><br /><br />

  
  <table width="97%" align="center" class="adminlist">
	<thead>
    <tr><td colspan="7"><hr /><br /></td></tr>
    <tr><th colspan="7"><?php echo JText::_('RS1_ADMIN_SCRN_CREDIT_ACTIVITY_INTRO_HISTORY');?><br /></th><tr>
    <tr>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_COMMENT_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BOOKING_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_INCREASE_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_DECREASE_COL_HEAD'); ?></th>
      <th class="sv_title" align="center"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_BALANCE_COL_HEAD'); ?></th>
      <th width="5%" class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_OPERATOR_COL_HEAD'); ?></th>
      <th class="sv_title" align="left"><?php echo JText::_('RS1_ADMIN_CREDIT_ACTIVITY_TIMESTAMP_COL_HEAD'); ?></th>
    </tr>
    </thead>
    <?php
	$k = 0;
	for($i=0; $i < sv_count_($activity_rows ); $i++) {
	$activity_row = $activity_rows[$i];
   ?>
    <tr class="<?php echo "row$k"; ?>">
      <td align="left"><?php echo stripslashes($activity_row->comment); ?>&nbsp;</td>
      <?php if($activity_row->request_id != ""){ ?>
      <td align="left"><?php echo $activity_row->display_startdate." / ".$activity_row->display_starttime; ?>&nbsp;- <?php echo JText::_(stripslashes($activity_row->resource)); ?></td>
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
    <?php } ?>
  </table>
<?php } ?>
  <p></p><p class="row0"><?php 
  if($apptpro_config->allow_cancellation == "Yes" OR $apptpro_config->allow_cancellation == "BEO" ){
  	//echo JText::_('RS1_MYBOOKINGS_SCRN_CANCEL_HOWTO');
	}?></p>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" id="task" name="task" value="customer_history" />
  <input type="hidden" name="view" id="view" value="front_desk" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="cancellation_id" id="cancellation_id"  />
  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" name="filter_order" value="<?php echo $ordering ?>" />
  <input type="hidden" name="filter_order_Dir" value ="<?php echo $direction ?>" />
  <input type="hidden" id="filter_user" name="filter_user" value="<?php echo $filter_user ?>" />
	<input type="hidden" name="alt_cal_pos" id="alt_cal_pos" value="<?php echo $apptpro_config->cal_position_method; ?>" />
   <input type="hidden" name="frompage" value="<?php echo $listpage ?>" />
  <input type="hidden" name="frompage_item" value="<?php echo $itemid ?>" />
 <br />
</div>
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
<?php echo JHtml::_('form.token'); ?>
</form>
