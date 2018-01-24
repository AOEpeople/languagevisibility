<?php

namespace AOE\Languagevisibility\Dao;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class DaoCommon
 * @package AOE\Languagevisibility
 */
class DaoCommon implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected static $cache = NULL;

	/**
	 * Gets a record by UID and table
	 *
	 * @param int $uid
	 * @param string $table
	 * @return array
	 */
	public static function getRecord($uid, $table) {
		$cacheKey = sha1($table . $uid);

		if (!self::getCache()->has($cacheKey)) {
			self::getCache()->set($cacheKey, self::getRequestedRecord($uid, $table));
		}

		return self::getCache()->get($cacheKey);
	}

	/**
	 * Gets a requested record
	 *
	 * @param int $uid
	 * @param string $table
	 * @return array
	 */
	protected static function getRequestedRecord($uid, $table) {
		$result = self::getDatabase()->exec_SELECTquery(
			'*',
			$table,
			'uid=' . intval($uid)
		);
		$row = self::getDatabase()->sql_fetch_assoc($result);
		self::getDatabase()->sql_free_result($result);

		return $row;
	}

	/**
	 * Gets records by table and where clause
	 *
	 * @param string $table
	 * @param string $where
	 * @return array
	 */
	public static function getRecords($table, $where) {
		$cacheKey = sha1($table . $where);

		if (!self::getCache()->has($cacheKey)) {
			self::getCache()->set($cacheKey, self::getRequestedRecords($table, $where));
		}

		return self::getCache()->get($cacheKey);
	}

	/**
	 * Gets requested records by table and where clause
	 *
	 * @param string $table
	 * @param string $where
	 * @return array
	 */
	protected static function getRequestedRecords($table, $where) {
		$result = self::getDatabase()->exec_SELECTquery(
			'*',
			$table,
			$where
		);
		$rows = array();
		while ($row = self::getDatabase()->sql_fetch_assoc($result)) {
			$rows[] = $row;
		}
		self::getDatabase()->sql_free_result($result);

		return $rows;
	}

	/**
	 * Gets the cache
	 *
	 * @return \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected static function getCache() {
		if (!self::$cache) {
			self::$cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
				->getCache('tx_languagevisibility');
		}

		return self::$cache;
	}

	/**
	 * Gets a database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected static function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}
