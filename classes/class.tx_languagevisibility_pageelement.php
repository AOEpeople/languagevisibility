<?php

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_element.php');

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
	 * Returns the name of the table.
	 *
	 * @return string
	 */
	protected function getTable() {
		return 'pages';
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
	 * @param int $id
	 * @param boolean $onlyUid
	 * @return array return the database row
	 */
	function getOverLayRecordForCertainLanguage($id, $onlyUid = FALSE) {		
		##
		# Ensure we have the live version
		##
		$row 	= $this->row;
		$useUid = $row ['uid'];
		
		if ($row ['pid'] == - 1) {
			$useUid = $row ['t3ver_oid'];
		}
		
		if ($GLOBALS ['BE_USER']->workspace == 0) {
			$addWhere = ' AND t3ver_state!=1'; //// Shadow state for new items MUST be ignored
		}
		
		$where 	= 'deleted = 0 AND hidden = 0 AND sys_language_uid=' . intval ( $id ) . ' AND pid=' . intval ( $useUid ) . $addWhere;
		$fields = ($onlyUid) ? 'uid' : '*';
		$table 	= 'pages_language_overlay';		
			
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $fields, $table, $where, '', '' );
		
		$olrow 	= $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result );	
		$olrow 	= $this->getContextIndependentWorkspaceOverlay($table,$olrow);
		
		return $olrow;
	}
	
	/**
	 *returns which field in the language should be used to read the default visibility
	 *
	 *@return string (blank=default / page=page)
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
		if ($this->row ['pid'] == - 1) {
			$useUid = $this->row ['t3ver_oid'];
		} else {
			$useUid = $this->row ['uid'];
		}
		
		// if a workspace record has an overlay, an overlay also exists in the livews with versionstate = 1
		// therefore we have to look for any undeleted overlays of the live version 		
		$fields = 'count(*) as anz';
		$table = 'pages_language_overlay';
		$where = 'deleted = 0 AND pid=' . $useUid;
		
		$rows = $GLOBALS ['TYPO3_DB']->exec_SELECTgetRows ( $fields, $table, $where );
		$anz = $rows [0] ['anz'];
		
		return ($anz > 0);
	}
}
?>