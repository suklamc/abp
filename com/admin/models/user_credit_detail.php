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

//DEVNOTE: import MODEL object class
jimport('joomla.application.component.model');


class user_credit_detailModeluser_credit_detail extends JModelLegacy
{
		var $_id_user_credit = null;
		var $_data = null;
		var $_table_prefix = null;
		var $_credit_usage_info = null;

	function __construct()
	{
		parent::__construct();
		
		//initialize class property
	  	$this->_table_prefix = '#__sv_apptpro3_';			

		$jinput = JFactory::getApplication()->input;
		$array = $jinput->get( 'cid', array(0), 'post', 'array' );
		
		$this->setId((int)$array[0]);

	}

	/**
	 * Method to set the user_credit identifier
	 *
	 * @access	public
	 * @param	int user_credit identifier
	 */
	function setId($id_user_credit)
	{
		// Set user_credit id and wipe data
		$this->_id_user_credit		= $id_user_credit;
		$this->_data	= null;
	}

	/**
	 * Method to get a user_credit
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the user_credit data
		if ($this->_loadData())
		{
		//load the data nothing else	  
		}
		else  $this->_initData();
		//print_r($this->_data);	
		
   	return $this->_data;
	}
	
	
	function &getCredit_usage_info()
	{
		// Load the user_credit data
		if ($this->_loadCredit_usage_info())
		{
		//load the data nothing else	  
		}
		//print_r($this->_data);	
		
   	return $this->_credit_usage_info;
	}
	/**
	 * Method to checkout/lock the user_credit
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id_user_credit)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	= JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$user_credit = $this->getTable();
			
			
			if(!$user_credit->checkout($uid, $this->_id_user_credit)) {
				$this->setError($row->getError());
				return false;
			}

			return true;
		}
		return false;
	}
	/**
	 * Method to checkin/unlock the user_credit
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id_user_credit)
		{
			$user_credit = $this->getTable();
			if(! $user_credit->checkin($this->_id_user_credit)) {
				$this->setError($row->getError());
				return false;
			}
		}
		return false;
	}	
	/**
	 * Tests if user_credit is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}	
		
	/**
	 * Method to load content user_credit data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM '.$this->_table_prefix.'user_credit WHERE id_user_credit = '. $this->_id_user_credit;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			//print_r($this->_data);
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the user_credit data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$detail = new stdClass();
			$detail->id_user_credit	= 0;
			$detail->credit_type = "uc";
			$detail->user_id = null;
			$detail->gift_cert = "";
			$detail->gift_cert_name = "";
			$detail->balance = 0.00;
			$detail->checked_out = 0;
			$detail->checked_out_time = 0;
			$detail->ordering = 1;
			$detail->published = 1;
			$this->_data	= $detail;
			return (boolean) $this->_data;
		}
		return true;
	}
  	

	/**
	 * Method to store the user_credit text
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		//DEVNOTE: Load table class from com_rsappt_pro3/tables/user_credit_detail.php	
		$row = $this->getTable();

		// Bind the form fields to the user_credit table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// if new item, order last in appropriate group
		if (!$row->id_user_credit) {
			$where = 'id_user_credit = ' . $row->id_user_credit ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		// If type = gc (gift certificate) ensure there are no dupes
		$sql = "SELECT count(*) as dupes FROM #__sv_apptpro3_user_credit WHERE gift_cert = '".$row->gift_cert."' AND id_user_credit != ".$row->id_user_credit;
		try{
			$this->_db->setQuery($sql);
			$dupes = $this->_db->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_model_user_credit_detail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		if($dupes > 0){
			$this->setError("Duplicate Gift Certificate number, changes not saved.");
			return false;
		}
		// Store the user_credit table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		// add to user_credit_activity
		$user = JFactory::getUser();	
		$jinput = JFactory::getApplication()->input;
		if($jinput->getString('id_user_credit') == ""){
			// adding new entry
			$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, gift_cert, increase, comment, operator_id, balance) '.
			"VALUES (".$jinput->getInt('user_id', "-1").",".
			"'".$row->gift_cert."',".
			"'".$jinput->getString('balance')."',".
			"'".JText::_('RS1_ADMIN_CREDIT_ACTIVITY_NEW_ENTRY')."',".
			$user->id.",".
			"'".$jinput->getString('balance')."')";
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_model_user_credit_detail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		} else {
			// admin edit
			$comment = JText::_('RS1_ADMIN_CREDIT_ACTIVITY_EDIT')." ".$jinput->getString('balance');
			if($jinput->getString('comment') != "" ){
				$comment = $jinput->getString('comment');
			}

			$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, gift_cert, comment, operator_id, balance) '.
			"VALUES (".$jinput->getInt('user_id',"-1").",".
			"'".$row->gift_cert."',".
			"'".$comment."',".
			$user->id.",".
			"(SELECT balance from #__sv_apptpro3_user_credit WHERE id_user_credit = ".$jinput->getString('id_user_credit')."))";
			try{
				$this->_db->setQuery($sql);
				$this->_db->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_model_user_credit_detail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		}


		return true;
	}
	
		/**
	 * Method to (un)publish a user_credit
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	= JFactory::getUser();

		if (sv_count_($cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE '.$this->_table_prefix.'user_credit'
				. ' SET published = ' . intval( $publish )
				. ' WHERE id_user_credit IN ( '.$cids.' )'
				. ' AND ( checked_out = 0 OR ISNULL(checked_out) OR ( checked_out = ' .$user->get('id'). ' ) )'
			;

			$this->_db->setQuery( $query );
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

		return true;
	}
	
	/**
	 * Method to move a user_credit_detail
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order)
	{
		//DEVNOTE: Load table class from com_sv_ser/tables/user_credit_detail.php		
		$row = $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < sv_count_($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}
			}
		}
		return true;
	}
		
		/**
	 * Method to move a user_credit 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
	//DEVNOTE: Load table class from com_sv_ser/tables/user_credit_detail.php	
		$row = $this->getTable();
	//DEVNOTE: we need to pass here id of user_credit detail 		
		if (!$row->load($this->_id_user_credit)) {
			$this->setError($row->getError());
		
			return false;
		}
  
	//DEVNOTE: call move method of JTABLE. 
  //first parameter: direction [up/down]
  //second parameter: condition
		if (!$row->move( $direction, ' published >= 0 ' )) {
			$this->setError($row->getError());
			return false;
		}

		return true;
	}		

	function delete($cid = array())
	{
		$result = false;


		if (sv_count_($cid ))
		{
			$cids = implode( ',', $cid );
			$query = "DELETE FROM #__sv_apptpro3_user_credit_activity WHERE user_id IN (SELECT user_id FROM #__sv_apptpro3_user_credit WHERE id_user_credit IN (".$cids."))";
			$this->_db->setQuery($query);
			if (!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
			$query = 'DELETE FROM '.$this->_table_prefix.'user_credit WHERE id_user_credit IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->execute()) {
				$this->setError($row->getError());
				return false;
			}
		}

		return true;
	}
	
	function _loadCredit_usage_info()
	{
		//$sql = "SELECT user_id FROM #__sv_apptpro3_user_credit WHERE id_user_credit = ".$this->_id_user_credit;
		$sql = "SELECT #__sv_apptpro3_user_credit_activity.*, #__users.name as operator, #__sv_apptpro3_requests.startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%b %e') as display_startdate, ".
			"DATE_FORMAT(#__sv_apptpro3_requests.starttime, '%H:%i') as display_starttime, ".
			"#__sv_apptpro3_resources.description as resource ".
			"FROM #__sv_apptpro3_user_credit_activity ".
			"  INNER JOIN #__users ON #__sv_apptpro3_user_credit_activity.operator_id = #__users.id ".
			"  LEFT OUTER JOIN #__sv_apptpro3_requests ON #__sv_apptpro3_user_credit_activity.request_id = #__sv_apptpro3_requests.id_requests ".
			"  LEFT OUTER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			"WHERE #__sv_apptpro3_user_credit_activity.user_id = ".
			" (SELECT user_id FROM #__sv_apptpro3_user_credit WHERE id_user_credit = ".$this->_id_user_credit.") ORDER BY stamp desc";
		//echo $sql;		
		$this->_db->setQuery($sql);
		$this->_credit_usage_info = $this->_db->loadObjectList();
		//print_r("here".$this->_credit_usage_info);
		return $this->_credit_usage_info;
	}

}

?>
