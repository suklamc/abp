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

<?php
					echo $udf_help_icon." id='opener".$i."' title='".JText::_('RS1_INPUT_SCRN_CLICK_FOR_HELP')."'>";		
					echo "<div id=\"udf_help".$i."\" title=\"".JText::_(stripslashes($udf_row->udf_label))."\">".JText::_(stripslashes($udf_row->udf_help))."</div>";	
						echo "<script>";
						echo "jQuery( \"#udf_help".$i."\" ).dialog({ autoOpen: false, ";
						//echo "  closeText:\"your close text\",";						
						echo "  position:{";
						echo "    my: \"left+10 bottom+5\",";
  						echo "    of: \"#opener".$i."\",";
						echo "    collision: \"fit\"";
						echo "  }";
						echo "});";
						
						echo "jQuery( \"#opener".$i."\" ).click(function() { ";
					  	echo "   jQuery( \"#udf_help".$i."\" ).dialog( \"open\" );";
						if($udf_row->udf_help_format == "Link"){					
							echo "jQuery( \"#udf_help".$i."\" ).load(\"".JText::_(stripslashes($udf_row->udf_help))."\", function() {});";
						}
						echo "});";

						echo "</script>";
?>