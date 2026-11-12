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


// No direct access to this file
defined('_JEXEC') or die;
/**
 * rsappt_pro3 component helper.
 */
abstract class rsappt_pro3Helper extends JHelperContent
{
        /**
         * Configure the Linkbar.
         */
        public static function addSubmenu($submenu)
        {
			$doc = JFactory::getDocument();
//			if(strpos($doc->getTitle(),"cpanel")===false){
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.CONTROL_PANEL'),
                        'index.php?option=com_rsappt_pro3&controller=cpanel',
                        $submenu=='cpanel');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.CONFIGURE'),
                        'index.php?option=com_rsappt_pro3&controller=config_detail',
                        $submenu=='config');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.APPOINTMENTS'),
                        'index.php?option=com_rsappt_pro3&controller=requests',
                        $submenu=='requests');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.BOOKDATES'),
                        'index.php?option=com_rsappt_pro3&controller=book_dates',
                        $submenu=='book_dates');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.BOOK-OFFS'),
                        'index.php?option=com_rsappt_pro3&controller=bookoffs',
                        $submenu=='bookoffs');
				JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.CATEGORIES'),
                        'index.php?option=com_rsappt_pro3&controller=categories_abp',
                        $submenu=='categories');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_COUPONS'),
                        'index.php?option=com_rsappt_pro3&controller=coupons',
                        $submenu=='coupons');
//                JHtmlSidebar::addEntry(
//                        JText::_('RS1_ADMIN_MENU_EMAIL_MARKETING'),
//                        'index.php?option=com_rsappt_pro3&controller=email_marketing',
//                        $submenu=='email_marketing');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_EXTRAS'),
                        'index.php?option=com_rsappt_pro3&controller=extras',
                        $submenu=='extras');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_GIFT_CERT'),
                        'index.php?option=com_rsappt_pro3&controller=user_credit&gc=gc',
                        $submenu=='user_credit');
				JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_MAIL'),
                        'index.php?option=com_rsappt_pro3&controller=mail',
                        $submenu=='mail');							
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_PAYPROC'),
                        'index.php?option=com_rsappt_pro3&controller=payment_processors',
                        $submenu=='payment_processors');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_PRODUCTS'),
                        'index.php?option=com_rsappt_pro3&controller=products',
                        $submenu=='products');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_RATE_ADJUSTMENTS'),
                        'index.php?option=com_rsappt_pro3&controller=rate_adjustments',
                        $submenu=='rate_adjustments');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_RATE_OVERRIDES'),
                        'index.php?option=com_rsappt_pro3&controller=rate_overrides',
                        $submenu=='rate_overrides');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.RESOURCES'),
                        'index.php?option=com_rsappt_pro3&controller=resources',
                        $submenu=='resources');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_SEAT_ADJUSTMENTS'),
                        'index.php?option=com_rsappt_pro3&controller=seat_adjustments',
                        $submenu=='seat_adjustments');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_SEATS'),
                        'index.php?option=com_rsappt_pro3&controller=seat_types',
                        $submenu=='seat_types');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.SERVICES'),
                        'index.php?option=com_rsappt_pro3&controller=services',
                        $submenu=='services');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_SMSPROC'),
                        'index.php?option=com_rsappt_pro3&controller=sms_processors',
                        $submenu=='sms_processors');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.TIME_SLOTS'),
                        'index.php?option=com_rsappt_pro3&controller=timeslots',
                        $submenu=='timeslots');
                JHtmlSidebar::addEntry(
                        JText::_('COM_RSAPPT_PRO_SUBMENU.UDFS'),
                        'index.php?option=com_rsappt_pro3&controller=udfs',
                        $submenu=='udfs');
                JHtmlSidebar::addEntry(
                        JText::_('RS1_ADMIN_MENU_CREDIT'),
                        'index.php?option=com_rsappt_pro3&controller=user_credit',
                        $submenu=='user_credit');
//			}
        }
}
?>