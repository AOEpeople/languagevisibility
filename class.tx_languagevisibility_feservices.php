<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007 AOE media (dev@aoemedia.de)
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

require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_visibilityService.php');
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/dao/class.tx_languagevisibility_daocommon.php');

/**
 * exceptions are not handled here.
 * This class just provides simple services and uses the domainmodel in classes directory!
 *
 * Methods can be used uninstanciated
 **/
class tx_languagevisibility_feservices {

	public static function getFallbackOrderForElement($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		return $language->getFallbackOrderElement($element);
	}

	public static function checkVisiblityForElement($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		return $visibility->isVisible($language, $element);
	}

	public static function getElement($uid, $table) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			/* @var $elementfactory tx_languagevisibility_elementFactory */
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid);
		return $element;
	}

	public static function getOverlayLanguageIdForElement($element, $lUid) {
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService'); /** @var $visibility tx_languagevisibility_visibilityService */
		return $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
	}

	public static function getOverlayLanguageIdForElementRecord($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');
		return $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
	}

	public static function getOverlayLanguageIdForElementRecordForced($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');
		$visibility->isVisible($language, $element);
		return $visibility->getLastRelevantOverlayLanguageId();
	}

	/**
	 * Check if given element has traslation in given language
	 * 
	 * @param int $elementUid
	 * @param string $table
	 * @param int $languageUid
	 * @return boolean
	 */
	public static function hasTranslation($elementUid, $table, $languageUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}

		$result = false;
		try {
			$element = $elementfactory->getElementForTable($table, $elementUid);
			$result = $element->hasTranslation($languageUid);
			
		} catch ( UnexpectedValueException $e ) {
			//the element can not be handeld by language visibility
			$result = false;
		}
		
		return $result;
	}	
}

?>
