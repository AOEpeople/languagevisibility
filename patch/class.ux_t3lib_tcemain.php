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
					if (!$visibilityservice->hasUserAccessToPageRecord($theUid,'delete')) {
						return 'Attempt to delete records without access to the visible languages';
					}									
				}	
		}
		return $return;
	}

}

?>
