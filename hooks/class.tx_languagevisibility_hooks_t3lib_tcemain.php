<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009 AOE media (dev@aoemedia.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
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
	public function checkRecordUpdateAccess($table, $id, $data, $res, $tcemain) {
		/**
		 * @var tx_languagevisibility_beservices $visibilityservice
		 */
		$visibilityservice = t3lib_div::makeInstance('tx_languagevisibility_beservices');
		if ($table == 'pages' && ! $visibilityservice->hasUserAccessToPageRecord($id, 'edit')) {
			$result = 0;
		} elseif (! $visibilityservice->hasUserAccessToEditRecord($table, $id)) {
			$result = 0;
		} else {
			$result = $res;
		}
		return $result;
	}

	/**
	 * This function is called my TYPO each time an element is saved in the backend
	 *
	 * @param array $incomingFieldArray
	 * @param string $table
	 * @param unknown_type $id
	 * @param unknown_type $reference
	 */
	public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$reference) {
		$data = $incomingFieldArray;

		if (! is_array($data))
			return; /* some strange DB situation */

		switch ($table) {
			case 'pages' :
			case 'tt_content' :
			case 'tt_news' :
			case 'pages_language_overlay' :

				/**
				 * NOTE: This code does not affect new records because the field 'tx_languagevisibility_visibility' is not set
				 */
				if (isset($incomingFieldArray['tx_languagevisibility_visibility']) && is_array($incomingFieldArray['tx_languagevisibility_visibility'])) {

					if ($table == 'pages') {

						$incomingFieldArray['tx_languagevisibility_inheritanceflag_original'] = (in_array('no+', $incomingFieldArray['tx_languagevisibility_visibility'])) ? '1' : '0';
					} elseif ($table == 'pages_language_overlay') {
						$incomingFieldArray['tx_languagevisibility_inheritanceflag_overlayed'] = (in_array('no+', $incomingFieldArray['tx_languagevisibility_visibility'])) ? '1' : '0';
					}

					$incomingFieldArray['tx_languagevisibility_visibility'] = serialize($incomingFieldArray['tx_languagevisibility_visibility']);

					//flush all caches
					tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
				}

				break;
			default :
				return;
				break;
		}
	}

	/**
	 * This method is used to initialize new Elements with the default
	 *
	 * @param unknown_type $status
	 * @param unknown_type $table
	 * @param unknown_type $id
	 * @param unknown_type $fieldArray
	 * @param unknown_type $reference
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$reference) {
		switch ($table) {
			/**
			 * Now we set the default visibility for elements which did not get a defaultvisibility array.
			 * This can happen, if a user creates a new element AND the user has no access for the languagevisibility_field
			 */
			case 'pages' :
			case 'tt_content' :
			case 'tt_news' :
			case 'pages_language_overlay' :

				if ($status == 'new') {
					$row['uid'] = $reference->substNEWwithIDs[$id];

					if ($fieldArray['pid'] == '-1') {
						$row = t3lib_BEfunc::getWorkspaceVersionOfRecord($fieldArray['t3ver_wsid'], $table, $row['uid'], $fields = '*');
					}

					require_once (t3lib_extMgm::extPath("languagevisibility") . 'class.tx_languagevisibility_beservices.php');
					$row['tx_languagevisibility_visibility'] = serialize(tx_languagevisibility_beservices::getDefaultVisibilityArray());
					$where = "tx_languagevisibility_visibility = '' AND uid=" . $row['uid'];

					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $row);
				}

				tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();

				break;
		}
	}
}

?>