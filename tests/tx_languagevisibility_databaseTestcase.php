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

abstract class tx_languagevisibility_databaseTestcase extends tx_phpunit_database_testcase {

	/**
	 * @var array holds the restored addRootlineFields array which will be stored in setUp and restored in tearDown.
	 */
	protected $restoredAddRootlineFields;

	/**
	 * @var array holds the restored pageOverlayFields array which will be stored in setUp and restored in tearDown
	 */
	protected $restoredPageOverlayFields;

	protected $restoredSCOptions;

	/**
	 *
	 */
	function setUp() {
		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		/* save current enviroment values */
		$this->restoredAddRootlineFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
		$this->restoredPageOverlayFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"];
		$this->restoredSCOptions = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'];

		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = "tx_languagevisibility_inheritanceflag_original,tx_languagevisibility_inheritanceflag_overlayed";
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = "uid,title,subtitle,nav_title,media,keywords,description,abstract,author,author_email,sys_language_uid,tx_languagevisibility_inheritanceflag_overlayed";
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']);

		// order of extension-loading is important !!!!
		$this->importExtensions(array('cms', 'languagevisibility' ));

		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 *
	 */
	function tearDown() {
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = $this->restoredAddRootlineFields;
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = $this->restoredPageOverlayFields;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'] = $this->restoredSCOptions;

		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}
}
?>