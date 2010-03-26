<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media (dev@aoemedia.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @author	Tolleiv Nietsch <nietsch@aoemedia.de>
 */
class tx_languagevisibility_hooks_t3lib_tcemain {

	/**
	 *
	 * @param string	 $table
	 * @param integer	 $id
	 * @param array		 $data
	 * @param integer	 $res (but only 0 and 1 is relevant so it's boolean technically)
	 * @param object	 $this
	 * @return integer
	 */
	public function checkRecordUpdateAccess($table, $id, $data, $res, $this) {
		$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
		if ($table=='pages' && !$visibilityservice->hasUserAccessToPageRecord($id, 'edit')) {
			$result = 0;
		} elseif (!$visibilityservice->hasUserAccessToEditRecord($table, $id)) {
			$result = 0;
		} else {
			$result = $res;
		}
		return $result;
	}
}

?>