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
 * Class tx_languagevisibility_hooks_t3lib_page
 *
 * @package Aoe\Languagevisibility\Hooks
 */
class tx_languagevisibility_hooks_t3lib_page implements \TYPO3\CMS\Frontend\Page\PageRepositoryGetPageOverlayHookInterface, \TYPO3\CMS\Frontend\Page\PageRepositoryGetRecordOverlayHookInterface {

	/**
	 * This function has various possible results:
	 * 1)    $lUid unchanged -
	 * there was nothing to do for langvis and the overlay is requested is fine
	 * 2)    $lUid == null
	 * is relevant if we did the overlay ourselfs and the processing within getPageOverlay function is not relevant anymore
	 * 3)    $lUid changed
	 * is relevant if we just changed the target-languge but require getPageOverlay to proceed with the overlay-chrunching
	 * 4)   $lUid changed to 0 (which may be the case for forced fallbacks to default). Please check Core Setting hideIfNotTranslated in this case to be sure the page can be shown in this case
	 *
	 * @param mixed $pageInput
	 * @param integer $lUid Passed ad reference!
	 * @param \TYPO3\CMS\Frontend\Page\PageRepository $parent
	 * @return void
	 */
	public function getPageOverlay_preProcess(&$pageInput, &$lUid, \TYPO3\CMS\Frontend\Page\PageRepository $parent) {
		if (is_int($pageInput)) {
			$page_id = $pageInput;
		} elseif (is_array($pageInput) && isset($pageInput['uid'])) {
			$page_id = $pageInput['uid'];
		} else {
			return;
		}

			// call service to know if element is visible and which overlay language to use
		$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecord($page_id, 'pages', $lUid);
		if ($overlayLanguage === FALSE) {
			$overlayLanguageForced = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecordForced($page_id, 'pages', $lUid);
				// don't use this recursion without further checks!!!!
				// this isn't used because there  seems to be no reason why we should overlay an invisible page...
				// $pageInput = $parent->getPageOverlay ( &$pageInput, $overlayLanguageForced );
			if (is_array($pageInput)) $pageInput['_NOTVISIBLE'] = TRUE;
			$lUid = NULL;
		} else {
			$lUid = $overlayLanguage;
		}
	}

	/**
	 * The flow in t3lib_page is:
	 *  - call preProcess
	 *  - if uid and pid > then overlay if langauge != 0
	 *  - after this postProcess is called - which only corrects the overlay row for certain elements
	 *
	 * @param string $table
	 * @param array $row
	 * @param integer $sys_language_content
	 * @param string $OLmode
	 * @param \TYPO3\CMS\Frontend\Page\PageRepository $parent
	 * @return void
	 */
	public function getRecordOverlay_preProcess($table, &$row, &$sys_language_content, $OLmode, \TYPO3\CMS\Frontend\Page\PageRepository $parent) {
		if (!tx_languagevisibility_feservices::isSupportedTable($table)
			|| (!is_array($row))
			|| (!isset($row['uid']))) {
			return;
		}

		try {
			$element = tx_languagevisibility_feservices::getElement($row['uid'], $table);
			$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElement($element, $sys_language_content);
		} catch ( tx_languagevisibility_InvalidRowException $e ) {
			$row['uid'] = 0;
			$row['pid'] = 0;
			return;
		}
		catch (Exception $e) {
			return;
		}

		if ($overlayLanguage === FALSE) {
			$row['uid'] = 0;
			$row['pid'] = 0;
			return;
		} elseif (!$element->isMonolithicTranslated()) {
				// for monolytic elements the tx_languagevisibility_feservices::getOverlayLanguageIdForElement return 0 to "tell" us that no overlay is required
				// but since the TYPO3 Core interprets a language with id 0 to not return anything we need to leave the $sys_language_content untouched for MonolithicTranslated elements
			$sys_language_content = $overlayLanguage;
		}

			/**
			 * the original value will be replaced by the original getRecordOverlay process
			 * therefore we've to store this elsewhere to make sure that the flexdata is available
			 * for the postProcess
			 */
		if ($element instanceof tx_languagevisibility_fceoverlayelement) {
			$row['_ORIG_tx_templavoila_flex'] = $row['tx_templavoila_flex'];
		}
	}

