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
require_once (t3lib_extMgm::extPath("languagevisibility") . 'patch/lib/class.tx_languagevisibility_beUser.php');

class tx_languagevisibility_beservices {

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
			if (version_compare(TYPO3_version, '4.3.0', '<')) {
				$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
				$elementfactory = new $elementfactoryName($dao);
			} else {
				$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
			}
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
	public static function getElement($uid, $table, $overlay_ids = true) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		$element = $elementfactory->getElementForTable($table, $uid, $overlay_ids);
		return $element;
	}

	/**
	 * Helper function to check i an element with a given uid and tablename is visible for a languageid.
	 *
	 * @param int $uid
	 * @param string $table
	 * @param int $languageUid
	 * @return boolean
	 */
	public static function isVisible($uid, $table, $languageUid, $omitLocal=false) {

		$cacheKey = sprintf('%s:%d:%d:%d', $table, $uid, $languageUid, $omitLocal);

		if (! isset(self::$cache_isVisible[$cacheKey])) {

			$rep = tx_languagevisibility_languagerepository::makeInstance();
			$language = $rep->getLanguageById($languageUid);

			$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
			if (version_compare(TYPO3_version, '4.3.0', '<')) {
				$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
				$elementfactory = new $elementfactoryName($dao);
			} else {
				$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
			}

			try {
				$element = $elementfactory->getElementForTable($table, $uid);
			} catch ( Exception $e ) {
				return false;
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
		/* @var $be_user tx_languagevisibility_beUser */
		$be_user = t3lib_div::makeInstance('tx_languagevisibility_beUser');
		$userId = $be_user->getUid();

		if (! isset(self::$cache_canBeUserCopyDelete[$userId])) {
			if ($be_user->allowCutCopyMoveDelete() || $be_user->isAdmin()) {
				$result = true;
			} else {
				$result = false;
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
		switch ($table) {
			case 'tt_news' :
			case 'tt_content' :
				global $TCA;
				t3lib_div::loadTCA($table);
				$tanslationIdField = $TCA[$table]['ctrl']['transOrigPointerField'];

				if ($tanslationIdField != '') {
					//if the field which points to the orginal of the translation is
					//not 0 a translation exists and we have an overlay record


					return $row[$tanslationIdField] != 0;
				} else {
					//if no translation field exists this is not an overlay
					return false;
				}
				break;

			case 'pages_language_overlay' :
				return true;
				break;
			case 'pages' :
				return false;
				break;
		}
	}

	/**
	 * Method to check if records of a given table support the languagevisibility feature
	 *
	 * @param string $table
	 * @return boolean
	 */
	public static function isSupportedTable($table) {
		$supported = array('tt_news', 'tt_content', 'pages' );
		if (in_array($table, $supported)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Static service method to determine if an record has a translation in any language
	 *
	 * @param int $uid
	 * @param string $table
	 * @return boolean.
	 */
	public static function hasTranslationInAnyLanguage($uid, $table) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		try {
			$element = $elementfactory->getElementForTable($table, $uid);
			$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$languages = $rep->getLanguages();

			foreach ( $languages as $language ) {
				//skip default language
				if ($language->getUid() != 0) {
					if ($element->hasTranslation($language->getUid())) {
						return true;
					}
				}
			}
		} catch ( UnexpectedValueException $e ) {
			//the element can not be handeld by language visibility
			return false;
		}
		return false;
	}

	/**
	 * checks if the current BE_USER has access to the page record:
	 * that is the case if:
	 * a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 * b) edit page record: only if the record is only visible in languages where the user has access to
	 * b.1) also if the languages taht are visibile and falls back to allowed languages
	 * c) delete: same as for edit (only if user has access to all visible languages)
	 **/
	public static function hasUserAccessToPageRecord($id, $cmd = 'edit') {

		global $BE_USER;
		if ($cmd == 'new') {
			return true;
		}
		if (! is_numeric($id)) {
			return false;
		}
		$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languages = $rep->getLanguages();
		foreach ( $languages as $language ) {
			//echo 'check '.$language->getUid();
			if (self::isVisible($id, 'pages', $language->getUid())) {
				if (! $BE_USER->checkLanguageAccess($language->getUid())) {
					//no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder(self::getContextElement('pages', $id));
					foreach ( $fallbacks as $lId ) {
						if ($GLOBALS['BE_USER']->checkLanguageAccess($lId)) {
							$isInFallback = TRUE;
							continue;
						}
					}
					if (! $isInFallback)
						return false;
				}
			}
		}
		return true;
	}

	/**
	 * checks if the current BE_USER has access to a record:
	 * that is the case if:
	 * a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 * b) edit page record: only if the record is only visible in languages where the user has access to
	 **/
	public static function hasUserAccessToEditRecord($table, $id) {
		global $BE_USER;

		if (! is_numeric($id)) {
			return false;
		}
		if (! self::isSupportedTable($table)) {
			return true;
		}

		//check if overlay record:
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$row = $dao->getRecord($id, $table);

		//@TODO check TCA for languagefield
		if (self::isOverlayRecord($row, $table)) {

			if ($BE_USER->checkLanguageAccess($row['sys_language_uid']))
				return true;
			else
				return false;
		}

		$rep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		$languages = $rep->getLanguages();
		foreach ( $languages as $language ) {
			if (tx_languagevisibility_beservices::isVisible($id, $table, $language->getUid())) {
				if (! $BE_USER->checkLanguageAccess($language->getUid())) {
					//no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder(self::getContextElement($table, $id));
					foreach ( $fallbacks as $lId ) {
						if ($GLOBALS['BE_USER']->checkLanguageAccess($lId)) {
							//TODO - write testcase - this can't be right
							$isInFallback = TRUE;
							continue;
						}
					}
					if (! $isInFallback)
						return false;
				}
			}
		}
		return true;
	}

	/**
	 *
	 * @param unknown_type $table
	 * @param unknown_type $id
	 * @return
	 */
	protected function getContextElement($table, $id) {
		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}
		try {
			$element = $elementfactory->getElementForTable('pages', $uid);
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
			return false;
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
			return false;
		}
	}

	/**
	 * returns array with the visibility options that are allowed for the current user.
	 *
	 * @param tx_languagevisibility_language $language
	 * @return array
	 */
	public static function getAvailableOptionsForLanguage(tx_languagevisibility_language $language, $isOverlay = false, $element = null) {

		$element = $element === null ? self::getContextElement('pages', self::_guessCurrentPid()) : $element;

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
			$options = tx_languagevisibility_beservices::getAvailableOptionsForLanguage($language);
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
		global $TCA;
		t3lib_div::loadTCA($table);

		$translationTable = $TCA[$table]['ctrl']['transOrigPointerTable'];
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
		global $TCA;
		t3lib_div::loadTCA($table);

		if (is_array($row) && is_array($TCA)) {
			return $row[$TCA[$table]['ctrl']['transOrigPointerField']];
		} else {
			return 0;
		}
	}
}
?>