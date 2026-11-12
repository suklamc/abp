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

	$resource_label = $params->get('res_label', $jinput->getString('res_label',''));
	if($resource_label == ""){
		// use override from menu parameter
		$resource_label = "RS1_INPUT_SCRN_RESOURCE";
	}

	$show_service_first = $params->get('serv_first', $jinput->getString('serv_first','No'));
?>

<div id="sv_codeblock_resource">
  <div class="sv_table">
    <?php if(sv_count_($res_cats) > 0 ){ ?>
		<!-- Categories --------------------------------------------------- -->
	    <!--  Show block with Category - [Sub Category] - Resource -->
    	<div class="sv_table_row" <?php echo ($single_category_mode?"style=\"visibility:hidden;display:none\"":"")?> >
      <div class="sv_table_cell_name">
        <label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES');?></label>
      </div>
      <div class="sv_table_cell_value">
        <select name="category_id" id="category_id" class="sv_apptpro_request_dropdown" onchange="changeCategory();"
        <?php echo ($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"");?>
            title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_TOOLTIP'));?>">
          <option value="0"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_PROMPT');?></option>
          <?php 
                    $k = 0;
                    for($i=0; $i < sv_count_($res_cats ); $i++) {
                    $res_cat = $res_cats[$i];
                    ?>
          <option value="<?php echo $res_cat->id_categories; ?>"><?php echo JText::_(stripslashes($res_cat->name)); ?></option>
          <?php $k = 1 - $k; 
                    } ?>
        </select>
        <?php if($apptpro_config->enable_ddslick == "Yes"){?>
        <select id="category_id_slick" >
          <option value="0"><?php echo JText::_('RS1_INPUT_SCRN_RESOURCE_CATEGORIES_PROMPT');?></option>
          <?php 
                    $k = 0;
                    for($i=0; $i < sv_count_($res_cats ); $i++) {
                    $res_cat = $res_cats[$i];
                    ?>
          <option value="<?php echo $res_cat->id_categories; ?>"
                data-imagesrc="<?php echo ($res_cat->ddslick_image_path!=""?getResourceImageURL($res_cat->ddslick_image_path):"")?>"
                    data-description="<?php echo $res_cat->ddslick_image_text?>"> <?php echo JText::_(stripslashes($res_cat->name)); ?></option>
          <?php $k = 1 - $k; 
                    } ?>
        </select>
        <?php } ?>
      </div>
    </div>
		<!-- end Categories  -->
		<?php if($sub_cat_count->count > 0 ){ // there are sub cats ?>
 	       <!-- Sub Categoreis  -->
	        <div class="sv_table_row" id="subcats_row" style="visibility:hidden; display:none">
          <div class="sv_table_cell_name"></div>
          <div class="sv_table_cell_value">
            <div id="subcats_div"></div>
          </div>
        </div>
	        <!-- end Sub Categoreis  -->
        <?php } ?>

		<?php if($show_service_first == "Yes") { ?>
            <!--  Show block with Service selector and resource when service selected -->
            <?php // No categories involved so just fetch all published services
				$sql = 'SELECT * FROM #__sv_apptpro3_services WHERE published=1 ORDER BY ordering';
				try{
					$database->setQuery($sql);
					$srv_rows = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "res_sv_codeblock", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return false;
				}				
			?>
            <div class="sv_table_cell_name" style="visibility:hidden;">
            	<label id="service_selector_label"><?php echo JText::_('RS1_SERVICE_SELECTOR_LABEL');?></label>
          	</div>
            <div class="sv_table_cell_value" style="visibility:hidden;">
            	<div id="service_selector_div"></div>
            </div>
          
          	<div id="resources_for_service" style="visibility:hidden; display:none;" class="sv_table_row">
                  <!-- Resources  -->
    	 	    	<div class="sv_table_cell_name">
                		<label id="resources_label"><?php echo JText::_($resource_label);?></label>
                	</div>
	              <div class="sv_table_cell_value">
				        <div id="resources_div" style="visibility:hidden;">&nbsp;</div>
					</div>
                  <!-- end Resources  -->
			</div>          
         <?php } else { ?>
            <div class="sv_table_row">
              <!-- Resources  -->
              <div class="sv_table_cell_name">
            <label id="resources_label" style="visibility:hidden;"><?php echo JText::_($resource_label);?></label>
          </div>
              <div class="sv_table_cell_value">
                <div id="resources_div" style="visibility:hidden;">&nbsp;</div>
              </div>
              <!-- end Resources  -->
		 <?php } ?>
  </div>


    <?php } else { ?>
		<!-- No Categories --------------------------------------------------- -->
		<?php if($show_service_first == "Yes") { ?>
            <!--  Show block with Service selector and resource when service selected -->
            <?php // No categories involved so just fetch all published services
				$sql = 'SELECT * FROM #__sv_apptpro3_services WHERE published=1 ORDER BY ordering';
				try{
					$database->setQuery($sql);
					$srv_rows = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "res_sv_codeblock", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return false;
				}				
			?>
            <div class="sv_table_cell_name">
                <label id="service_selector_label"><?php echo JText::_('RS1_SERVICE_SELECTOR_LABEL');?></label>
            </div>
            <div class="sv_table_cell_value">
                <div id="service_selector_div"></div>
                <select name="service_selector" id="service_selector" class="sv_apptpro_request_dropdown" onchange="changeServiceSelector()" 
                title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_SERVICE_SELECTOR_TOOLTIP'));?>">
                      <option value=""><?php echo JText::_('RS1_SERVICE_SELECTOR_FIRSTROW');?></option>
                  <?php 
                            $k = 0;
                            for($i=0; $i < sv_count_($srv_rows ); $i++) {
                            $srv_row = $srv_rows[$i];
                            ?>
                              <option value="<?php echo $srv_row->id_services; ?>"><?php echo JText::_(stripslashes($srv_row->name));?></option>
                      <?php $k = 1 - $k; 
                            } ?>
                </select>
                </div>
           	<div id="resources_for_service" style="visibility:hidden; display:none;" class="sv_table_row">
                  <!-- Resources  -->
    	 	    	<div class="sv_table_cell_name">
                		<label id="resources_label"><?php echo JText::_($resource_label);?></label>
                	</div>
	              <div class="sv_table_cell_value">
				        <div id="resources_div" style="visibility:hidden;">&nbsp;</div>
					</div>
                  <!-- end Resources  -->
			</div>          
            
         <?php } else { ?>
            <!--  Show block with Resource -->
            <div class="sv_table_row">
              <!-- Resources  -->
              <div class="sv_table_cell_name">
            		<label id="resources_label" style="visibility:hidden;"><?php echo JText::_($resource_label);?></label>
          	  </div>
              <div class="sv_table_cell_value">
                <select name="resources" id="resources" class="sv_apptpro_request_dropdown" onchange="changeResource()" 
                <?php echo ($apptpro_config->enable_ddslick == "Yes"?" style=\"visibility:hidden; display:none\"":"");?>
                title="<?php echo (blockIETooltips($apptpro_config->use_jquery_tooltips)?"":JText::_('RS1_INPUT_SCRN_RESOURCE_TOOLTIP'));?>">
                  <?php 
                            $k = 0;
                            for($i=0; $i < sv_count_($res_rows ); $i++) {
                            $res_row = $res_rows[$i];
                            ?>
                  <option value="<?php echo $res_row->id_resources; ?>" <?php //if($resource == $res_row->id_resources ){echo " selected='selected' ";} ?>><?php echo JText::_(stripslashes($res_row->name)); echo ($res_row->cost==""?"":" - "); echo JText::_(stripslashes($res_row->cost)); ?></option>
                  <?php $k = 1 - $k; 
                            } ?>
                </select>
            	<?php if($apptpro_config->enable_ddslick == "Yes"){?>
                <select id="resources_slick" >
                  <?php 
                            $k = 0;
                            for($i=0; $i < sv_count_($res_rows ); $i++) {
                            $res_row = $res_rows[$i];
                            ?>
                  <option value="<?php echo $res_row->id_resources; ?>"
                        data-imagesrc="<?php echo ($res_row->ddslick_image_path!=""?getResourceImageURL($res_row->ddslick_image_path):"")?>"
                            data-description="<?php echo $res_row->ddslick_image_text?>"> <?php echo JText::_(stripslashes($res_row->name)); echo ($res_row->cost==""?"":" - "); echo JText::_(stripslashes($res_row->cost)); ?></option>
                  <?php $k = 1 - $k; 
                            } ?>
                </select>
	            <br/>
            <?php } ?>
            </div>
            <!-- end Resources  -->
            </div>
        <?php } ?>    
    <?php } ?>
    
  	<!--  Services --------------------------------------------------- -->
    <!--  Show block with Service -->
    <div class="sv_table_row" id="services" style="visibility:hidden; display:none">
      <div class="sv_table_cell_name">
        <label class="sv_apptpro_request_label"><?php echo JText::_('RS1_INPUT_SCRN_SERVICES');?></label>
      </div>
      <div class="sv_table_cell_value">
        <div id="services_div">&nbsp;</div>
      </div>
    </div>
   	<!--  Services -->

    <?php echo ($single_category_mode?"<input type=\"hidden\" name=\"category_id\" value=\"".$single_category_id."\">":"")?>    
  
</div>
</div>
