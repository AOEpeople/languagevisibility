<?php
/***************************************************************
 * Copyright notice
 *
 * Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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
class tx_visibilityBEService_testcase extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('version', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/languagevisibility');

	/**
	 * Simple test with a tt_content element and a translation.
	 * The beService should return true, because an translation for
	 * the element exists.
	 *
	 * @param void
	 * @return void
	 * @test
	 */
	public function canDetectTranslationsInAnyLanguage() {
		$this->importDataSet(__DIR__ . '/Fixtures/canDetectTranslationsInAnyLanguage.xml');
		$hasTranslation = tx_languagevisibility_beservices::hasTranslationInAnyLanguage(1, 'tt_content');

		$this->assertTrue($hasTranslation, 'Determined no translations for a translated element');
	}
}
