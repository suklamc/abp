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

<style type="text/css">
<!--
.icon-48-about { background-image: url(./components/com_rsappt_pro3/images/about.png); }
-->
}
</style>
<script language="javascript">
	
</script>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<table border="0">
  <tr>
    <td colspan="2"><h3><?php echo JText::_('RS1_ADMIN_SCRN_ABOUT');?></h3></td>
  </tr>
  <tr>
    <td width="10%">&nbsp;</td>
    <td><p>Appointment Booking Pro is <br />written and supported by:<br />Soft Ventures, Inc.
    <br /><a href=http://www.appointmentbookingpro.com target='_blank'>www.AppointmentBookingPro.com</a>
    <br /><a href=http://www.softventures.com target='_blank'>www.SoftVentures.com</a></p>
      <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td colspan="2"><h3><?php echo JText::_('RS1_ADMIN_SCRN_LICENSE');?></h3></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><p>GNU/GPL, see http://www.gnu.org/licenses/gpl-2.0.html<p>&nbsp;</p>      </td>
  </tr>
  <tr>
    <td colspan="2"><h3><?php echo JText::_('Version');?></h3></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>ABPro Version 4.0.5, beta 11, Apr 6/22</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

  <p>&nbsp;</p>
  <p>
  <input type="hidden" name="controller" value="about" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="hidemainmenu" value="0" />  
  <input type="hidden" name="task" value="" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
