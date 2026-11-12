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
        <div class="sv_table" style="width:100%">
        <div class="sv_table_row" id="resource_udfs" style="visibility:hidden; display:none">
            <div id="resource_udfs_div"></div></div>
        </div>              
    <?php if($udf_rows != null && sv_count_($udf_rows) > 0){ ?>
	    <div id="common_udfs_div">
        <div class="sv_table" style="width:100%;">
    	<?php
	        // (to be added at a later date) if logged in user, fetch udf values from last booking                
    	    $k = 0;
        	for($i=0; $i < sv_count_($udf_rows ); $i++) {
            $udf_row = $udf_rows[$i];
            // if cb_mapping value specified, fetch the cb data
            if($user->guest == false and $udf_row->cb_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
                $udf_value = getCBdata($udf_row->cb_mapping, $user->id);
            } else if($user->guest == false and $udf_row->profile_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
                $udf_value = getProfiledata($udf_row->profile_mapping, $user->id);
            } else if($user->guest == false and $udf_row->js_mapping != "" and $jinput->getString('user_field'.$i.'_value', '') == ""){
                $udf_value = getJSdata($udf_row->js_mapping, $user->id);
            } else {
                $udf_value = $jinput->getString('user_field'.$i.'_value', '');
            }
            ?>
            <div class="sv_table_row">
              <div class="sv_table_cell_name" style="vertical-align:top"><label id="<?php echo 'user_field'.$i.'_label'; ?>" class="sv_apptpro_request_text"><?php echo JText::_(stripslashes($udf_row->udf_label)) ?></label></div>
              <div class="sv_table_cell_value sv_udf_cell_value" style="vertical-align:top">
               <?php 
                if($udf_row->read_only == "Yes" && $udf_row->cb_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
                else if($udf_row->js_read_only == "Yes" && $udf_row->js_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
                else if($udf_row->profile_read_only == "Yes" && $udf_row->profile_mapping != "" && $user->guest == false){$readonly = " readonly='readonly' ";}
                else {$readonly ="";}
                ?>
                <?php if($udf_row->udf_type == 'Textbox'){ ?>
                    <input name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" type="text" value="<?php echo $udf_value; ?>" 
                    size="<?php echo $udf_row->udf_size ?>" maxlength="255" <?php echo $readonly?>
                     <?php echo ($udf_row->udf_placeholder_text != ""?" placeholder='".JText::_($udf_row->udf_placeholder_text)."'":"")?> 
                     class="sv_apptpro_request_text" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'Textarea'){ ?>
                    <textarea name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" 
                     <?php echo ($udf_row->udf_placeholder_text != ""?" placeholder='".JText::_($udf_row->udf_placeholder_text)."'":"")?> 
                    <?php echo $readonly?>
                    rows="<?php echo $udf_row->udf_rows ?>" cols="<?php echo $udf_row->udf_cols ?>" 
                     class="sv_apptpro_request_text" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/><?php echo $udf_value; ?></textarea>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />                     
                <?php } else if($udf_row->udf_type == 'Radio'){ 
                        $col_count = 0;
                        $aryButtons = explode(",", JText::sprintf("%s",stripslashes(JText::_($udf_row->udf_radio_options))));
                        echo "<table class='sv_udf_radio_table'><tr><td>";
                        foreach ($aryButtons as $button){ 
                            $col_count++; ?>
                            <input name="user_field<?php echo $i?>_value" type="radio" id="user_field<?php echo $i?>_value" 
                            <?php  
                                if(strpos($button, "(d)")>-1){
                                    echo " checked=\"checked\" ";
                                    $button = trim(str_replace("(d)","", $button));
                                } ?>
                            value="<?php echo JText::_(stripslashes(trim($button))) ?>" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                            <span class='sv_udf_radio_text'><?php echo JText::_(stripslashes(trim($button)))?></span>
                            <?php if($col_count >= $udf_row->udf_cols){$col_count = 0; echo "</td></tr><tr><td>";}else{echo "</td><td>";}?>
                            <?php // if($col_count >= $udf_row->udf_cols){$col_count = 0; echo "<br />";}else{echo "&emsp;";}?>
                        <?php } 
                        echo ($udf_row->udf_required == "Yes"?"<td>".$required_symbol."</td>":"");
                        echo "</tr></table>"; ?>
                     <?php //echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else if($udf_row->udf_type == 'List'){ 
                        $aryOptions = explode(",", JText::sprintf("%s",stripslashes(JText::_($udf_row->udf_radio_options)))); ?>
                        <select name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" class="sv_apptpro_request_dropdown"
                        title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_(stripslashes($udf_row->udf_tooltip))) ?>"> 
                        <?php 
                        foreach ($aryOptions as $listitem){ ?>
                            <option value="<?php echo trim(JText::_(str_replace("(d)","", $listitem))); ?>"
                            <?php  
                                if(strpos($listitem, "(d)")>-1){
                                    echo " selected=true ";
                                    $listitem = trim(str_replace("(d)","", $listitem));
                                } ?>
                                ><?php echo JText::_(stripslashes($listitem)); ?></option>
                        <?php } ?>              
                        </select>                 
                <?php } else if($udf_row->udf_type == 'Content'){ ?>
                    <label> <?php echo JText::_($udf_row->udf_content) ?></label>
                    <input type="hidden" name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" value="<?php echo JText::_(htmlentities($udf_row->udf_content, ENT_QUOTES, "UTF-8"));?>">
                    <input type="hidden" name="user_field<?php echo $i?>_type" id="user_field<?php echo $i?>_type" value='Content'>
                <?php } else if($udf_row->udf_type == 'Date'){ ?>
                    <script >
                        jQuery(function() {
                            jQuery( "#user_field<?php echo $i?>_value" ).datepicker({
                                showOn: "button",
			autoSize: true,
                                firstDay: <?php echo $apptpro_config->popup_week_start_day ?>, 
                                changeMonth: true,
                                changeYear: true,
                                yearRange: "1920:2020",
                                dateFormat: "<?php echo $apptpro_config->date_picker_format;?>",
                                buttonImage: "<?php echo JURI::base( true );?>/components/com_rsappt_pro3/icon_cal_gr.png",
                                buttonImageOnly: true,
                                buttonText: "<?php echo JText::_('RS1_INPUT_SCRN_DATE_PROMPT');?>"
                            });
                        });
                    </script>
                    <input type="text" readonly="readonly" id="user_field<?php echo $i?>_value" name="user_field<?php echo $i?>_value" 
                        class="sv_date_box" size="10" maxlength="10" value="<?php echo $display_picker_date ?>">
                        <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } else { ?>
                    <input name="user_field<?php echo $i?>_value" id="user_field<?php echo $i?>_value" type="checkbox" value="<?php echo JText::_('RS1_INPUT_SCRN_CHECKED');?>" title="<?php echo JText::_(stripslashes($udf_row->udf_tooltip)) ?>"/>
                     <?php echo ($udf_row->udf_required == "Yes"?$required_symbol:"")?>
                     <input type="hidden" name="user_field<?php echo $i?>_is_required" id="user_field<?php echo $i?>_is_required" value="<?php echo $udf_row->udf_required ?>" />
                <?php } ?>    
                     <input type="hidden" name="user_field<?php echo $i?>_udf_id" id="user_field<?php echo $i?>_udf_id" value="<?php echo $udf_row->id_udfs ?>" />
                <?php if($udf_row->udf_help != "" && $udf_row->udf_help_as_icon == "Yes" ){      
                    //echo $udf_help_icon." title='".JText::_(stripslashes($udf_row->udf_help))."'>";
                    include JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_udf_help.php";
                } ?>	
               </div>
              </div>
            <?php if($udf_row->udf_help_as_icon == "No" && $udf_row->udf_help != ""){ ?>
            <div class="sv_table_row">
            <div class="sv_table_cell_name"></div>
            <div class="sv_table_cell_value sv_udf_cell_value sv_apptpro_request_helptext"><?php echo JText::_(stripslashes($udf_row->udf_help)) ?></div>
            </div>
            <?php } ?>
          <?php $k = 1 - $k; 		  
        } ?>
        </div>
        </div>
	    <?php }?>  
</div>