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
		$display_picker_date_2co = $this->filter_2co_startdate;	
		if($display_picker_date_2co != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date_2co = date("Y-m-d", strtotime($this->filter_2co_startdate));
					break;
				case "dd-mm-yy":
					$display_picker_date_2co = date("d-m-Y", strtotime($this->filter_2co_startdate));
					break;
				case "mm-dd-yy":
					$display_picker_date_2co = date("m-d-Y", strtotime($this->filter_2co_startdate));
					break;
				default:	
					$display_picker_date_2co = date("Y-m-d", strtotime($this->filter_2co_startdate));
					break;
			}
		}
	
		$display_picker_date2_2co = $this->filter_2co_enddate;
		if($display_picker_date2_2co != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2_2co = date("Y-m-d", strtotime($this->filter_2co_enddate));
					break;
				case "dd-mm-yy":
					$display_picker_date2_2co = date("d-m-Y", strtotime($this->filter_2co_enddate));
					break;
				case "mm-dd-yy":
					$display_picker_date2_2co = date("m-d-Y", strtotime($this->filter_2co_enddate));
					break;
				default:	
					$display_picker_date2_2co = date("Y-m-d", strtotime($this->filter_2co_enddate));
					break;
			}
		}
?>
<script>
	jQuery(function() {
  		jQuery( "#display_picker_date_2co" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#_2costartdateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2_2co" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#_2coenddateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});
</script>
<?php echo JText::_('RS1_ADMIN_SCRN_TAB_2CO_FULL');?>
	  <table class="table table-striped" width="100%" >
        <tr>
          <td>
            <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_STAMP_DATEFILTER');?></div>
            <div class="controls">
               <input readonly="readonly" name="_2costartdateFilter" id="_2costartdateFilter" type="hidden" 
                  class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_2co_startdate; ?>" />
        
                <input type="text" readonly="readonly" id="display_picker_date_2co" name="display_picker_date_2co" class="sv_date_box" size="10" maxlength="10" 
                    value="<?php echo $display_picker_date_2co ?>" onchange="select2COStartDate(); return false;">          
                <br/>           
               <input readonly="readonly" name="_2coenddateFilter" id="_2coenddateFilter" type="hidden" 
                  class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_2co_enddate; ?>" />
        
                <input type="text" readonly="readonly" id="display_picker_date2_2co" name="display_picker_date2_2co" class="sv_date_box" size="10" maxlength="10" 
                    value="<?php echo $display_picker_date2_2co?>" onchange="select2COEndDate(); return false;">
            </div>
          </td>
        </tr>
        <tr>
            <td>
                <table width="100%"> 
                    <tr class="fe_admin_header">
                      <!--<th  class="svtitle" width="5%" align="center"><input type="checkbox" name="toggle9" value="" onclick="checkAll2(<?php echo sv_count_($this->items_2co); ?>, '_2co_cb',9);" /></th>-->
                      <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_2CO_ORDER_COL_HEAD'), 'order_number', $this->lists['order_Dir_2co'], $this->lists['order_2co'], "_2co_" ); ?></th>
                      <!--<th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_REQ_ID_COL_HEAD'), 'merchant_order_id', $this->lists['order_Dir_2co'], $this->lists['order_2co'], "_2co_" ); ?></th>-->
                      <!--<th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_BUYER_COL_HEAD'), 'last_name', $this->lists['order_Dir_2co'], $this->lists['order_2co'], "_2co_" ); ?></th>-->
                      <th class="svtitle" align="left"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_PAY_AMOUNT_COL_HEAD'), 'total', $this->lists['order_Dir_2co'], $this->lists['order_2co'], "_2co_" ); ?></th>
                      <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_SCRN_TIMESTAMP_COL_HEAD'), 'stamp', $this->lists['order_Dir_2co'], $this->lists['order_2co'], "_2co_" ); ?></th>
                    </tr>
                  </thead>
                    <?php
                    $k = 0;
                    for($i=0; $i < sv_count_($this->items_2co); $i++) {
                    $_2co_row = $this->items_2co[$i];
                    $link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=_2co_transactions_detail&cid='.$_2co_row->id__2co_transactions.'&frompage=advadmin&tab='.$tab);
                   ?>
                    <tr class="<?php echo "row$k"; ?>">
                      <!--<td align="center"><input type="checkbox" id="_2co_cb<?php echo $i;?>" name="cid_2co[]" value="<?php echo $_2co_row->id__2co_transactions; ?>" onclick="Joomla.isChecked(this.checked);" /></td>-->
                      <td align="center"><a href="<?php echo $link; ?>"><u><?php echo stripslashes($_2co_row->order_number); ?></u></a></td>
                      <!--<td align="center"><?php echo $_2co_row->merchant_order_id; ?>&nbsp;</td>-->
                      <!--<td align="left"><?php echo stripslashes($_2co_row->last_name.", ".$_2co_row->first_name); ?>&nbsp;</td>-->
                      <td align="left"><?php echo $_2co_row->total; ?>&nbsp;</td>
                      <td align="center"><?php echo $_2co_row->stamp; ?>&nbsp;</td>
                      <?php $k = 1 - $k; ?>
                    </tr>
                    <?php } ?>	
               </table>
            </td>
        </tr>
        </table>

	  <input type="hidden" name="_2co_filter_order" value="<?php echo $this->lists['order_2co'];?>" />
  	  <input type="hidden" name="_2co_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_2co'] ?>" />
  	  <input type="hidden" name="_2co_tab" id="_2co_tab"  value ="<?php echo $tab ?>" />
    
   
		
		