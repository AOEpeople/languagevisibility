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

require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

class tx_cachedWorkflow_testcase extends tx_languagevisibility_databaseTestcase{
	/**
	 * Force the enable state of the cache and flush it.
	 *
	 * @param void
	 * @return void
	 */
	public function setUp(){
		parent::setUp();
			//TODO get rid of the extConf push/pop stuff
		$this->oldExtConfSetting = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'];
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = serialize(array('useCache'=>1));
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = $this->oldExtConfSetting;
	}

	/**
	 * Situation:
	 *
	 * We have a page structure like the following:
	 *
	 *  a (uid 1)
	 *  |
	 *  +--- b (uid 2)
	 *       |
	 *       +---- c (uid 3)
	 *
	 * Page a has the inherited force to no setting (no +) for the
	 * language 3. Therefor the element should not be visible.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @test
	 */
	public function canDetermineInheritedVisibility(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new tx_languagevisibility_language();
		$fixtureLanguage->setData($fixtureLanguageRow);

		$dao				= new tx_languagevisibility_daocommon();
		$elementFactory 	= new tx_languagevisibility_elementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages',3);

		$visibilityService 	= new tx_languagevisibility_visibilityService();
		$visibilityService->setUseInheritance();
		$visibilityResult	= true;
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);

		$this->assertFalse($visibilityResult,'The element should not be visibile because of the inherited force to no setting');
	}

	/**
	 * We have the same situation as before but in this
	 * testcase we test the visibility of page c and change
	 * the pid afterward. Because the visibility is forced
	 * to no by inheritance, it should normaly be visible,
	 * but the result of the visibility is chached in
	 * that situation and the visibility will only change afterwards
	 * when the cache was flushed.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @test
	 */
	public function changeOfPidDoesNotInfluenceCachedResult(){

		$cacheManager	= tx_languagevisibility_cacheManager::getInstance();
		$isCacheEnabled	= $cacheManager->isCacheEnabled();
		$this->assertTrue($isCacheEnabled, 'Cache needs to be enabled to perform this test');

		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new tx_languagevisibility_language();
		$fixtureLanguage->setData($fixtureLanguageRow);

		$dao				= new tx_languagevisibility_daocommon();
		$elementFactory 	= new tx_languagevisibility_elementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages',3);

		$visibilityService 	= new tx_languagevisibility_visibilityService();
		$visibilityResult	= true;
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);
		$this->assertFalse($visibilityResult,'The element should not be visibile because of the inherited force to no setting');

		$db = $GLOBALS['TYPO3_DB'];
		/* @var  $db t3lib_db */
		$db->exec_UPDATEquery('pages','pid=2',array('pid' => 0));
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);
		$this->assertFalse($visibilityResult,'The element should not still not be visible because the visibility result is cached');

		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();

		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);
		$this->assertTrue($visibilityResult,'Now the element should be visible because the cache was flushed');
	}
}
?>