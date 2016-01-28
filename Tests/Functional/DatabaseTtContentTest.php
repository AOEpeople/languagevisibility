<?php

namespace AOE\Languagevisibility\Tests\Functional;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use AOE\Languagevisibility\Dao\DaoCommon;
use AOE\Languagevisibility\ElementFactory;

/**
 * Class DatabaseTtContentTest
 * @package AOE\Languagevisibility\Tests\Functional
 */
abstract class DatabaseTtContentTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	/**
	 * @var bool
	 */
	protected $_langImport = FALSE;

	/**
	 * @var bool
	 */
	protected $_ceImport = FALSE;

	public function setUp() {
		parent::setUp();
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$this->_loadWorkspaces();
		$this->flushCache();
	}

	protected function _loadWorkspaces() {
		$this->importDataSet(__DIR__ . '/Fixtures/dbDefaultWorkspaces.xml');
	}

	protected function _fakeWorkspaceContext($uid) {
		$GLOBALS['BE_USER']->workspace = $uid;
	}

	protected function _getLang($uid) {
		$this->makeSureLanguagesImported();
		$languageRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		return $languageRep->getLanguageById($uid);
	}

	protected function _getContent($table, $uid) {
		$this->makeSureContentElementsImported();
		$dao = new DaoCommon();

		$factory = new ElementFactory($dao);
		return $factory->getElementForTable($table, $uid);
	}

	protected function makeSureLanguagesImported() {
		if (!$this->_langImport) {
			$this->_langImport = TRUE;
			$this->importDataSet(__DIR__ . '/Fixtures/dbDefaultLangs.xml');
		}
	}

	protected function makeSureContentElementsImported() {
		if (!$this->_ceImport) {
			$this->_ceImport = TRUE;
			$this->importDataSet(__DIR__ . '/Fixtures/dbContentWithVisibilityTestdata.xml');
		}
	}

	protected function flushCache() {
		\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
			->getCache('tx_languagevisibility')
			->flush();
	}
}
