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

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' ); 


class sms_processorsController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		require_once JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'rsappt_pro3.php';
		rsappt_pro3Helper::addSubmenu('sms_processors');
		
		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
		
	}

	/** function edit
	*
	* Create a new item or edit existing item 
	* 
	* 1) set a custom VIEW layout to 'form'  
	* so expecting path is : [componentpath]/views/[$controller->_name]/'form.php';			
    * 2) show the view
    * 3) get(create) MODEL and checkout item
	*/
	function edit($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'sms_processors' );
		$jinput->set( 'layout', 'form'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

		// Checkin the config
		$model = $this->getModel('config_detail');
		$model->checkout();
	}

	function cancel($key=null)
	{
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel' );
	}
      
	/** function save
	*
	* Save the selected item specified by id
	* and set Redirection to the list of items	
	* 		
	* @param int id - keyvalue of the item
	* @return set Redirection
	*/
	function save($key=null, $urlVar=null)
	{
		$jinput = JFactory::getApplication()->input;
	
		$filter = JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$jinput = new JInput( null, array('filter' => $filter) );
		$post = $jinput->post->getArray();
		
		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$post['id'] = $cid[0];

//		$model = $this->getModel('sms_processors');
		$data = $post;
	
		$database = JFactory::getDBO();

		$query = "UPDATE #__sv_apptpro3_config ".
		"SET ".
		"enable_clickatell='".$database->escape($data[enable_clickatell])."', ".
		"clickatell_user='".$database->escape($data[clickatell_user])."', ".
		"clickatell_password='".encrypt_decrypt('encrypt', $database->escape($data[clickatell_password]))."', ".
		"clickatell_api_id='".$database->escape($data[clickatell_api_id])."', ".
		"clickatell_sender_id='".$database->escape($data[clickatell_sender_id])."', ".
		"clickatell_dialing_code='".$database->escape($data[clickatell_dialing_code])."', ".
		"clickatell_what_to_send='".$database->escape($data[clickatell_what_to_send])."', ".
		"clickatell_show_code='".$database->escape($data[clickatell_show_code])."', ".
		"clickatell_enable_unicode='".$database->escape($data[clickatell_enable_unicode])."', ".
		"enable_eztexting='".$database->escape($data[enable_eztexting])."', ".
		"eztexting_user='".$database->escape($data[eztexting_user])."', ".
		"eztexting_password='".encrypt_decrypt('encrypt', $database->escape($data[eztexting_password]))."', ".
		"enable_twilio='".$database->escape($data[enable_twilio])."', ".
		"twilio_sid='".$database->escape($data[twilio_sid])."', ".
		"twilio_token='".$database->escape($data[twilio_token])."', ".
		"twilio_phone='".$database->escape($data[twilio_phone])."', ".
		"sms_to_resource_only='".$database->escape($data[sms_to_resource_only])."', ".
		"sms_phone_profile_mapping='".$database->escape($data[sms_phone_profile_mapping])."', ".
		"sms_phone_cb_mapping='".$database->escape($data[sms_phone_cb_mapping])."', ".
		"sms_phone_js_mapping='".$database->escape($data[sms_phone_js_mapping])."', ".
		"sms_confirmation='".$database->escape($data[sms_confirmation])."', ".
		"sms_phone_size=".$database->escape($data[sms_phone_size])." ".
		" WHERE id_config = 1";
		try{
			$database->setQuery($query);
			$database->execute();
			$msg = JText::_( 'COM_RSAPPT_SAVE_OK' );
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_ctrl_sms_proc", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}

		if($apply=="yes"){
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=sms_processors',$msg );
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=cpanel',$msg );
		}
	}

	function apply()
	{
		$this->save("yes");
	}

}

