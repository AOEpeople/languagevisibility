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
 * @author	Daniel Pötzinger
 */
require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon_stub.php');

// require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_test.php');
require_once (PATH_t3lib . 'class.t3lib_tcemain.php');

class tx_elementFactory_testcase extends tx_languagevisibility_databaseTestcase {

	/**
	 *
	 * @test
	 */
	public function getElementForTable_pageelement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'pages';
		$fixture = array ('uid' => $_uid );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );

		//get element from factory:
		$factory = new tx_languagevisibility_elementFactory ( $daostub );
		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertEquals($element->getTable(),'pages');
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_pageelement, "not object of type tx_languagevisibility_pageelement returned!" );
	}

	/**
	 *
	 * @test
	 */
	public function getElementForTable_celement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_content';

		$fixture = array ('uid' => $_uid );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );

		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertEquals($element->getTable(),'tt_content');
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_celement, "not object of type tx_languagevisibility_celement returned!" );
	}

	/**
	 *
	 * @test
	 */
	public function canCreateDraftWorkspaceElementFromLiveWorkspaceUidInWorkspaceContext(){

		if(version_compare(TYPO3_version,'4.3','>') && !t3lib_extMgm::isLoaded('version')) {
			$this->markTestSkipped('Not relevant if "version" is not installed');
		}

		$this->importDataSet(dirname(__FILE__). '/fixtures/getLiveWorkspaceElementFromWorkspaceUid.xml');

		$dao 			= t3lib_div::makeInstance( 'tx_languagevisibility_daocommon' );

		/* @var $factory tx_languagevisibility_elementFactory */
		$factory = new tx_languagevisibility_elementFactory ( $dao );

		//store context
		$oldWS = $GLOBALS['BE_USER']->workspace;

		$GLOBALS['BE_USER']->workspace = 12;

		/* @var $element tx_languagevisibility_celement */
		$element = $factory->getElementForTable('tt_content',10);

		$this->assertEquals($element->getUid(),11,'Uid of element should be workspace uid in workspace context.');
		$this->assertEquals($element->getPid(),-1,'Pid should be -1 because the content element is a workspace content element.');

		//restore context
		$GLOBALS['BE_USER']->workspace = $oldWS;
	}

	/**
	 *
	 * @test
	 */
	public function getElementForTable_fcelement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_content';

		$fixture = array ('uid' => $_uid, 'CType' => 'templavoila_pi1' );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );

		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertEquals($element->getTable(),'tt_content');
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_fceelement, "not object of type tx_languagevisibility_fcelement returned!" );
	}

	/**
	 * Test to ensure the factory delivers an instance for a tt_news element.
	 *
	 * @test
	 */
	public function canGetElementForTTNEWS() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_news';

		$fixture = array ('uid' => $_uid, 'title' => 'news' );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );

		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		/* @var $element  tx_languagevisibility_ttnewselement */
		$element = $factory->getElementForTable ( $_table, $_uid );

		$this->assertEquals($element->getTable(),'tt_news');
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_ttnewselement, "not object of type tx_languagevisibility_ttnewselement returned!" );
	}

	/**
	 * Records elements store theire translation in the same table. The factory class should
	 * not allow to get an element from this table which is an overlay.
	 *
	 * @test
	 */
	public function canNotGetElementForOverlayElement() {
		$_uid = 1;
		$_table = 'tt_content';

		//create fixture element
		$fixture = array ('uid' => 1, 'title' => 'overlay', 'sys_language_uid' => 1, 'l18n_parent' => 12 );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, 'tt_content' );

		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		$exceptionCatched = false;

		//try instanciation an catch expected exception
		try {
			$element = $factory->getElementForTable ( $_table, $_uid );
		} catch ( Exception $e ) {
			$exceptionCatched = true;
		}

		$this->assertTrue ( $exceptionCatched, 'Error: Factory can create instance of overlay element' );
	}

	/**
	 * This testcase is used to test that an element from an unsupported table can
	 * not be created.
	 *
	 * @test
	 * @expectedException UnexpectedValueException
	 */
	public function canNotGetElementOfUnsupportedTable() {
		$_uid 	= 4711;
		$_table	= 'tx_notexisting';
		$fixture = array ('uid' => $_uid, 'title' => 'record' );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, 'tx_notexisting' );

			/* @var $factory tx_languagevisibility_elementFactory */
		$factory = new tx_languagevisibility_elementFactory ( $daostub );

		$element = $factory->getElementForTable($_table, $_uid);

		$this->assertNull($element,'Element should be null');
	}
}
?>