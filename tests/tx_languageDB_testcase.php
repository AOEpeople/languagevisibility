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

require_once (t3lib_extMgm::extPath("languagevisibility") . 'tests/tx_languagevisibility_databaseTestcase.php');
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_element.php');

class tx_languageDB_testcase extends tx_languagevisibility_databaseTestcase {


	/**
	 * The fallback order property is cached. This testcase
	 * should ensure that it can be read multiple times.
	 *
	 * @test
	 * @return void
	 */
	public function getFallBackOrderMultipleTimes() {

		$el = $this->getMockForAbstractClass('tx_languagevisibility_element', array(), '', false);

		$languageRepository = new tx_languagevisibility_languagerepository();

		/* @var $language tx_languagevisibility_language */
		$language = $languageRepository->getLanguageById(1);

		$this->assertEquals(array(0 => 0 ), $language->getFallbackOrder($el));
		$this->assertEquals(count(array(0 => 0 )), 1);
		$this->assertEquals(array(0 => 0 ), $language->getFallbackOrder($el));

		$this->assertFalse($language->isLanguageUidInFallbackOrder(22, $el));
		$this->assertTrue($language->isLanguageUidInFallbackOrder(0, $el));
	}

	/**

	 */
	function setUp() {
		parent::setUp();
		$this->importDataSet(dirname(__FILE__) . '/fixtures/dbDefaultLangs.xml');
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']);
	}
}

?>