<?php

class ux_t3lib_TCEmain extends t3lib_TCEmain	{

	/**
	 * Used to evaluate if a page can be deleted
	 *
	 * @param	integer		Page id
	 * @return	mixed		If array: List of page uids to traverse and delete (means OK), if string: error code.
	 */
	function canDeletePage($uid)	{
		$return = parent::canDeletePage($uid);
		if (is_array($return)) {			
			if (t3lib_extMgm::isLoaded('languagevisibility')) {
					require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');									
					$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
					if (!$visibilityservice->hasUserAccessToPageRecord($uid,'delete')) {
						return 'Attempt to delete records without access to the visible languages';
					}									
				}	
		}
		return $return;
	}
	
	/**
	 * Checks if user may update a record with uid=$id from $table
	 *
	 * @param	string		Record table
	 * @param	integer		Record UID
	 * @return	boolean		Returns true if the user may update the record given by $table and $id
	 */
	function checkRecordUpdateAccess($table,$id)	{
		global $TCA;
		$res = 0;
		if ($TCA[$table] && intval($id)>0)	{
			if (isset($this->recUpdateAccessCache[$table][$id]))	{	// If information is cached, return it
				return $this->recUpdateAccessCache[$table][$id];
				// Check if record exists and 1) if 'pages' the page may be edited, 2) if page-content the page allows for editing
			} elseif ($this->doesRecordExist($table,$id,'edit'))	{
				$res = 1;
				if (t3lib_extMgm::isLoaded('languagevisibility')) {
					require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');									
					$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
					if ($table=='pages') {
						if (!$visibilityservice->hasUserAccessToPageRecord($id,'edit')) {
							$res = 0;
						}
					}
					else {
						if (!$visibilityservice->hasUserAccessToEditRecord($table,$id)) {
							$res=0;									
						}
					}
				}
				
			}
			$this->recUpdateAccessCache[$table][$id]=$res;	// Cache the result
		}
		return $res;
	}

}

?>
