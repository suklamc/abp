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

use Joomla\CMS\Component\Router\RouterBase;

class rsappt_pro3Router extends RouterBase
{
	/**
	 * Build the route for the com_banners component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();
		
		switch($segments[0])
		{
			case 'booking_screen_gad':
			case 'booking_screen_fd':
			case 'booking_screen_simple':
			case 'bookingscreengadwiz':
				//index.php?option=com_rsappt_pro3&view=booking_screen_gad&Itemid='.$frompage_item.'&task='.$next_view.'&req_id=
				$vars['view'] = $segments[0];
				if(count($segments)>1){
					$vars['Itemid'] = $segments[1];		
				}
				if(count($segments)>2){
					$vars['task'] = $segments[2];
				}
				if(count($segments)>3){
					$vars['cc'] = $segments[3];
				}
				if(count($segments)>4){
					$vars['req_id'] = $segments[4];
				}
				break;		
	
			case 'front_desk':
				if(count($segments)>2){
					$vars['view'] = 'front_desk';
					$vars['task'] = $segments[1];
					$vars['frompage'] = $segments[2];
					if(count($segments)>3){
						$vars['Itemid'] = $segments[3];
					}
				} else {
					$vars['view'] = 'front_desk';
					if(count($segments)>1){
						$vars['Itemid'] = $segments[1];
					}
				}			
				break;
	
		   case 'mail_detail':
				$vars['controller'] = 'mail_detail';
				if(count($segments)>1){
					$vars['task'] = $segments[1];
				}
				if(count($segments)>2){
					$vars['cid'] = $segments[2];
				}
				if(count($segments)>3){
					$vars['frompage'] = $segments[3];
				}
				if(count($segments)>4){
					$vars['Itemid'] = $segments[4];
				}
				break;
	
		   case 'admin_detail':
				$vars['controller'] = 'admin_detail';
				$vars['task'] = $segments[1];
				$vars['cid'] = $segments[2];
				if(count($segments)>3){
					$vars['frompage'] = $segments[3];
				}
				if(count($segments)>4){
					$vars['Itemid'] = $segments[4];
				}
				break;
			   
		   case 'advadmin':
			   $vars['view'] = 'advadmin';
			   break;
	
		   case 'mail':
			   $vars['view'] = 'mail';
			   break;
	
			case 'admin':
				$vars['view'] = 'admin';
				if(count($segments)>1){
					if($segments[1] == "printer"){
						$vars['task'] = $segments[1];
						$vars['layout'] = 'default_prt';
						$vars['tmpl'] = 'component';
					}
				}
				break;
	
		   case 'admin_invoice':
				//  index.php?option=com_rsappt_pro3&controller=admin_invoice&task=create_invoice&frompage=advadmin
			   $vars['view'] = 'admin_invoice';
				$vars['task'] = $segments[1];
				if(count($segments)>2){
					$vars['frompage'] = $segments[2];
				}
			   break;
	
		   case 'payfail':
				//  index.php?option=com_rsappt_pro3&view=payfail
				break;
	
			case 'calendar_view';
				$vars['view'] = 'calendar_view';
			   break;
			
			case 'ajax':
				$vars['controller'] = 'ajax';
				$vars['task'] = $segments[1];
				break;				
			case 'json_x':
				$vars['controller'] = 'json_x';
				$vars['ts_date'] = $segments[1];
				$vars['res_id'] = $segments[2];
				break;				
			   
		}
		

//		for ($i = 0; $i < $total; $i++)
//		{
//			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
//		}
//
//		// View is always the first element of the array
//		$count = count($segments);
//
//		if ($count)
//		{
//			$count--;
//			$segment = array_shift($segments);
//
//			if (is_numeric($segment))
//			{
//				$vars['id'] = $segment;
//			}
//			else
//			{
//				$vars['task'] = $segment;
//			}
//		}
//
//		if ($count)
//		{
//			$segment = array_shift($segments);
//
//			if (is_numeric($segment))
//			{
//				$vars['id'] = $segment;
//			}
//		}

		return $vars;
	}
}
