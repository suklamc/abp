#
# Table structure for table #__sv_apptpro3_config
#

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_config` (
  `id_config` int(11) NOT NULL DEFAULT '1',
  `mailTO` varchar(255) NOT NULL DEFAULT '',
  `mailFROM` varchar(255) NOT NULL DEFAULT '',
  `mailSubject` varchar(80) NOT NULL DEFAULT '',
  `requireLogin` char(3) DEFAULT 'Yes',
  `headerText` text,
  `footerText` text,
  `which_calendar` varchar(20) DEFAULT NULL,
  `calendar_title` varchar(255) DEFAULT 'resource.name',
  `calendar_body` varchar(255) DEFAULT 'request.name',
  `calendar_body2` varchar(255) DEFAULT '[resource]<br>[requester name]',
  `prevent_dupe_bookings` char(3) DEFAULT 'Yes',
  `requireEmail` char(10) DEFAULT 'Yes',
  `requirePhone` char(10) DEFAULT 'No',
  `multiDay` varchar(12) DEFAULT 'Singleday',
  `allowSunday` char(3) DEFAULT 'Yes',
  `allowMonday` char(3) DEFAULT 'Yes',
  `allowTuesday` char(3) DEFAULT 'Yes',
  `allowWednesday` char(3) DEFAULT 'Yes',
  `allowThursday` char(3) DEFAULT 'Yes',
  `allowFriday` char(3) DEFAULT 'Yes',
  `allowSaturday` char(3) DEFAULT 'Yes',
  `hoursLimit` varchar(10) DEFAULT '24Hour',
  `timeRangeStart` varchar(20) DEFAULT NULL,
  `timeRangeEnd` varchar(20) DEFAULT NULL,
  `timeIncrement` varchar(20) DEFAULT 'Hour',
  `bookingIncrement` varchar(20) DEFAULT 'Range',
  `timeFormat` char(2) DEFAULT '12',
  `accept_when_paid` varchar(3) DEFAULT 'Yes',
  `enable_paypal` varchar(3) DEFAULT 'No',
  `additional_fee` decimal(10,2) DEFAULT '5.00',
  `fee_rate` varchar(20) DEFAULT 'Fixed',
  `paypal_button_url` varchar(255) DEFAULT 'https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif',
  `paypal_logo_url` varchar(255) DEFAULT '',
  `paypal_currency_code` varchar(6) DEFAULT 'CAD',
  `paypal_account` varchar(255) DEFAULT 'enter your account here',
  `paypal_sandbox_url` varchar(255) DEFAULT 'https://www.sandbox.paypal.com/us/cgi-bin/webscr',
  `paypal_use_sandbox` varchar(3) DEFAULT 'No',
  `paypal_production_url` varchar(255) DEFAULT 'https://www.paypal.com/us/cgi-bin/webscr',
  `paypal_identity_token` varchar(255) DEFAULT NULL,
  `html_email` varchar(3) DEFAULT 'Yes',
  `timeSlotMode` varchar(4) DEFAULT 'Yes',
  `booking_succeeded` text,
  `booking_succeeded_admin` text,
  `booking_succeeded_sms` varchar(255) DEFAULT '',
  `booking_in_progress` text,
  `booking_in_progress_admin` text,
  `booking_in_progress_sms` varchar(255) DEFAULT '',
  `booking_cancel` text,
  `booking_cancel_sms` varchar(255) DEFAULT '',
  `booking_too_close_to_cancel` text,
  `booking_reminder` text,
  `booking_reminder_sms` varchar(255) DEFAULT '',
  `auto_accept` varchar(4) DEFAULT 'No',
  `hide_logo` varchar(4) DEFAULT 'YEs',
  `allow_cancellation` varchar(4) DEFAULT 'No',
  `hours_before_cancel` varchar(4) DEFAULT '24',
  `use_div_calendar` varchar(4) DEFAULT 'Yes',
  `def_gad_grid_start` varchar(10) DEFAULT '8:00',
  `def_gad_grid_end` varchar(10) DEFAULT '17:00',
  `gad_grid_start_day` varchar(25) DEFAULT 'Tomorrow',
  `gad_grid_width` varchar(10) DEFAULT '650',
  `gad_grid_num_of_days` varchar(2) DEFAULT '7',
  `gad_name_width` varchar(10) DEFAULT '100',
  `gad_booked_image` varchar(255) DEFAULT './components/com_rsappt_pro3/publish_x.png',
  `gad_available_image` varchar(255) DEFAULT './components/com_rsappt_pro3/tick.png',
  `gad_date_format` varchar(20) DEFAULT '%a %d-%b-%Y',
  `gad_who_booked` varchar(20) DEFAULT 'No',
  `enable_clickatell` varchar(6) DEFAULT 'No',
  `clickatell_user` varchar(30) DEFAULT NULL,
  `clickatell_password` varchar(255) DEFAULT NULL,
  `clickatell_api_id` varchar(30) DEFAULT NULL,
  `clickatell_sender_id` varchar(30) DEFAULT NULL,
  `clickatell_dialing_code` varchar(10) DEFAULT '1',
  `clickatell_what_to_send` varchar(20) DEFAULT 'Reminders',
  `clickatell_show_code` varchar(6) DEFAULT 'No',
  `clickatell_enable_unicode` varchar(6) DEFAULT 'No',
  `gad_grid_start_day_days` int(11) DEFAULT '1',
  `google_user` varchar(255) DEFAULT NULL,
  `google_password` varchar(255) DEFAULT NULL,
  `google_default_calendar_name` varchar(255) DEFAULT NULL,
  `profile_cb_mapping` varchar(255) DEFAULT '',
  `profile_read_only` varchar(6) DEFAULT 'No',
  `name_cb_mapping` varchar(255) DEFAULT '',
  `name_read_only` varchar(6) DEFAULT 'No',
  `phone_profile_mapping` varchar(255) DEFAULT '',
  `phone_cb_mapping` varchar(255) DEFAULT '',
  `phone_read_only` varchar(6) DEFAULT 'No',
  `email_cb_mapping` varchar(255) DEFAULT '',
  `email_read_only` varchar(6) DEFAULT 'No',
  `daylight_savings_time` varchar(6) DEFAULT 'No',
  `popup_week_start_day` int(3) DEFAULT '0',
  `enable_coupons` char(3) DEFAULT 'No',
  `show_available_seats` varchar(6) DEFAULT 'No',
  `gad_grid_hide_startend` varchar(6) DEFAULT 'Yes',
  `limit_bookings` int(3) DEFAULT '0',
  `limit_bookings_days` int(11) DEFAULT '1',
  `activity_logging` varchar(6) DEFAULT 'No',
  `phone_js_mapping` varchar(255) DEFAULT '',
  `paypal_itemname` varchar(126) DEFAULT '[resource]: [startdate] [starttime]',
  `paypal_on0` varchar(64) DEFAULT NULL,
  `paypal_os0` varchar(200) DEFAULT NULL,
  `paypal_on1` varchar(64) DEFAULT NULL,
  `paypal_os1` varchar(200) DEFAULT NULL,
  `paypal_on2` varchar(64) DEFAULT NULL,
  `paypal_os2` varchar(200) DEFAULT NULL,
  `paypal_on3` varchar(64) DEFAULT NULL,
  `paypal_os3` varchar(200) DEFAULT NULL,
  `allow_user_credit_refunds` varchar(6) DEFAULT 'Yes',
  `use_gad2` varchar(6) DEFAULT 'No',
  `gad2_row_height` int(6) DEFAULT '40',
  `dst_start_date` date DEFAULT NULL,
  `dst_end_date` date DEFAULT NULL,
  `adv_admin_show_resources` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_services` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_timeslots` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_bookoffs` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_paypal` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_authnet` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_2co` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_coupons` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_extras` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_udfs` varchar(6) NOT NULL DEFAULT 'Yes',
  `adv_admin_show_user_credits` varchar(6) NOT NULL DEFAULT 'No',
  `adv_admin_show_rate_adj` varchar(6) NOT NULL DEFAULT 'No',
  `adv_admin_show_seat_adj` varchar(6) NOT NULL DEFAULT 'No',
  `adv_admin_show_book_dates` varchar(6) NOT NULL DEFAULT 'No',
  `attach_ics_resource` varchar(6) DEFAULT 'No',
  `attach_ics_admin` varchar(6) DEFAULT 'No',
  `attach_ics_customer` varchar(6) DEFAULT 'No',
  `purge_stale_paypal` varchar(6) DEFAULT 'No',
  `minutes_to_stale` int(11) DEFAULT '10',
  `enable_eztexting` varchar(11) DEFAULT 'No',
  `eztexting_user` varchar(30) DEFAULT NULL,
  `eztexting_password` varchar(255) DEFAULT NULL,
  `authnet_enable` varchar(30) NOT NULL DEFAULT 'No',
  `authnet_api_login_id` varchar(30) NOT NULL,
  `authnet_transaction_key` varchar(30) NOT NULL,
  `authnet_header_text` varchar(255) NOT NULL,
  `authnet_footer_text` varchar(255) NOT NULL,
  `authnet_button_url` varchar(255) NOT NULL,
  `_2co_enable` varchar(11) DEFAULT 'No',
  `_2co_account` varchar(30) DEFAULT NULL,
  `_2co_demo` varchar(11) DEFAULT 'Yes',
  `_2co_button_url` varchar(255) DEFAULT NULL,
  `_2co_item_name` varchar(255) DEFAULT NULL,
  `_2co_currency` varchar(11) DEFAULT 'USD',
  `non_pay_booking_button` varchar(6) DEFAULT 'No',
  `cal_position_method` smallint(5) NOT NULL DEFAULT '1',
  `site_access_code` varchar(20) DEFAULT '',
  `cart_enable` varchar(11) DEFAULT 'No',
  `cart_max_items` tinyint(3) DEFAULT '0',
  `cart_msg_header` text,
  `cart_msg_confirm` text,
  `cart_msg_inprogress` text,
  `cart_msg_footer` text,
  `cart_paypal_item` varchar(255) DEFAULT NULL,
  `admin_show_email` varchar(11) DEFAULT 'Yes',
  `admin_show_category` varchar(11) DEFAULT 'Yes',
  `admin_show_resource` varchar(11) DEFAULT 'Yes',
  `admin_show_service` varchar(11) DEFAULT 'Yes',
  `admin_show_seats` varchar(11) DEFAULT 'No',
  `admin_show_pay_id` varchar(11) DEFAULT 'No',
  `admin_show_pay_stat` varchar(11) DEFAULT 'Yes',
  `enable_overrides` varchar(11) DEFAULT 'No',
  `thank_you_msg` text,
  `send_on_status` varchar(255) DEFAULT 'completed',
  `rebook_msg` text,
  `enable_twilio` varchar(6) DEFAULT 'No',
  `twilio_sid` varchar(255) DEFAULT NULL,
  `twilio_token` varchar(255) DEFAULT NULL,
  `twilio_phone` varchar(255) DEFAULT NULL,
  `sms_to_resource_only` varchar(11) DEFAULT 'No',
  `mobile_show_simple` varchar(11) DEFAULT 'No',
  `date_picker_format` varchar(255) DEFAULT 'YYYY-MM-DD' COMMENT 'DD-MM-YYYY, MM-DD-YYYY',
  `use_jquery_tooltips` varchar(11) DEFAULT 'No',  
  `enable_eb_discount` varchar(11) DEFAULT 'No',
  `gap` smallint(5) DEFAULT '0',  
  `long_date_format` varchar(255) DEFAULT '%W %B %e',
  `staff_booking_in_the_past` varchar(11) DEFAULT '0',
  `block_new` varchar(11) DEFAULT 'No',
  `jit_submit` varchar(11) DEFAULT 'No',
  `status_quick_change` varchar(11) DEFAULT 'No',  
  `extras_on_request` varchar(11) DEFAULT 'No',  
  `ccinvoice_item_name` varchar(255) DEFAULT '',
  `ccinvoice_item_description` varchar(255),
  `enable_gift_cert` varchar(11) DEFAULT 'No',    
  `enable_ddslick` varchar(11) DEFAULT 'No',  
  `enable_auto_resource` varchar(11) DEFAULT 'No',  
  `auto_resource_groups` varchar(255) DEFAULT '|2|' COMMENT 'Default Registered',
  `auto_resource_category` varchar(255) DEFAULT '',
  `expand_timeslots` varchar(11) DEFAULT 'No',
  `adv_admin_show_credits` varchar(11) DEFAULT 'No',
  `recaptcha_enabled` varchar(11) DEFAULT 'No',
  `non_pay_btn_group` varchar(255) DEFAULT '',
  `sms_use_phone` varchar(11) DEFAULT 'No',
  `sms_confirmation` varchar(11) DEFAULT 'No',
  `sms_phone_profile_mapping` varchar(255) DEFAULT NULL,
  `sms_phone_cb_mapping` varchar(255) DEFAULT NULL,
  `sms_phone_js_mapping` varchar(255) DEFAULT NULL,  
  `sms_phone_size` smallint(6) DEFAULT '10',
  `enable_notification_list` varchar(11) DEFAULT 'No',
  `show_services_first` varchar(11) DEFAULT 'No',
  `checked_out` smallint(5) DEFAULT NULL,  
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_config
#
INSERT INTO `#__sv_apptpro3_config` 
VALUES (1,
'your.email@goes.here.com',
'your_from.email@goes.here.com',
'Appointment Booking',
'No',
'Enter header text here - clear for no header',
'Enter footer text here - clear for no footer',
'None',
'resource.name',
'resource.name',
'',
'Yes',
'No',
'No',
'Multiday',
'Yes',
'Yes',
'Yes',
'Yes',
'Yes',
'Yes',
'Yes',
'Range',
'8',
'17',
'HalfHour',
'Range',
'12',
'Yes',
'No',
0,
'Fixed',
'https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif',
'',
'CAD',
'your paypal account here',
'https://www.sandbox.paypal.com/us/cgi-bin/webscr',
'Yes',
'https://www.paypal.com/us/cgi-bin/webscr',
'',
'Yes',
'Yes',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'Yes',
'No',
'No',
'4',
'Yes',
'8:00',
'17:00',
'Tomorrow',
'-1',
'7',
'100',
'publish_x.png',
'tick.png',
'%a %d-%b-%Y',
'No',
'No',
'',
'',
'',
'',
'1',
'Reminders',
'No',
'No',
2,
'',
'',
'',
'',
'No',
'',
'No',
'',
'',
'No',
'',
'No',
'Yes',
0,
'No',
'No',
'Yes',
0,
1,
'Off',
'',
'[resource]: [startdate] [starttime]',
'',
'',
'',
'',
'',
'',
'',
'',
'Yes',
'No',
50,
'2010-04-25',
'2010-10-10',
'Yes',
'Yes',
'Yes',
'Yes',
'No',
'No',
'No',
'Yes',
'Yes',
'Yes',
'No',
'No',
'No',
'No',
'No',
'No',
'No',
'No',
10,
'No',
'',
'',
'No',
'',
'',
'',
'',
'http://www.authorize.net/resources/images/merchants/products/buy_now_blue.gif',
'No',
'',
'Yes',
'https://www2.2checkout.com/static/checkout/CheckoutButton2COCards.gif',
'[resource]: [startdate] [starttime]',
'USD',
'No',
1,
'',
'No',
'0',
'<span style=\"font-family: arial,helvetica,sans-serif;\">[cart header here]</span>',
'<span style=\"font-family: arial,helvetica,sans-serif;\">New Booking: [requester name] for [resource], [startdate] [starttime], cost $[booking_total]</span>',
'<span style=\"font-family: arial,helvetica,sans-serif;\">Request awaiting approval: [requester name] for [resource], [startdate] [starttime]</span>',
'<span style=\"font-family: arial,helvetica,sans-serif;\">Cart total is $[cart_total]</span>',
'Appointment booking cart.',
'Yes',
'Yes',
'Yes',
'Yes',
'No',
'No',
'Yes',
'No',
'Thank You for your business.',
'completed',
'Time to re-book!',
'No',
'',
'',
'',
'No',
'No',
'YYYY-MM-DD',
'No',
'No',
0,
'%W %B %e',
0,
'No',
'Yes',
'No',
'No',
'[resource] booking',
'[resource], booked for [requester name] for : [startdate] [starttime] to [endtime]',
'No',
'No',
'No',
'|2|',
'',
'No',
'No',
'No',
'',
'No',
'No',
'','','',10,
'No',
'No',
NULL,
NULL,
0,
1);



