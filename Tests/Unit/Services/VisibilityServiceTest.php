<?php

namespace AOE\Languagevisibility\Tests\Unit\Services;

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

use AOE\Languagevisibility\Dao\DaoCommonStub;
use AOE\Languagevisibility\ElementFactory;
use AOE\Languagevisibility\Language;
use AOE\Languagevisibility\PageElement;
use AOE\Languagevisibility\Services\VisibilityService;
use AOE\Languagevisibility\TtnewsElement;

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 * @author	Daniel Pötzinger
 */
class VisibilityServiceTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	public function setUp() {
		$this->markTestIncomplete('Needs refactoring');
	}

	/**
	 * @test
	 */
	public function canGetSupportedTables() {
		$expectedSupportedTables = array('pages', 'pages_language_overlay', 'tt_content', 'tt_news');
		$supportedTables = VisibilityService::getSupportedTables();

		$this->assertTrue(is_array($supportedTables));

		foreach ($expectedSupportedTables as $expectedSupportedTable) {
			$this->assertContains($expectedSupportedTable, $supportedTables);
		}
	}

	public function test_visibility() {

		// Create the language object fixture.
		$fixture = array('uid' => 1, 'tx_languagevisibility_fallbackorder' => '2' );
		$language1 = new Language();
		$language1->setData($fixture);

		$rep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$deflanguage = $rep->getDefaultLanguage();
		//Create the element object fixture.
		$_table = 'tt_content';
		$_uid = 1;
		$visibility = array('0' => 'yes', '1' => 't', '2' => '' );
		$fixture = array('uid' => $_uid, 'tx_languagevisibility_visibility' => serialize($visibility) );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);

		//test
		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

		// language 1 should be set local to "t"
		$this->assertEquals('t', $visibility->getVisibilitySetting($language1, $element), "setting t expected");
		$this->assertEquals(TRUE, $visibility->isVisible($deflanguage, $element), "default lang should be visible");
	}

	public function test_visibility_fixture_ce() {

		$language = $this->_fixture_getLanguageOneWithDefaultFallback();
		$element = $this->_fixture_getElementWithDefaultVisibility();

		//test
		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

		// language 1 should be set local to "t"
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language, $element), "setting f expected (because default is used)");
		$this->assertEquals(TRUE, $visibility->isVisible($language, $element), "default lang should be visible");
		$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element), "default should be overlay");
	}

	public function test_visibility_fixture_page() {

		$language = $this->_fixture_getLanguageOneWithDefaultFallback();
		$element = $this->_fixture_getPageElementWithDefaultVisibility();

		//test
		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

		// language 1 should be set local to "t"
		$this->assertTrue($element instanceof PageElement, 'factory return instance of wrong type');
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language, $element), "setting f expected (because default is used)");
		$this->assertEquals(TRUE, $visibility->isVisible($language, $element), "default lang should be visible");
		$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language, $element), "default record should be overlay");
	}

	public function test_visibility_fixture_news() {

		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_news')) {
			$this->markTestSkipped('Not relevant if "tt_news" is not installed');
		}

		$language = $this->_fixture_getLanguageOneWithDefaultFallback();
		$element = $this->_fixture_getNewsElementWithDefaultVisibility();

		$visibility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

		$this->assertTrue($element instanceof TtnewsElement, 'factory returned instance of wrong type');
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language, $element), "setting f expected (because default is used)");
		$this->assertEquals(TRUE, $visibility->isVisible($language, $element), "default lang should be visible");

	}

	function _fixture_getLanguageOneWithDefaultFallback() {
		// Create the language object fixture.
		$fixture = array('uid' => 1, 'tx_languagevisibility_fallbackorder' => '0,1', 'tx_languagevisibility_fallbackorderel' => '0,1', 'tx_languagevisibility_defaultvisibility' => 'f', 'tx_languagevisibility_defaultvisibilityel' => 'f', 'tx_languagevisibility_defaultvisibilityttnewsel' => 'f' );
		$language1 = new Language();
		$language1->setData($fixture);
		return $language1;
	}

	function _fixture_getLanguageThreeWithMultiFallback() {
		// Create the language object fixture.
		$fixture = array('uid' => 1, 'tx_languagevisibility_fallbackorder' => '0,1', 'tx_languagevisibility_fallbackorderel' => '0,1', 'tx_languagevisibility_defaultvisibility' => 'f', 'tx_languagevisibility_defaultvisibilityel' => 'f' );
		$language1 = new Language();
		$language1->setData($fixture);
		return $language1;
	}

	function _fixture_getLanguageFourWithElementFallback() {
		$fixture = array('uid' => 2, 'tx_languagevisibility_fallbackorder' => '0,1', 'tx_languagevisibility_fallbackorderel' => '1', 'tx_languagevisibility_defaultvisibility' => 'f', 'tx_languagevisibility_defaultvisibilityel' => 'f' );
		$language4 = new Language();
		$language4->setData($fixture);
		return $language4;
	}

	function _fixture_getElementWithDefaultVisibility() {
		//Create the element object fixture.
		$_table = 'tt_content';
		$_uid = 9999;
		$visibility = array('0' => '-', '1' => '-', '2' => '-' );
		$fixture = array('uid' => $_uid, 'sys_language_uid' => '0', 'tx_languagevisibility_visibility' => serialize($visibility) );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);
		return $element;
	}

	function _fixture_getElementWithLangOneVisibility() {
		//Create the element object fixture.
		$_table = 'tt_content';
		$_uid = 9999;
		$visibility = array('0' => '-', '1' => '-', '2' => '-' );
		$fixtureL1Rec = array('uid' => $_uid, 'l18n_parent' => $_uid - 1, 'sys_language_uid' => 1, 'tx_languagevisibility_visibility' => serialize($visibility) );
		$fixtureL0Rec = array('uid' => $_uid - 1, 'sys_language_uid' => 0 );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixtureL1Rec, $_table);
		$daostub->stub_setRow($fixtureL0Rec, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);
		return $element;
	}

	function _fixture_getFCEElementWithDefaultVisibility() {
		//Create the element object fixture.
		$_table = 'tt_content';
		$_uid = 9999;
		$visibility = array('0' => '-', '1' => '-', '2' => '-' );
		$fixture = array('uid' => $_uid, 'tx_languagevisibility_visibility' => serialize($visibility), 'CType' => 'templavoila_pi1', 'tx_templavoila_to' => '39', 'tx_templavoila_ds' => '28', 'tx_templavoila_flex' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
   <data>
       <sheet index="sDEF">
           <language index="lDEF">
               <field index="field_text">
                   <value index="vDEF">free trial</value>
                   <value index="vEN">aust: free trial</value>
                   <value index="vPT"></value>
                   <value index="v_2"></value>
                   <value index="vZH"></value>
                   <value index="vFR"></value>
                   <value index="vDE"></value>
                   <value index="vJA"></value>
                   <value index="vES"></value>
               </field>
               <field index="field_smalltext">
                   <value index="vDEF"></value>
                   <value index="vEN"></value>
                   <value index="vPT"></value>
                   <value index="v_2"></value>
                   <value index="vZH"></value>
                   <value index="vFR"></value>
                   <value index="vDE"></value>
                   <value index="vJA"></value>
                   <value index="vES"></value>
               </field>
               <field index="field_link">
                   <value index="vDEF"></value>
                   <value index="vEN"></value>
                   <value index="vPT"></value>
                   <value index="v_2"></value>
                   <value index="vZH"></value>
                   <value index="vFR"></value>
                   <value index="vDE"></value>
                   <value index="vJA"></value>
                   <value index="vES"></value>
               </field>
           </language>
       </sheet>
   </data>
</T3FlexForms>' );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);
		return $element;
	}

	function _fixture_getPageElementWithDefaultVisibility() {
		//Create the element object fixture.
		$_table = 'pages';
		$_uid = 99999999999;
		$visibility = array('0' => '-', '1' => '-', '2' => '-' );
		$fixture = array('uid' => $_uid, 'tx_languagevisibility_visibility' => serialize($visibility), 'tx_languagevisibility_inheritanceflag_original' => 0, 'tx_languagevisibility_inheritanceflag_overlayed' => 0 );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);
		return $element;
	}

	function _fixture_getNewsElementWithDefaultVisibility() {
		//Create the element object fixture.
		$_table = 'tt_news';
		$_uid = 99999;
		$visibility = array('0' => '-', '1' => '-', '2' => '-' );
		$fixture = array('uid' => $_uid, 'tx_languagevisibility_visibility' => serialize($visibility), 'tx_languagevisibility_inheritanceflag_original' => 0, 'tx_languagevisibility_inheritanceflag_overlayed' => 0 );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);
		//get element from factory:
		$element = $factory->getElementForTable($_table, $_uid);
		return $element;
	}
}
