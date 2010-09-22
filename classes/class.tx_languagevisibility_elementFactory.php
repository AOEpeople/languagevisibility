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
/**
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_cacheManager.php';
require_once (PATH_t3lib . 'class.t3lib_page.php');

class tx_languagevisibility_elementFactory {

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
	 * @param $table	table
	 * @param $uid	identifier
	 * @param $overlay_ids boolean parameter to overlay uids if the user is in workspace context
	 *
	 * @throws Unknown_Element_Exception
	 **/
	function getElementForTable($table, $uid, $overlay_ids = true) {

		if (! is_numeric($uid)) {
			//no uid => maybe NEW element in BE
			$row = array();
		} else {
			/***
			 ** WORKSPACE NOTE
			 * the diffrent usecases has to be defined and checked..
			 **/
			if (is_object($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->workspace != 0 && $overlay_ids) {
				$row = $this->dao->getRecord($uid, $table);
				if (is_object($GLOBALS['TSFE'])) {
					$GLOBALS['TSFE']->sys_page->versionOL($table, $row);
				} else {
					t3lib_BEfunc::workspaceOL($table, $row);
				}
			} else {
				$row = $this->dao->getRecord($uid, $table);
			}
		}

		//@todo isSupported table
		/* @var $element tx_languagevisibility_element */
		switch ($table) {
			case 'pages' :
				if (version_compare(TYPO3_version, '4.3.0', '<')) {
					require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_pageelement.php');
					$elementclass = t3lib_div::makeInstanceClassName('tx_languagevisibility_pageelement');
					$element = new $elementclass($row);
				} else {
					$element = t3lib_div::makeInstance('tx_languagevisibility_pageelement', $row);
				}
				break;
			case 'tt_news' :
				if (version_compare(TYPO3_version, '4.3.0', '<')) {
					require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_ttnewselement.php');
					$elementclass = t3lib_div::makeInstanceClassName('tx_languagevisibility_ttnewselement');
					$element = new $elementclass($row);
				} else {
					$element = t3lib_div::makeInstance('tx_languagevisibility_ttnewselement', $row);
				}
				break;
			case 'tt_content' :
				if ($row['CType'] == 'templavoila_pi1') {
					//read DS:
					$srcPointer = $row['tx_templavoila_ds'];
					$DS = $this->_getTVDS($srcPointer);
					if (is_array($DS)) {
						if ($DS['meta']['langDisable'] == 1 && $DS['meta']['langDatabaseOverlay'] == 1) {
							//handle as special FCE with normal tt_content overlay:
							if (version_compare(TYPO3_version, '4.3.0', '<')) {
								require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_fceoverlayelement.php');
								$elementclass = t3lib_div::makeInstanceClassName('tx_languagevisibility_fceoverlayelement');
								$element = new $elementclass($row);
							} else {
								$element = t3lib_div::makeInstance('tx_languagevisibility_fceoverlayelement', $row);
							}
						} else {
							if (version_compare(TYPO3_version, '4.3.0', '<')) {
								require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_fceelement.php');
								$elementclass = t3lib_div::makeInstanceClassName('tx_languagevisibility_fceelement');
								$element = new $elementclass($row, $DS);
							} else {
								$element = t3lib_div::makeInstance('tx_languagevisibility_fceelement', $row, $DS);
							}
						}
					} else {
						throw new UnexpectedValueException($table . ' uid:' . $row['uid'] . ' has no valid Datastructure ', 1195039394);
					}
				} else {
					if (version_compare(TYPO3_version, '4.3.0', '<')) {
						require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_celement.php');
						$elementclass = t3lib_div::makeInstanceClassName('tx_languagevisibility_celement');
						$element = new $elementclass($row);
					} else {
						$element = t3lib_div::makeInstance('tx_languagevisibility_celement', $row);
					}
				}
				break;
			default :
				throw new UnexpectedValueException($table . ' not supported ', 1195039394);
				break;
		}

		$element->setTable($table);

		return $element;
	}

	/**
	 * This method is used to retrieve all parent elements (an parent elements needs to
	 * have the flag 'tx_languagevisibility_inheritanceflag_original' or
	 * needs to be orverlayed with a record, that has the field 'tx_languagevisibility_inheritanceflag_overlayed'
	 * configured or is the first element of the rootline
	 *
	 * @param tx_languagevisibility_element $element
	 * @return array $elements (collection of tx_languagevisibility_element)
	 */
	public function getParentElementsFromElement(tx_languagevisibility_element $element, $language) {
		$elements = array();

		if ($element instanceof tx_languagevisibility_pageelement) {
			/* @var $sys_page t3lib_pageSelect */
			$rootline = $this->getOverlayedRootLine($element->getUid(), $language->getUid());

			if (is_array($rootline)) {
				foreach ( $rootline as $rootlineElement ) {
					if ($rootlineElement['tx_languagevisibility_inheritanceflag_original'] == 1 ||
						$rootlineElement['tx_languagevisibility_inheritanceflag_overlayed'] == 1
					) {
						$elements[] = self::getElementForTable('pages', $rootlineElement['uid']);
					}
				}

				if(sizeof($elements) == 0) {
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
	 * This method is needed because the getRootline method from t3lib_pageSelect causes an error when
	 * getRootline is called be cause getRootline internally uses languagevisibility to determine the
	 * visibility during the rootline calculation. This results in an unlimited recursion.
	 *
	 * @todo The rooline can be build in a smarter way, once the rootline for a page has been created
	 * same parts of the rootline not have to be calculated twice.
	 *
	 * @param	integer		The page uid for which to seek back to the page tree root.
	 * @see tslib_fe::getPageAndRootline()
	 */
	protected function getOverlayedRootLine($uid, $languageid) {
		$cacheManager = tx_languagevisibility_cacheManager::getInstance();

		$cacheData = $cacheManager->get('overlayedRootline');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if (! $isCacheEnabled || ! isset($cacheData[$uid][$languageid])) {
			$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
			$sys_page->sys_language_uid = $languageid;

			$uid = intval($uid);

			// Initialize:
			$selFields = t3lib_div::uniqueList('pid,uid,t3ver_oid,t3ver_wsid,t3ver_state,t3ver_swapmode,title,alias,nav_title,media,layout,hidden,starttime,endtime,fe_group,extendToSubpages,doktype,TSconfig,storage_pid,is_siteroot,mount_pid,mount_pid_ol,fe_login_mode,' . $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);

			$loopCheck = 0;
			$theRowArray = Array();

			while ( $uid != 0 && $loopCheck < 20 ) { // Max 20 levels in the page tree.


				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selFields, 'pages', 'uid=' . intval($uid) . ' AND pages.deleted=0 AND pages.doktype!=255');
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
					if (version_compare(TYPO3_version, '4.3', '>')) {
						$langvisHook = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'];
						unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility']);
						$theRowArray[] = $sys_page->getPageOverlay($row, $languageid);
						$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = $langvisHook;
					} else {
						$theRowArray[] = $sys_page->_original_getPageOverlay($row, $languageid);
					}
				} else {
					return array(); // broken rootline.
				}

				$loopCheck ++;
			}

			// Create output array (with reversed order of numeric keys):
			$output = Array();
			$c = count($theRowArray);
			foreach ( $theRowArray as $key => $val ) {
				$c --;
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
		$cacheManager = tx_languagevisibility_cacheManager::getInstance();

		$cacheData = $cacheManager->get('dataStructureCache');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		if (! $isCacheEnabled || ! isset($cacheData[$srcPointer])) {
			$DS = array();
			if (t3lib_div::testInt($srcPointer)) { // If integer, then its a record we will look up:
				$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
				$DSrec = $sys_page->getRawRecord('tx_templavoila_datastructure', $srcPointer, 'dataprot');
				$DS = t3lib_div::xml2array($DSrec['dataprot']);
			} else { // Otherwise expect it to be a file:
				$file = t3lib_div::getFileAbsFileName($srcPointer);
				if ($file && @is_file($file)) {
					$DS = t3lib_div::xml2array(t3lib_div::getUrl($file));
				}
			}

			$cacheData[$srcPointer] = $DS;
			$cacheManager->set('dataStructureCache', $cacheData);
		}

		return $cacheData[$srcPointer];
	}
}
?>