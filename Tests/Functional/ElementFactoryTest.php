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
use AOE\Languagevisibility\CElement;
use AOE\Languagevisibility\Dao\DaoCommonStub;
use AOE\Languagevisibility\Element;
use AOE\Languagevisibility\ElementFactory;
use AOE\Languagevisibility\FceElement;
use AOE\Languagevisibility\PageElement;
use AOE\Languagevisibility\TtnewsElement;

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 * @author	Daniel Pötzinger
 */
class ElementFactoryTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	/**
	 *
	 * @test
	 */
	public function getElementForTable_pageelement() {
		// Create the Array fixture.
		$uid = 1;
		$table = 'pages';
		$fixture = array('uid' => $uid );
		$daoStub = new DaoCommonStub();
		$daoStub->stub_setRow($fixture, $table);

		//get element from factory:
		$factory = new ElementFactory($daoStub);
		$element = $factory->getElementForTable($table, $uid);

		$this->assertEquals($element->getTable(), 'pages');
		$this->assertTrue($element instanceof Element, 'not object of type \AOE\Languagevisibility\Element returned!');
		$this->assertTrue($element instanceof PageElement, 'not object of type \AOE\Languagevisibility\PageElement returned!');
	}

	/**
	 *
	 * @test
	 */
	public function getElementForTable_celement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_content';

		$fixture = array('uid' => $_uid );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);

		$element = $factory->getElementForTable($_table, $_uid);

		$this->assertEquals($element->getTable(), 'tt_content');
		$this->assertTrue($element instanceof Element, "not object of type tx_languagevisibility_element returned!");
		$this->assertTrue($element instanceof CElement, "not object of type tx_languagevisibility_celement returned!");
	}

	/**
	 *
	 * @test
	 */
	public function canCreateDraftWorkspaceElementFromLiveWorkspaceUidInWorkspaceContext() {
		$this->importDataSet(__DIR__ . '/Fixtures/getLiveWorkspaceElementFromWorkspaceUid.xml');

		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$dao = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\\DaoCommon');

		/* @var $factory ElementFactory */
		$factory = new ElementFactory($dao);

			//store context
		$oldWS = $GLOBALS['BE_USER']->workspace;
		$GLOBALS['BE_USER']->workspace = 12;

		/* @var $element CElement */
		$element = $factory->getElementForTable('tt_content', 10);
		$this->assertEquals($element->getTitle(), 'WS header');

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

		$fixture = array('uid' => $_uid, 'CType' => 'templavoila_pi1' );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, $_table);

		$factory = new ElementFactory($daostub);

		$element = $factory->getElementForTable($_table, $_uid);

		$this->assertEquals($element->getTable(), 'tt_content');
		$this->assertTrue($element instanceof Element, "not object of type tx_languagevisibility_element returned!");
		$this->assertTrue($element instanceof FceElement, "not object of type tx_languagevisibility_fcelement returned!");
	}

	/**
	 * Test to ensure the factory delivers an instance for a tt_news element.
	 *
	 * @test
	 */
	public function canGetElementForTTNEWS() {
		// Create the Array fixture.
		$uid = 1;
		$table = 'tt_news';

		$fixture = array('uid' => $uid, 'title' => 'news' );
		$daoStub = new DaoCommonStub();
		$daoStub->stub_setRow($fixture, $table);

		$factory = new ElementFactory($daoStub);

		/* @var $element \AOE\Languagevisibility\TtnewsElement */
		$element = $factory->getElementForTable($table, $uid);

		$this->assertEquals($element->getTable(), 'tt_news');
		$this->assertTrue($element instanceof Element, 'not object of type AOE\Languagevisibility\Element returned!');
		$this->assertTrue($element instanceof TtnewsElement, get_class($element) . ':not object of type AOE\Languagevisibility\TtnewsElement returned!');
	}

	/**
	 * Records elements store theire translation in the same table. The factory class should
	 * not allow to get an element from this table which is an overlay.
	 *
	 * @test
	 */
	public function canNotGetElementForOverlayElement() {
		$uid = 1;
		$table = 'tt_content';

		//create fixture element
		$fixture = array('uid' => 1, 'title' => 'overlay', 'sys_language_uid' => 1, 'l18n_parent' => 12 );
		$daoStub = new DaoCommonStub();
		$daoStub->stub_setRow($fixture, 'tt_content');

		$factory = new ElementFactory($daoStub);

		$exceptionCatched = FALSE;

		//try instantiation an catch expected exception
		try {
			$factory->getElementForTable($table, $uid);
		} catch ( \Exception $e ) {
			$exceptionCatched = TRUE;
		}

		$this->assertTrue($exceptionCatched, 'Error: Factory can create instance of overlay element');
	}

	/**
	 * This testcase is used to test that an element from an unsupported table can
	 * not be created.
	 *
	 * @test
	 * @expectedException \UnexpectedValueException
	 */
	public function canNotGetElementOfUnsupportedTable() {
		$_uid = 4711;
		$_table = 'tx_notexisting';
		$fixture = array('uid' => $_uid, 'title' => 'record' );
		$daostub = new DaoCommonStub();
		$daostub->stub_setRow($fixture, 'tx_notexisting');

		/* @var $factory ElementFactory */
		$factory = new ElementFactory($daostub);

		$element = $factory->getElementForTable($_table, $_uid);

		$this->assertNull($element, 'Element should be null');
	}
}
