<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Tolleiv
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
 *
 * @author	 Tolleiv
 * @package	 TYPO3
 * @version $Id:$
 */
class tx_languagevisibility_hooks_t3lib_page implements t3lib_pageSelect_getPageOverlayHook, t3lib_pageSelect_getRecordOverlayHook {

	/**
	 * This function has various possible results:
	 * 1)	$lUid unchanged -
	 * 			there was nothing to do for langvis and the overlay is requested is fine
	 * 2)	$lUid == null
	 * 			is relevant if we did the overlay ourselfs and the processing within getPageOverlay function is not relevant anymore
	 * 3)	$lUid changed
	 * 			is relevant if we just changed the target-languge but require getPageOverlay to proceed with the overlay-chrunching
	 *
	 * @param mixed $pageInput
	 * @param integer $lUid
	 * @param t3lib_pageSelect $parent
	 * @return void
	 */
	public function getPageOverlay_preProcess(&$pageInput, &$lUid, t3lib_pageSelect $parent) {
		if(!is_array($pageInput) || !isset($pageInput['uid'])) {
			return;
		}
		$page_id = $pageInput ['uid'];

			//call service to know if element is visible and which overlay language to use
		$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecord ( $page_id, 'pages', $lUid );
		if ($overlayLanguage === false) {
			$overlayLanguageForced = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecordForced ( $page_id, 'pages', $lUid );
				// don't use this recursion without further checks!!!!
				// this isn't used because there  seems to be no reason why we should overlay an invisible page...
			// $pageInput = $parent->getPageOverlay ( &$pageInput, $overlayLanguageForced );
			$pageInput ['_NOTVISIBLE'] = TRUE;
			$lUid = null;
		} else {
			$lUid = $overlayLanguage;
		}
	}

	/**
	 *
	 * @param string $table
	 * @param array $row
	 * @param integer $sys_language_content
	 * @param string $OLmode
	 * @param t3lib_pageSelect $parent
	 * @return void
	 */
	public function getRecordOverlay_preProcess($table, &$row, &$sys_language_content, $OLmode, t3lib_pageSelect $parent) {
		if(!is_array($row) || !isset($row['uid'])) {
			return;
		}
		try {
			$element = tx_languagevisibility_feservices::getElement ( $row ['uid'], $table );
			$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElement ( $element, $sys_language_content );
		} catch ( Exception $e ) {
			return;
		}

		if ($overlayLanguage === false || $overlayLanguage === 0) {
			unset ( $row );
			return;
		} else {
			$sys_language_content = $overlayLanguage;
		}
	}

	/**
	 *
	 * @param unknown_type $table
	 * @param unknown_type $row
	 * @param unknown_type $sys_language_content
	 * @param unknown_type $OLmode
	 * @param t3lib_pageSelect $parent
	 * @return void
	 */
	public function getRecordOverlay_postProcess($table, &$row, &$sys_language_content, $OLmode, t3lib_pageSelect $parent) {
		if(!is_array($row) || !isset($row['uid']) || $sys_language_content == 0) {
			return;
		}
		try {
			$element = tx_languagevisibility_feservices::getElement ( $row ['uid'], $table );
			$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElement ( $element, $sys_language_content );
		} catch ( Exception $e ) {
			return;
		}

		if ($element instanceof tx_languagevisibility_fceelement) {
				//for FCE the overlay processing is handled by templavoila module, so mark the row with additional infos:
			$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
			$overlayLanguageObj = $languageRep->getLanguageById ( $overlayLanguage );
			$row ['_OVERLAYLANGUAGEISOCODE'] = $overlayLanguageObj->getIsoCode ();
		} elseif ($element instanceof tx_languagevisibility_fceoverlayelement) {
				//now its getting tricky: we need to return overlay record with merged XML
			$olrow = $this->_getDatabaseTranslationOverlayRecord ( 'tt_content', $row, $overlayLanguage );
			if ($GLOBALS ['TSFE']) {
				$GLOBALS ['TSFE']->includeTCA ( 'tt_content' );
			}
				//parse fce xml, and where a xml field is empty in olrow -> use default one
			$flexObj = t3lib_div::makeInstance ( 't3lib_flexformtools' );
			$this->_callbackVar_defaultXML = t3lib_div::xml2array ( $row ['tx_templavoila_flex'] );
			$this->_callbackVar_overlayXML = t3lib_div::xml2array ( $olrow ['tx_templavoila_flex'] );
			if (! is_array ( $this->_callbackVar_overlayXML )) {
				$this->_callbackVar_overlayXML = array ();
			}
			$return = $flexObj->traverseFlexFormXMLData ( 'tt_content', 'tx_templavoila_flex', $row, $this, '_callback_checkXMLFieldsForFallback' );

			if($sys_language_content != $overlayLanguage) {
				$row = $parent->getRecordOverlay ( $table, $row, $overlayLanguage, $OLmode );
			}
			$row ['tx_templavoila_flex'] = t3lib_div::array2xml ( $this->_callbackVar_overlayXML );
		}
	}

	/**
	 *
	 *
	 */
	public function _callback_checkXMLFieldsForFallback($dsArr, $dataValue, $PA, $structurePath, &$pObj) {
		if ($dataValue != '' && ($dsArr ['TCEforms'] ['l10n_mode'] == 'mergeIfNotBlank' || $dsArr ['TCEforms'] ['l10n_mode'] == 'exclude')) {
			if ($dsArr ['TCEforms'] ['l10n_mode'] == 'exclude') {
				$pObj->setArrayValueByPath ( $structurePath, $this->_callbackVar_overlayXML, $dataValue );
			} else {
				$overlayValue = $pObj->getArrayValueByPath ( $structurePath, $this->_callbackVar_overlayXML );
				if ($overlayValue == '' && $dsArr ['TCEforms'] ['l10n_mode'] == 'mergeIfNotBlank') {
					$pObj->setArrayValueByPath ( $structurePath, $this->_callbackVar_overlayXML, $dataValue );
				}
			}
		}
	}

	protected function _getDatabaseTranslationOverlayRecord($table, $row, $languageId) {
		global $TCA;
		// Select overlay record:
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', $table, 'pid=' . intval ( $row ['pid'] ) . ' AND ' . $TCA [$table] ['ctrl'] ['languageField'] . '=' . intval ( $languageId ) . ' AND ' . $TCA [$table] ['ctrl'] ['transOrigPointerField'] . '=' . intval ( $row ['uid'] ) . $GLOBALS['TSFE']->sys_page->enableFields ( $table ), '', '', '1' );
		$olrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res );
		$GLOBALS['TSFE']->sys_page->versionOL ( $table, $olrow );
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $olrow;
	}
}
