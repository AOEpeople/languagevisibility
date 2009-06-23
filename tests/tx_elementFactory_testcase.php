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

class tx_elementFactory_testcase extends tx_phpunit_testcase {
	
	public function test_getElementForTable_pageelement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'pages';
		$fixture = array ('uid' => $_uid );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );
		
		//get element from factory:
		$factoryClass = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$factory = new $factoryClass ( $daostub );
		$element = $factory->getElementForTable ( $_table, $_uid );
		
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_pageelement, "not object of type tx_languagevisibility_pageelement returned!" );
	
	}
	
	public function test_getElementForTable_celement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_content';
		
		$fixture = array ('uid' => $_uid );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );
		
		$factoryClass = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$factory = new $factoryClass ( $daostub );
		
		$element = $factory->getElementForTable ( $_table, $_uid );
		
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_celement, "not object of type tx_languagevisibility_celement returned!" );
	
	}
	
	public function test_getElementForTable_fcelement() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_content';
		
		$fixture = array ('uid' => $_uid, 'CType' => 'templavoila_pi1' );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );
		
		$factoryClass = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$factory = new $factoryClass ( $daostub );
		
		$element = $factory->getElementForTable ( $_table, $_uid );
		
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_fceelement, "not object of type tx_languagevisibility_fcelement returned!" );
	}
	
	/**
	 * Test to ensure the factory delivers an instance for a tt_news element.
	 * 
	 * @return 
	 */
	public function test_canGetElementForTTNEWS() {
		// Create the Array fixture.
		$_uid = 1;
		$_table = 'tt_news';
		
		$fixture = array ('uid' => $_uid, 'title' => 'news' );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, $_table );
		
		$factoryClass = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$factory = new $factoryClass ( $daostub );
		
		$element = $factory->getElementForTable ( $_table, $_uid );
		
		$this->assertTrue ( $element instanceof tx_languagevisibility_element, "not object of type tx_languagevisibility_element returned!" );
		$this->assertTrue ( $element instanceof tx_languagevisibility_ttnewselement, "not object of type tx_languagevisibility_ttnewselement returned!" );
	}
	
	/**
	 * Records elements store theire translation in the same table. The factory class should
	 * not allow to get an element from this table which is an overlay.
	 * 
	 * @return 
	 */
	public function test_canNotGetElementForOverlayElement() {
		//create fixture element
		$fixture = array ('uid' => 1, 'title' => 'overlay', 'sys_language_uid' => 1, 'l18n_parent' => 12 );
		$daostub = new tx_languagevisibility_daocommon_stub ( );
		$daostub->stub_setRow ( $fixture, 'tt_content' );
		
		$factoryClass = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$factory = new $factoryClass ( $daostub );
		
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
	 * 
	 * @todo
	 */
	
	public function test_canNotGetElementOfUnsupportedTable() {
	
	}
}
?>