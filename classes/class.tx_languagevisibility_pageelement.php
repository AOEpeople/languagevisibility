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
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_element.php');

class tx_languagevisibility_pageelement extends tx_languagevisibility_element {

	/**
	 * Returns if this Element is a translation or not.
	 * Pages are always original records, because overlays are stored in the
	 * table pages_language_overlay
	 *
	 * @return boolean
	 */
	protected function isOrigElement() {
		return true;
	}

	/**
	 * Returns a simple description of the element type.
	 *
	 * @return string
	 */
	public function getElementDescription() {
		return 'Page';
	}

	/**
	 * This method is used to determine if this element is an element
	 * in the default language. For pages this is always true, because
	 * the table pages does not contain translations.
	 *
	 * @return boolean
	 */
	function isLanguageSetToDefault() {
		return true;
	}

	/**
	 * Returns an Informative description of the element.
	 *
	 * @return string
	 */
	function getInformativeDescription() {
		return 'this is a normal page element (translations are managed with seperate overlay records)';
	}

	/**
	 * Method to get an overlay of an element for a certain langugae
	 *
	 * @param int $lUid
	 * @param boolean $onlyUid
	 * @return array return the database row
	 */
	protected function getOverLayRecordForCertainLanguageImplementation($lUid) {
		if ($lUid>0) {
			$fieldArr = explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields']);

			$page_id = $this->row['t3ver_oid']?$this->row['t3ver_oid']:$this->getUid();	// Was the whole record
			$fieldArr = array_intersect($fieldArr,array_keys($this->row));		// Make sure that only fields which exist in the incoming record are overlaid!

			if (count($fieldArr))	{
				$table = 'pages_language_overlay';
				if (is_object($GLOBALS['TSFE']->sys_page)) {
					$enableFields = $GLOBALS['TSFE']->sys_page->enableFields($table);
				} else {
					$enableFields = '';
				}
				$fieldArr[] = 'deleted';
				$fieldArr[] = 'hidden';				
					// Selecting overlay record:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					implode(',',$fieldArr),
					'pages_language_overlay',
					'pid='.intval($page_id).'
						AND sys_language_uid='.intval($lUid).
						$enableFields,
					'',
					'',
					'1'
				);
				$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$olrow = $this->getContextIndependentWorkspaceOverlay($table, $olrow);
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				if (!$olrow['hidden'] && !$olrow['deleted']) {
					$overlayRecord = $olrow;
				}
			} else {
				$overlayRecord =  $this->row;
			}
		} else {
			$overlayRecord =  $this->row;
		}
		return $overlayRecord;
	}

	/**
	 * Returns which field in the language should be used to read the default visibility
	 *
	 * @return string (blank=default / page=page)
	 **/
	function getFieldToUseForDefaultVisibility() {
		return 'page';
	}

	/**
	 * Method to determine if this element has any translations in any workspace.
	 *
	 * @return boolean
	 */
	function hasOverLayRecordForAnyLanguageInAnyWorkspace() {

		//if we handle a workspace record, we need to get it's live version
		if ($this->row['pid'] == - 1) {
			$useUid = $this->row['t3ver_oid'];
		} else {
			$useUid = $this->row['uid'];
		}

		// if a workspace record has an overlay, an overlay also exists in the livews with versionstate = 1
		// therefore we have to look for any undeleted overlays of the live version
		$fields = 'count(uid) as anz';
		$table = 'pages_language_overlay';
		$where = 'deleted = 0 AND pid=' . $useUid;

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields, $table, $where);
		$anz = $rows[0]['anz'];

		return ($anz > 0);
	}

	/**
	 * The page elements supports inheritance.
	 *
	 * @param void
	 * @return boolean
	 */
	public function supportsInheritance() {
		return true;
	}
}
?>