#
# Table structure for table #__sv_apptpro3_errorlog
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_errorlog` (
  `id_errorlog` int(11) NOT NULL AUTO_INCREMENT,
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `sql_data` varchar(255) DEFAULT NULL,
  `err_object` varchar(252) DEFAULT NULL,
  `err_severity` varchar(25) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_errorlog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


#
# Table structure for table #__sv_apptpro3_paypal_transactions
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_paypal_transactions` (
  `id_paypal_transactions` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL DEFAULT '',
  `lastname` varchar(100) NOT NULL DEFAULT '',
  `buyer_email` varchar(127) NOT NULL DEFAULT '',
  `street` varchar(200) NOT NULL DEFAULT '',
  `city` varchar(40) NOT NULL DEFAULT '',
  `state` char(40) NOT NULL DEFAULT '',
  `zipcode` varchar(20) NOT NULL DEFAULT '',
  `memo` varchar(255) DEFAULT NULL,
  `itemname` varchar(255) DEFAULT NULL,
  `itemnumber` varchar(127) DEFAULT NULL,
  `os0` varchar(20) DEFAULT NULL,
  `on0` varchar(50) DEFAULT NULL,
  `os1` varchar(20) DEFAULT NULL,
  `on1` varchar(50) DEFAULT NULL,
  `quantity` char(3) DEFAULT NULL,
  `paymentdate` varchar(50) NOT NULL DEFAULT '',
  `paymenttype` varchar(10) NOT NULL DEFAULT '',
  `txnid` varchar(30) NOT NULL DEFAULT '',
  `mc_gross` varchar(6) NOT NULL DEFAULT '',
  `mc_fee` varchar(5) NOT NULL DEFAULT '',
  `paymentstatus` varchar(15) NOT NULL DEFAULT '',
  `pendingreason` varchar(10) DEFAULT NULL,
  `txntype` varchar(10) NOT NULL DEFAULT '',
  `tax` varchar(10) DEFAULT NULL,
  `mc_currency` varchar(5) NOT NULL DEFAULT '',
  `reasoncode` varchar(20) NOT NULL DEFAULT '',
  `custom` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(20) NOT NULL DEFAULT '',
  `datecreation` date NOT NULL DEFAULT '0000-00-00',
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_paypal_transactions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


#
# Table structure for table #__sv_apptpro3_pp_currency
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_pp_currency` (
  `Id` int(11) NOT NULL auto_increment,
  `code` varchar(3) default NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_pp_currency
#
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (1,'AUD','Australian Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (2,'CAD','Canadian Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (3,'CHF','Swiss Franc');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (4,'CZK','Czech Koruna');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (5,'DKK','Danish Krone');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (6,'EUR','Euro');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (7,'GBP','Pound Sterling');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (8,'HKD','Hong Kong Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (9,'HUF','Hungarian Forint');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (10,'JPY','Japanese Yen');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (11,'NOK','Norwegian Krone');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (12,'NZD','New Zealand Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (13,'PLN','Polish Zloty');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (14,'SEK','Swedish Krona');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (15,'SGD','Singapore Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (16,'USD','U.S. Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (17,'ILS','Israeli New Sheqel');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (18,'MXN','Mexican Peso');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (19,'BRL','Brazilian Real');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (20,'MYR','Malaysian Ringgit');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (21,'PHP','Philippine Peso');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (22,'TWD','Taiwan New Dollar');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (23,'THB','Thai Baht');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (24,'RUB','Russian Ruble');
INSERT INTO `#__sv_apptpro3_pp_currency` VALUES (25,'TRY','Turkish Lira');

#
# Table structure for table #__sv_apptpro3_requests
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_requests` (
  `id_requests` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `resource` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT '',
  `startdate` date DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `admin_comment` varchar(255) DEFAULT NULL,
  `request_status` varchar(20) DEFAULT 'new',
  `payment_status` varchar(20) DEFAULT 'pending',
  `show_on_calendar` char(3) NOT NULL DEFAULT '1',
  `calendar_comment` varchar(200) NOT NULL DEFAULT '',
  `calendar_category` int(11) DEFAULT NULL,
  `calendar_calendar` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cancellation_id` varchar(255) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `txnid` varchar(255) DEFAULT NULL,
  `sms_reminders` varchar(10) DEFAULT 'No',
  `sms_phone` varchar(50) DEFAULT NULL,
  `sms_dial_code` varchar(4) DEFAULT '1',
  `google_event_id` varchar(255) DEFAULT '',
  `google_calendar_id` varchar(255) DEFAULT '',
  `booking_total` decimal(10,2) DEFAULT '0.00',
  `booking_deposit` decimal(10,2) DEFAULT '0.00',
  `booking_due` decimal(10,2) DEFAULT '0.00',
  `coupon_code` varchar(255) DEFAULT NULL,
  `booked_seats` int(11) DEFAULT '1',
  `booking_language` varchar(25) DEFAULT 'en-gb',
  `credit_used` float(10,2) DEFAULT '0.00',
  `payment_processor_used` varchar(30) DEFAULT 'None',
  `manual_payment_collected` float(10,2) DEFAULT '0.00',
  `last_change_operator` int(11) DEFAULT NULL,  
  `invoice_number` varchar(255) DEFAULT '',
  `gift_cert` varchar(255) DEFAULT '',
  `cancel_reason` varchar(255) DEFAULT '',
  `loyalty_points` int(11) DEFAULT 0,  
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_requests`),
  KEY `startdate` (`startdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Table structure for table #__sv_apptpro3_resources
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_resources` (
  `id_resources` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `category_scope` varchar(255) DEFAULT '',
  `mail_id` int(11) DEFAULT 1,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `cost` varchar(40) DEFAULT NULL,
  `resource_email` varchar(200) DEFAULT NULL,
  `prevent_dupe_bookings` varchar(10) DEFAULT 'Global',
  `max_dupes` int(11) DEFAULT '0',
  `resource_admins` varchar(255) DEFAULT '',
  `rate` decimal(10,2) DEFAULT '0.00',
  `rate_unit` varchar(25) DEFAULT 'Flat',
  `default_calendar_category` varchar(50) DEFAULT 'General',
  `default_calendar` varchar(50) DEFAULT 'Default',
  `allowSunday` char(3) DEFAULT 'Yes',
  `allowMonday` char(3) DEFAULT 'Yes',
  `allowTuesday` char(3) DEFAULT 'Yes',
  `allowWednesday` char(3) DEFAULT 'Yes',
  `allowThursday` char(3) DEFAULT 'Yes',
  `allowFriday` char(3) DEFAULT 'Yes',
  `allowSaturday` char(3) DEFAULT 'Yes',
  `timeslots` varchar(10) DEFAULT 'Global',
  `disable_dates_before` varchar(30) DEFAULT 'Tomorrow',
  `disable_dates_before_days` int(11) DEFAULT '0',
  `min_lead_time` smallint(5) DEFAULT '1',
  `disable_dates_after` varchar(30) DEFAULT 'Not Set',
  `disable_dates_after_days` int(11) DEFAULT '0',
  `sms_phone` varchar(30) DEFAULT '',
  `google_user` varchar(255) DEFAULT NULL,
  `google_password` varchar(255) DEFAULT NULL,
  `google_default_calendar_name` varchar(255) DEFAULT NULL,
  `access` varchar(50) DEFAULT 'everyone',
  `enable_coupons` char(3) DEFAULT 'No',
  `max_seats` int(11) DEFAULT '1',
  `non_work_day_message` varchar(255) DEFAULT '',
  `non_work_day_hide` varchar(6) DEFAULT 'Yes',
  `paypal_account` varchar(50) DEFAULT '',
  `auto_accept` varchar(20) DEFAULT 'Global',
  `deposit_only` varchar(6) DEFAULT 'No',
  `deposit_amount` decimal(10,2) DEFAULT '0.00',
  `deposit_unit` varchar(25) DEFAULT 'Flat' COMMENT 'Flat or Percent',
  `resource_eb_discount` decimal(10,2) DEFAULT '0.00',
  `resource_eb_discount_unit` varchar(25) DEFAULT 'Flat' COMMENT 'Flat or Percent',
  `resource_eb_discount_lead` smallint(6) DEFAULT '7' COMMENT 'days',   
  `gap` smallint(5) DEFAULT '0',
  `mailchimp_list_id` varchar(255) DEFAULT '',
  `acymailing_list_id` varchar(255) DEFAULT NULL,
  `google_client_id` varchar(255) DEFAULT NULL,
  `google_app_name` varchar(255) DEFAULT NULL,
  `google_app_email_address` varchar(255) DEFAULT NULL,
  `google_p12_key_filename` varchar(255) DEFAULT NULL,
  `ddslick_image_path` varchar(255) DEFAULT NULL,
  `ddslick_image_text` varchar(255) DEFAULT NULL,
  `show_image_in_grid` varchar(11) DEFAULT 'No',
  `free_booking` varchar(11) DEFAULT 'No',
  `date_specific_booking` varchar(11) DEFAULT 'No',
  `res_user_drag_duration_enable` varchar(11) DEFAULT 'No',
  `res_user_drag_duration_snap` smallint(6) DEFAULT '30',
  `res_stripe_sk` varchar(255) DEFAULT NULL,
  `res_stripe_pk` varchar(255) DEFAULT NULL,
  `checked_out` int(10) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_resources`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Table structure for table #__sv_apptpro3_timeslots
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_timeslots` (
  `id_timeslots` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) DEFAULT NULL,
  `day_number` int(11) DEFAULT NULL,
  `timeslot_starttime` time DEFAULT NULL,
  `timeslot_endtime` time DEFAULT NULL,
  `timeslot_description` varchar(255) DEFAULT '',
  `staff_only` varchar(6) DEFAULT 'No',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '0',
  `start_publishing` date DEFAULT NULL,
  `end_publishing` date DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  PRIMARY KEY (`id_timeslots`),
  KEY `start_publish` (`start_publishing`),
  KEY `end_publish` (`end_publishing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_timeslots
#
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (1,0,1,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (2,0,1,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (3,0,1,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (4,0,1,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (5,0,1,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (6,0,1,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (7,0,1,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (9,0,2,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (10,0,2,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (11,0,2,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (12,0,2,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (13,0,2,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (14,0,2,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (15,0,2,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (16,0,3,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (17,0,3,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (18,0,3,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (19,0,3,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (20,0,3,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (21,0,3,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (22,0,3,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (23,0,4,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (24,0,4,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (25,0,4,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (26,0,4,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (27,0,4,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (28,0,4,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (29,0,4,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (30,0,5,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (31,0,5,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (32,0,5,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (33,0,5,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (34,0,5,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (35,0,5,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (36,0,5,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (37,0,6,'08:00:00','09:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (38,0,6,'09:00:00','10:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (39,0,6,'10:00:00','11:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (40,0,6,'11:00:00','12:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (41,0,6,'13:00:00','14:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (42,0,6,'14:00:00','15:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (43,0,6,'15:00:00','16:00:00','',NULL,NULL,1,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (44,0,0,'08:00:00','09:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (45,0,0,'09:00:00','10:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (46,0,0,'10:00:00','11:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (47,0,0,'11:00:00','12:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (48,0,0,'13:00:00','14:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (49,0,0,'14:00:00','15:00:00','',NULL,NULL,0,NULL,NULL,0);
INSERT INTO `#__sv_apptpro3_timeslots` (`id_timeslots`,`resource_id`,`day_number`,`timeslot_starttime`,`timeslot_endtime`,`timeslot_description`,`checked_out`,`checked_out_time`,`published`,`start_publishing`,`end_publishing`,`ordering`) VALUES (50,0,0,'15:00:00','16:00:00','',NULL,NULL,0,NULL,NULL,0);


#
# Table structure for table #__sv_apptpro3_tokens
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_tokens` (
  `Id` int(11) NOT NULL auto_increment,
  `token_text` varchar(50) default NULL,
  `db_field` varchar(100) default NULL,
  `db_table` varchar(50) default NULL,
  `description` varchar(100) default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_tokens
#
INSERT INTO `#__sv_apptpro3_tokens` VALUES (1,'[resource]','name','resources','name of the resource being booked');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (2,'[requester name]','name','requests',' the name of the requester');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (3,'[startdate]','startdate','requests','start date of the booking');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (4,'[starttime]','starttime','requests','start time of the booking');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (5,'[enddate]','enddate','requests','end date of the booking');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (6,'[endtime]','endtime','requests','end time of the booking');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (7,'[phone]','phone','requests','the phone number of the requester');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (8,'[email]','email','requests','the email address of the requester');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (17,'[cancellation_id]','cancellation_id','requests','the system generated cancellation_id');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (18,'[resource_category]','name','categories','the resource category');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (19,'[resource_service]','name','services','the resource service');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (20,'[sms_phone]','sms_phone','requests','customers sms phone');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (21,'[booking_total]','booking_total','requests','booking total cost');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (22,'[booking_due]','booking_due','requests','booking amount due');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (23,'[coupon]','coupon_code','requests','coupon used for booking');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (24,'[booking_id]','id_requests','requests','booking request id');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (25,'[admin_comment]','admin_comment','requests','Admin comment');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (26,'[booked_seats]','booked_seats','requests','total seats booked');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (27,'[username]','username','users','Joomla users username of booking user');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (28,'[user_email]','email','users','Joomla users email for user');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (29,'[user_name]','name','users','Joomla users name');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (30,'[booking_deposit]','booking_deposit','requests','booking deposit paid');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (31,'[manual_payment_collected]','manual_payment_collected','requests','manual payment collected');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (32,'[cancel_reason]','cancel_reason','requests','reason for cancellation');
INSERT INTO `#__sv_apptpro3_tokens` VALUES (33,'[res_id]','resource','requests','resource id');


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_bookoffs` (
  `id_bookoffs` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) DEFAULT NULL,
  `description` varchar(100) DEFAULT 'Day Off',
  `off_date` date DEFAULT NULL,
  `full_day` varchar(6) DEFAULT 'Yes',
  `bookoff_starttime` time DEFAULT NULL,
  `bookoff_endtime` time DEFAULT NULL,
  `rolling_bookoff` varchar(50) DEFAULT 'No',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_bookoffs`),
  KEY `off_date` (`off_date`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_categories` (
  `id_categories` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent_category` int(11) DEFAULT NULL,
  `group_scope` varchar(255) DEFAULT '|1|',
  `mail_id` int(11) DEFAULT 1,
  `category_duration` int(11) DEFAULT '0',
  `category_duration_unit` varchar(25) DEFAULT 'Minute',
  `ddslick_image_path` varchar(255) DEFAULT NULL,
  `ddslick_image_text` varchar(255) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_categories`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_services` (
  `id_services` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) DEFAULT NULL,
  `resource_scope` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `service_duration` int(11) DEFAULT '0',
  `service_duration_unit` varchar(25) DEFAULT 'Minute',
  `service_rate` decimal(10,2) DEFAULT '0.00',
  `service_rate_unit` varchar(25) DEFAULT 'Hour',
  `service_fee` decimal(10,2) DEFAULT '0.00',
  `staff_only` varchar(6) DEFAULT 'No',
  `service_eb_discount` decimal(10,2) DEFAULT '0.00',
  `service_eb_discount_unit` varchar(25) DEFAULT 'Flat' COMMENT 'Flat or Percent',
  `service_eb_discount_lead` smallint(6) DEFAULT '7' COMMENT 'days', 
  `ddslick_image_path` varchar(255) DEFAULT NULL,
  `ddslick_image_text` varchar(255) DEFAULT NULL,
  `category_scope` varchar(255) DEFAULT '',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_services`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


#
# Table structure for table #__sv_apptpro3_udfs
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_udfs` (
  `id_udfs` int(11) NOT NULL AUTO_INCREMENT,
  `udf_label` varchar(40) DEFAULT NULL,
  `udf_type` varchar(10) NOT NULL DEFAULT 'Textbox',
  `udf_size` varchar(3) DEFAULT '40',
  `udf_rows` varchar(3) DEFAULT '2',
  `udf_cols` varchar(3) DEFAULT '40',
  `udf_radio_options` varchar(255) DEFAULT NULL,
  `udf_required` varchar(4) DEFAULT 'No',
  `udf_tooltip` varchar(255) DEFAULT NULL,
  `udf_help` varchar(255) DEFAULT NULL,
  `udf_content` text,
  `udf_show_on_screen` varchar(6) DEFAULT 'Yes',
  `profile_mapping` varchar(255) DEFAULT NULL,
  `profile_read_only` varchar(6) DEFAULT 'No',
  `cb_mapping` varchar(255) DEFAULT '',
  `read_only` varchar(4) DEFAULT 'No',
  `js_mapping` varchar(255) DEFAULT '',
  `js_read_only` varchar(6) DEFAULT 'No',
  `scope` varchar(255) DEFAULT '',
  `staff_only` varchar(10) DEFAULT 'No',
  `udf_help_as_icon` varchar(11) DEFAULT 'Yes',
  `udf_help_format` varchar(11) DEFAULT 'Text',
  `udf_placeholder_text` varchar(255) DEFAULT '',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_udfs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_udfs
#
INSERT INTO `#__sv_apptpro3_udfs` VALUES (1,'Comments','Textarea','40','2','40','','No','Max 255 characters','','','Yes','','No','','No','','No','','No','Yes','Text','Enter your comments here.',NULL,NULL,10,0);

#
# Table structure for table #__sv_apptpro3_udfvalues
#
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_udfvalues` (
  `id` int(11) NOT NULL auto_increment,
  `udf_id` int(11) default NULL,
  `request_id` int(11) default NULL,
  `udf_value` TEXT,
  PRIMARY KEY  (`id`),
  KEY `request_id` (`request_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_dialing_codes` (
  `id` int(11) NOT NULL auto_increment,
  `country` varchar(255) default NULL,
  `dial_code` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_dialing_codes
#
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (1,'Afganistan','93');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (2,'Albania','355');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (3,'Algeria','213');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (4,'Andorra','376');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (5,'Angola','244');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (6,'Antilles Netherland','599');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (7,'Antiqua','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (8,'Argentina','54');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (9,'Armenia','374');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (10,'Aruba','297');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (11,'Australia','61');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (12,'Austria','43');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (13,'Azerbaijan','994');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (14,'Bahamas','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (15,'Bahrain','973');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (16,'Bangladesh','880');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (17,'Barbados','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (18,'Belarus','375');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (19,'Belgium','32');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (20,'Belize','501');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (21,'Benin','229');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (22,'Bermuda','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (23,'Bhutan','975');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (24,'Bolivia','591');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (25,'Bosnia Herzegovina','387');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (26,'Botswana','267');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (27,'Brazil','55');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (28,'British Virgin','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (29,'Brunei Darussalam','673');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (30,'Bulgaria','359');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (31,'Burkina Faso','226');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (32,'Cameroon','237');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (33,'Canada','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (34,'Canary Islands','0');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (35,'Cayman Islands','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (36,'Central African','236');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (37,'Chad','235');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (38,'Chile','56');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (39,'China','86');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (40,'Chinese Taipei','0');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (41,'Colombia','57');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (42,'Congo Republic','242');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (43,'Cook Islands','682');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (44,'Costa Rica','506');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (45,'Croatia','385');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (46,'Cuba','53');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (47,'Cyprus','357');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (48,'Czech Republic','420');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (49,'Denmark','45');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (50,'Djibouti','253');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (51,'Dominican Republic','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (52,'Ecuador','593');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (53,'Egypt','20');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (54,'El Salvador','503');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (55,'Equatorial Guinea','240');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (56,'Estonia','372');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (57,'Ethiopia','251');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (58,'Faeroe Islands','298');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (59,'Fiji','679');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (60,'Finland','358');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (61,'France','33');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (62,'French Guiana','594');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (63,'Gabon Republic','241');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (64,'Georgia','995');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (65,'Germany','49');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (66,'Ghana','233');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (67,'Gibraltar','350');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (68,'Greece','30');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (69,'Greenland','299');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (70,'Grenada','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (71,'Guadeloupe','590');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (72,'Guatemala','502');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (73,'Guinea (PRP)','224');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (74,'Guinea - Bissau','245');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (75,'Guyana','592');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (76,'Haiti','509');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (77,'Honduras','504');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (78,'Hong Kong','852');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (79,'Hungary','36');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (80,'Iceland','354');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (81,'India','91');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (82,'Indonesia','62');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (83,'Iran','98');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (84,'Iraq','964');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (85,'Ireland','353');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (86,'Israel','972');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (87,'Italy','39');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (88,'Ivory Coast','225');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (89,'Jamaica','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (90,'Japan','81');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (91,'Jordan','962');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (92,'Kazakhstan','7');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (93,'Kenya','254');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (94,'Kuwait','965');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (95,'Kyrghyzstan','996');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (96,'Laos','856');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (97,'Latvia','371');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (98,'Lebanon','961');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (99,'Lesotho','266');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (100,'Liberia','231');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (101,'Libya','218');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (102,'Liechtenstein','423');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (103,'Lithuania','370');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (104,'Luxembourg','352');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (105,'Macau','853');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (106,'Macedonia','389');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (107,'Madagascar','261');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (108,'Malawi','265');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (109,'Malaysia','60');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (110,'Maldives','960');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (111,'Mali','223');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (112,'Malta','356');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (113,'Martinique','596');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (114,'Mauritania','222');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (115,'Mauritius','230');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (116,'Mexico','52');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (117,'Moldova','373');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (118,'Monaco','377');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (119,'Mongolia','976');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (120,'Montenegro','381');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (121,'Morocco','212');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (122,'Mozambique','258');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (123,'Namibia','264');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (124,'Nauru','674');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (125,'Nepal','977');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (126,'Netherlands','31');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (127,'New Caledonia','687');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (128,'New Zealand','64');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (129,'Nicaragua','505');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (130,'Niger','227');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (131,'Nigeria','234');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (132,'North Korea','850');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (133,'Norway','47');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (134,'Oman','968');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (135,'Pakistan','92');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (136,'Panama','507');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (137,'Paraguay','595');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (138,'Peru','51');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (139,'Philippines','63');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (140,'Poland','48');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (141,'Portugal','351');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (142,'Qatar','974');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (143,'Reunion Is.','262');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (144,'Romania','40');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (145,'Russia','7');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (146,'Rwanda','250');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (147,'Samoa','685');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (148,'San Marino','378');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (149,'Saudi Arabia','966');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (150,'Senegal','221');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (151,'Serbia','381');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (152,'Seychelles','248');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (153,'Sierra Leone','232');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (154,'Singapore','65');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (155,'Slovakia','421');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (156,'Slovenia','386');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (157,'Solomon Islands','677');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (158,'Somalia','252');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (159,'South Africa','27');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (160,'South Africa','27');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (161,'South Korea','82');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (162,'Spain','34');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (163,'Sri Lanka','94');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (164,'St. Kitts','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (165,'St. Lucia','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (166,'St. Pierre','508');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (167,'St. Vincent','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (168,'Sudan','249');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (169,'Surinam','597');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (170,'Swaziland','268');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (171,'Sweden','46');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (172,'Switzerland','41');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (173,'Syria','963');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (174,'Tadjikistan','992');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (175,'Tahiti','689');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (176,'Taiwan ROC','886');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (177,'Tanzania','255');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (178,'Thailand','66');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (179,'Togo','228');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (180,'Tonga','676');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (181,'Trinidad','868');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (182,'Tunisia','216');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (183,'Turkey','90');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (184,'Uganda','256');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (185,'Ukraine','380');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (186,'United Arab Emirates','971');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (187,'United Kingdom','44');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (188,'Uruguay','598');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (189,'USA','1');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (190,'Vanuatu','678');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (191,'Vatican City','39');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (192,'Venezuela','58');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (193,'Vietnam','84');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (194,'Yemen','967');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (195,'Yugoslavia','11');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (196,'Zaire','243');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (197,'Zambia','260');
INSERT INTO `#__sv_apptpro3_dialing_codes` VALUES (198,'Zimbabwe','263');


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_reminderlog` (
  `id_reminderlog` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `local_time` varchar(50) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_reminderlog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_coupons` (
  `id_coupons` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT 'Discount Coupon',
  `coupon_code` varchar(255) DEFAULT NULL,
  `discount` float DEFAULT '10',
  `discount_unit` varchar(25) DEFAULT 'percent',
  `max_total_use` int(11) DEFAULT '0',
  `max_user_use` int(11) DEFAULT '0',
  `expiry_date` datetime DEFAULT NULL,
  `scope` varchar(255) DEFAULT '',
  `valid_range_start` date DEFAULT NULL,
  `valid_range_end` date DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_coupons`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_seat_types` (
  `id_seat_types` int(11) NOT NULL AUTO_INCREMENT,
  `seat_type_label` varchar(40) DEFAULT NULL,
  `seat_type_tooltip` varchar(255) DEFAULT '',
  `seat_type_cost` decimal(10,2) DEFAULT '0.00',
  `default_quantity` tinyint(3) DEFAULT '1',
  `seat_type_help` varchar(255) DEFAULT NULL,
  `seat_group` varchar(6) DEFAULT 'No',
  `seat_group_max` int(11) DEFAULT '5',
  `scope` varchar(255) DEFAULT '',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_seat_types`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_seat_types` (`id_seat_types`,`seat_type_label`,`seat_type_tooltip`,`seat_type_cost`,`default_quantity`,`seat_type_help`,`seat_group`,`seat_group_max`,`scope`,`checked_out`,`checked_out_time`,`ordering`,`published`) VALUES (1,'Adult','Select the number of Adult seats you wish to order.',10,0,'$10.00 (whatever text you want here ;-)','No',10,'',NULL,NULL,1,0);
INSERT INTO `#__sv_apptpro3_seat_types` (`id_seat_types`,`seat_type_label`,`seat_type_tooltip`,`seat_type_cost`,`default_quantity`,`seat_type_help`,`seat_group`,`seat_group_max`,`scope`,`checked_out`,`checked_out_time`,`ordering`,`published`) VALUES (2,'Youth','Select the number of Youth seats you wish to order.',5,0,'$ 5.00 (12-18 yrs)','No',10,'',NULL,NULL,2,0);
INSERT INTO `#__sv_apptpro3_seat_types` (`id_seat_types`,`seat_type_label`,`seat_type_tooltip`,`seat_type_cost`,`default_quantity`,`seat_type_help`,`seat_group`,`seat_group_max`,`scope`,`checked_out`,`checked_out_time`,`ordering`,`published`) VALUES (3,'Family','Select total seats required to a maximum of 5',25,0,'&nbsp;&nbsp;$25.00 (Select total seats required)','Yes',5,'',NULL,NULL,4,0);
INSERT INTO `#__sv_apptpro3_seat_types` (`id_seat_types`,`seat_type_label`,`seat_type_tooltip`,`seat_type_cost`,`default_quantity`,`seat_type_help`,`seat_group`,`seat_group_max`,`scope`,`checked_out`,`checked_out_time`,`ordering`,`published`) VALUES (4,'Large Group','Select total seats required to a maximum of 20',75,0,'$75.00 (Select total seats required)','Yes',20,'',NULL,NULL,5,0);
INSERT INTO `#__sv_apptpro3_seat_types` (`id_seat_types`,`seat_type_label`,`seat_type_tooltip`,`seat_type_cost`,`default_quantity`,`seat_type_help`,`seat_group`,`seat_group_max`,`scope`,`checked_out`,`checked_out_time`,`ordering`,`published`) VALUES (5,'Child','Select the number of Child seats you wish to order.',0,0,'Free (under 12 yrs - must be accompanied by an Adult)','No',10,'',NULL,NULL,3,0);


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_seat_counts` (
  `id` int(11) NOT NULL auto_increment,
  `seat_type_id` int(11) default NULL,
  `request_id` int(11) default NULL,
  `seat_type_qty` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_extras` (
  `id_extras` int(11) NOT NULL AUTO_INCREMENT,
  `extras_label` varchar(40) DEFAULT '',
  `extras_tooltip` varchar(255) DEFAULT '',
  `extras_cost` decimal(10,2) DEFAULT '0.00',
  `cost_unit` varchar(25) DEFAULT 'Hour',
  `default_quantity` tinyint(3) DEFAULT '1',
  `min_quantity` tinyint(3) DEFAULT '0',
  `max_quantity` tinyint(3) DEFAULT '1',
  `extras_help` varchar(255) DEFAULT '',
  `resource_scope` varchar(255) DEFAULT '',
  `service_scope` varchar(255) DEFAULT '',
  `show_as_checkbox` varchar(6) DEFAULT 'No',
  `extras_duration` int(11) DEFAULT '0',
  `extras_duration_unit` varchar(25) DEFAULT 'Minute',
  `extras_duration_effect` varchar(20) DEFAULT 'PerUnit' COMMENT 'PerUnit or PerBooking',
  `staff_only` varchar(6) DEFAULT 'No',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_extras`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_extras_data` (
  `id` int(11) NOT NULL auto_increment,
  `extras_id` int(11) default NULL,
  `request_id` int(11) default NULL,
  `extras_qty` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_activitylog` (
  `id_activitylog` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `local_time` varchar(50) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_activitylog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_user_credit` (
  `id_user_credit` int(11) NOT NULL AUTO_INCREMENT,
  `credit_type` varchar(11) DEFAULT 'uc',
  `user_id` int(11) DEFAULT NULL,
  `gift_cert` varchar(255) DEFAULT NULL,
  `gift_cert_name` varchar(255) DEFAULT NULL,
  `balance` float(10,2) DEFAULT '0.00',
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_user_credit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_user_credit_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `gift_cert` varchar(255) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `increase` float(10,2) DEFAULT NULL,
  `decrease` float(10,2) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `balance` float(10,2) DEFAULT '0.00',
  `operator_id` int(11) DEFAULT NULL,
  `stamp` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(50) DEFAULT NULL,
  `internal_value` varchar(30) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Dumping data for table #__sv_apptpro3_status
#
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (2,'RS1_ADMIN_SCRN_REQUEST_STATUS_PENDING','pending',2);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (3,'RS1_ADMIN_SCRN_REQUEST_STATUS_ACCEPTED','accepted',3);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (4,'RS1_ADMIN_SCRN_REQUEST_STATUS_CANCELED','canceled',4);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (5,'RS1_ADMIN_SCRN_REQUEST_STATUS_DELETED','deleted',5);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (6,'RS1_ADMIN_SCRN_REQUEST_STATUS_COMPLETED','completed',6);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (7,'RS1_ADMIN_SCRN_REQUEST_STATUS_DECLINED','declined',7);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (8,'RS1_ADMIN_SCRN_REQUEST_STATUS_NO_SHOW','no_show',8);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (9,'RS1_ADMIN_SCRN_REQUEST_STATUS_ATTENDED','attended',9);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (10,'RS1_ADMIN_SCRN_REQUEST_STATUS_PAYPAL_TIMEOUT','timeout',10);
INSERT INTO `#__sv_apptpro3_status` (`id`,`status`,`internal_value`,`ordering`) VALUES (11,'RS1_ADMIN_SCRN_REQUEST_STATUS_NEW','new',1);

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_authnet_transactions` (
  `id_authnet_transactions` int(11) NOT NULL AUTO_INCREMENT,
  `x_response_code` varchar(20) DEFAULT NULL,
  `x_response_subcode` varchar(20) DEFAULT NULL,
  `x_response_reason_code` varchar(20) DEFAULT NULL,
  `x_response_reason_text` varchar(200) NOT NULL DEFAULT '',
  `x_auth_code` varchar(20) DEFAULT NULL,
  `x_avs_code` varchar(20) DEFAULT NULL,
  `x_trans_id` varchar(100) NOT NULL DEFAULT '',
  `x_invoice_num` varchar(100) NOT NULL DEFAULT '',
  `x_description` varchar(200) NOT NULL DEFAULT '',
  `x_amount` varchar(10) DEFAULT NULL,
  `x_method` varchar(10) DEFAULT NULL,
  `x_type` varchar(20) DEFAULT NULL,
  `x_cust_id` varchar(20) DEFAULT NULL,  
  `x_first_name` varchar(100) NOT NULL DEFAULT '',
  `x_last_name` varchar(100) NOT NULL DEFAULT '',
  `x_company` varchar(100) NOT NULL DEFAULT '',
  `x_address` varchar(200) NOT NULL DEFAULT '',
  `x_city` varchar(40) NOT NULL DEFAULT '',
  `x_state` varchar(40) NOT NULL DEFAULT '',
  `x_zip` varchar(20) NOT NULL DEFAULT '',
  `x_country` varchar(40) NOT NULL DEFAULT '',
  `x_phone` varchar(40) NOT NULL DEFAULT '',
  `x_fax` varchar(40) NOT NULL DEFAULT '',
  `x_email` varchar(127) NOT NULL DEFAULT '',
  `x_tax` varchar(10) NOT NULL DEFAULT '',
  `x_duty` varchar(10) NOT NULL DEFAULT '',
  `x_freight` varchar(10) NOT NULL DEFAULT '',
  `x_tax_exempt` varchar(10) NOT NULL DEFAULT '',
  `x_po_num` varchar(10) NOT NULL DEFAULT '',
  `x_MD5_Hash` varchar(40) NOT NULL DEFAULT '',
  `x_cavv_response` varchar(10) NOT NULL DEFAULT '',
  `x_test_request` varchar(10) NOT NULL DEFAULT '',
  `x_subscription_id` varchar(10) NOT NULL DEFAULT '',
  `x_subscription_paynum` varchar(10) NOT NULL DEFAULT '',
  `x_cim_profile_id` varchar(20) NOT NULL DEFAULT '',
  `datecreation` date NOT NULL DEFAULT '0000-00-00',
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_authnet_transactions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3__2co_transactions` (
  `id__2co_transactions` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_order_id` varchar(30) NOT NULL DEFAULT '',
  `order_number` varchar(30) NOT NULL DEFAULT '',
  `invoice_id` varchar(30) NOT NULL DEFAULT '',
  `li_1_type` varchar(30) NOT NULL DEFAULT '',
  `li_1_name` varchar(30) NOT NULL DEFAULT '',
  `li_1_description` varchar(30) NOT NULL DEFAULT '',
  `li_1_price` varchar(30) NOT NULL DEFAULT '',
  `li_1_quantity` varchar(30) NOT NULL DEFAULT '',
  `total` varchar(30) NOT NULL DEFAULT '',
  `first_name` varchar(30) NOT NULL DEFAULT '',
  `last_name` varchar(30) NOT NULL DEFAULT '',
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL DEFAULT '',
  `street_address` varchar(30) NOT NULL DEFAULT '',
  `street_address2` varchar(30) NOT NULL DEFAULT '',
  `city` varchar(30) NOT NULL DEFAULT '',
  `state` varchar(30) NOT NULL DEFAULT '',
  `country` varchar(30) NOT NULL DEFAULT '',
  `zip` varchar(30) NOT NULL DEFAULT '',
  `ip_country` varchar(30) NOT NULL DEFAULT '',
  `lang` varchar(30) NOT NULL DEFAULT '',
  `pay_method` varchar(30) NOT NULL DEFAULT '',
  `card_holder_name` varchar(30) NOT NULL DEFAULT '',
  `credit_card_processed` varchar(30) NOT NULL DEFAULT '',
  `demo` varchar(30) NOT NULL DEFAULT '',
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id__2co_transactions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_cart` (
  `id_row_cart` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `item_total` decimal(10,2) DEFAULT '0.00',
  `coupon_code` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_row_cart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_rate_overrides` (
  `id_rate_overrides` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'resource, service, extra, seat',
  `entity_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `rate_override` decimal(10,2) DEFAULT NULL,
  `rate_unit_override` varchar(25) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_rate_overrides`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_mail` (
  `id_mail` int(11) NOT NULL AUTO_INCREMENT,
  `secured` smallint(5) DEFAULT NULL,
  `mail_label` varchar(40) DEFAULT '',
  `booking_succeeded` text,
  `booking_succeeded_admin` text,
  `booking_succeeded_sms` varchar(160) DEFAULT '',
  `booking_succeeded_sms_customer` varchar(160) DEFAULT '',
  `booking_in_progress` text,
  `booking_in_progress_admin` text,
  `booking_in_progress_sms` varchar(160) DEFAULT '',
  `booking_cancel` text,
  `booking_cancel_sms` varchar(160) DEFAULT '',
  `booking_too_close_to_cancel` text,
  `booking_reminder` text,
  `booking_reminder_sms` varchar(160) DEFAULT '',
  `attach_ics_resource` varchar(6) DEFAULT 'No',
  `attach_ics_admin` varchar(6) DEFAULT 'No',
  `attach_ics_customer` varchar(6) DEFAULT 'No',
  `thank_you_msg` text,
  `send_on_status` varchar(255) DEFAULT 'completed',
  `rebook_msg` text,
  `birthday_msg` text,
  `confirmation_attachment` varchar(255),
  `notification_msg` text,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



#
# Dumping data for table #__sv_apptpro3_mail
#
INSERT INTO `#__sv_apptpro3_mail` 
VALUES (1,
1,
'Global',
'<p><strong><span style=\"font-family: arial,helvetica,sans-serif;\">Thank you for your order.</span></strong></p>\r\n<p><span style=\"font-family: arial,helvetica,sans-serif;\"><strong>[resource]</strong>,  has been booked for <em>[requester name]</em> for this<em> <em>date/time</em>:</em></span><span style=\"font-family: arial,helvetica,sans-serif;\"> [startdate] [starttime] to [endtime]</span></p>\r\n<p><span style=\"font-family: arial,helvetica,sans-serif;\"><br /></span></p>',
'<p><strong><span style=\"font-family: arial,helvetica,sans-serif;\">New Booking.</span></strong></p>\r\n<p><span style=\"font-family: arial,helvetica,sans-serif;\"><strong>[resource]</strong>,  has been booked for <em>[requester name]</em> for this<em> <em>date/time</em>:</em></span><span style=\"font-family: arial,helvetica,sans-serif;\"> [startdate] [starttime] to [endtime]</span></p>\r\n<p><span style=\"font-family: arial,helvetica,sans-serif;\"><br /></span></p>',
'New Booking: [requester name] for [resource], [startdate] [starttime] to [endtime]',
'New Booking: [requester name] for [resource], [startdate] [starttime] to [endtime]',
'<p>Thank you, [requester name].</p>\r\n<p>Your request will be reviewed shortly.</p>',
'<p>New order, id = [booking_id].</p>\r\n<p>Request requires review.</p>',
'New Booking Request: [requester name] for [resource], [startdate] [starttime]',
'<p><span style=\"font-family: arial,helvetica,sans-serif;\">Booking Cancellation: [requester name] for [resource], [startdate] [starttime]</span></p>',
'Booking Cancellation: [requester name] for [resource], [startdate] [starttime]',
'<p>Cancellation is only accepted up to <span style=\"color: #ff0000;\">24 hours</span> before your booking.</p>',
'<p><span style=\"font-family: arial,helvetica,sans-serif;\">Just a Reminder.<br /><strong>[resource]</strong>, has been booked for [requester name] for this <em>date/time</em>:<br /> [startdate] [starttime] to [endtime]</span></p>\r\n<p><span style=\"font-family: arial,helvetica,sans-serif;\">Your Cancellation ID is <span style=\"color: red;\">[cancellation_id]</span><br /> To cancel your booking enter this code into the <span style=\"background-color: #ffff99;\">booking screen</span>.</span></p>',
'Reminder: [resource] has been booked for [requester name] for: [startdate] [starttime] to [endtime]',
'No',
'No',
'No',
'Thank You for your business.',
'completed',
'Time to re-book!',
'<span style=\"font-family: arial,helvetica,sans-serif;\">Happy (almost here) Birthday!<br /><br />We would like to help you celebrate by offering a %20 appointment discount on your birthday.<br /><br />This coupon is valid for %20 off appointments booked on [birthday].<br /><br />Or if you have set a range of date the coupon is valid for use something like..<br />This coupon is valid for %20 off appointments booked between [range_start] and [range_end]<br /><br /><b>[birthday_coupon]</b><br /><br />Thanks<br /><br /> </span>',
'',
'<span style="font-family: arial,helvetica,sans-serif;">This is a courtesy message sent to you because an appointment time you had wanted has become available. <br />Please note, the appointment time is not held and you are under no obligation to take this appointment.<br /></span><br /><span style="font-family: arial,helvetica,sans-serif;">The appointment you asked to be notified about is:</span><br /><span style="font-family: arial,helvetica,sans-serif;">[resource]</span><br /><span style="font-family: arial,helvetica,sans-serif;">[startdate] [starttime]</span><br /><br /><span style="font-family: arial,helvetica,sans-serif;">The link below will take you to the appointment booking screen..</span> <br /><span style="font-family: arial,helvetica,sans-serif;">Click here to </span><a href="http://demo.appointmentbookingpro.com/abpro-gad"><span style="font-family: arial,helvetica,sans-serif;"> Book</span><br /></a> <br />[Go into HTML view to add link to your booking screen.]',
NULL,
NULL,
0,
1);

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_payment_processors` (
  `id_payment_processors` int(11) NOT NULL AUTO_INCREMENT,
  `processor_name` varchar(255) DEFAULT '',
  `prefix` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `config_table` varchar(255) DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '0',
  `ordering` smallint(5) DEFAULT '0',
  PRIMARY KEY (`id_payment_processors`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (1,'PayPal','paypal','RS1_ADMIN_PAYMENT_PROCESSORS_PAYPAL_TAB','paypal_settings',1,1);
INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (3,'Authorize.net','authnet','RS1_ADMIN_AUTHNET_TAB','authnet_settings',0,3);
INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (4,'Authrize.net (AIM)','authnet_aim','RS1_ADMIN_AUTHNET_AIM_TAB','authnet_aim_settings',0,4);
INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (5,'2Checkout.com','_2co','RS1_ADMIN_2CO_TAB','_2co_settings',0,5);
INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (6,'Payage','payage','RS1_ADMIN_PAYAGE','payage_settings',0,6);
INSERT INTO `#__sv_apptpro3_payment_processors` VALUES (7,'Stripe','stripe','RS1_ADMIN_STRIPE','stripe_settings',0,7);

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_paypal_settings` (
  `id_paypal_settings` int(11) NOT NULL AUTO_INCREMENT,
  `paypal_enable` varchar(3) DEFAULT 'No',
  `paypal_button_url` varchar(255) DEFAULT 'https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif',
  `paypal_logo_url` varchar(255) DEFAULT '',
  `paypal_currency_code` varchar(6) DEFAULT 'CAD',
  `paypal_account` varchar(255) DEFAULT 'enter your account here',
  `paypal_sandbox_url` varchar(255) DEFAULT 'https://www.sandbox.paypal.com/us/cgi-bin/webscr',
  `paypal_use_sandbox` varchar(3) DEFAULT 'No',
  `paypal_production_url` varchar(255) DEFAULT 'https://www.paypal.com/us/cgi-bin/webscr',
  `paypal_itemname` varchar(126) DEFAULT '[resource]: [startdate] [starttime]',
  `paypal_on0` varchar(64) DEFAULT NULL,
  `paypal_os0` varchar(200) DEFAULT NULL,
  `paypal_on1` varchar(64) DEFAULT NULL,
  `paypal_os1` varchar(200) DEFAULT NULL,
  `paypal_on2` varchar(64) DEFAULT NULL,
  `paypal_os2` varchar(200) DEFAULT NULL,
  `paypal_on3` varchar(64) DEFAULT NULL,
  `paypal_os3` varchar(200) DEFAULT NULL,
  `paypal_show_trans_in_fe` varchar(10) DEFAULT 'No' COMMENT 'show transactions in front end admin',
  PRIMARY KEY (`id_paypal_settings`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_paypal_settings` VALUES (1,'No','https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif','','CAD','','https://www.sandbox.paypal.com/us/cgi-bin/webscr','Yes','https://www.paypal.com/us/cgi-bin/webscr','','','','','','','','','','No');


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_authnet_settings` (
  `id_authnet_settings` int(11) NOT NULL AUTO_INCREMENT,
  `authnet_enable` varchar(30) NOT NULL DEFAULT 'No',
  `authnet_server` varchar(255) DEFAULT 'None',
  `authnet_api_login_id` varchar(30) NOT NULL,
  `authnet_transaction_key` varchar(30) NOT NULL,
  `authnet_header_text` varchar(255) NOT NULL,
  `authnet_footer_text` varchar(255) NOT NULL,
  `authnet_button_url` varchar(255) NOT NULL,
  `authnet_show_trans_in_fe` varchar(10) DEFAULT 'No' COMMENT 'show transactions in front end admin',
  PRIMARY KEY (`id_authnet_settings`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_authnet_settings` VALUES (1,'No','Test','','','[header here]','[footer here]','http://www.authorize.net/resources/images/merchants/products/buy_now_blue.gif','No');


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3__2co_settings` (
  `id_2co_settings` int(11) NOT NULL AUTO_INCREMENT,
  `_2co_enable` varchar(11) DEFAULT 'No',
  `_2co_account` varchar(30) DEFAULT NULL,
  `_2co_demo` varchar(11) DEFAULT 'Yes',
  `_2co_button_url` varchar(255) DEFAULT NULL,
  `_2co_item_name` varchar(255) DEFAULT NULL,
  `_2co_show_trans_in_fe` varchar(10) DEFAULT 'No' COMMENT 'show transactions in front end admin',
  PRIMARY KEY (`id_2co_settings`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3__2co_settings` VALUES (1,'No','','Yes','https://www2.2checkout.com/static/checkout/CheckoutButton2COCards.gif','','No');

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_authnet_aim_settings` (
  `id_authnet_aim_settings` int(11) NOT NULL AUTO_INCREMENT,
  `authnet_aim_enable` varchar(30) NOT NULL DEFAULT 'No',
  `authnet_aim_server` varchar(255) DEFAULT 'None',
  `authnet_aim_api_login_id` varchar(30) NOT NULL,
  `authnet_aim_transaction_key` varchar(30) NOT NULL,
  `authnet_aim_header_text` varchar(255) NOT NULL,
  `authnet_aim_footer_text` varchar(255) NOT NULL,
  `authnet_aim_button_url` varchar(255) NOT NULL,
  `authnet_aim_show_trans_in_fe` varchar(10) DEFAULT 'No' COMMENT 'show transactions in front end admin',
  PRIMARY KEY (`id_authnet_aim_settings`)
) ENGINE=MyIsam  DEFAULT CHARSET=utf8;

INSERT INTO `#__sv_apptpro3_authnet_aim_settings` VALUES (1,'No','Test','','','','','http://www.authorize.net/resources/images/merchants/products/buy_now_blue.gif','No');

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_authnet_aim_transactions` (
  `id_authnet_aim_transactions` int(11) NOT NULL AUTO_INCREMENT,
  `x_response_code` varchar(20) DEFAULT NULL,
  `x_response_subcode` varchar(20) DEFAULT NULL,
  `x_response_reason_code` varchar(20) DEFAULT NULL,
  `x_response_reason_text` varchar(200) NOT NULL DEFAULT '',
  `x_auth_code` varchar(20) DEFAULT NULL,
  `x_avs_code` varchar(20) DEFAULT NULL,
  `x_trans_id` varchar(100) NOT NULL DEFAULT '',
  `x_invoice_num` varchar(100) NOT NULL DEFAULT '',
  `x_description` varchar(200) NOT NULL DEFAULT '',
  `x_amount` varchar(10) DEFAULT NULL,
  `x_method` varchar(10) DEFAULT NULL,
  `x_type` varchar(20) DEFAULT NULL,
  `x_cust_id` varchar(20) DEFAULT NULL,
  `x_first_name` varchar(100) NOT NULL DEFAULT '',
  `x_last_name` varchar(100) NOT NULL DEFAULT '',
  `x_company` varchar(100) NOT NULL DEFAULT '',
  `x_address` varchar(200) NOT NULL DEFAULT '',
  `x_city` varchar(40) NOT NULL DEFAULT '',
  `x_state` varchar(40) NOT NULL DEFAULT '',
  `x_zip` varchar(20) NOT NULL DEFAULT '',
  `x_country` varchar(40) NOT NULL DEFAULT '',
  `x_phone` varchar(40) NOT NULL DEFAULT '',
  `x_fax` varchar(40) NOT NULL DEFAULT '',
  `x_email` varchar(127) NOT NULL DEFAULT '',
  `x_tax` varchar(10) NOT NULL DEFAULT '',
  `x_duty` varchar(10) NOT NULL DEFAULT '',
  `x_freight` varchar(10) NOT NULL DEFAULT '',
  `x_tax_exempt` varchar(10) NOT NULL DEFAULT '',
  `x_po_num` varchar(10) NOT NULL DEFAULT '',
  `x_MD5_Hash` varchar(40) NOT NULL DEFAULT '',
  `x_cavv_response` varchar(10) NOT NULL DEFAULT '',
  `x_test_request` varchar(10) NOT NULL DEFAULT '',
  `x_subscription_id` varchar(10) NOT NULL DEFAULT '',
  `x_subscription_paynum` varchar(10) NOT NULL DEFAULT '',
  `x_cim_profile_id` varchar(20) NOT NULL DEFAULT '',
  `datecreation` date NOT NULL DEFAULT '0000-00-00',
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_authnet_aim_transactions`)
) ENGINE=MyIsam  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_rate_adjustments` (
  `id_rate_adjustments` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'resource, service, extra, seat',
  `entity_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `rate_adjustment` decimal(10,2) DEFAULT NULL,
  `rate_adjustment_unit` varchar(25) DEFAULT 'Percent' COMMENT 'Percent/Flat',
  `by_day_time` varchar(11) DEFAULT 'DayOnly' COMMENT 'DayOnly/TimeOnly/DayAndTime',
  `adjustSunday` char(3) DEFAULT 'No',
  `adjustMonday` char(3) DEFAULT 'No',
  `adjustTuesday` char(3) DEFAULT 'No',
  `adjustWednesday` char(3) DEFAULT 'No',
  `adjustThursday` char(3) DEFAULT 'No',
  `adjustFriday` char(3) DEFAULT 'No',
  `adjustSaturday` char(3) DEFAULT 'No',
  `timeRangeStart` time DEFAULT NULL,
  `timeRangeEnd` time DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `start_publishing` date DEFAULT NULL,
  `end_publishing` date DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_rate_adjustments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_email_marketing` (
  `id_email_marketing` int(11) NOT NULL AUTO_INCREMENT,
  `mailchimp_enable` varchar(9) DEFAULT 'No',
  `mailchimp_api_key` varchar(255) DEFAULT NULL,
  `mailchimp_split_name` varchar(9) DEFAULT 'Yes',
  `mailchimp_default_list_id` varchar(255) DEFAULT NULL,
  `mailchimp_update_existing` bit(1) DEFAULT b'0',
  `mailchimp_send_welcome` tinyint(3) DEFAULT '0',
  `acymailing_enable` varchar(9) DEFAULT 'No',
  `acymailing_default_list_id` varchar(255) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_email_marketing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_email_marketing` VALUES (1,'No','','Yes','',b'0',0,'No','',NULL,NULL,0,0);

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_seat_adjustments` (
  `id_seat_adjustments` int(11) NOT NULL AUTO_INCREMENT,
  `id_resources` int(11) NOT NULL DEFAULT '0',
  `seat_adjustment` tinyint(3) DEFAULT '0',
  `by_day_time` varchar(11) DEFAULT 'DayOnly' COMMENT 'DayOnly/TimeOnly/DayAndTime',
  `adjustSunday` char(3) DEFAULT 'No',
  `adjustMonday` char(3) DEFAULT 'No',
  `adjustTuesday` char(3) DEFAULT 'No',
  `adjustWednesday` char(3) DEFAULT 'No',
  `adjustThursday` char(3) DEFAULT 'No',
  `adjustFriday` char(3) DEFAULT 'No',
  `adjustSaturday` char(3) DEFAULT 'No',
  `timeRangeStart` time DEFAULT NULL,
  `timeRangeEnd` time DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `start_publishing` date DEFAULT NULL,
  `end_publishing` date DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_seat_adjustments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_payment_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(50) DEFAULT NULL,
  `internal_value` varchar(30) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_payment_status` VALUES (1,'RS1_ADMIN_SCRN_PAY_STATUS_PENDING','pending',1); 
INSERT INTO `#__sv_apptpro3_payment_status` VALUES (2,'RS1_ADMIN_SCRN_PAY_STATUS_PAID','paid',2);
INSERT INTO `#__sv_apptpro3_payment_status` VALUES (3,'RS1_ADMIN_SCRN_PAY_STATUS_INVOICED','invoiced',3);
INSERT INTO `#__sv_apptpro3_payment_status` VALUES (4,'RS1_ADMIN_SCRN_PAY_STATUS_REFUNDED','refunded',4);
INSERT INTO `#__sv_apptpro3_payment_status` VALUES (5,'RS1_ADMIN_SCRN_PAY_STATUS_NA','na',0);
 
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_payage_settings` (
  `id_payage_settings` int(11) NOT NULL AUTO_INCREMENT,
  `payage_enable` varchar(11) DEFAULT 'No',
  `payage_group` int(11) DEFAULT NULL,
  `payage_currency` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id_payage_settings`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__sv_apptpro3_payage_settings` VALUES (1,'No',1,'USD');
 
CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_products` (
  `id_products` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) DEFAULT NULL,
  `product_sku` varchar(255) DEFAULT NULL,
  `product_desc` varchar(255) DEFAULT NULL,
  `product_type` varchar(4) DEFAULT 'UC' COMMENT 'UC or GC',
  `product_price` decimal(6,2) DEFAULT '0.00' COMMENT 'What the customer pays',
  `product_value` decimal(6,2) DEFAULT NULL COMMENT 'Amount to add to UC or GC',
  `product_group` varchar(255) DEFAULT '2' COMMENT 'Joomla Group eligible for this',
  `product_image_path` varchar(255) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id_products`)
) ENGINE=MyIsam AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `#__sv_apptpro3_products` VALUES (1,'$100 Credit Package','123456-7','$100 in booking credits for $75','UC',75,100,'2',NULL,NULL,NULL,3,0);
INSERT INTO `#__sv_apptpro3_products` VALUES (2,'$50 Credit Package','123456-8','$50 in booking credit for $40','UC',40,50,'2',NULL,NULL,NULL,2,0);
INSERT INTO `#__sv_apptpro3_products` VALUES (3,'$25 Credit Package','123456-9','$25 in booking credit for $20','UC',20,25,'2',NULL,NULL,NULL,1,0);



CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_notification_list` (
  `id_notification_list` int(11) NOT NULL AUTO_INCREMENT,
  `resource` int(11) NOT NULL DEFAULT '0',
  `booking_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_notification_list`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_book_dates` (
  `id_book_dates` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) DEFAULT NULL,
  `book_date` date DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT '1',
  PRIMARY KEY (`id_book_dates`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_stripe_settings` (
  `id_stripe_settings` int(11) NOT NULL AUTO_INCREMENT,
  `stripe_enable` varchar(255) DEFAULT 'No',
  `stripe_sk` varchar(255) DEFAULT NULL,
  `stripe_pk` varchar(255) DEFAULT NULL,
  `stripe_company_name` varchar(255) DEFAULT NULL,
  `stripe_billing_description` varchar(255) DEFAULT 'Appointment Booking',
  `stripe_button_image` varchar(255) DEFAULT '\\payment_processors\\stripe\\stripe_01.jpg',
  `stripe_image` varchar(255) DEFAULT 'https://stripe.com/img/documentation/checkout/marketplace.png',
  `stripe_currency` varchar(255) DEFAULT 'CAD',
  `stripe_show_trans_in_fe` varchar(10) DEFAULT 'No' COMMENT 'show transactions in front end admin',
  PRIMARY KEY (`id_stripe_settings`)
) ENGINE=MyIsam AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


INSERT INTO `#__sv_apptpro3_stripe_settings` VALUES (1,'No','[your secret key]','[your public key]','[your company name]','Appointment Booking','','https://stripe.com/img/documentation/checkout/marketplace.png','CAD','No');


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_stripe_currency` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Data for table "#__sv_apptpro3_stripe_currency"
#

INSERT INTO `#__sv_apptpro3_stripe_currency` VALUES (4,'ALL','Albanian Lek'),(5,'ANG','Netherlands Antillean Gulden'),(6,'ARS','Argentine Peso'),(7,'AUD','Australian Dollar'),(8,'AWG','Aruban Florin'),(9,'BBD','Barbadian Dollar'),(10,'BDT','Bangladeshi Taka'),(11,'BIF','Burundian Franc'),(12,'BMD','Bermudian Dollar'),(13,'BND','Brunei Dollar'),(14,'BOB','Bolivian Boliviano'),(15,'BRL','Brazilian Real'),(16,'BSD','Bahamian Dollar'),(17,'BWP','Botswana Pula'),(18,'BZD','Belize Dollar'),(19,'CAD','Canadian Dollar'),(20,'CHF','Swiss Franc'),(21,'CLP','Chilean Peso'),(22,'CNY','Chinese Renminbi Yuan'),(23,'COP','Colombian Peso'),(24,'CRC','Costa Rican ColÃ³n'),(25,'CVE','Cape Verdean Escudo'),(26,'CZK','Czech Koruna'),(27,'DJF','Djiboutian Franc'),(28,'DKK','Danish Krone'),(29,'DOP','Dominican Peso'),(30,'DZD','Algerian Dinar'),(31,'EGP','Egyptian Pound'),(32,'ETB','Ethiopian Birr'),(33,'EUR','Euro'),(34,'FJD','Fijian Dollar'),(35,'FKP','Falkland Islands Pound'),(36,'GBP','British Pound'),(37,'GIP','Gibraltar Pound'),(38,'GMD','Gambian Dalasi'),(39,'GNF','Guinean Franc'),(40,'GTQ','Guatemalan Quetzal'),(41,'GYD','Guyanese Dollar'),(42,'HKD','Hong Kong Dollar'),(43,'HNL','Honduran Lempira'),(44,'HRK','Croatian Kuna'),(45,'HTG','Haitian Gourde'),(46,'HUF','Hungarian Forint'),(47,'IDR','Indonesian Rupiah'),(48,'ILS','Israeli New Sheqel'),(49,'INR','Indian Rupee'),(50,'ISK','Icelandic KrÃ³na'),(51,'JMD','Jamaican Dollar'),(52,'JPY','Japanese Yen'),(53,'KES','Kenyan Shilling'),(54,'KHR','Cambodian Riel'),(55,'KMF','Comorian Franc'),(56,'KRW','South Korean Won'),(57,'KYD','Cayman Islands Dollar'),(58,'KZT','Kazakhstani Tenge'),(59,'LAK','Lao Kip'),(60,'LBP','Lebanese Pound'),(61,'LKR','Sri Lankan Rupee'),(62,'LRD','Liberian Dollar'),(63,'MAD','Moroccan Dirham'),(64,'MDL','Moldovan Leu'),(65,'MNT','Mongolian TÃ¶grÃ¶g'),(66,'MOP','Macanese Pataca'),(67,'MRO','Mauritanian Ouguiya'),(68,'MUR','Mauritian Rupee'),(69,'MVR','Maldivian Rufiyaa'),(70,'MWK','Malawian Kwacha'),(71,'MXN','Mexican Peso'),(72,'MYR','Malaysian Ringgit'),(73,'NAD','Namibian Dollar'),(74,'NGN','Nigerian Naira'),(75,'NIO','Nicaraguan CÃ³rdoba'),(76,'NOK','Norwegian Krone'),(77,'NPR','Nepalese Rupee'),(78,'NZD','New Zealand Dollar'),(79,'PAB','Panamanian Balboa'),(80,'PEN','Peruvian Nuevo Sol'),(81,'PGK','Papua New Guinean Kina'),(82,'PHP','Philippine Peso'),(83,'PKR','Pakistani Rupee'),(84,'PLN','Polish ZÅ‚oty'),(85,'PYG','Paraguayan GuaranÃ­'),(86,'QAR','Qatari Riyal'),(87,'RUB','Russian Ruble'),(88,'SAR','Saudi Riyal'),(89,'SBD','Solomon Islands Dollar'),(90,'SCR','Seychellois Rupee'),(91,'SEK','Swedish Krona'),(92,'SGD','Singapore Dollar'),(93,'SHP','Saint Helenian Pound'),(94,'SLL','Sierra Leonean Leone'),(95,'SOS','Somali Shilling'),(96,'STD','SÃ£o TomÃ© and PrÃ­ncipe Dobra'),(97,'SVC','Salvadoran ColÃ³n'),(98,'SZL','Swazi Lilangeni'),(99,'THB','Thai Baht'),(100,'TOP','Tongan PaÊ»anga'),(101,'TTD','Trinidad and Tobago Dollar'),(102,'TWD','New Taiwan Dollar'),(103,'TZS','Tanzanian Shilling'),(104,'UAH','Ukrainian Hryvnia'),(105,'UGX','Ugandan Shilling'),(106,'USD','United States Dollar'),(107,'UYU','Uruguayan Peso'),(108,'UZS','Uzbekistani Som'),(109,'VND','Vietnamese'),(110,'VUV','Vanuatu Vatu'),(111,'WST','Samoan Tala'),(112,'XAF','Central African Cfa Franc'),(113,'XOF','West African Cfa Franc'),(114,'XPF','Cfp Franc'),(115,'YER','Yemeni Rial'),(116,'ZAR','South African Rand');

CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_stripe_transactions` (
  `id_stripe_transactions` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `cart` varchar(20) DEFAULT 'No',
  `stripe_txn_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL COMMENT 'Stripe stores amount as int',
  `currency` varchar(20) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `seller_message` varchar(255) DEFAULT NULL,
  `card_brand` varchar(255) DEFAULT NULL,
  `card_country` varchar(255) DEFAULT NULL,
  `card_last4` varchar(20) DEFAULT NULL,
  `card_exp_month` smallint(3) DEFAULT NULL,
  `card_exp_year` smallint(6) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out` smallint(5) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` smallint(5) DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT '1',
  PRIMARY KEY (`id_stripe_transactions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_export_columns` (
  `id_export_columns` int(11) NOT NULL AUTO_INCREMENT,
  `export_column_type` varchar(255) DEFAULT NULL COMMENT 'core, udf, extra, seat',
  `export_table` varchar(255) DEFAULT NULL,
  `export_field` varchar(255) DEFAULT NULL,
  `export_format` varchar(255) DEFAULT NULL,
  `export_header` varchar(255) DEFAULT NULL,
  `export_order` smallint(6) DEFAULT NULL,
  `export_foreign_key` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_export_columns`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

#
# Data for table "#__sv_apptpro3_export_columns"
#

INSERT INTO `#__sv_apptpro3_export_columns` VALUES (1,'core','sv_apptpro3_requests','id_requests',NULL,'Booking ID',1,NULL),(2,'core','sv_apptpro3_requests','name',NULL,'Name',2,NULL),(3,'core','sv_apptpro3_requests','email',NULL,'Email',3,NULL),(4,'core','sv_apptpro3_requests','phone',NULL,'Phone',4,NULL),(5,'core','sv_apptpro3_requests','startdate','%c-%b-%Y','Date',5,NULL),(6,'core','sv_apptpro3_requests','starttime','%I:%i %p','Start',6,NULL),(7,'core','sv_apptpro3_requests','endtime','%I:%i %p','End',7,NULL),(8,'core','sv_apptpro3_requests','request_status',NULL,'Status',8,NULL),(9,'core','sv_apptpro3_requests','payment_status',NULL,'Payment',9,NULL),(10,'core','sv_apptpro3_requests','booking_total',NULL,'Total',10,NULL),(11,'core','sv_apptpro3_requests','booking_due',NULL,NULL,11,NULL),(12,'core','sv_apptpro3_resources','name',NULL,'Resource',13,NULL),(13,'core','sv_apptpro3_categories','name',NULL,'Category',14,NULL),(14,'core','sv_apptpro3_services','name',NULL,'Service',15,NULL),(15,'core','sv_apptpro3_requests','booked_seats',NULL,'Booked Seats',12,NULL),(16,'core','sv_apptpro3_resources','rate',NULL,'Rate',16,NULL);
