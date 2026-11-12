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

<div id="sv_codeblock_ts_select">
   <div id="datetime" style="display: none">
    <div class="sv_table">
     <div class="sv_table_row" id="datetime">
	<div class="sv_table_cell_name">
		<label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_DATETIME');?></label></div>
     </div>
     <div class="sv_table_row">
	    <div class="sv_table_cell_value">
       	<input name="startdate" id="startdate" type="hidden" 
                  class="sv_date_box" value="<?php echo $display_picker_date ?>"/>                 

		<input type="text" readonly="readonly" id="display_startdate" name="display_startdate" class="sv_date_box" size="10" maxlength="10" value="<?php echo $display_picker_date ?>"
	      onchange="getSlots();">
      
        <input type="hidden" name="selected_resource_id" id="selected_resource_id" value="-1" />
        <input type="hidden" name="enddate" id="enddate" value="<?php echo $enddate ?>" />
        <input type="hidden" name="starttime" id="starttime" value="<?php echo $starttime ?>"/>
        <input type="hidden" name="endtime" id="endtime" value="<?php echo $endtime ?>"/>  
        <input type="hidden" name="endtime_original" id="endtime_original" value=""/>  
        
        <div id="slots" style="visibility:hidden;">&nbsp;</div>
        </div>
    </div>
	</div>
    </div>
</div>
