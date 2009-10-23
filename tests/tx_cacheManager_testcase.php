<?php

class tx_cacheManager_testcase extends tx_phpunit_testcase{

	protected $oldExtConfSetting;

	/**
	 * Force the enable state of the cache and flush it.
	 *
	 * @param void
	 * @return void
	 */
	public function setUp(){

			//TODO get rid of the extConf push/pop stuff
		$this->oldExtConfSetting = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'];
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = serialize(array('useCache'=>1));

		tx_languagevisibility_cacheManager::enableCache();
		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = $this->oldExtConfSetting;
	}

	/**
	 * This method should be used to test that the cacheManager is a
	 * singleton.
	 *
	 * @test
	 * @return void
	 */
	public function cacheManagerIsSingleton(){
		$one 	= tx_languagevisibility_cacheManager::getInstance();

		$two	= tx_languagevisibility_cacheManager::getInstance();

		$one->set('test','12345');
		$this->assertEquals($two->get('test'),'12345');
	}

	/**
	 * This method is used to check if the cache can be flushed.
	 *
	 * @test
	 * @return void
	 */
	public function canFlushCache(){
		$cache 	= tx_languagevisibility_cacheManager::getInstance();
		$cache->flushAllCaches();

		$cache->set('test',array('one' => 'blabla'));

		$resultA = $cache->get('test');
		$this->assertTrue(is_array($resultA));
		$this->assertEquals($resultA['one'],'blabla');

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
	public function canCacheBeDisabled(){
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
	public function disabledCacheReturnsNoData(){
		$cache = tx_languagevisibility_cacheManager::getInstance();
		$cache->enableCache();

		$cache->flushAllCaches();

		$cache->set('aaaa',12);
		$this->assertEquals(12,$cache->get('aaaa'));

		$cache->disableCache();

		$this->assertEquals(array(),$cache->get('aaaa'));

		$cache->enableCache();
		$this->assertEquals(12,$cache->get('aaaa'));
	}
}
?>