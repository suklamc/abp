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
	<tr><td colspan="4">
    <table width="100%">
	<tr>
    	<td ><label class="sv_apptpro_request_label"><?php echo ($this->device == "mobile"?"":JText::_('RS1_WIZARD_SCRN_SUMMARY'));?></label></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_NAME');?></label></td><td><label id="summary_name"></label></td>
	</tr>
	<tr>
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_EMAIL');?></label></td><td><label id="summary_email"></label></td>
	</tr>
	<tr>
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_PHONE');?></label></td><td><label id="summary_phone"></label></td>
	</tr>
    <?php if(sv_count_($res_cats) > 0 ){ ?>
	<tr>
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_CATEGORY');?></label></td><td><label id="summary_cat"></label></td>
	</tr>
    <?php } ?>
	<tr>
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_RESOURCE');?></label></td><td><label id="summary_resource"></label></td>
	</tr>    
	<tr id="service_summary" style="visibility:hidden; display:none">
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_SERVICE');?></label></td><td><label id="summary_service"></label></td>
	</tr>
	<tr id="udfs_summary" style="visibility:hidden; display:none">
    	<td></td>
        <td></td><td><label id="summary_udfs"></label></td>
	</tr>
	<tr id="extras_summary" style="visibility:hidden; display:none">
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_EXTRAS');?></label></td><td><label id="summary_extras"></label></td>
	</tr>
	<tr>
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_DATETIME');?></label></td><td><label id="summary_datetime"></label></td>
	</tr>
	<tr id="summary_seats_row">
    	<td></td>
        <td><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_WIZARD_SCRN_SEATS');?></label></td><td><label id="summary_seats"></label></td>
	</tr>
    </table>
    </td></tr>
 </div>    
</div>