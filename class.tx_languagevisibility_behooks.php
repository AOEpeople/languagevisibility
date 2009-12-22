<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Daniel P?tzinger (poetzinger@aoemedia.de)
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
 * Class/Function which manipulates the item-array for the  listing (see piFlexform).
 *
 * @author	Daniel P?tzinger <poetzinger@aoemedia.de>
 */

require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_cacheManager.php';

/**
 * SELECT box processing
 *
 */
class tx_languagevisibility_behooks {

	private static $updateLanguagevisibilityIds;

	/**
	 * This function is called my TYPO each time an element is saved in the backend
	 *
	 * @param array $incomingFieldArray
	 * @param string $table
	 * @param unknown_type $id
	 * @param unknown_type $reference
	 */
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$reference) {
		$data = $incomingFieldArray;

		if (! is_array ( $data ))
			return; /* some strange DB situation */

		switch ($table) {
			case 'pages' :
			case 'tt_content' :
			case 'tt_news' :
			case 'pages_language_overlay' :

				/**
				 * NOTE: This code does not affect new records because the field 'tx_languagevisibility_visibility' is not set
				 */
				if (isset ( $incomingFieldArray ['tx_languagevisibility_visibility'] ) && is_array ( $incomingFieldArray ['tx_languagevisibility_visibility'] )) {

					if($table == 'pages'){

						 $incomingFieldArray ['tx_languagevisibility_inheritanceflag_original'] = (in_array('no+',$incomingFieldArray ['tx_languagevisibility_visibility'])) ? '1' : '0';
					}elseif($table == 'pages_language_overlay'){
						$incomingFieldArray ['tx_languagevisibility_inheritanceflag_overlayed'] = (in_array('no+',$incomingFieldArray ['tx_languagevisibility_visibility'])) ? '1' : '0';
					}

					$incomingFieldArray ['tx_languagevisibility_visibility'] = serialize ( $incomingFieldArray ['tx_languagevisibility_visibility'] );

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
					$row ['uid'] = $reference->substNEWwithIDs [$id];

					if ($fieldArray ['pid'] == '-1') {
						$row = t3lib_BEfunc::getWorkspaceVersionOfRecord ( $fieldArray ['t3ver_wsid'], $table, $row ['uid'], $fields = '*' );
					}

					require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'class.tx_languagevisibility_beservices.php');
					$row ['tx_languagevisibility_visibility'] 	= serialize ( tx_languagevisibility_beservices::getDefaultVisibilityArray () );
					$where 										= "tx_languagevisibility_visibility = '' AND uid=" . $row ['uid'];

					$GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( $table, $where, $row );
				}

				tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();

			break;
		}
	}

	/**
	 * Alternative diff implementation
	 *
	 * @param array params
	 * @return array element
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 */
	public function aoewspreview_createDiff(array $params) {


		$element = $params['element'];

		if (/* ($params['table'] == 'tt_content') && */ ($params['fieldName'] == 'tx_languagevisibility_visibility')) {
			$diff = array();

			$recordNew = unserialize($params['newRecord'][$params['fieldName']]);
			$recordOld = unserialize($params['oldRecord'][$params['fieldName']]);

			$recordNew = is_array($recordNew) ? $recordNew : array();
			$recordOld = is_array($recordOld) ? $recordOld : array();


			$equalInBoth				 	= array_intersect_assoc($recordNew,$recordOld);
			$changedKeyOld 					= array_keys(array_diff_assoc($recordOld,$equalInBoth));
			$changedKeyNew 					= array_keys(array_diff_assoc($recordNew,$equalInBoth));
			$keyOfChangedLanguageSettings	= array_unique(array_merge($changedKeyNew,$changedKeyOld));

			if(is_array($keyOfChangedLanguageSettings) && (count($keyOfChangedLanguageSettings) > 0)){
				foreach($keyOfChangedLanguageSettings as $key) {
					if (empty($recordOld[$key]) && ($recordNew[$key] == '-')) {
						// this is equal, too!
						//we need to inform the user what happens because he doesn't understand
						//what happend if the field tx_languagevisibility_visibility was configured as critical field
						//and if was just initialized with default values.
						$diff[] = sprintf('%s Visibility was initialized with the default value (%s)',
							tx_mvc_common_typo3::getLanguageFlag($key, $params['newRecord']['pid']),
							$recordNew[$key]
						);
					} elseif(empty($recordNew[$key]) ){
						//in this case an old visibility setting has been changed to an empty value,
						//this can happen when a new workspace version is created
						$diff[0] = 'Languagevisibility was changed from '.serialize($recordOld).' to '.serialize($recordNew);
					}else {

						$diff[] = sprintf('%s Visibility changed from <span class="diff-r">%s</span> to <span class="diff-g">%s</span>',
							tx_mvc_common_typo3::getLanguageFlag($key, $params['newRecord']['pid']),
							$GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.'.$recordOld[$key]),
							$GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.'.$recordNew[$key])
						);
					}
				}
			}else{
				//in this case the structure of the languagevisibility was changed but no visibility setting
			}

			if (count($diff) > 0) {
				$element['diffResult'] = implode('<br />', $diff);
			} else {
				// element will be removed when returning "false"
				$element = false;
			}
		}

		return $element;
	}
}



?>