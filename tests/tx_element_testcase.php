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
 * @author	Daniel P�tzinger
 */

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon_stub.php');

// require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_test.php');
require_once (PATH_t3lib . 'class.t3lib_tcemain.php');

require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

class tx_element_testcase extends tx_languagevisibility_databaseTestcase {

	public function test_hasTranslation_pageelement() {
		//this time data in DB is tested!
		$this->_create_fixture_pagerecords ();
		$this->_create_fixture_languagerecords ();
		$_uid = 9990;
		$_table = 'pages';

		$dao = new tx_languagevisibility_daocommon ( );
		$factory = new tx_languagevisibility_elementFactory ( $dao );

		//get element from factory:
		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertTrue ( $element->hasTranslation ( '98' ), "element 9990 should have translation for language 98" );
		$this->assertTrue ( $element->hasTranslation ( '0' ), "default translation should be there always" );
		$this->assertEquals ( 'page', $element->getFieldToUseForDefaultVisibility (), "page element should return page as field to use for default visibility" );
		$this->assertFalse ( $element->hasTranslation ( '99' ), "element 9990 should not be translated for 99!" );
	}

	public function test_hasTranslation_celement() {
		//this time data in DB is tested!
		$this->_create_fixture_ttcontentrecords ();
		$this->_create_fixture_languagerecords ();
		$_uid = 9990;
		$_table = 'tt_content';

		$dao = new tx_languagevisibility_daocommon ( );
		$factory = new tx_languagevisibility_elementFactory ( $dao );

		//get element from factory:
		$element = $factory->getElementForTable ( $_table, $_uid );

		//test element 210
		$this->assertTrue ( $element->hasTranslation ( '98' ), "record should have translation" );
		$this->assertTrue ( $element->hasTranslation ( '0' ), "default transla should be there always" );

		$element = $factory->getElementForTable ( $_table, '4922' );
		$this->assertFalse ( $element->hasTranslation ( '99' ), "element 4922 should not be translated!" );
	}

	public function test_hasTranslation_normalfcelement() {
		//this time data in DB is tested!
		$this->_create_fixture_fcecontentrecord ();
		$this->_create_fixture_fcedatastructures ();
		$this->_create_static_inforecords ();
		$this->_create_fixture_languagerecords ();
		$_uid = 9992;
		$_table = 'tt_content';

		$dao = new tx_languagevisibility_daocommon ( );
		$factory = new tx_languagevisibility_elementFactory ( $dao );

		//get element from factory:
		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertEquals ( true, ($element instanceof tx_languagevisibility_fceelement), "not object of type tx_languagevisibility_fcelement returned!" );

		$this->assertEquals ( true, $element->hasTranslation ( '98' ), "record should have translation" );

		$this->assertEquals ( true, $element->hasTranslation ( '0' ), "default transla should be there always" );

		$this->assertEquals ( false, $element->hasTranslation ( '99' ), "element should not be translated!" );

	}

	public function test_hasTranslation_overlayfcelement() {
		//this time data in DB is tested!
		$this->_create_fixture_fcecontentrecordoverlay ();
		$this->_create_fixture_fcedatastructures ();
		$this->_create_fixture_languagerecords ();
		$_uid = '9993';
		$_table = 'tt_content';

		$dao = new tx_languagevisibility_daocommon ( );
		$factory = new tx_languagevisibility_elementFactory ( $dao );

		//get element from factory:
		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertTrue ( $element instanceof tx_languagevisibility_fceoverlayelement, "not object of type tx_languagevisibility_fceoverlayelement returned!" );
		$this->assertTrue ( $element->hasTranslation ( '98' ), "record should have translation" );
		$this->assertTrue ( $element->hasTranslation ( '0' ), "default transla should be there always" );

		$this->assertFalse ( $element->hasTranslation ( '99' ), "element should not be translated!" );
	}

	public function test_getLocalVisibilitySetting_celement() {
		//this time data in DB is tested!
		$_table = 'tt_content';
		$_uid = 1;
		$visibility = array ('0' => 'yes', '1' => t, '2' => '' );

		$fixture = array ('uid' => $_uid, 'tx_languagevisibility_visibility' => serialize ( $visibility ) );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );

		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		//get element from factory:
		$element = $factory->getElementForTable ( $_table, $_uid );

