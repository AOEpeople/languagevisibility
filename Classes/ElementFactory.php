<?php

namespace AOE\Languagevisibility;

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

use AOE\Languagevisibility\Dao\DaoCommon;
use AOE\Languagevisibility\Services\FeServices;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class \AOE\Languagevisibility\ElementFactory
 */
class ElementFactory {

	/**
	 * @var DaoCommon
	 */
	protected $dao;

	/**
	 * Dependency is injected, this object needs a simple Data Access Object (can be replaced in testcase)
	 */
	public function __construct($dao) {
		$this->dao = $dao;
	}

	/**
	 * Returns ready initialised "element" object. Depending on the element the correct element class is used. (e.g. page/content/fce)
	 *
	 * @param string $table table
	 * @param int $uid identifier
	 * @param bool $overlay_ids boolean parameter to overlay uids if the user is in workspace context
	 * @throws \UnexpectedValueException
	 * @return Element
	 */
	public function getElementForTable($table, $uid, $overlay_ids = TRUE) {
		if (!FeServices::isSupportedTable($table)) {
			throw new \UnexpectedValueException($table . ' not supported ', 1195039394);
		}

		if (!is_numeric($uid) || (intval($uid) === 0)) {
				// no uid => maybe NEW element in BE
			$row = array();
		} else {
			if (is_object($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->workspace != 0 && $overlay_ids) {
				$row = $this->getWorkspaceOverlay($table, $uid);
			} else {
				$row = $this->dao->getRecord($uid, $table);
			}
		}

		/** @var Element $element */
		switch ($table) {
			case 'pages':
				$element = GeneralUtility::makeInstance('AOE\\Languagevisibility\\PageElement', $row, $table);
				break;
			case 'tt_news':
				$element = GeneralUtility::makeInstance('AOE\\Languagevisibility\\TtnewsElement', $row, $table);
				break;
			case 'tt_content':
				if ($row['CType'] == 'templavoila_pi1') {
						// read DS:
					$srcPointer = $row['tx_templavoila_ds'];
					$DS = $this->_getTVDS($srcPointer);
					if (is_array($DS)) {
						if ($DS['meta']['langDisable'] == 1 && $DS['meta']['langDatabaseOverlay'] == 1) {
								// handle as special FCE with normal tt_content overlay:
							$element = GeneralUtility::makeInstance('AOE\\Languagevisibility\\FceOverlayElement', $row);
						} else {
							$element = GeneralUtility::makeInstance('AOE\\Languagevisibility\\FceElement', $row, $DS);
						}
					} else {
						throw new \UnexpectedValueException($table . ' uid:' . $row['uid'] . ' has no valid Datastructure ', 1195039394);
					}
				} else {
					$element = GeneralUtility::makeInstance('AOE\\Languagevisibility\\CElement', $row, $table);
				}
				break;
			default:
				if (isset ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['getElementForTable'][$table])) {
					$hookObj = GeneralUtility::getUserObj($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['getElementForTable'][$table]);
					if (method_exists($hookObj, 'getElementForTable')) {
						$element = $hookObj->getElementForTable($table, $uid, $row, $overlay_ids);
					}
				} elseif (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['recordElementSupportedTables'][$table])) {
					$element = $this->getElementInstance('AOE\\Languagevisibility\\RecordElement', $row, $table);
				} else {
					throw new \UnexpectedValueException($table . ' not supported ', 1195039394);
				}
				break;
		}

		$element->setTable($table);

