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
			$this->_langImport = true;
			$this->importDataSet(dirname(__FILE__) . '/fixtures/dbDefaultLangs.xml');
		}
	}

	protected function makeSureContentElementsImported() {
		if (! $this->_ceImport) {
			$this->_ceImport = true;
			$this->importDataSet(dirname(__FILE__) . '/fixtures/dbContentWithVisibilityTestdata.xml');
		}
	}

	public function setUp() {
		parent::setUp();
		$this->_loadWorkspaces();

	}
}
?>