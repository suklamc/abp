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
?>

<?php 
		$display_picker_datean_aim = $this->filter_an_aim_startdate;	
		if($display_picker_datean_aim != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_datean_aim = date("Y-m-d", strtotime($this->filter_an_aim_startdate));
					break;
				case "dd-mm-yy":
					$display_picker_datean_aim = date("d-m-Y", strtotime($this->filter_an_aim_startdate));
					break;
				case "mm-dd-yy":
					$display_picker_datean_aim = date("m-d-Y", strtotime($this->filter_an_aim_startdate));
					break;
				default:	
					$display_picker_datean_aim = date("Y-m-d", strtotime($this->filter_an_aim_startdate));
					break;
			}
		}
	
		$display_picker_date2an_aim = $this->filter_an_aim_enddate;
		if($display_picker_date2an_aim != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2an_aim = date("Y-m-d", strtotime($this->filter_an_aim_enddate));
					break;
				case "dd-mm-yy":
					$display_picker_date2an_aim = date("d-m-Y", strtotime($this->filter_an_aim_enddate));
					break;
				case "mm-dd-yy":
					$display_picker_date2an_aim = date("m-d-Y", strtotime($this->filter_an_aim_enddate));
					break;
				default:	
					$display_picker_date2an_aim = date("Y-m-d", strtotime($this->filter_an_aim_enddate));
					break;
			}
		}
?>
<script>
	jQuery(function() {
  		jQuery( "#display_picker_datean_aim" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#an_aimstartdateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2an_aim" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#an_aimenddateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});
</script>
        <table style="border-bottom:1px solid #666666;" width="100%">
            <tr>
              <th align="left" ><?php echo JText::_('RS1_ADMIN_SCRN_TAB_AUTHNET_FULL');?><br /></th>
                <th align="right"></th> 
          </tr>
        </table>
        <table cellpadding="4" cellspacing="0" border="0" class="adminlist" width="100%">
		<thead>
        <tr class="fe_admin_header">
          <td align="right"  style="font-size:11px; text-align:right" colspan="7">
            <?php echo JText::_('RS1_ADMIN_SCRN_STAMP_DATEFILTER');?>&nbsp;
            
           <input readonly="readonly" name="an_aimstartdateFilter" id="an_aimstartdateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_an_aim_startdate; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_datean_aim" name="display_picker_datean_aim" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_datean_aim ?>" onchange="selectANAIMStartDate(); return false;">          
            &nbsp;           
           <input readonly="readonly" name="an_aimenddateFilter" id="an_aimenddateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_an_aim_enddate; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date2an_aim" name="display_picker_date2an_aim" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date2an_aim?>" onchange="selectANAIMEndDate(); return false;">
           
             <!--<a href="#" onclick="ppcleardate();"><?php echo JText::_('RS1_ADMIN_SCRN_DATEFILTER_CLEAR');?></a>&nbsp;&nbsp;-->
          </td>
        </tr>
        <tr class="fe_admin_header">
          <!--<th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle6" value="" onclick="checkAll2(<?php echo sv_count_($this->items_pp); ?>, 'pp_cb',6);" /></th>-->
          <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PP_TXN_COL_HEAD'), 'x_trans_id', $this->lists['order_Dir_an_aim'], $this->lists['order_an_aim'], "an_aim_" ); ?></th>
          <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_REQ_ID_COL_HEAD'), 'x_invoice_num', $this->lists['order_Dir_an_aim'], $this->lists['order_an_aim'], "an_aim_" ); ?></th>
          <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_BUYER_COL_HEAD'), 'x_last_name', $this->lists['order_Dir_an_aim'], $this->lists['order_an_aim'], "an_aim_" ); ?></th>
          <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PAY_AMOUNT_COL_HEAD'), 'x_amount', $this->lists['order_Dir_an_aim'], $this->lists['order_an_aim'], "an_aim_" ); ?></th>
          <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_TIMESTAMP_COL_HEAD'), 'stamp', $this->lists['order_Dir_an_aim'], $this->lists['order_an_aim'], "an_aim_" ); ?></th>
        </tr>
      </thead>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($this->items_an_aim ); $i++) {
        $an_row = $this->items_an_aim[$i];
		$link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=authnet_aim_transactions_detail&cid='. $an_row->id_authnet_aim_transactions.'&frompage=advadmin&tab='.$tab);
       ?>
        <tr class="<?php echo "row$k"; ?>">
          <!--<td width="5%" align="center"><input type="checkbox" id="an_cb<?php echo $i;?>" name="cid_an[]" value="<?php echo $an_row->id_authnet_aim_transactions; ?>" onclick="Joomla.isChecked(this.checked);" /></td>-->
          <td width="5%" align="center"><a href="<?php echo $link; ?>"><?php echo stripslashes($an_row->x_trans_id); ?></a></td>
          <?php if(strpos($an_row->x_invoice_num, "cart|") === false ){?>
            <td width="20%" align="center"><?php echo $an_row->x_invoice_num; ?>&nbsp;</td>
		  <?php } else {?>
            <td width="20%" align="center"><?php echo "cart"; ?></td>
          <?php } ?>  
          <td width="20%" align="left"><?php echo stripslashes($an_row->x_last_name.", ".$an_row->x_first_name); ?>&nbsp;</td>
          <td width="20%" align="left"><?php echo $an_row->x_amount; ?>&nbsp;</td>
          <td width="20%" align="center"><?php echo $an_row->stamp; ?>&nbsp;</td>
          <?php $k = 1 - $k; ?>
        </tr>
        <?php } 
    
    ?>	
      </table>

	  <input type="hidden" name="an_aim_filter_order" value="<?php echo $this->lists['order_an_aim'];?>" />
  	  <input type="hidden" name="an_aim_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_an_aim'] ?>" />
  	  <input type="hidden" name="authnet_aim_tab" id="authnet_aim_tab"  value ="<?php echo $tab ?>" />
       
		
		