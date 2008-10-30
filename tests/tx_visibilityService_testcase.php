<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Kasper Ligaard (ligaard@daimi.au.dk)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 *
 * @author	Daniel P�nger
 */

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_language.php');

// require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_test.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');

class tx_visibilityService_testcase extends tx_phpunit_testcase {


	public function test_visibility()
	{

		// Create the language object fixture.
			$fixture = array('uid'=>1,'tx_languagevisibility_fallbackorder'=>'2');
			$language1=new tx_languagevisibility_language;
			$language1->setData($fixture);

			$rep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$deflanguage=$rep->getDefaultLanguage();
		//Create the element object fixture.
			$_table='tt_content';
			$_uid=1;
			$visibility=array('0'=>'yes','1'=>'t','2'=>'');
			$fixture = array('uid'=>$_uid,'tx_languagevisibility_visibility'=>serialize($visibility));
			$daostub=new tx_languagevisibility_daocommon_stub;
			$daostub->stub_setRow($fixture,$_table);
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($daostub);
			//get element from factory:
		    $element=$factory->getElementForTable($_table,$_uid);

			//test
			$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

			// language 1 should be set local to "t"
			$this->assertEquals('t', $visibility->getVisibilitySetting($language1,$element), "setting t expected");
			$this->assertEquals(true, $visibility->isVisible($deflanguage,$element), "default lang should be visible");
	}

