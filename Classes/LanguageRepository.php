<?php

namespace AOE\Languagevisibility;

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
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
class LanguageRepository {

	protected static $instance;

	/**
	 * Internal method to fetch all language rows from the database.
	 *
	 * @see self::$allLanguageRows
	 * @param void
	 * @return void
	 */
	protected function fetchAllLanguageRows() {
		$cacheManager = CacheManager::getInstance();
		$cacheData = $cacheManager->get('allLanguageRows');

		if (count($cacheData) <= 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', '', '', '', '');
			while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
				$cacheData[$row['uid']] = $row;
			}

			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			$cacheManager->set('allLanguageRows', $cacheData);
		}
	}

	/**
	 * Returns an array with all languages depending on the cache setting directly from
	 * the database or cached.
	 *
	 * @return array
	 */
	protected function getCachedOrUncacheResults() {
		$results = array();
		$cacheManager = CacheManager::getInstance();
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if ($isCacheEnabled) {
			$this->fetchAllLanguageRows();
			$results = $cacheManager->get('allLanguageRows');
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', '', '', '', '');
			while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
				$results[] = $row;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		return $results;
	}

	/**
	 * This method returns an array with all available language objects in the system.
	 *
	 * @see tx_languagevisibility_language
	 * @return array
	 */
	public function getLanguages() {
		$return = array();
		$results = $this->getCachedOrUncacheResults();

		$return[] = $this->getDefaultLanguage();
		foreach ( $results as $row ) {
			$language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Language');
			$language->setData($row);
			$return[] = $language;
		}

		return $return;
	}

	/**
	 * Returns an array with all available languages of a backend user.
	 *
	 * @return array
	 */
	public function getLanguagesForBEUser() {
		$return = array();
		$results = $this->getCachedOrUncacheResults();

		if ($GLOBALS['BE_USER']->checkLanguageAccess(0)) {
			$return[] = $this->getDefaultLanguage();
		}

		foreach ( $results as $row ) {
			if ($GLOBALS['BE_USER']->checkLanguageAccess($row['uid'])) {
				$language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Language');
				$language->setData($row);
				$return[] = $language;
			}
		}

		return $return;
	}

	/**
	 * Retruns an instance of the language object for the default language.
	 *
	 * @param void
	 * @return tx_languagevisibility_language
	 */
	public function getDefaultLanguage() {
		$row = array();
		$language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Language');
		$row['uid'] = 0;
		$row['title'] = 'Default';

		$language->setData($row);
		return $language;
	}

	/**
	 * Returns an instance for a language by the id.
	 * Note: since the language is an value object all languages can be cached
	 *
	 * @param $id
	 * @return tx_languagevisibility_language
	 */
	public function getLanguageById($id) {
		$cacheManager = CacheManager::getInstance();
		$cacheData = $cacheManager->get('languagesCache');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if (! $isCacheEnabled || ! isset($cacheData[$id])) {
			if ($id == 0) {
				$cacheData[$id] = $this->getDefaultLanguage();
			} else {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', 'uid=' . intval($id), '', '', '');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Language');

				$language->setData($row);
				$id = is_array($id) ?
					array_shift($id) :
					$id;
				$cacheData[$id] = $language;
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				$cacheManager->set('languagesCache', $cacheData);
			}
		}
		return $cacheData[$id];
	}

	/**
	 * returns an instance of the language repository as singleton.
	 *
	 * @param void
	 * @return Languagerepository
	 */
	public static function makeInstance() {
		if (! self::$instance instanceof LanguageRepository) {
			self::$instance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		}

		return self::$instance;
	}
}
