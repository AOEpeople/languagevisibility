<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 AOE media (dev@aoe.com)
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

require_once __DIR__ . '/tx_languagevisibility_databaseTtContentTestcase.php';

class tx_languagevisibility_hooks_t3lib_page_ttcontent_testcase extends tx_languagevisibility_databaseTtContentTestcase {

	/**
	 * @var t3lib_pageSelect
	 */
	protected $t3lib_page;

	public function setUp() {
		parent::setUp();

		$this->makeSureContentElementsImported();
		$this->makeSureLanguagesImported();

		$this->t3lib_page = new t3lib_pageSelect();
		$this->t3lib_page->init(FALSE);
	}

	/**
	 * Check the visibility of some content elements with overlay-records
	 *
	 * @test
	 * @dataProvider getTtContentDataSets
	 * @param integer $uid
	 * @param integer $language
	 * @param integer $assertUid assert that record with this uid is used as overlay. NULL means record is removed.
	 * @param string $comment
	 */
	function visibility_overlay_ttcontent($uid, $language, $assertUid, $comment = '') {
		// check environment ...
		if (!t3lib_extMgm::isLoaded('version')) {
			$this->markTestSkipped('Not relevant if "version" is not installed');
		}

		if (is_object($GLOBALS['TSFE'])) {
			$this->markTestSkipped('Please turn off the fake frontend (phpunit extension configuration) - this test won\'t work with "fake" frontends ;)');
		}

		// ... get original record ...
		$unOverlayedRow = $this->getContentElementRow($uid);
		$this->assertTrue(
			is_array($unOverlayedRow) && $unOverlayedRow['uid'] == $uid,
			sprintf('record with uid %d found', $uid)
		);

		// ... overlay ...
		$overlayedRow = $this->t3lib_page->getRecordOverlay('tt_content', $unOverlayedRow, $language);

		// ... test
		if ($assertUid === NULL) {
			$this->assertEquals(
				FALSE,
				$overlayedRow,
				sprintf('record with id %d is removed in language %d', $uid, $language)
			);
		} elseif (array_key_exists('_LOCALIZED_UID', $overlayedRow)) {
			$this->assertSame(
				$assertUid,
				$overlayedRow['_LOCALIZED_UID'],
				sprintf('record %d in language %d is overlaid with record %d', $uid, $language, $assertUid)
			);
		} else {
			$this->assertSame(
				$assertUid,
				$overlayedRow['uid'],
				sprintf('record %d in language %d uses record %d', $uid, $language, $assertUid)
			);
		}
	}

	public function getTtContentDataSets() {
		$testDataSet = array(
			array(1,  1, '1',   '"default" without translation'),
			array(2,  1, '3',   '"default" with translation'),
			array(2,  2, '3',   '"default" with translation in fallback'),
			array(19, 1, '19',  '"forcedToYes" without translation'),
			array(20, 1, '21',  '"forcedToYes" with translation'),
			array(20, 2, '21',  '"forcedToYes" with translation in fallback'),
			array(22, 3, NULL,  '"forcedToNo" without translation'),
			array(22, 1, NULL,  '"forcedToNo" with translation'),
			array(22, 2, NULL,  '"forcedToNo" with translation in fallback'),
			array(15, 4, '16',  '"ifTranslated" with translation'),
			array(15, 5, NULL,  '"ifTranslated" with hidden translation'),
			array(24, 3, NULL,  '"ifTranslated" without translation'),
			array(24, 2, NULL,  '"ifTranslated" with translation in fallback'),
			array(26, 1, '27',  '"ifTranslatedInFallback" with translation'),
			array(26, 7, NULL,  '"ifTranslatedInFallback" without translation in any fallback'),
			array(26, 2, '27',  '"ifTranslatedInFallback" with translation in fallback'),

			// edge cases
			array(4,  1, NULL,  '"forcedToYes" in record, but "forcedToNo" set in overlay'),
			array(10, 1, NULL,  '"forcedToNo" in record, but "forcedToYes" set in overlay'),
			array(12, 1, '13',  'corrupted visibility setting in element is ignored'),
		);

		// set comment as key for each entry in the array (this labels the data set when running the test)
		return array_combine(array_map(function($row) { return $row[3]; }, $testDataSet), $testDataSet);
	}

	protected function getContentElementRow($uid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'tt_content',
			'uid = ' . intval($uid) . ' AND deleted = 0 AND hidden = 0 AND l18n_parent = 0 AND sys_language_uid IN (-1,0)'
		);
	}
}
