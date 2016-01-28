<?php

namespace AOE\Languagevisibility\Tests\Functional;
use AOE\Languagevisibility\Language;
use AOE\Languagevisibility\LanguageRepository;

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

class LanguageDbTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	public function setUp(){
		parent::setUp();
		$this->importDataSet(__DIR__ . '/Fixtures/dbDefaultLangs.xml');
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']);
	}

	/**
	 * The fallback order property is cached. This testcase
	 * should ensure that it can be read multiple times.
	 *
	 * @test
	 * @return void
	 */
	public function getFallBackOrderMultipleTimes() {

		$el = $this->getMockForAbstractClass('AOE\\Languagevisibility\\Element', array(), '', FALSE);

		$languageRepository = new LanguageRepository();

		/* @var $language Language */
		$language = $languageRepository->getLanguageById(1);

		$this->assertEquals(array(0 => 0 ), $language->getFallbackOrder($el));
		$this->assertEquals(count(array(0 => 0 )), 1);
		$this->assertEquals(array(0 => 0 ), $language->getFallbackOrder($el));

		$this->assertFalse($language->isLanguageUidInFallbackOrder(22, $el));
		$this->assertTrue($language->isLanguageUidInFallbackOrder(0, $el));
	}
}
