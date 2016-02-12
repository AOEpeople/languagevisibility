<?php

namespace AOE\Languagevisibility\Tests\Unit;

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
 * @author	Daniel Pötzinger
 */
class LanguageTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']);
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function getLanguageUidDB() {
		// Create the Array fixture.
		$fixture = array('uid' => 1 );

		$language = new \AOE\Languagevisibility\Language();
		$language->setData($fixture);

		// Assert that the size of the Array fixture is 0.
		$this->assertEquals(1, $language->getUid(), 'wrong uid 1');
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function getFallbackOrder() {

		$el = $this->getMockForAbstractClass('AOE\\Languagevisibility\\Element', array(), 'tx_languagevisibility_element_x', FALSE);

		// Create the Array fixture.
		$fixture = array('uid' => 1, 'tx_languagevisibility_complexfallbacksetting' => '0', 'tx_languagevisibility_fallbackorder' => '0,1,2', 'tx_languagevisibility_fallbackorderel' => '0,1', 'tx_languagevisibility_fallbackorderttnewsel' => '0,2' );

		$language = new Language();
		$language->setData($fixture);

		$this->assertEquals(array('0', '1', '2' ), $language->getFallbackOrder($el), "wrong getFallbackOrder");
		$this->assertEquals(array('0', '1', '2' ), $language->getFallbackOrderElement($el), "wrong getFallbackOrder - complex applied where normal is excepted");
		$this->assertEquals(array('0', '1', '2' ), $language->getFallbackOrderTTNewsElement($el), "wrong getFallbackOrder - complex applied where normal is excepted"); // Create the Array fixture.


		$this->assertTrue($language->isLanguageUidInFallbackOrder(0, $el));
		$this->assertTrue($language->isLanguageUidInFallbackOrder(2, $el));
		$this->assertFalse($language->isLanguageUidInFallbackOrder(4711, $el));

		$fixture = array('uid' => 1, 'tx_languagevisibility_complexfallbacksetting' => '1', 'tx_languagevisibility_fallbackorder' => '0,1,2', 'tx_languagevisibility_fallbackorderel' => '0,1', 'tx_languagevisibility_fallbackorderttnewsel' => '0,2' );

		$language = new Language();
		$language->setData($fixture);

		$this->assertEquals(array('0', '1', '2' ), $language->getFallbackOrder($el), "wrong getFallbackOrder");
		$this->assertEquals(array('0', '1' ), $language->getFallbackOrderElement($el), "wrong getFallbackOrder");
		$this->assertEquals(array('0', '2' ), $language->getFallbackOrderTTNewsElement($el), "wrong getFallbackOrder");
	}

	/**
	 *
	 * @return void
	 */
	public function canGetGlobalVisibility() {
		// Create the Array fixture.
		$fixture = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't', 'tx_languagevisibility_defaultvisibilityel' => 'f', 'tx_languagevisibility_defaultvisibilityttnewsel' => 'y' );

		$language = new Language();
		$language->setData($fixture);

		$this->assertEquals('y', $language->getDefaultVisibilityForTTNewsElement(), "wrong visibility");
		$this->assertEquals('f', $language->getDefaultVisibilityForElement(), "wrong visibility");
		$this->assertEquals('t', $language->getDefaultVisibilityForPage(), "wrong visibility");
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function getIsoCode() {
		// TODO ->getIsoCode should be refactored so that we do not need to mock internals
		// or remove this unit test altogether or move this to functional tests
		$databaseMock = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
		$databaseMock->expects($this->once())
			->method('exec_SELECTquery')
			->with('lg_iso_2', 'static_languages', 'uid=49', '', '');
		$databaseMock->expects($this->once())
			->method('sql_fetch_assoc')
			->will($this->returnValue(array('lg_iso_2' => 'HE')));
		$GLOBALS['TYPO3_DB'] = $databaseMock;

		// Create the Array fixture.
		$fixture = array('uid' => 1, 'static_lang_isocode' => '49' );

		$language = new Language();
		$language->setData($fixture);

		// Assert that the size of the Array fixture is 0.
		$this->assertEquals('HE', $language->getIsoCode(), "wrong getIsoCode");
	}
}
