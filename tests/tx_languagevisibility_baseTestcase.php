<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011 AOE media (dev@aoemedia.de)
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

/**
 * Basic testclass for all non database tests.
 * Used to backup and restore the environment.
 * 
 */
abstract class tx_languagevisibility_baseTestcase extends tx_phpunit_testcase {
	/**
	 * @var tx_languagevisibility_tests_helper_environmentHelper
	 */
	protected $environmentSaver = null;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->environmentSaver = new tx_languagevisibility_tests_helper_environmentHelper();
		$this->environmentSaver->save();
		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		$this->environmentSaver->restore();
	}
}