	public function test_visibility_fixture_ce()
	{


		$language=$this->_fixture_getLanguageOneWithDefaultFallback();
		$element=$this->_fixture_getElementWithDefaultVisibility();


		//test
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		// language 1 should be set local to "t"
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language,$element), "setting f expected (because default is used)");
		$this->assertEquals(true, $visibility->isVisible($language,$element), "default lang should be visible");
		$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), "default should be overlay");
	}

	public function test_visibility_fixture_fce_db()
	{
		$language=$this->_fixture_getLanguageThreeWithMultiFallback();
		$element=$this->_fixture_getFCEElementWithDefaultVisibility();


		//test
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');


		// language 1 should be set local to "t"
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting default (-) expected");

		$this->assertEquals('f', $visibility->getVisibilitySetting($language,$element), "setting f expected (because default is used)");
		$this->assertEquals(true, $visibility->isVisible($language,$element), "in aust it should be visible");
		//$this->assertEquals(1, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), "default should be overlay");
	}

	public function test_visibility_fixture_page()
	{


			$language=$this->_fixture_getLanguageOneWithDefaultFallback();
			$element=$this->_fixture_getPageElementWithDefaultVisibility();


		//test
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		// language 1 should be set local to "t"
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language,$element), "setting f expected (because default is used)");
		$this->assertEquals(true, $visibility->isVisible($language,$element), "default lang should be visible");
		$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), "default record should be overlay");
	}

	function _fixture_getLanguageOneWithDefaultFallback() {
		// Create the language object fixture.
			$fixture = array('uid'=>1,'tx_languagevisibility_fallbackorder'=>'0,1','tx_languagevisibility_fallbackorderel'=>'0,1','tx_languagevisibility_defaultvisibility'=>'f','tx_languagevisibility_defaultvisibilityel'=>'f');
			$language1=new tx_languagevisibility_language;
			$language1->setData($fixture);
			return $language1;
	}
	function _fixture_getLanguageThreeWithMultiFallback() {
		// Create the language object fixture.
			$fixture = array('uid'=>1,'tx_languagevisibility_fallbackorder'=>'0,1','tx_languagevisibility_fallbackorderel'=>'0,1','tx_languagevisibility_defaultvisibility'=>'f','tx_languagevisibility_defaultvisibilityel'=>'f');
			$language1=new tx_languagevisibility_language;
			$language1->setData($fixture);
			return $language1;
	}

	function _fixture_getLanguageFourWithElementFallback() {
		$fixture = array('uid'=>2,'tx_languagevisibility_fallbackorder'=>'0,1','tx_languagevisibility_fallbackorderel'=>'1','tx_languagevisibility_defaultvisibility'=>'f','tx_languagevisibility_defaultvisibilityel'=>'f');
		$language4 = new tx_languagevisibility_language();
		$language4->setData($fixture);
		return $language4;
	}


	function _fixture_getElementWithDefaultVisibility() {
		//Create the element object fixture.
			$_table='tt_content';
			$_uid=9999;
			$visibility=array('0'=>'-','1'=>'-','2'=>'-');
			$fixture = array('uid'=>$_uid,'tx_languagevisibility_visibility'=>serialize($visibility));
			$daostub=new tx_languagevisibility_daocommon_stub;
			$daostub->stub_setRow($fixture,$_table);
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($daostub);
			//get element from factory:
	    	$element=$factory->getElementForTable($_table,$_uid);
			return $element;
	}
	function _fixture_getElementWithLangOneVisibility() {
		//Create the element object fixture.
			$_table='tt_content';
			$_uid=9999;
			$visibility=array('0'=>'-','1'=>'-','2'=>'-');
			$fixtureL1Rec = array('uid'=>$_uid,'l18n_parent'=>$_uid-1,'sys_language_uid'=>1, 'tx_languagevisibility_visibility'=>serialize($visibility));
			$fixtureL0Rec = array('uid'=>$_uid-1,'sys_language_uid'=>0);
			$daostub=new tx_languagevisibility_daocommon_stub;
			$daostub->stub_setRow($fixtureL1Rec,$_table);
			$daostub->stub_setRow($fixtureL0Rec,$_table);
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($daostub);
			//get element from factory:
	    	$element=$factory->getElementForTable($_table,$_uid);
			return $element;
	}

	function _fixture_getFCEElementWithDefaultVisibility() {
		//Create the element object fixture.
			$_table='tt_content';
			$_uid=9999;
			$visibility=array('0'=>'-','1'=>'-','2'=>'-');
			$fixture = array('uid'=>$_uid,
											'tx_languagevisibility_visibility'=>serialize($visibility),
											'CType'=>'templavoila_pi1',
											'tx_templavoila_to'=>'39',
											'tx_templavoila_ds'=>'28',
											'tx_templavoila_flex'=>'<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
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
</T3FlexForms>');
			$daostub=new tx_languagevisibility_daocommon_stub;
			$daostub->stub_setRow($fixture,$_table);
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($daostub);
			//get element from factory:
	    $element=$factory->getElementForTable($_table,$_uid);
			return $element;
	}
	function _fixture_getPageElementWithDefaultVisibility() {
		//Create the element object fixture.
			$_table='pages';
			$_uid=9999;
			$visibility=array('0'=>'-','1'=>'-','2'=>'-');
			$fixture = array('uid'=>$_uid,'tx_languagevisibility_visibility'=>serialize($visibility));
			$daostub=new tx_languagevisibility_daocommon_stub;
			$daostub->stub_setRow($fixture,$_table);
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($daostub);
			//get element from factory:
	    $element=$factory->getElementForTable($_table,$_uid);
			return $element;
	}


	/*
	public function test_visibilityTestDB()
	{

		// Create the language object.
			$rep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$language0=$rep->getLanguageById(0);
			$language1=$rep->getLanguageById(1);
			$language2=$rep->getLanguageById(2);
			$language3=$rep->getLanguageById(3);

			//Create the element object fixture.
			$_table='pages';


			$dao=t3lib_div::makeInstance('tx_languagevisibility_daocommon');
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($dao);

			//get element from factory:
	    $element_subpage3=$factory->getElementForTable($_table,'208');
	    $element_subpage2=$factory->getElementForTable($_table,'209');
	    $element_subpage1=$factory->getElementForTable($_table,'210');
	    $element_subpage4=$factory->getElementForTable($_table,'820');

			//test
			$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

			// language 1 should be set local to "t"
			$this->assertEquals(false, $visibility->getOverlayLanguageIdForLanguageAndElement($language1,$element_subpage3), "208 is not translated");

			$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language1,$element_subpage1), "210 should fallback to 0");

			$this->assertEquals(1, $visibility->getOverlayLanguageIdForLanguageAndElement($language1,$element_subpage2), "209 is  translated in 1");
			$this->assertEquals(1, $visibility->getOverlayLanguageIdForLanguageAndElement($language2,$element_subpage2), "209 is  translated in 1 should fallback");
			$this->assertEquals(false, $visibility->getOverlayLanguageIdForLanguageAndElement($language3,$element_subpage2), "209 is  translated in 1 should fallback");

			$this->assertEquals(false, $visibility->getOverlayLanguageIdForLanguageAndElement($language0,$element_subpage4), "820 not visible in default");

	}

	public function test_visibility_testfce_db()
	{
		$dao=t3lib_div::makeInstance('tx_languagevisibility_daocommon');
			$factoryClass=t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$factory=new $factoryClass($dao);
		$rep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$language=$rep->getLanguageById(3);
			$element=$factory->getElementForTable('tt_content','4948');


		//test
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		// language 1 should be set local to "t"
		$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting default (-) expected");
		$this->assertEquals('f', $visibility->getVisibilitySetting($language,$element), "setting f expected (because default is used)");
		$this->assertEquals(true, $visibility->isVisible($language,$element), "in aust it should be visible");
		$this->assertEquals(1, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), "default should be overlay");
	}


	*/


}
?>