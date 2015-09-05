<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2015 AOE GmbH (dev@aoe.com)
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

class tx_languagevisibility_beservices extends tx_languagevisibility_abstractservices {

	protected static $cache_canBeUserCopyDelete = array();

	protected static $visibleFlagsCache = array();

	protected static $cache_isVisible = array();

	/**
	 *
	 * @param $uid
	 * @param $table
	 * @return string
	 */
	public static function getVisibleFlagsForElement($uid, $table) {
		$cacheKey = $uid . ':' . $table;

		$cacheManager = tx_languagevisibility_cacheManager::getInstance();
		$isCacheEnabled = $cacheManager->isCacheEnabled();
		$cacheData = $cacheManager->get('visibleFlagsCache');

		if (! $isCacheEnabled || ! isset($cacheData[$cacheKey])) {

			$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
			try {
				$element = $elementfactory->getElementForTable($table, $uid);
			} catch ( Exception $e ) {
				return '-';
			}

			$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$languageList = $languageRep->getLanguages();

			$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

			$visibleFlags = array();
			foreach ( $languageList as $language ) {
				if ($visibility->isVisible($language, $element)) {
					$visibleFlags[] = $language->getFlagImg(0);
				}
			}

			$cacheData[$cacheKey] = implode('', $visibleFlags);
			$cacheManager->set('visibleFlagsCache', $cacheData);
		}

		return $cacheData[$cacheKey];
	}

