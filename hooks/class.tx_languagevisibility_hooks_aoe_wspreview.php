<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media <dev@aoemedia.de>
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
 * @author	Fabrizio Brance
 * @author	Timo Schmidt
 */

class tx_languagevisibility_hooks_aoe_wspreview {

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