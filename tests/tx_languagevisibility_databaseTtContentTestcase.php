<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 AOE GmbH <dev@aoe.com>
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

/**
 * Class tx_languagevisibility_databaseTtContentTestcase
 */
abstract class tx_languagevisibility_databaseTtContentTestcase extends tx_languagevisibility_databaseTestcase {

	protected function _loadWorkspaces() {
		$this->importDataSet(dirname(__FILE__) . '/fixtures/dbDefaultWorkspaces.xml');
	}

	protected function _fakeWorkspaceContext($uid) {
		$GLOBALS['BE_USER']->workspace = $uid;
	}

	protected function _getLang($uid) {
		$this->makeSureLanguagesImported();
		$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		return $languageRep->getLanguageById($uid);
	}

	protected function _getContent($table, $uid) {
		$this->makeSureContentElementsImported();
		$dao = new tx_languagevisibility_daocommon();

		$factory = new tx_languagevisibility_elementFactory($dao);
		return $factory->getElementForTable($table, $uid);
	}

	protected function makeSureLanguagesImported() {
		if (! $this->_langImport) {
			$this->_langImport = TRUE;
			$this->importDataSet(dirname(__FILE__) . '/fixtures/dbDefaultLangs.xml');
		}
	}

	protected function makeSureContentElementsImported() {
		if (! $this->_ceImport) {
			$this->_ceImport = TRUE;
			$this->importDataSet(dirname(__FILE__) . '/fixtures/dbContentWithVisibilityTestdata.xml');
		}
	}

	protected function flushCache() {
		\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
			->getCache('tx_languagevisibility')
			->flush();
	}

	public function setUp() {
		parent::setUp();
		$this->_loadWorkspaces();
		$this->flushCache();
	}
}