	/**
	 *
	 * @param string $table
	 * @param array $row
	 * @param integer $sys_language_content
	 * @param string $OLmode
	 * @param \TYPO3\CMS\Frontend\Page\PageRepository $parent
	 * @return void
	 */
	public function getRecordOverlay_postProcess($table, &$row, &$sys_language_content, $OLmode, \TYPO3\CMS\Frontend\Page\PageRepository $parent) {
		if (is_array($row) && $row['uid'] === 0 && $row['pid'] === 0) {
			$row = FALSE;
			return;
		}

		if (!tx_languagevisibility_feservices::isSupportedTable($table)
			|| (!is_array($row))
			|| (!isset($row['uid']))
			|| ($sys_language_content == 0)) {
			return;
		}

		try {
			$element = tx_languagevisibility_feservices::getElement($row['uid'], $table);
			$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElement($element, $sys_language_content);
		} catch (Exception $e) {
			return;
		}

		if ($element instanceof tx_languagevisibility_fceelement) {
				//for FCE the overlay processing is handled by templavoila module, so mark the row with additional infos:
			$languageRep = t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
			$overlayLanguageObj = $languageRep->getLanguageById($overlayLanguage);
			$row['_OVERLAYLANGUAGEISOCODE'] = $overlayLanguageObj->getIsoCode();
		} elseif ($element instanceof tx_languagevisibility_fceoverlayelement) {
				//now its getting tricky: we need to return overlay record with merged XML
			$row['tx_templavoila_flex'] = $row['_ORIG_tx_templavoila_flex'];
			unset($row['_ORIG_tx_templavoila_flex']);
			$olrow = $this->_getDatabaseTranslationOverlayRecord('tt_content', $row, $overlayLanguage);
			if ($GLOBALS['TSFE']) {
				$GLOBALS['TSFE']->includeTCA('tt_content');
			}
				//parse fce xml, and where a xml field is empty in olrow -> use default one
			$flexObj = t3lib_div::makeInstance('t3lib_flexformtools');
			$this->_callbackVar_defaultXML = t3lib_div::xml2array($row['tx_templavoila_flex']);
			$this->_callbackVar_overlayXML = t3lib_div::xml2array($olrow['tx_templavoila_flex']);
			if (! is_array($this->_callbackVar_overlayXML)) {
				$this->_callbackVar_overlayXML = array();
			}
			$return = $flexObj->traverseFlexFormXMLData('tt_content', 'tx_templavoila_flex', $row, $this, '_callback_checkXMLFieldsForFallback');

			if ($sys_language_content != $overlayLanguage) {
				$row = $parent->getRecordOverlay($table, $row, $overlayLanguage, $OLmode);
			}
			$row['tx_templavoila_flex'] = t3lib_div::array2xml_cs($this->_callbackVar_overlayXML, 'T3FlexForms', array('useCDATA' => TRUE));
		}
	}

	/**
	 * @param $dsArr
	 * @param $dataValue
	 * @param $PA
	 * @param $structurePath
	 * @param $pObj
	 */
	public function _callback_checkXMLFieldsForFallback($dsArr, $dataValue, $PA, $structurePath, &$pObj) {
		if ($dsArr['TCEforms']['l10n_mode'] == 'exclude') {
			$pObj->setArrayValueByPath($structurePath, $this->_callbackVar_overlayXML, $dataValue);
		} elseif ($dataValue != '' && $dsArr['TCEforms']['l10n_mode'] == 'mergeIfNotBlank') {
			$overlayValue = $pObj->getArrayValueByPath($structurePath, $this->_callbackVar_overlayXML);
			if ($overlayValue == '') {
				$pObj->setArrayValueByPath($structurePath, $this->_callbackVar_overlayXML, $dataValue);
			}
		}
	}

	/**
	 * @param $table
	 * @param $row
	 * @param $languageId
	 * @return mixed
	 */
	protected function _getDatabaseTranslationOverlayRecord($table, $row, $languageId) {
			// Select overlay record:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'pid=' . intval($row['pid']) . ' AND ' . $GLOBALS['TCA'][$table]['ctrl']['languageField'] . '=' . intval($languageId) . ' AND ' . $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] . '=' . intval($row['uid']) . $GLOBALS['TSFE']->sys_page->enableFields($table), '', '', '1');
		$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TSFE']->sys_page->versionOL($table, $olrow);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $olrow;
	}
}
