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
		$display_picker_datestripe = $this->filter_stripe_startdate;	
		if($display_picker_datestripe != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_datestripe = date("Y-m-d", strtotime($this->filter_stripe_startdate));
					break;
				case "dd-mm-yy":
					$display_picker_datestripe = date("d-m-Y", strtotime($this->filter_stripe_startdate));
					break;
				case "mm-dd-yy":
					$display_picker_datestripe = date("m-d-Y", strtotime($this->filter_stripe_startdate));
					break;
				default:	
					$display_picker_datestripe = date("Y-m-d", strtotime($this->filter_stripe_startdate));
					break;
			}
		}
	
		$display_picker_date2stripe = $this->filter_stripe_enddate;
		if($display_picker_date2stripe != ""){
			switch ($apptpro_config->date_picker_format) {
				case "yy-mm-dd":
					$display_picker_date2stripe = date("Y-m-d", strtotime($this->filter_stripe_enddate));
					break;
				case "dd-mm-yy":
					$display_picker_date2stripe = date("d-m-Y", strtotime($this->filter_stripe_enddate));
					break;
				case "mm-dd-yy":
					$display_picker_date2stripe = date("m-d-Y", strtotime($this->filter_stripe_enddate));
					break;
				default:	
					$display_picker_date2stripe = date("Y-m-d", strtotime($this->filter_stripe_enddate));
					break;
			}
		}
?>
<script>
	jQuery(function() {
  		jQuery( "#display_picker_datestripe" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#stripestartdateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
  		jQuery( "#display_picker_date2stripe" ).datepicker({
			showOn: "button",
			autoSize: true,
	 		dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
			firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
			buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
			buttonImageOnly: true,
			buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>",
			altField: "#stripeenddateFilter",
			altFormat: "yy-mm-dd" //DO NOT CHANGE 			
    	});
	});
</script>
<?php echo JText::_('RS1_ADMIN_SCRN_TAB_STRIPE_FULL');?>
	  <table class="table table-striped" width="100%" >
        <tr>
          <td>
          <div class="control-label"><?php echo JText::_('RS1_ADMIN_SCRN_STAMP_DATEFILTER');?></div>
          <div class="controls">
           <input readonly="readonly" name="stripestartdateFilter" id="stripestartdateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_stripe_startdate; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_datestripe" name="display_picker_datestripe" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_datestripe ?>" onchange="selectSTRIPEStartDate(); return false;">     
          <br/>
           <input readonly="readonly" name="stripeenddateFilter" id="stripeenddateFilter" type="hidden" 
              class="sv_date_box" size="10" maxlength="10" value="<?php echo $this->filter_stripe_enddate; ?>" />
    
            <input type="text" readonly="readonly" id="display_picker_date2pp" name="display_picker_date2pp" class="sv_date_box" size="10" maxlength="10" 
                value="<?php echo $display_picker_date2pp?>" onchange="selectSTRIPEEndDate(); return false;">
                     
		  </div>
          </td>
        </tr>
        <tr>
        <td>
        	<table width="100%">
				<tr>
                  <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_TXN_COL_HEAD'), 'stripe_txn_id', $this->lists['order_Dir_stripe'], $this->lists['order_stripe'], "stripe_" ); ?></th>
                  <!--<th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_REQ_ID_COL_HEAD'), 'request_id', $this->lists['order_Dir_stripe'], $this->lists['order_stripe'], "stripe_" ); ?></th>-->
                  <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_AMOUNT_COL_HEAD'), 'amount', $this->lists['order_Dir_stripe'], $this->lists['order_stripe'], "stripe_" ); ?></th>
                  <th class="svtitle" align="center"><?php echo JHTML::_( 'grid.sort', JText::_('RS1_ADMIN_STRIPE_TIMESTAMP_COL_HEAD'), 'stamp', $this->lists['order_Dir_stripe'], $this->lists['order_stripe'], "stripe_" ); ?></th>
                </tr>
			<?php
            $k = 0;
            for($i=0; $i < sv_count_($this->items_stripe ); $i++) {
            $stripe_row = $this->items_stripe[$i];
            $link = JRoute::_( 'index.php?option=com_rsappt_pro3&controller=admin_detail&task=stripe_transactions_detail&cid='. $stripe_row->id_stripe_transactions.'&frompage=advadmin&tab='.$tab);
           ?>
            <tr class="<?php echo "row$k"; ?>">
              <td align="center"><a href="<?php echo $link; ?>"><u>...<?php echo stripslashes(substr($stripe_row->stripe_txn_id,strlen($stripe_row->stripe_txn_id-10),10)); ?></u></a></td>
              <!--<td width="20%" align="center"><?php echo $stripe_row->request_id; ?>&nbsp;</td>-->
              <td align="center"><?php echo number_format($stripe_row->amount/100,2) ?>&nbsp;</td> 
              <td align="center"><?php echo $stripe_row->stamp; ?>&nbsp;</td>
              <?php $k = 1 - $k; ?>
            </tr>
            <?php } ?>	
          </table>
	  </td>
      </tr>
      </table>

	  <input type="hidden" name="stripe_filter_order" value="<?php echo $this->lists['order_stripe'];?>" />
  	  <input type="hidden" name="stripe_filter_order_Dir" value ="<?php echo $this->lists['order_Dir_stripe'] ?>" />
  	  <input type="hidden" name="stripe_tab" id="stripe_tab"  value ="<?php echo $tab ?>" />
    
   
		
		