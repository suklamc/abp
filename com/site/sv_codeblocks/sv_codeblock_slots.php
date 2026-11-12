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
    <div id="accordion_grid">
        <!-- *********************  GAD *******************************-->
        <table align="center" >
        <tr>
            <td colspan="4">        
              <table class="sv_gad_container_table" id="gad_container" style="display:none; width:100%;" >
                <tr>
                  <td><div style="display:table-cell;"><label class="sv_apptpro_request_label"><?php echo JText::_('RS1_GAD_SCRN_DATE');?></label></div>
                  	<div style="display:table-cell;">&nbsp;</div>
                       <div style="display:table-cell;" ><input type="text" readonly="readonly" id="display_grid_date" name="display_grid_date" class="sv_date_box" size="10" maxlength="10" 
                        value="<?php echo $display_grid_date ?>" onchange="changeDate();">
                        <input name="grid_date" id="grid_date" type="hidden" value="<?php echo $grid_date ?>" />  </div>      
                  </td>                 
                  <td>&nbsp; <input type="button" id="btnPrev" class="sv_grid_button" onclick="gridPrevious();" value="<<-" disabled>
                  &nbsp; <input type="button" id="btnNext" class="sv_grid_button" onclick="gridNext();" value="->>"></td>
                  <?php if($apptpro_config->gad_grid_hide_startend == "Yes"){?>
                      <td><input type="hidden" name="gridstarttime" id="gridstarttime" value="<?php echo $gridstarttime ?>"/>
                          <input type="hidden" name="gridendtime" id="gridendtime" value="<?php echo $gridendtime ?>"/>&nbsp;</td>
                  <?php } else { ?>
                      <td style="text-align:right"><?php echo JText::_('RS1_GAD_SCRN_GRID_START');?>&nbsp;<select name="gridstarttime" id="gridstarttime" class="sv_apptpro_request_dropdown" onchange="changeGrid();" style="width:auto">
                        <?php 
                        for($x=0; $x<25; $x+=1){
                            if($x==12){
                                echo "<option value=".$x.":00 "; if($gridstarttime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_NOON')."</option>";  
                            } else if($x==24){
                                echo "<option value=".$x.":00 "; if($gridstarttime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_MIDNIGHT')."</option>";  
                            } else {
                                if($apptpro_config->timeFormat == "12"){
                                    $AMPM = " AM";
                                    $x1 = $x;
                                    if($x>12){ 
                                        $AMPM = " PM";
                                        $x1 = $x-12;
                                    }
                                } else {
                                    $AMPM = "";
                                    $x1 = $x;
                                }
                                echo "<option value=".$x.":00 "; if(trim($gridstarttime) == $x.":00") {echo " selected='selected' ";} echo "> ".$x1.":00".$AMPM." </option>";  
                            }
                        }
                        ?>
                        </select> &nbsp; <?php echo JText::_('RS1_GAD_SCRN_GRID_END');?> <select name="gridendtime" id="gridendtime" class="sv_apptpro_request_dropdown" onchange="changeGrid();" style="width:auto">
                        <?php 
                        for($x=0; $x<25; $x+=1){
                            if($x==12){
                                echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_NOON')."</option>";  
                            } else if($x==24){
                                echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo ">".JText::_('RS1_INPUT_SCRN_MIDNIGHT')."</option>";  
                            } else {
                                if($apptpro_config->timeFormat == "12"){
                                    $AMPM = " AM";
                                    $x1 = $x;
                                    if($x>12){ 
                                        $AMPM = " PM";
                                        $x1 = $x-12;
                                    }
                                } else {
                                    $AMPM = "";
                                    $x1 = $x;
                                }
                                echo "<option value=".$x.":00 "; if($gridendtime == $x.":00") {echo " selected='selected' ";} echo "> ".$x1.":00".$AMPM." </option>";  
                            }
                        }
                        ?>
                        </select> </td>
                <?php } ?>
                </tr>                           
                <tr>
                  <td colspan="3" style="margin:auto; width:<?php echo $gridwidth?>"><div id="table_here"></div></td>
                </tr>
             </table>            
            <input type="hidden" id="mode" name="mode" value="<?php echo $mode?>" />
            <?php if($gridwidth>-1){ ?>
                <input type="hidden" id="gridwidth" name="gridwidth" value="<?php echo $gridwidth?>" />
            <?php } ?>
            <input type="hidden" id="grid_days" name="grid_days" value="<?php echo $griddays?>" />   
            <input type="hidden" id="namewidth" name="namewidth" value="<?php echo $namewidth?>" />        
    
            <input type="hidden" name="selected_resource_id" id="selected_resource_id" value="-1" />
            <input type="hidden" name="startdate" id="startdate" value="" />
            <input type="hidden" name="enddate" id="enddate" value="" />
            <input type="hidden" name="starttime" id="starttime" value=""/>
            <input type="hidden" name="endtime" id="endtime" value=""/>  
            <input type="hidden" name="endtime_original" id="endtime_original" value=""/>  
            <input type="hidden" name="sub_cat_count" id="sub_cat_count" value="<?php echo $sub_cat_count->count ?>"/>  
            <input type="hidden" name="user_duration" id="user_duration" value="0"/>                
            </td>
            </tr>
        </table>    
        <!-- *********************  GAD *******************************-->
    </div>
</div>