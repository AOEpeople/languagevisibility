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

/**
 * exceptions are not handled here.
 * This class just provides simple services and uses the domainmodel in classes directory!
 *
 * Methods can be used uninstanciated
 */
class tx_languagevisibility_feservices {

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getFallbackOrderForElement($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		return $language->getFallbackOrderElement($element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function checkVisiblityForElement($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);
		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		return $visibility->isVisible($language, $element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @return tx_languagevisibility_element
	 */
	public static function getElement($uid, $table) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		/** @var $elementfactory tx_languagevisibility_elementFactory */
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);

		return $element;
	}

	/**
	 * @param $element
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElement($element, $lUid) {
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);
		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService'); /** @var $visibility tx_languagevisibility_visibilityService */

		return $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElementRecord($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');
		return $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElementRecordForced($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
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
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);

		$result = FALSE;
		try {
			$element = $elementfactory->getElementForTable($table, $elementUid);
			$result = $element->hasTranslation($languageUid);
		} catch ( UnexpectedValueException $e ) {
				//the element can not be handeld by language visibility
			$result = FALSE;
		}

		return $result;
	}
}