	/**
	 * Helper function to get an element by uid and tablename.
	 *
	 * @param int $uid
	 * @param string $table
	 * @param boolean $overlay_ids
	 * @return tx_languagevisibility_element
	 */
	public static function getElement($uid, $table, $overlay_ids = TRUE) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid, $overlay_ids);

		return $element;
	}

	/**
	 * Helper function to check i an element with a given uid and tablename is visible for a languageid.
	 *
	 * @param int $uid
	 * @param string $table
	 * @param int $languageUid
	 * @param bool $omitLocal
	 * @return boolean
	 */
	public static function isVisible($uid, $table, $languageUid, $omitLocal = FALSE) {

		$cacheKey = sprintf('%s:%d:%d:%d', $table, $uid, $languageUid, $omitLocal);

		if (!isset(self::$cache_isVisible[$cacheKey])) {

			$rep = tx_languagevisibility_languagerepository::makeInstance();
			$language = $rep->getLanguageById($languageUid);

			$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);

			try {
				$element = $elementfactory->getElementForTable($table, $uid);
			} catch ( Exception $e ) {
				return FALSE;
			}

			$visibility = t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

			self::$cache_isVisible[$cacheKey] = $visibility->isVisible($language, $element, $omitLocal);
		}

		return self::$cache_isVisible[$cacheKey];
	}

	/**
	 * Helper function to check if the current backend user has rights to cut,copy or delete
	 *
	 * @return boolean
	 */
	public static function canCurrrentUserCutCopyMoveDelete() {
			//current element is no overlay -> if user has rights to cutMoveDelete or is an admin don't filter commants
		/** @var $beUser tx_languagevisibility_beUser */
		$beUser = t3lib_div::makeInstance('tx_languagevisibility_beUser');
		$userId = $beUser->getUid();

		if (!isset(self::$cache_canBeUserCopyDelete[$userId])) {
			if ($beUser->allowCutCopyMoveDelete() || $beUser->isAdmin()) {
				$result = TRUE;
			} else {
				$result = FALSE;
			}

			self::$cache_canBeUserCopyDelete[$userId] = $result;
		}

		return self::$cache_canBeUserCopyDelete[$userId];
	}

	/**
	 * Helper function to check if a record from a given table in an overlayrecord
	 *
	 * @param array $row
	 * @param string $table
	 * @return boolean
	 */
	public static function isOverlayRecord($row, $table) {

		$result = FALSE;

		switch ($table) {
			case 'pages_language_overlay' :
				$result = TRUE;
				break;
			case 'pages' :
				$result = FALSE;
				break;
			default:

				if (in_array($table, tx_languagevisibility_visibilityService::getSupportedTables())) {
					$tanslationIdField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
					if ($tanslationIdField != '') {
							// if the field which points to the orginal of the translation is
							// not 0 a translation exists and we have an overlay record
						$result = $row[$tanslationIdField] != 0;
					}
				}

				break;
		}

		return $result;
	}

	/**
	 * Static service method to determine if an record has a translation in any language
	 *
	 * @param int $uid
	 * @param string $table
	 * @return boolean
	 */
	public static function hasTranslationInAnyLanguage($uid, $table) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);

		try {
			$element = $elementfactory->getElementForTable($table, $uid);
			$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$languages = $rep->getLanguages();

			foreach ( $languages as $language ) {
					//skip default language
				if ($language->getUid() != 0) {
					if ($element->hasTranslation($language->getUid())) {
						return TRUE;
					}
				}
			}
		} catch ( UnexpectedValueException $e ) {
				//the element can not be handeld by language visibility
			return FALSE;
		}
		return FALSE;
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

	/**
	 * checks if the current BE_USER has access to the page record:
	 * that is the case if:
	 * a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 * b) edit page record: only if the record is only visible in languages where the user has access to
	 * b.1) also if the languages taht are visibile and falls back to allowed languages
	 * c) delete: same as for edit (only if user has access to all visible languages)
	 */
	public static function hasUserAccessToPageRecord($id, $cmd = 'edit') {
		if ($cmd == 'new') {
			return TRUE;
		}
		if (!is_numeric($id)) {
			return FALSE;
		}
		$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languages = $rep->getLanguages();
		foreach ( $languages as $language ) {
			if (self::isVisible($id, 'pages', $language->getUid())) {
				if (!$GLOBALS['BE_USER']->checkLanguageAccess($language->getUid())) {
						//no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder(self::getContextElement('pages', $id));
					foreach ($fallbacks as $lId) {
						if ($GLOBALS['BE_USER']->checkLanguageAccess($lId)) {
							$isInFallback = TRUE;
							continue;
						}
					}
					if (!$isInFallback) {
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}

	/**
	 * checks if the current BE_USER has access to a record:
	 * that is the case if:
	 * a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 * b) edit page record: only if the record is only visible in languages where the user has access to
	 */
	public static function hasUserAccessToEditRecord($table, $id) {
		if (!is_numeric($id)) {
			return FALSE;
		}
		if (!self::isSupportedTable($table)) {
			return TRUE;
		}

			// check if overlay record:
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$row = $dao->getRecord($id, $table);

			// @TODO check TCA for languagefield
		if (self::isOverlayRecord($row, $table)) {

			if ($GLOBALS['BE_USER']->checkLanguageAccess($row['sys_language_uid'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languages = $rep->getLanguages();
		foreach ($languages as $language) {
			if (self::isVisible($id, $table, $language->getUid())) {
				if (!$GLOBALS['BE_USER']->checkLanguageAccess($language->getUid())) {
						// no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder(self::getContextElement($table, $id));
					foreach ($fallbacks as $lId) {
						if ($GLOBALS['BE_USER']->checkLanguageAccess($lId)) {
								// TODO - write testcase - this can't be right
							$isInFallback = TRUE;
							continue;
						}
					}
					if (!$isInFallback) {
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}

	/**
	 * @param string $table
	 * @param int $id
	 * @return string
	 */
	protected function getContextElement($table, $id) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		try {
			$element = $elementfactory->getElementForTable($table, $id);
		} catch ( Exception $e ) {
			return '-';
		}
		return $element;
	}


	/**
	 * Method to check if the inheritance is enabled or not
	 *
	 * @return boolean
	 */
	protected function isInheritanceEnabled() {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
		if (is_array($confArr)) {
			return ($confArr['inheritanceEnabled'] == 1);
		} else {
			return FALSE;
		}
	}

	/**
	 * Method to check if the inheritance is enabled or not
	 *
	 * @return boolean
	 */
	protected function isTranslatedAsDefaultEnabled() {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
		if (is_array($confArr)) {
			return ($confArr['translatedAsDefaultEnabled'] == 1);
		} else {
			return FALSE;
		}
	}

	/**
	 * returns array with the visibility options that are allowed for the current user.
	 *
	 * @param tx_languagevisibility_language $language
	 * @param bool $isOverlay
	 * @param null $element
	 * @return array
	 */
	public static function getAvailableOptionsForLanguage(tx_languagevisibility_language $language, $isOverlay = FALSE, $element = NULL) {

		$element = $element === NULL ? self::getContextElement('pages', self::_guessCurrentPid()) : $element;

		$elementSupportsInheritance = $element->supportsInheritance();

		$uid = $language->getUid();
		$select = array();
		$useInheritance = ($elementSupportsInheritance && self::isInheritanceEnabled());

		if (! $isOverlay) {
			if ($uid == 0) {
				$select['-'] = '-';
				$select['yes'] = 'yes';
				$select['no'] = 'no';
				if ($useInheritance) {
					$select['no+'] = 'no+';
				}
			} else {
				$select['-'] = '-';
				$select['yes'] = 'yes';
				$select['no'] = 'no';
				if ($useInheritance) {
					$select['no+'] = 'no+';
				}
				$select['t'] = 't';
				$select['f'] = 'f';
			}

				//check permissions, if user has no permission only no for the language is allowed
				// if the user has permissions for languages that act as fallbacklanguage: then the languages that falls back can have "-" in the options!
			if (! $GLOBALS['BE_USER']->checkLanguageAccess($uid)) {

					//check if the language falls back to one of the languages the user has permissions:
				$isInFallback = FALSE;
				$fallbacks = $language->getFallbackOrder($element);
				foreach ( $fallbacks as $lId ) {
					if ($GLOBALS['BE_USER']->checkLanguageAccess($lId)) {
						$isInFallback = TRUE;
						continue;
					}
				}
				$select = array();
				if ($isInFallback) {
					$select['-'] = '-';
				}

				if ($uid != 0 && self::isTranslatedAsDefaultEnabled()) {
					$select['t'] = 't';
				}
				$select['no'] = 'no';
				if ($useInheritance) {
					$select['no+'] = 'no+';
				}
			}
		} else {
				//overlays elements can only have "force to no" or "force to no inherited"
			$select['-'] = '-';
			$select['no'] = 'no';
			if ($useInheritance) {
				$select['no+'] = 'no+';
			}
		}

		/**
		 * Get translations of labels from the locallang file
		 */
		if (is_object($GLOBALS['LANG'])) {
				//get value from locallang:
			foreach ( $select as $k => $v ) {
				$select[$k] = $GLOBALS['LANG']->sl('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.' . $v);
			}
		}

		return $select;
	}

	/**
	 * @return mixed
	 */
	protected static function _guessCurrentPid() {
		return t3lib_div::_GP('id');
	}

	/**
	 * This method is used to create an visibility array with the default settings
	 * for all languages.
	 *
	 * @return array
	 */
	public static function getDefaultVisibilityArray() {
		/* @var $languageRep tx_languagevisibility_languagerepository */
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languageList = $languageRep->getLanguages();
		$default = array();
		foreach ( $languageList as $language ) {
			$options = self::getAvailableOptionsForLanguage($language);
			$default[$language->getUid()] = array_shift(array_keys($options));

		}
		return $default;
	}

	/**
	 * This method is used to get the table where original elements of the
	 * given table are stored.
	 *
	 * @param string $table
	 * @return string
	 */
	public static function getOriginalTableOfTranslation($table) {
		$translationTable = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerTable'];
		if ($translationTable != '') {
			return $translationTable;
		} else {
			return $table;
		}
	}

	/**
	 * This method is used to determine the original uid of a translation
	 *
	 * @param array $row
	 * @param string $table
	 * @return string
	 */
	public static function getOriginalUidOfTranslation($row, $table) {
		if (is_array($row) && is_array($GLOBALS['TCA'])) {
			return $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']];
		} else {
			return 0;
		}
	}
}
