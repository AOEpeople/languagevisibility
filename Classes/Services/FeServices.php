<?php

namespace AOE\Languagevisibility\Services;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 AOE GmbH <dev@aoe.com>
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
class FeServices extends AbstractServices {

	/**
	 * The cache frontend
	 *
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected static $cache = NULL;

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getFallbackOrderForElement($uid, $table, $lUid) {
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
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
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById($lUid);
		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

		return $visibility->isVisible($language, $element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @return \AOE\Languagevisibility\Element
	 */
	public static function getElement($uid, $table) {
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		/** @var $elementfactory \\AOE\\Languagevisibility\\ElementFactory */
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);

		return $element;
	}

	/**
	 * @param $element
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElement($element, $lUid) {
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById($lUid);
		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService'); /** @var $visibility \\AOE\\Languagevisibility\\Services\\VisibilityService */

		return $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
	}

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElementRecord($uid, $table, $lUid) {
		$cacheKey = sha1(implode('_', array(get_class(), __FUNCTION__, $uid, $table, $lUid)));
		if (self::getCache()->has($cacheKey)) {
			return self::getCache()->get($cacheKey);
		}
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');
		$overlayLanguageId = $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element);
		self::getCache()->set($cacheKey, $overlayLanguageId);
		return $overlayLanguageId;
	}

	/**
	 * @param $uid
	 * @param $table
	 * @param $lUid
	 * @return mixed
	 */
	public static function getOverlayLanguageIdForElementRecordForced($uid, $table, $lUid) {
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$element = $elementfactory->getElementForTable($table, $uid);
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById($lUid);

		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');
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
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);

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
	 * Gets the cache frontend for tx_languagevisibility
	 *
	 * @return \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	public static function getCache() {
		if (!self::$cache) {
			self::$cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
				->getCache('tx_languagevisibility');
		}
		return self::$cache;
	}
}
