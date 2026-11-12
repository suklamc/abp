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
<div id="addto_notification_dialog" style="display:none" title=" <?php echo JText::_('RS1_INPUT_SCRN_ADD_TO_NOTIFICATION_TITLE');?>">
    <form>
    <?php echo JText::_('RS1_INPUT_SCRN_ADD_TO_NOTIFICATION_PROMPT');?>
    <br/><br/>
    <input type="text" name="notification" id="notification_email"  />
     <?php echo JText::_('RS1_INPUT_SCRN_REMOVE_FROM_NOTIFICATION_PROMPT');?>&nbsp;<input type="checkbox" name="chk_remove_from_list" id="chk_remove_from_list" />
    <label id="results"></label>
    </form>
</div>