		//test
		$this->assertEquals ( $element->getLocalVisibilitySetting ( '1' ), 't', "t expected" );
		$this->assertEquals ( $element->getLocalVisibilitySetting ( '0' ), 'yes', "yes expected" );
	}

	/*****************************************************************************************************
	 ************************************ FICTURE Creation
	 ********************************************************************************************************/
	function fixture_getDefaultVisibilityArrayString() {
		return serialize ( array ('0' => '-', '98' => '-', '99' => '-' ) );
	}
	function _create_fixture_ttcontentrecords() {
		$fields_values = array ('uid' => 9990, 'pid' => 1, 'sys_language_uid' => 0, 'header' => 'test', 't3ver_oid' => '0', 't3ver_state' => '0', 'CType' => 'text', 'bodytext' => 'test', 'tx_languagevisibility_visibility' => $this->fixture_getDefaultVisibilityArrayString () );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tt_content', 'uid=9990' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tt_content', $fields_values );

		$fields_values = array ('uid' => 9991, 'pid' => 1, 'l18n_parent' => 9990, 'sys_language_uid' => 98, 'header' => 'test', 't3ver_oid' => '0', 't3ver_state' => '0', 'CType' => 'text', 'bodytext' => 'test_translated' );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tt_content', 'uid=9991' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tt_content', $fields_values );

	}
	function _create_fixture_fcecontentrecord() {
		$fields_values = array ('uid' => 9992, 'pid' => 1, 'sys_language_uid' => 0, 'header' => 'test', 'tx_templavoila_ds' => 9990, 'tx_templavoila_to' => 9990, 't3ver_oid' => '0', 't3ver_state' => '0', 'CType' => 'templavoila_pi1', 'bodytext' => '', 'tx_languagevisibility_visibility' => $this->fixture_getDefaultVisibilityArrayString (), 'tx_templavoila_flex' => file_get_contents ( t3lib_extMgm::extPath ( "languagevisibility" ) . 'tests/fixtures/fce_buttonelement_contentxml.xml' ) );

		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tt_content', 'uid=9992' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tt_content', $fields_values );
	}
	function _create_fixture_fcecontentrecordoverlay() {
		$fields_values = array ('uid' => 9993, 'pid' => 1, 'sys_language_uid' => 0, 'header' => 'test', 'tx_templavoila_ds' => 9991, 'tx_templavoila_to' => 9991, 't3ver_oid' => '0', 't3ver_state' => '0', 'CType' => 'templavoila_pi1', 'bodytext' => '', 'tx_languagevisibility_visibility' => $this->fixture_getDefaultVisibilityArrayString (), 'tx_templavoila_flex' => file_get_contents ( t3lib_extMgm::extPath ( "languagevisibility" ) . 'tests/fixtures/fce_buttonelement_contentxml.xml' ) );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tt_content', 'uid=9993' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tt_content', $fields_values );

		$fields_values = array ('uid' => 9994, 'pid' => 1, 'l18n_parent' => 9993, 'sys_language_uid' => 98, 'header' => 'test', 'tx_templavoila_ds' => 9991, 'tx_templavoila_to' => 9991, 't3ver_oid' => '0', 't3ver_state' => '0', 'CType' => 'templavoila_pi1', 'bodytext' => '', 'tx_languagevisibility_visibility' => $this->fixture_getDefaultVisibilityArrayString (), 'tx_templavoila_flex' => file_get_contents ( t3lib_extMgm::extPath ( "languagevisibility" ) . 'tests/fixtures/fce_buttonelement_contentxml.xml' ) );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tt_content', 'uid=9994' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tt_content', $fields_values );

	}

	function _create_fixture_fcedatastructures() {
		$fields_values = array ('uid' => 9990, 'pid' => 1, 'title' => 'testds for normal fces', 't3ver_oid' => '0', 't3ver_state' => '0', 'scope' => '2', 'dataprot' => file_get_contents ( t3lib_extMgm::extPath ( "languagevisibility" ) . 'tests/fixtures/fce_buttonelement_datastructure.xml' ) );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_templavoila_datastructure', 'uid=9990' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_templavoila_datastructure', $fields_values );

		$fields_values = array ('uid' => 9990, 'pid' => 1, 'datastructure' => 9990, 'title' => 'testds for normal fces', 't3ver_oid' => '0', 't3ver_state' => '0' );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_templavoila_tmplobj', 'uid=9990' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_templavoila_tmplobj', $fields_values );

		//DS / TO 2
		//**************************
		$fields_values = array ('uid' => 9991, 'pid' => 1, 'title' => 'testds for overlay fces', 't3ver_oid' => '0', 't3ver_state' => '0', 'scope' => '2', 'dataprot' => file_get_contents ( t3lib_extMgm::extPath ( "languagevisibility" ) . 'tests/fixtures/fce_buttonelement_datastructure_useOverlay.xml' ) );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_templavoila_datastructure', 'uid=9991' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_templavoila_datastructure', $fields_values );

		$fields_values = array ('uid' => 9991, 'pid' => 1, 'datastructure' => 9991, 'title' => 'testds for normal overlay fces', 't3ver_oid' => '0', 't3ver_state' => '0' );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_templavoila_tmplobj', 'uid=9991' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_templavoila_tmplobj', $fields_values );
	}

	function _create_fixture_pagerecords() {
		$fields_values = array ('uid' => 9990, 'pid' => 1, 'title' => 'test', 't3ver_oid' => '0', 't3ver_state' => '0', 'tx_languagevisibility_visibility' => $this->fixture_getDefaultVisibilityArrayString () );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'pages', 'uid=9990' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'pages', $fields_values );

		$fields_values = array ('uid' => 9990, 'pid' => 9990, 'sys_language_uid' => 98, 'title' => 'test_translated' );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'pages_language_overlay', 'uid=9990' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'pages_language_overlay', $fields_values );

	}

	function _create_fixture_languagerecords() {
		//normal language
		$fields_values = array ('uid' => 98, 'pid' => 0, 'static_lang_isocode' => '999', 'title' => 'testlanguage(translatedmode)', 'tx_languagevisibility_defaultvisibility' => 't', 'tx_languagevisibility_defaultvisibilityel' => 't' );

		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'sys_language', 'uid=98' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'sys_language', $fields_values );
		//fallback language
		$fields_values = array ('uid' => 99, 'pid' => 0, 'title' => 'testlanguage(translatedmode)', 'tx_languagevisibility_defaultvisibility' => 'f', 'tx_languagevisibility_defaultvisibilityel' => 'f', 'tx_languagevisibility_fallbackorder' => '98' );

		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'sys_language', 'uid=99' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'sys_language', $fields_values );

	}

	function _create_static_inforecords() {
		$fields_values = array ('uid' => 999, 'pid' => 0, 'lg_iso_2' => 'EN', 'lg_name_en' => 'English' );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'static_languages', 'uid=999' );
		$GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'static_languages', $fields_values );
	}

	function setUp() {
		parent::setUp();
		// order of extension-loading is important !!!!
		$this->importExtensions ( array ( 'cms', 'static_info_tables', 'templavoila', 'languagevisibility', 'mwimagemap','aoe_xml2array' ) );
	}


}
?>