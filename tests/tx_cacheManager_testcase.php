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

class tx_cacheManager_testcase extends tx_languagevisibility_baseTestcase {

	protected $oldExtConfSetting;

	/**
	 * Force the enable state of the cache and flush it.
	 *
	 * @param void
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
			//TODO get rid of the extConf push/pop stuff
		$this->oldExtConfSetting = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'];
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = serialize(array('useCache' => 1));

		tx_languagevisibility_cacheManager::enableCache();
		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = $this->oldExtConfSetting;

		parent::tearDown();
	}

	/**
	 * This method should be used to test that the cacheManager is a
	 * singleton.
	 *
	 * @test
	 * @return void
	 */
	public function cacheManagerIsSingleton() {
		$one 	= tx_languagevisibility_cacheManager::getInstance();
		$two	= tx_languagevisibility_cacheManager::getInstance();

		$one->set('test', '12345');
		$this->assertEquals($two->get('test'), '12345');
	}

	/**
	 * This method is used to check if the cache can be flushed.
	 *
	 * @test
	 * @return void
	 */
	public function canFlushCache() {
		$cache 	= tx_languagevisibility_cacheManager::getInstance();
		$cache->flushAllCaches();

		$cache->set('test', array('one' => 'blabla'));

		$resultA = $cache->get('test');
		$this->assertTrue(is_array($resultA));
		$this->assertEquals($resultA['one'], 'blabla');

		$cache->flushAllCaches();
		$resultB = $cache->get('test');

		$this->assertTrue(empty($resultB));
	}

	/**
	 * This method is used to test if the cache can be disabled global.
	 *
	 * @test
	 * @return void
	 */
	public function canCacheBeDisabled() {
		$cache = tx_languagevisibility_cacheManager::getInstance();

		$cache->enableCache();
		$this->assertTrue($cache->isCacheEnabled());
		$cache->disableCache();

		$this->assertFalse($cache->isCacheEnabled());
	}

	/**
	 * This method is used to test if a disabled cache returns cache hits.
	 *
	 * @test
	 * @return void
	 */
	public function disabledCacheReturnsNoData() {
		$cache = tx_languagevisibility_cacheManager::getInstance();
		$cache->enableCache();

		$cache->flushAllCaches();

		$cache->set('aaaa', 12);
		$this->assertEquals(12, $cache->get('aaaa'));

		$cache->disableCache();

		$this->assertEquals(array(), $cache->get('aaaa'));

		$cache->enableCache();
		$this->assertEquals(12, $cache->get('aaaa'));
	}
}
