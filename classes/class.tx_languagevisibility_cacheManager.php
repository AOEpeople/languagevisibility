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
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_language.php');

class tx_languagevisibility_cacheManager {

	/**
	 * @var boolean
	 */
	protected static $useCache;

	/**
	 * @var boolean
	 */
	protected static $enableCache = true;

	/**
	 * @var tx_languagevisibility_cacheManager
	 */
	protected static $instance;

	/*
	 * Holds the cached data.
	 */
	protected $cache = array();

	/**
	 * @param void
	 * @return  void
	 */
	protected function __construct() {
		$this->cache = array();
	}

	public function __destruct() {
		/*echo round(memory_get_usage()/1048576,2)." megabytes";*/
	/*	$before = memory_get_usage();
		 unset($this->cache);
		 $after 	= memory_get_usage();

		 $mem_usage = $before - $after;
		 if ($mem_usage < 1024)
		 echo $mem_usage." bytes";
		 elseif ($mem_usage < 1048576)
		 echo round($mem_usage/1024,2)." kilobytes";
		 else
		 echo round($mem_usage/1048576,2)." megabytes"; */
	}

	/**
	 * Method to determine if preCaching should be used or not.
	 *
	 * @return boolean
	 */
	public static function isCacheEnabled() {
		if (! isset(self::$useCache)) {
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
			if (is_array($confArr) && $confArr['useCache']) {
				self::$useCache = ($confArr['useCache'] == 1);
			}
		}

		return (self::$useCache && self::$enableCache);
	}

	/**
	 * Use this method to force the cache usage.
	 *
	 * @param $boolean
	 * @return void
	 */
	public static function enableCache() {
		self::$enableCache = true;
	}

	/**
	 * Use this method to unforce the cache usage.
	 *
	 * @return void
	 */
	public static function disableCache() {
		self::$enableCache = false;
	}

	/**
	 * Flushed all caches.
	 *
	 * @return void
	 */
	public function flushAllCaches() {
		$this->cache = array();
	}

	/**
	 * Returns the cache array for a given name space.
	 *
	 * @param $namespace
	 * @return array
	 */
	public function get($namespace) {
		if (array_key_exists($namespace, $this->cache) && self::isCacheEnabled()) {
			return $this->cache[$namespace];
		} else {
			return array();
		}
	}

	/**
	 * Method to write content into the cache.
	 *
	 * @param $namespace
	 * @param $content
	 * @return void
	 */
	public function set($namespace, $content) {
		$this->cache[$namespace] = $content;
	}

	/**
	 * Returns an instance of the cacheManager singleton.
	 *
	 * @return  tx_languagevisibility_cacheManager
	 */
	public static function getInstance() {
		if (! self::$instance instanceof tx_languagevisibility_cacheManager) {
			self::$instance = new tx_languagevisibility_cacheManager();
		}

		return self::$instance;
	}

	/**
	 * Prevent from cloning
	 *
	 * @param void
	 * @return void
	 */
	public final function __clone() {
		trigger_error('Clone is not allowed for ' . get_class($this) . ' (Singleton)', E_USER_ERROR);
	}
}
?>