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

defined('_JEXEC') or die('Restricted access');

$jinput = JFactory::getApplication()->input;

?>

<script language="javascript">
Joomla.submitbutton = function(pressbutton){
	var ok = "yes";
   	if (pressbutton == 'save' || pressbutton == 'save2new'){
		if(document.getElementById("product_name").value == ""){
			alert("Please enter a Product Name");
			ok = "no";
		}
		if(ok == "yes"){
			Joomla.submitform(pressbutton);
		}
	} else {
		Joomla.submitform(pressbutton);
	}		
}


</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<link href="<?php echo JURI::root( true );?>/components/com_rsappt_pro3/sv_apptpro.css" rel="stylesheet">
<fieldset class="adminform">
<?php echo JText::_('RS1_ADMIN_PRODUCT_LIST_INTRO');?>
  <table class="table table-striped" >
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_DETAIL_ID');?></td>
      <td><?php echo $this->detail->id_products ?> </td>
      <td>&nbsp;</td>
    </tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_SKU_COL_HEAD');?></td>
      <td><input type="text" maxsize="250" name="product_sku" id="product_sku" value="<?php echo $this->detail->product_sku; ?>" /></td>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_SKU_COL_HELP');?></td>
    </tr>
    </tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_NAME_COL_HEAD');?></td>
      <td><input type="text" maxsize="250" name="product_name" id="product_name" value="<?php echo $this->detail->product_name; ?>" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_DESC_COL_HEAD');?></td>
      <td><input type="text" maxsize="250" name="product_desc" value="<?php echo stripslashes($this->detail->product_desc); ?>" /></td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_VALUE_COL_HEAD');?></td>
      <td ><input type="text" style="width:50px; text-align: center" size="8" maxsize="10" name="product_value" value="<?php echo $this->detail->product_value; ?>" />
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_VALUE_COL_HELP');?></td>
    </tr>
    <tr>
      <td ><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_PRICE_COL_HEAD');?></td>
      <td ><div ><input style="width:50px; text-align: center" type="text" size="8" maxsize="10" name="product_price" value="<?php echo $this->detail->product_price; ?>" /></div>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_PRICE_COL_HELP');?></td>
    </tr>
  <tr>
    <td><?php echo JText::_('RS1_ADMIN_PRODUCT_IMAGE');?> </td>
    <td><input type="text" style="width:90%;" name="product_image_path" value="<?php echo $this->detail->product_image_path; ?>" />
	<?php echo ($this->detail->product_image_path != ""?"<br/><img src=\"".getResourceImageURL($this->detail->product_image_path)."\" style='max-height: 64px;'/>":"")?>		
    </td>
    <td><?php echo JText::_('RS1_ADMIN_PRODUCT_IMAGE_HELP');?></td>
  <tr>

    <tr>
      <td><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_DETAIL_ORDER');?></td>
      <td colspan="2"><input style="width:30px; text-align: center" type="text" size="5" maxsize="2" name="ordering" value="<?php echo $this->detail->ordering; ?>" />
        &nbsp;&nbsp;</td>
    </tr>
    <tr>
        <td ><?php echo JText::_('RS1_ADMIN_SCRN_PRODUCT_DETAIL_PUBLISHED');?></td>
        <td colspan="2">
            <select name="published">
            <option value="0" <?php if($this->detail->published == "0"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_NO');?></option>
            <option value="1" <?php if($this->detail->published == "1"){echo " selected='selected' ";} ?>><?php echo JText::_('RS1_ADMIN_SCRN_YES');?></option>
            </select>        </td>
    </tr>
    <tr>
      <td colspan="2" >
      <p>&nbsp;</p></td>
    </tr>  
  </table>

</fieldset>
  <input type="hidden" name="id_products" value="<?php echo $this->detail->id_products; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="controller" value="products_detail" />
  <br />
  <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
</form>