		return $element;
	}

	/**
	 * Get workspace overlay for a record.
	 *
	 * @param  string  $table   Table name
	 * @param  integer $uid     Record UID
	 * @return array
	 */
	protected function getWorkspaceOverlay($table, $uid) {
		$row = $this->dao->getRecord($uid, $table);

		if (is_array($row)) {
			if (is_object($GLOBALS['TSFE'])) {
				$GLOBALS['TSFE']->sys_page->versionOL($table, $row);
			} else {
				\TYPO3\CMS\Backend\Utility\BackendUtility::workspaceOL($table, $row);
			}
		}
			// the overlay row might be FALSE if the record is hidden
			// or deleted in workspace. In this case we return an empty array.
		if (!is_array($row)) {
			$row = array();
		}

		return $row;
	}

	/**
	 * This method is used to retrieve all parent elements (an parent elements needs to
	 * have the flag 'tx_languagevisibility_inheritanceflag_original' or
	 * needs to be orverlayed with a record, that has the field 'tx_languagevisibility_inheritanceflag_overlayed'
	 * configured or is the first element of the rootline
	 *
	 * @param tx_languagevisibility_element $element
	 * @param $language
	 * @return array $elements (collection of tx_languagevisibility_element)
	 */
	public function getParentElementsFromElement(Element $element, $language) {
		$elements = array();

		if ($element instanceof PageElement) {
			$rootline = $this->getOverlayedRootLine($element->getUid(), $language->getUid());

			if (is_array($rootline)) {
				foreach ($rootline as $rootlineElement) {
					if ($rootlineElement['tx_languagevisibility_inheritanceflag_original'] == 1 ||
							$rootlineElement['tx_languagevisibility_inheritanceflag_overlayed'] == 1
					) {
						$elements[] = self::getElementForTable('pages', $rootlineElement['uid']);
					}
				}

				if (sizeof($elements) == 0) {
					$root = end($rootline);
					$elements[] = self::getElementForTable('pages', $root['uid']);
				}
			}
		} else {
			$parentPage = $this->getElementForTable('pages', $element->getUid());
			$elements = $this->getParentElementsFromElement($parentPage, $language);
		}

		return $elements;
	}

	/**
	 * This method is needed because the getRootline method from \TYPO3\CMS\Frontend\Page\PageRepository causes an error when
	 * getRootline is called be cause getRootline internally uses languagevisibility to determine the
	 * visibility during the rootline calculation. This results in an unlimited recursion.
	 *
	 * @todo The rooline can be build in a smarter way, once the rootline for a page has been created
	 * same parts of the rootline not have to be calculated twice.
	 * @param $uid
	 * @param $languageid
	 * @return array
	 * @internal param \The $integer page uid for which to seek back to the page tree root.
	 * @see tslib_fe::getPageAndRootline()
	 */
	protected function getOverlayedRootLine($uid, $languageid) {
		$cacheManager = CacheManager::getInstance();

		$cacheData = $cacheManager->get('overlayedRootline');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if (!$isCacheEnabled || !isset($cacheData[$uid][$languageid])) {
			$sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			$sys_page->sys_language_uid = $languageid;

			$uid = intval($uid);

			$loopCheck = 0;
			$theRowArray = Array();

			while ($uid != 0 && $loopCheck < 20) { // Max 20 levels in the page tree.

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid=' . intval($uid) . ' AND pages.deleted=0 AND pages.doktype!=255');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				if ($row) {
					$sys_page->versionOL('pages', $row, FALSE, TRUE);
					$sys_page->fixVersioningPid('pages', $row);

					if (is_array($row)) {
							// Mount Point page types are allowed ONLY a) if they are the outermost record in rootline and b) if the overlay flag is not set:
						$uid = $row['pid']; // Next uid
					}
						// Add row to rootline with language overlaid:
					$langvisHook = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'];
					unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility']);
					$theRowArray[] = $sys_page->getPageOverlay($row, $languageid);
					$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = $langvisHook;
				} else {
					return array(); // broken rootline.
				}

				$loopCheck++;
			}

				// Create output array (with reversed order of numeric keys):
			$output = Array();
			$c = count($theRowArray);
			foreach ($theRowArray as $key => $val) {
				$c--;
				$output[$c] = $val;
			}

			$cacheData[$uid][$languageid] = $output;
			$cacheManager->set('overlayedRootline', $cacheData);
		}

		return $cacheData[$uid][$languageid];
	}

	/**
	 * Determines the dataStructure from a given sourcePointer.
	 *
	 * @param $srcPointer
	 * @return array
	 */
	protected function _getTVDS($srcPointer) {
		$cacheManager = CacheManager::getInstance();

		$cacheData = $cacheManager->get('dataStructureCache');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if (!$isCacheEnabled || !isset($cacheData[$srcPointer])) {
			$DS = array();
			$srcPointerIsInteger = \TYPO3\CMS\Core\Utility\MathUtility::convertToPositiveInteger($srcPointer);
			if ($srcPointerIsInteger) { // If integer, then its a record we will look up:
				$sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
				$DSrec = $sys_page->getRawRecord('tx_templavoila_datastructure', $srcPointer, 'dataprot');
				$DS = GeneralUtility::xml2array($DSrec['dataprot']);
			} else { // Otherwise expect it to be a file:
				$file = GeneralUtility::getFileAbsFileName($srcPointer);
				if ($file && @is_file($file)) {
					$DS = GeneralUtility::xml2array(GeneralUtility::getUrl($file));
				}
			}

			$cacheData[$srcPointer] = $DS;
			$cacheManager->set('dataStructureCache', $cacheData);
		}

		return $cacheData[$srcPointer];
	}

	/**
	 * Gets instance depending on TYPO3 version
	 * @param $name name of the class
	 * @param array $row row that is used to initialaze element instance
	 * @return tx_languagevisibility_element
	 */
	private function getElementInstance($name, $row) {
		return GeneralUtility::makeInstance($name, $row);
	}
}
