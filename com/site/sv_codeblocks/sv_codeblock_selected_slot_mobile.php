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
    <table width="100%">
        <tr>
          <td>
          <div id="booking_detail_div">
          <div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_GAD_SCRN_DETAIL');?></label></div>
          <div class="controls"><label class="sv_apptpro_errors" id="selected_resource_wait"></label>
            <div>
            <div><label class="sv_apptpro_selected_resource_mobile" id="selected_resource"> </label></div>
            <div><label class="sv_apptpro_selected_resource_mobile" id="selected_date"> </label></div>
            <div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile" id="selected_starttime"> </label></div>
            <div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile"  style="padding:5px"><?php echo JText::_('RS1_TO');?></label></div>
            <div style="display: table-cell;"><label class="sv_apptpro_selected_resource_mobile" id="selected_endtime"> </label></div>
            </div>
         </div>   
         </div>
            <input type="hidden" name="selected_resource" id="selected_resource" value=""/>        
        </td>
        </tr>    
    </table>    
</div>

