#
# Table changes for table ABPro
#

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
) ENGINE=MyIsam DEFAULT CHARSET=utf8;

#
# Data for table "#__sv_apptpro3_export_columns"
#

INSERT IGNORE INTO `#__sv_apptpro3_export_columns` VALUES (1,'core','sv_apptpro3_requests','id_requests',NULL,'Booking ID',1,NULL),(2,'core','sv_apptpro3_requests','name',NULL,'Name',2,NULL),(3,'core','sv_apptpro3_requests','email',NULL,'Email',3,NULL),(4,'core','sv_apptpro3_requests','phone',NULL,'Phone',4,NULL),(5,'core','sv_apptpro3_requests','startdate','%c-%b-%Y','Date',5,NULL),(6,'core','sv_apptpro3_requests','starttime','%I:%i %p','Start',6,NULL),(7,'core','sv_apptpro3_requests','endtime','%I:%i %p','End',7,NULL),(8,'core','sv_apptpro3_requests','request_status',NULL,'Status',8,NULL),(9,'core','sv_apptpro3_requests','payment_status',NULL,'Payment',9,NULL),(10,'core','sv_apptpro3_requests','booking_total',NULL,'Total',10,NULL),(11,'core','sv_apptpro3_requests','booking_due',NULL,NULL,11,NULL),(12,'core','sv_apptpro3_resources','name',NULL,'Resource',13,NULL),(13,'core','sv_apptpro3_categories','name',NULL,'Category',14,NULL),(14,'core','sv_apptpro3_services','name',NULL,'Service',15,NULL),(15,'core','sv_apptpro3_requests','booked_seats',NULL,'Booked Seats',12,NULL),(16,'core','sv_apptpro3_resources','rate',NULL,'Rate',16,NULL);

