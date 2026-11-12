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
	$showform= true;	 

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

	if(!$user->guest){
		$ary_mail_ids = null;
		$sql = "SELECT DISTINCT mail_id FROM #__sv_apptpro3_resources ".
			" WHERE resource_admins LIKE '%|".$user->id."|%'";
		try{
			$database->setQuery( $sql );
			$ary_mail_ids = $database -> loadColumn();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_msg_ctr_def_tmpl", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;;
		}
	
		$pub = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_available_image)."' border='0'>";
		$unpub = "<img alt=\"\" src='".getImageSrc($apptpro_config->gad_booked_image)."' border='0'>";

		include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_security_check.php";


	} else{
		echo "<font color='red'>".JText::_('RS1_ADMIN_SCRN_NO_LOGIN')."</font>";
		$showform = false;
	}

?>

<script language="javascript" type="text/javascript">
	function myonsubmit(){
		task = document.adminForm.task.value;
		var form = document.adminForm;
		if ((task=='add')||(task=='edit')||(task=='publish')||(task=='unpublish')||(task=='remove')||(task=='new_from_global') ){
			form.controller.value="mail_detail";
		} else {
			form.controller.value="mail";		
		}
		return true;	
	}

	function doPublish(id){
		if(id != undefined){			
			document.getElementById('cb'+id).checked = true;
		}
		Joomla.submitbutton('publish');
		return false;		
	}

	function doUnPublish(id){
		if(id != undefined){			
			document.getElementById('cb'+id).checked = true;
		}
		Joomla.submitbutton('unpublish');
		return false;		
	}

</script>
<link href="<?php echo JURI::root( true );?>/administrator/components/com_rsappt_pro3/abpro_admin.css" rel="stylesheet">

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" onsubmit="myonsubmit();">
<?php if($showform){?>
<div id="sv_apptpro_fe_cpanel">
<?php echo JText::_('RS1_ADMIN_MAIL_LIST');?>
<p><?php echo JText::_('RS1_ADMIN_MAIL_INTRO');?></p>
    <table class="table table-striped" >
        <thead>
        <tr>
          <th width="3%"><!--<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />--></th>
          <th width="5%" class="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_ID_COL_HEAD'), 'id_mail', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th class="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_MAIL_LABEL'), 'mail_label', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th class="center" width="5%" nowrap="nowrap"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PUBLISHED_COL_HEAD'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
        </tr>
        </thead>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items); $i++) {
            $row = $this->items[$i];
            if (in_array($row->id_mail, $ary_mail_ids)) {
                $published 	= JHTML::_('grid.published', $row, $i );
                if($row->published==1){
                    $published 	= "<a href='#' OnClick='javascript:doUnPublish(".$i.");return false;'>".$pub."</a>";
                } else {
                    $published 	= "<a href='#' OnClick='javascript:doPublish(".$i.");return false;'>".$unpub."</a>";
                }	
                
                $link 	= JRoute::_( 'index.php?option=com_rsappt_pro3&view=mail_detail&task=edit&cid[]='. $row->id_mail );
                $checked 	= JHTML::_('grid.checkedout', $row, $i, 'id_mail');
               ?>
                <tr class="<?php echo "row$k"; ?>">
                  <td class="center"><?php if ($row->secured == 1) { ?>&nbsp;</td>
                  <?php } else { ?>	  
                  <input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id_mail; ?>" onclick="Joomla.isChecked(this.checked);" /></td> <?php }?>
                  <td class="center"><?php echo $row->id_mail; ?>&nbsp;</td>
                  <td class="left"><a href=<?php echo $link; ?>><?php echo  $row->mail_label; ?></a></td>
                  <td class="center"><?php echo $published;?></td>
                  <?php $k = 1 - $k; ?>
                </tr>
    <?php }  // end  if (in_array($row->id_mail, $ary_mail_ids)) {
        } 
    
    ?>
      </table>

      <input type="hidden" id="controller" name="controller" value="mail" />
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="hidemainmenu" value="0" />  
      <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    
  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
</div>
<?php } ?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
