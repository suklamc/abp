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
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class com_rsappt_pro3InstallerScript
{

	public static $languageFiles = array('en-GB.com_rsappt_pro3.ini');

	private $installType = null;

	/**
	 * Method to run before installing the component. Using to backup language file in this case
	 */
	function preflight($type, $parent)
	{//
//		// Installing component manifest file version
//		$this->release = $parent->getManifest()->version;
//		if($type == 'update'){
//			$xml_path = JPATH_ADMINISTRATOR . '/components/com_rsappt_pro3/rsappt_pro3.xml';
//			try{		
//				$xml_obj = new SimpleXMLElement(file_get_contents($xml_path));
//				$rel = strval($xml_obj->version); 
//				if(version_compare( $rel, '4.0.2 (beta 4)', 'le' ) ) {
//					JFactory::getApplication()->enqueueMessage('Unable to do an in-place upgrade of ABPro versions prior to 4.0.3, please see Update Instructions at AppointmentBookingPro.com', error);
//					return false;				
//				}
//			} catch (Exception $e) {
//			}
//			
//			//Backup the old language file
//			foreach (self::$languageFiles as $languageFile)
//			{
//				if (JFile::exists(JPATH_ROOT . '/language/en-GB/' . $languageFile))
//				{
//					JFile::copy(JPATH_ROOT . '/language/en-GB/' . $languageFile, JPATH_ROOT . '/language/en-GB/bak.' . $languageFile);
//				}
//			}
//
//			if (JFile::exists(JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.css'))
//			{
//				JFile::copy(JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.css',
//					JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css');
//			}
//			
//			// beta 1 did not have the sql schema file so the sql update is not run, manually do it here..
//			if($rel = "4.0.3 (beta 1)"){
//				$database = JFactory::getDBO(); 
//
//				$sql = "CREATE TABLE IF NOT EXISTS `#__sv_apptpro3_export_columns` (
//				  `id_export_columns` int(11) NOT NULL AUTO_INCREMENT,
//				  `export_column_type` varchar(255) DEFAULT NULL COMMENT 'core, udf, extra, seat',
//				  `export_table` varchar(255) DEFAULT NULL,
//				  `export_field` varchar(255) DEFAULT NULL,
//				  `export_format` varchar(255) DEFAULT NULL,
//				  `export_header` varchar(255) DEFAULT NULL,
//				  `export_order` smallint(6) DEFAULT NULL,
//				  `export_foreign_key` int(11) DEFAULT NULL,
//				  PRIMARY KEY (`id_export_columns`)
//				) ENGINE=MyIsam DEFAULT CHARSET=utf8;";
//				
//				try{
//					$database->setQuery( $sql );
//					$database->execute();
//				} catch (RuntimeException $e) {
//					logIt($e->getMessage(), "update from 4.0.3 beta 1 fix", "", "");
//					echo json_encode(JText::_('RS1_SQL_ERROR'));
//					jExit();
//				}		
//				
//				$sql = "INSERT IGNORE INTO `#__sv_apptpro3_export_columns` VALUES (1,'core','sv_apptpro3_requests','id_requests',NULL,'Booking ID',1,NULL),".
//				"(2,'core','sv_apptpro3_requests','name',NULL,'Name',2,NULL),(3,'core','sv_apptpro3_requests','email',NULL,'Email',3,NULL),".
//				"(4,'core','sv_apptpro3_requests','phone',NULL,'Phone',4,NULL),(5,'core','sv_apptpro3_requests','startdate','%c-%b-%Y','Date',5,NULL),".
//				"(6,'core','sv_apptpro3_requests','starttime','%I:%i %p','Start',6,NULL),(7,'core','sv_apptpro3_requests','endtime','%I:%i %p','End',7,NULL),".
//				"(8,'core','sv_apptpro3_requests','request_status',NULL,'Status',8,NULL),(9,'core','sv_apptpro3_requests','payment_status',NULL,'Payment',9,NULL),".
//				"(10,'core','sv_apptpro3_requests','booking_total',NULL,'Total',10,NULL),(11,'core','sv_apptpro3_requests','booking_due',NULL,NULL,11,NULL),".
//				"(12,'core','sv_apptpro3_resources','name',NULL,'Resource',13,NULL),(13,'core','sv_apptpro3_categories','name',NULL,'Category',14,NULL),".
//				"(14,'core','sv_apptpro3_services','name',NULL,'Service',15,NULL),(15,'core','sv_apptpro3_requests','booked_seats',NULL,'Booked Seats',12,NULL),".
//				"(16,'core','sv_apptpro3_resources','rate',NULL,'Rate',16,NULL);";
//				try{
//					$database->setQuery( $sql );
//					$database->execute();
//				} catch (RuntimeException $e) {
//					logIt($e->getMessage(), "update from 4.0.3 beta 1 fix", "", "");
//					echo json_encode(JText::_('RS1_SQL_ERROR'));
//					jExit();
//				}		
//				
//			}
//		}
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$this->installType = 'install';
		echo "install";
	}

	function update($parent)
	{
		$this->installType = 'upgrade';
		echo "upgrade";

	}

	/**
	 * Method to run after installing the component
	 */
	function postflight($type, $parent)
	{
		//Restore the modified language strings by merging to language files
		$registry = new JRegistry();
		foreach (self::$languageFiles as $languageFile)
		{
			$backupFile  = JPATH_ROOT . '/language/en-GB/bak.' . $languageFile;
			$currentFile = JPATH_ROOT . '/language/en-GB/' . $languageFile;
			if (JFile::exists($currentFile) && JFile::exists($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items       = array_merge($currentItems, $backupItems);
				$content     = "";
				foreach ($items as $key => $value)
				{
					$content .= "$key=\"$value\"\n";
				}
				JFile::write($currentFile, $content);
			}
		}

		// Restore custom modified css file
		if (JFile::exists(JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css',
				JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.css');
//			JFile::delete(JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css');

			$oldCSS  = JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css';
			$xml_path = JPATH_ADMINISTRATOR . '/components/com_rsappt_pro3/rsappt_pro3.xml';
			try{		
				$xml_obj = new SimpleXMLElement(file_get_contents($xml_path));
			} catch (Exception $e) {}
			$rel = strval($xml_obj->version); 
			$newCSS = JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.'.$rel.'.css';
			if (JFile::exists($newCSS) && JFile::exists($oldCSS))
			{
				$new_stuff = file_get_contents(JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.'.$rel.'.css');
				file_put_contents(JPATH_ROOT . '/components/com_rsappt_pro3/sv_apptpro.css', $new_stuff, FILE_APPEND);
				JFile::delete(JPATH_ROOT . '/components/com_rsappt_pro3/bak.sv_apptpro.css');
			}
		}

//		JFactory::getApplication()->redirect(
//			JRoute::_('index.php?option=com_rsappt_pro3&task=upgrade&install_type=' . $this->installType, false));
	}
}