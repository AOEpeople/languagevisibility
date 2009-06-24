<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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
 * This testcase is used to test the functionallity of the beservice
 *
 * {@inheritdoc}
 *
 * class.tx_visibilityBEService_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_visibilityBEService_testcase.php $
 * @date 23.06.2009 14:39:00
 * @seetx_phpunit_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage languagevisibility
 * @access public
 */
 
class tx_visibilityBEService_testcase extends tx_phpunit_database_testcase {	

	/**
	* Creates the test environment.
	*
	*/
	function setUp() {
		$this->createDatabase();
		$db = $this->useTestDatabase();
		
		// order of extension-loading is important !!!!
		$this->importStdDB();
		$this->importExtensions(array('cms','languagevisibility'));
	}

	/**
	* Resets the test enviroment after the test.
	*/
	function tearDown() {
		$this->cleanDatabase();
   		$this->dropDatabase();
   		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}
	
	/**
	* Simple test to check that supported tables can be determined correctly
	* 
	* @param void
	* @return void
	* @author Timo Schmidt <timo.schmidt@aoemedia.de>
	* @test
	*/
	public function canDetermineSupportedTables(){
		$this->assertTrue(tx_languagevisibility_beservices::isSupportedTable('tt_news'));
		$this->assertTrue(tx_languagevisibility_beservices::isSupportedTable('pages'));
		$this->assertTrue(tx_languagevisibility_beservices::isSupportedTable('tt_content'));
		
	}
	
	/**
	 * Simple test with a tt_content element and a translation.
	 * The beService should return true, because an translation for
	 * the element exists.
	 * 
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @test
	 */
	public function canDetectTranslationsInAnyLanguage(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetectTranslationsInAnyLanguage.xml');
		$hasTranslation = tx_languagevisibility_beservices::hasTranslationInAnyLanguage(1,'tt_content');
		
		$this->assertTrue($hasTranslation,'Determined no translations for a translated element');
	}
}

?>