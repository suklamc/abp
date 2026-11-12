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

<div id="booking_detail" style="visibility:hidden; display:none">
  <div id="booking_detail_div">
    <div id="booking_detail_table" class="sv_table">
      <div class="sv_table_row">
        <div class="sv_table_cell_name" style="width:30%; height:35px;">
          <label class="sv_apptpro_request_label"><?php echo JText::_('RS1_GAD_SCRN_DETAIL');?></label>         
          <label class="sv_apptpro_errors" id="selected_resource_wait"></label>
        </div>
        <div class="sv_table_cell_value">
          <div id="sv_gad_user_selection_div">
            <div style="display: table-cell;">
              <label class="sv_apptpro_selected_resource" id="selected_resource"> </label>
            </div>
            <div style="display: table-cell;">
              <label class="sv_apptpro_selected_resource" id="selected_date"> </label>
            </div>
            <div style="display: table-cell;">
              <label class="sv_apptpro_selected_resource" id="selected_starttime"> </label>
            </div>
            <div style="display: table-cell;">
              <label class="sv_apptpro_selected_resource"  style="text-align:center"><?php echo JText::_('RS1_TO');?></label>
            </div>
            <div style="display: table-cell;">
              <label class="sv_apptpro_selected_resource" id="selected_endtime"> </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>