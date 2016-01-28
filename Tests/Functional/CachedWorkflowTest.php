<?php

namespace AOE\Languagevisibility\Tests\Functional;

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

use AOE\Languagevisibility\CacheManager;
use AOE\Languagevisibility\Dao\DaoCommon;
use AOE\Languagevisibility\ElementFactory;
use AOE\Languagevisibility\Language;
use AOE\Languagevisibility\Services\VisibilityService;

/**
 * Class CachedWorkflowTest
 * @package AOE\Languagevisibility\Tests\Functional
 */
class CachedWorkflowTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	/**
	 * Force the enable state of the cache and flush it.
	 *
	 * @param void
	 * @return void
	 */
	public function setUp() {
		$optionalExtensionKeys = array('static_info_tables', 'templavoila');
		foreach ($optionalExtensionKeys as $extensionKey) {
			$extensionPath = 'typo3conf/ext/' . $extensionKey;
			if (is_dir(ORIGINAL_ROOT . $extensionPath)) {
				$this->testExtensionsToLoad[] = $extensionPath;
			}
		}
		parent::setUp();
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility'] = serialize(array('useCache' => 1));
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
	 * Page a has the inherited force to no setting (no +),
	 * therefore the element should not be visible.
	 *
	 * @test
	 */
	public function canDetermineInheritedVisibility() {
		$this->importDataSet(__DIR__ . '/Fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new Language();
		$fixtureLanguage->setData($fixtureLanguageRow);

		$dao				= new DaoCommon();
		$elementFactory 	= new ElementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages', 3);

		$visibilityService 	= new VisibilityService();
		$visibilityService->setUseInheritance();
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage, $fixtureElement);

		$this->assertFalse($visibilityResult, 'The element should not be visible because of the inherited force to no setting');
	}

	/**
	 * We have the same situation as before but in this
	 * test we check the visibility of page c and change
	 * the pid afterward. Because the visibility is forced
	 * to no by inheritance, it should normally be visible,
	 * but the result of the visibility is cached in
	 * that situation and the visibility will only change afterwards
	 * when the cache was flushed.
	 *
	 * @param void
	 * @return void
	 * @test
	 */
	public function changeOfPidDoesNotInfluenceCachedResult() {
		$cacheManager	= CacheManager::getInstance();
		$isCacheEnabled	= $cacheManager->isCacheEnabled();
		$this->assertTrue($isCacheEnabled, 'Cache needs to be enabled to perform this test');

		// Same code as in canDetermineInheritedVisibility() used to populate the cache now
		$this->importDataSet(__DIR__ . '/Fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new Language();
		$fixtureLanguage->setData($fixtureLanguageRow);

		$dao				= new DaoCommon();
		$elementFactory 	= new ElementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages', 3);

		$visibilityService 	= new VisibilityService();
		$visibilityService->setUseInheritance();
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage, $fixtureElement);

		$this->assertFalse($visibilityResult, 'The element should not be visible because of the inherited force to no setting');

		$db = $GLOBALS['TYPO3_DB'];
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
		$db->exec_UPDATEquery('pages', 'pid=2', array('pid' => 0));
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage, $fixtureElement);
		$this->assertFalse($visibilityResult, 'The element should not still not be visible because the visibility result is cached');

		CacheManager::getInstance()->flushAllCaches();

		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage, $fixtureElement);
		$this->assertTrue($visibilityResult, 'Now the element should be visible because the cache was flushed');
	}
}
