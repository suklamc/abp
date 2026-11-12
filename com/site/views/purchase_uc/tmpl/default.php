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

	// require the html view class
	jimport( 'joomla.application.helper' );

	JHtml::_('jquery.framework');

	$jinput = JFactory::getApplication()->input;
	$option = $jinput->getString( 'option', '' );
	
	$user = JFactory::getUser();
	$showform = true;	 
	$direction = null;
	$ordering = null;
		
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "purchase_uc_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get products
	$sql = "SELECT * FROM #__sv_apptpro3_products WHERE product_type = 'UC' AND published = 1 ORDER BY ordering";
	try{
		$database->setQuery($sql);
		$products = NULL;
		$products = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "purchase_uc_tmpl_default", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	//print_r($products);
 
	if(!$user->guest){
		$database = JFactory::getDBO();
		
		$lang = JFactory::getLanguage();
		$sql = "SET lc_time_names = '".str_replace("-", "_", $lang->getTag())."';";		
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "purchase_uc_tmpl_default", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}


	} else{
		echo "<font color='red'>".JText::_('RS1_PURCHASE_UC_NO_LOGIN')."</font>";
		$showform= false;	 
	}
	if(!isPayProcEnabled()){
		echo "<font color='red'>".JText::_('RS1_NO_PAY_PROC')."</font>";
		$showform= false;	 
	}
	
	$msg = $jinput->getString( 'payment_return_msg', '' );
	if($msg != ""){
		echo "<div class='alert'>";
		echo $msg;
		echo "</div>";
	}
	
?>
<script src="<?php echo JURI::base( true );?>/components/com_rsappt_pro3/script.js"></script>
<?php $document = JFactory::getDocument();
$document->addStyleSheet( JURI::base( true )."/components/com_rsappt_pro3/sv_apptpro.css");
?>

<script>
	function doPurchase(id){
		document.getElementById("product_id").value=id;
		Joomla.submitbutton('');
	}

</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<?php if($showform){?>
<div id="sv_apptpro_purcase_uc">
          <h3><?php echo JText::_('RS1_PURCHASE_UC_SCRN_TITLE');?></h3>


    <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
        <tr class="adminheading"  bgcolor="#F4F4F4">
          <th></th>
          <th class="sv_title" align="left"><?php echo JText::_('RS1_PURCHASE_UC_SCRN_SKU_COL_HEAD')?></th>
          <th class="sv_title" align="left"><?php echo JText::_('RS1_PURCHASE_UC_SCRN_NAME_COL_HEAD') ?></th>
          <th class="sv_title" align="left"><?php echo JText::_('RS1_PURCHASE_UC_SCRN_DESC_COL_HEAD')?></th>
          <th class="sv_title" align="left"><?php echo JText::_('RS1_PURCHASE_UC_SCRN_PRICE_COL_HEAD')?></th>
          <th class="sv_title" align="left"></th>
         </tr>
        <?php
        $k = 0;
        for($i=0; $i < sv_count_($products ); $i++) {
        $row = $products[$i];
       ?>
        <tr class="<?php echo "row$k"; ?>">
          <td align="center" valign="middle"><?php echo ($row->product_image_path != ""?"<img src=\"".getResourceImageURL($row->product_image_path)."\" class=\"product_image\">":""); ?></td>
          <td align="left" valign="middle"><?php echo ($row->product_sku != ""?$row->product_sku:""); ?></td>
          <td align="left" valign="middle"><?php echo JText::_(stripslashes($row->product_name)); ?></td>
          <td align="left" valign="middle"><?php echo JText::_(stripslashes($row->product_desc)); ?></td>
          <td align="left" valign="middle"><?php echo $row->product_price; ?> </td>
          <td align="center" valign="middle"><input type="button" value="<?php echo JText::_('RS1_PURCHASE_UC_BUY_NOW')?>" onclick="doPurchase(<?php echo $row->id_products;?>);" /></td>
        </tr>
        <?php } 
    
    ?>
      </table>	

  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="purchase_uc" />
  <input type="hidden" name="view" id="view" value="purchase_uc" />
  <input type="hidden" id="wait_text" value="<?php echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');?>" />
  <input type="hidden" id="product_id" name="product_id" value="" />
  <input type="hidden" id="uid" name="uid" value="<?php echo $user->id;?>" />
    
  <br />
  <?php if($apptpro_config->hide_logo == 'No'){ ?>
    <span style="font-size:10px"> Appointment Booking Pro Ver. 4.0.5 - Copyright 2008-20<?php echo date("y");?> - <a href='http://www.softventures.com' target="_blank">Soft Ventures, Inc.</a></span>
  <?php } ?>
  
</div>
	<?php 
	} // end of if showform
	?>

</form>
