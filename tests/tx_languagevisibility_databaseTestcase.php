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

require_once (t3lib_extMgm::extPath("languagevisibility") . 'tests/classes/class.tx_languagevisibility_tests_helper_environmentSaver.php');

abstract class tx_languagevisibility_databaseTestcase extends tx_phpunit_database_testcase {

	/**
	 * @return void
	 */
	function setUp() {
			/*
			 * save the current environment  this should allways be done first because
			 * database tests may get skipped because no testdatabase exists
			 */
		$this->environmentSaver = new tx_languagevisibility_tests_helper_environmentHelper();
		$this->environmentSaver->save();

		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = "tx_languagevisibility_inheritanceflag_original,tx_languagevisibility_inheritanceflag_overlayed";
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = "uid,title,subtitle,nav_title,media,keywords,description,abstract,author,author_email,sys_language_uid,tx_languagevisibility_inheritanceflag_overlayed";
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']);

		// order of extension-loading is important !!!!
        if (version_compare(TYPO3_version, '4.5.0', '<')) {
		    $this->importExtensions(array('cms', 'languagevisibility' ));
        } else {
            $this->importExtensions(array('cms', 'extbase', 'fluid', 'version', 'workspaces', 'languagevisibility' ));
        }

		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 * @return void
	 */
	function tearDown() {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

			/**
			 * In the end we should restore the environement
			 */
		$this->environmentSaver->restore();
	}
}
?>