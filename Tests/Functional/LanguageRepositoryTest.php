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
use AOE\Languagevisibility\Language;

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 *
 * @author	Daniel Pötzinger
 */
class LanguageRepositoryTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	function setUp() {
		parent::setUp();
		$this->importDataSet(__DIR__ . '/Fixtures/dbDefaultLangs.xml');
	}

	public function test_getLanguages() {
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$languageList = $languageRep->getLanguages();
		$this->assertTrue(is_array($languageList), "no array");

	}

	public function test_getLanguageById() {
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById(0);
		$this->assertTrue($language instanceof Language, "no language object");
		$this->assertEquals($language->getUid(), 0, "wrong uid");

		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$language = $languageRep->getLanguageById(1);
		$this->assertTrue($language instanceof Language, "no language object");
		$this->assertEquals($language->getUid(), 1, "wrong uid");
	}
}
