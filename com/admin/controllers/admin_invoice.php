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


class admin_invoiceController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		// Register Extra tasks
//		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'invoice', 'create_invoice' );
		$this->registerTask( 'create_and_send', 'create_and_send' );
		$this->registerTask( 'create_only', 'create_only' );

		
	}

	  
	function create_invoice(){

		$ccinvoices_file = JPATH_SITE."/administrator/components/com_ccinvoices/controllers/invoices.php";
		if (file_exists($ccinvoices_file)) {
			require_once( $ccinvoices_file );
		} else {
			echo JText::_('RS1_REQUIRES_CCINVOICE');
			exit();
		}
		
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'admin_invoice' );
		$jinput->set( 'hidemainmenu', 1);

		parent::display();
		
		
	}
	
	
	function cancel($key=null)
	{
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=requests' );
	}	


	function create_only(){
		$this->create_and_send("No");
	}

	function create_and_send($ok_to_send = "Yes"){
		
		$contact_id = null; // ccInvoices contact id, eiter an existing one or we will create a contact
		
		// Who is it going to?
		// 1. Existing ccInvoices contact
		//		- just use the passed in contact id
		// 2. Joomla user account
		//		- if the email address matched an existing contact, use that contact
		//		- if no matches, create a new contact and add an xref to the joomla account
		// 3. Manually entered email address
		//		- if the email matches an exiting contact use that
		//		- if it matched a user account use that and add an xref to the joomla account
		//		- if no mathes, create a new contact
		
		$database = JFactory::getDBO(); 
		$jinput = JFactory::getApplication()->input;

		$sent_to_id = $jinput->getInt('sent_to_id');
		$id_source = $jinput->getString('id_source');
		$sent_to_name = $jinput->getString('sent_to_name');
		$sent_to_email = $jinput->getString('sent_to_email');
		$cid = $jinput->getString('cid');

		if($id_source == "cci"){
			// existing contact
			$contact_id = $sent_to_id;
		} else if($id_source == "joomla"){
			// joomla account, see if there is already a ccInvoices conatct for this id.
			$sql = "SELECT contact_id FROM #__ccinvoices_users WHERE user_id = ".$sent_to_id;
			try{
				$database->setQuery($sql);
				$contact_id = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ctl_admin_invoice", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			if($contact_id == null){
				// add a new ccInvoices Contact
				$contact_id = $this->add_ccinvoices_contact($sent_to_name, $sent_to_email);
				
				// add xref to link new contact with joomla user
				$sql = "INSERT INTO #__ccinvoices_users (user_id, contact_id) VALUES('".$database->escape($sent_to_id)."', '".
					$database->escape($contact_id)."')";
				try{
					$database->setQuery( $sql );
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctl_admin_invoice", "", "");
					echo JText::_('RS1_SQL_ERROR');	
					exit;
				}		

			}
			
		} else {
			// manually entered name and email
			// Check to see if email matches an existing contact			
			$sql = "SELECT id FROM #__ccinvoices_contacts WHERE email = '".$sent_to_email."'";
			try{
				$database->setQuery($sql);
				$contact_id = $database -> loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ctl_admin_invoice", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			if($contact_id == null){
				// no match
				// create a new contact
				$contact_id = $this->add_ccinvoices_contact($sent_to_name, $sent_to_email);			
			}
		}

		// Ok, we are ready to go now we have a contact for the invoice.
		
		// get ccInvoices config
		$sql = 'SELECT * FROM #__ccinvoices_configuration';
		try{
			$database->setQuery($sql);
			$ccinvoices_config = NULL;
			$ccinvoices_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		

		// get ABPro config
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$abpro_config = NULL;
			$abpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		
		// get last invoice number used + 1
		$next_invoice_number = -1;
		$sql = "SELECT MAX(number)+1 FROM #__ccinvoices_invoices";
		try{
			$database->setQuery( $sql );
			$next_invoice_number = $database->loadResult();
			if($next_invoice_number == 0){$next_invoice_number = 1;}
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		

		// get bookings
		$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.name AS '.
			'ResourceName, #__sv_apptpro3_services.name AS ServiceName, '.
			'#__sv_apptpro3_categories.name AS CategoryName, '.
			"CONCAT(#__sv_apptpro3_requests.startdate,' ',#__sv_apptpro3_requests.starttime) as startdatetime, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ".
			'#__sv_apptpro3_paypal_transactions.id_paypal_transactions AS id_transaction '.
			'FROM ('.
			'#__sv_apptpro3_requests LEFT JOIN '.
			'#__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = '.
			'#__sv_apptpro3_resources.id_resources LEFT JOIN '.
			'#__sv_apptpro3_services ON #__sv_apptpro3_requests.service = '.
			'#__sv_apptpro3_services.id_services LEFT JOIN '.
			'#__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = '.
			'#__sv_apptpro3_categories.id_categories LEFT JOIN '.
			'#__sv_apptpro3_paypal_transactions ON '.
			'#__sv_apptpro3_paypal_transactions.custom = '.
			'#__sv_apptpro3_requests.id_requests) '.
			' WHERE id_requests IN('.$cid.')'.
			' AND request_status NOT IN("canceled", "deleted", "new", "timeout")'.
			' AND payment_status = "pending"';
		try{
			$database->setQuery($sql);
			$booking_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
		
		if(sv_count_($booking_rows) == 0){
			// should never get here, but just in case
			$msg = "NO bookings found, NO INVOICE created.";
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=requests', $msg);
		}
		
		$inv_sent_date = "''";
		$inv_communication = "0";
		$inv_status = "1"; // draft
		if($ok_to_send == "Yes"){
			$inv_sent_date = "CURDATE()";
			$inv_communication = "1";
			$inv_status = "2"; // open
		}
		
		$inv_quantity = "";
		$inv_pname = "";
		$inv_price = "";
		$inv_tax = "";
		$inv_item_id = "";
		$inv_item_description = "";
		$inv_subtotal = "";
		$inv_total = "";
		$calc_total = 0.00;
		$inv_totaltax = "";
		$inv_custom_invoice_number = str_replace("[invoicenumber]","",$ccinvoices_config->invoice_format).sprintf("%'.04d",$next_invoice_number);	// custom_invoice_number

		if(sv_count_($booking_rows) == 1){
			// a single item invoice requires different settings than a multi item one.
			$inv_quantity = "1";			
			$inv_pname = processTokens($booking_rows[0]->id_requests, $abpro_config->ccinvoice_item_name);
			$inv_price = $booking_rows[0]->booking_due;
			$inv_tax = "0";
			$inv_item_id = $booking_rows[0]->id_requests;
			$inv_item_description = processTokens($booking_rows[0]->id_requests, $abpro_config->ccinvoice_item_description); 
			$inv_subtotal = $booking_rows[0]->booking_due;	
			$calc_total = floatval($booking_rows[0]->booking_due);
			$inv_total = number_format($calc_total, 2);
			
		} else {
			// multi-item invoice
			$inv_delimiter = "|#$|";
			$row_count = sv_count_($booking_rows);
			for($i=0; $i < $row_count; $i++) {
				$booking_row = $booking_rows[$i];
				$inv_quantity .= "1";
				$inv_pname .= processTokens($booking_rows[0]->id_requests, $abpro_config->ccinvoice_item_name);
				$inv_price .= $booking_row->booking_due;
				$inv_tax .= "0";
				$inv_item_id .= $booking_row->id_requests;
				$inv_item_description .= processTokens($booking_rows[0]->id_requests, $abpro_config->ccinvoice_item_description); 
				$calc_total = $calc_total + floatval($booking_row->booking_due);
				if($i < $row_count-1){
					$inv_quantity .= $inv_delimiter;
					$inv_pname .= $inv_delimiter;
					$inv_price .= $inv_delimiter;
					$inv_tax .= $inv_delimiter;
					$inv_item_id .= $inv_delimiter;
					$inv_item_description .= $inv_delimiter;
					$inv_subtotal .= $inv_delimiter;
				}
				$inv_total = number_format($calc_total, 2);
			}			
		}


		$sql = "INSERT INTO #__ccinvoices_invoices (".
			"number,invoice_date,status,duedate,numbercheck,invoice_sent_date,communication,discount,subtotal,totaltax,total,".
			"quantity,pname,price,tax,note,contact_id,custom_invoice_number,reset_inv,ordering,item_id,item_description".
			") VALUES(".
			$next_invoice_number.",".  // number
			"CURDATE(),".		// invoice_date
			$inv_status.",". // status
			"CURDATE() + ".($ccinvoices_config->default_due_days!=""?$ccinvoices_config->default_due_days:"0").",".		// duedate
			$next_invoice_number.",".	// numbercheck
			$inv_sent_date.",".			// sent_date
			$inv_communication.",".		// communications
			"'0.00',".  				// discount
			$inv_total.",". 			// subtotal
			"0.00,".					// totaltax
			$inv_total.",".				// total
			"'".$inv_quantity."',".		// quantity
			"'".$inv_pname."',".		// pname
			"'".$inv_price."',".		// price
			"'".$inv_tax."',".			// tax
			"'".$ccinvoices_config->default_note."',".	// note
			$contact_id.",".			// contact_id
			"'".$inv_custom_invoice_number."',".	// custom_invoice_number
			"'',".						// reset_inv
			"0,".						// ordering
			"'".$inv_item_id."',".		// item_id 
			"'".$database->escape($inv_item_description)."'".	// item_description
			")";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		$sSql = "SELECT LAST_INSERT_ID() AS last_id";
		try{
			$database->setQuery($sSql);
			$last_id = NULL;
			$last_id = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		$new_invoice_id = $last_id->last_id;

	
		// set payment status to 'invoiced'
		$sql = "UPDATE #__sv_apptpro3_requests SET payment_status = 'invoiced', ".
			" invoice_number = '".$inv_custom_invoice_number."'".
			" WHERE id_requests IN (".$cid.")".
			' AND request_status NOT IN("canceled", "deleted", "new", "timeout")'.
			' AND payment_status = "pending"';			
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		
		
		if($ok_to_send == "Yes"){
			require_once( JPATH_SITE."/administrator/components/com_ccinvoices/controllers/invoices.php" );
			$language = JFactory::getLanguage();
			$language->load('com_ccinvoices', JPATH_SITE, null, true);

			// J Request::setVar("id",$new_invoice_id);
			// needs to be fixed for depricated J Request
			$ctrl = new ccInvoicesControllerInvoices();
			$ctrl->sendInvoice();
		}
		
		$msg = "Invoice ".$inv_custom_invoice_number." created.";
		$this->setRedirect( 'index.php?option=com_rsappt_pro3&controller=requests', $msg);
	}


	function add_ccinvoices_contact($cci_name, $cci_email){
		$retval = "";
		
		$database = JFactory::getDBO(); 
		$sql = "INSERT INTO #__ccinvoices_contacts (name, contact, email) VALUES('".$database->escape($cci_name)."', '".
			$database->escape($cci_name)."', '".$database->escape($cci_email)."')";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			echo $e->getMessage();
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		$sSql = "SELECT LAST_INSERT_ID() AS last_id";
		try{
			$database->setQuery($sSql);
			$last_id = NULL;
			$last_id = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctl_admin_invoice", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		
		$retVal = $last_id->last_id;
		
		return $retVal;	
	}
}

