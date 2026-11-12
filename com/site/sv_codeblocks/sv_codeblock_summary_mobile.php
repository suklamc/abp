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
<div>
<div id="summary_section">
	<tr>
    	<td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_SUMMARY');?></label></div>
        <div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_NAME');?></label></div><div class="controls"><label id="summary_name"/></div></td>
	</tr>
	<tr>
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_EMAIL');?></label></div><div class="controls"><label id="summary_email"/></div></td>
	</tr>
	<tr>
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_PHONE');?></label></div><div class="controls"><label id="summary_phone"/></div></td>
	</tr>
    <?php if(sv_count_($res_cats) > 0 ){ ?>
	<tr>
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_CATEGORY');?></label></div><div class="controls"><label id="summary_cat"/></div></td>
	</tr>
    <?php } ?>
	<tr>
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_RESOURCE');?></label></div><div class="controls"><label id="summary_resource"/></div></td>
	</tr>    
	<tr id="service_summary" style="visibility:hidden; display:none">
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_SERVICE');?></label></div><div class="controls"><label id="summary_service"/></div></td>
	</tr>
	<tr id="udfs_summary" style="visibility:hidden; display:none">
        <td><div class="control-label"><label id="summary_udfs"></label></div></td>
	</tr>
	<tr id="extras_summary" style="visibility:hidden; display:none">
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_EXTRAS');?></label></div><div class="controls"><label id="summary_extras"/></div></td>
	</tr>
	<tr>
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_DATETIME');?></label></div><div class="controls"><label id="summary_datetime"/></div></td>
	</tr>
	<tr id="summary_seats_row">
        <td><div class="control-label"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_SEATS');?></label></div><div class="controls"><label id="summary_seats"/></div></td>
	</tr>
 </div>    
</div>