<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Daniel P?tzinger (poetzinger@aoemedia.de)
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
 * Abstract basis class for all elements (elements are any translateable records in the system)
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 */
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/exceptions/class.tx_languagevisibility_InvalidRowException.php');

abstract class tx_languagevisibility_element {
	
	/**
	 * This array holds the local visibility settings (from the 'tx_languagevisibility_visibility' field)
	 *
	 * @var array
	 */
	private $localVisibilitySetting;
	
	/**
	 * This array holds the global visibility setting, the global visibility setting. The translation of an element can overwrite 
	 * the visibility of its own language. 
	 *
	 * @var array
	 */
	private $overlayVisibilitySetting;
	
	/**
	 * Merged visibility
	 *
	 * @var array
	 */
	private $allVisibilitySetting;
	
	public function __construct($row) {
		
		if(!$this->isRowOriginal($row)){
			throw new tx_languagevisibility_InvalidRowException();
		}
		
		$this->row = $row;
		$this->localVisibilitySetting = @unserialize ( $this->row ['tx_languagevisibility_visibility'] );
		
		if (! is_array ( $this->localVisibilitySetting )) {
			$this->localVisibilitySetting = array ();
		}
		$this->initialisations ();
	}
	
	/**
	 * Method to deternmine that an Element will not be instanciated with
	 * data of an overlay.
	 */
	protected function isRowOriginal($row){
		return $row ['l18n_parent'] == 0;
	}
	
	/**
	 * possibility to add inits in subclasses
	 **/
	protected function initialisations() {
	
	}
	
	#############
	# GET METHODS
	#############
	/**
	 * Returns the Uid of the Element
	 *
	 * @return int
	 */
	public function getUid() {
		return $this->row ['uid'];
	}
	
	/**
	 * Returns the pid of the Element
	 *
	 * @return int
	 */
	public function getPid() {
		return $this->row ['pid'];
	}
	
	/**
	 * Return the content of the title field
	 *
	 * @return unknown
	 */
	public function getTitle() {
		return $this->row ['title'];
	}
	
	/**
	 * Returns the uid of the original element. This method will only return 
	 * a non zero value if the element is an overlay;
	 *
	 * @return int
	 */
	public function getOrigElementUid() {
		return $this->row ['l18n_parent'];
	}
	
	/**
	 * Returns the workspace uid of an element.
	 *
	 * @return unknown
	 */
	public function getWorkspaceUid() {
		return $this->row ['t3ver_wsid'];
	}
	
	/**
	 * Returns the uid or the LiveWorkspace Record
	 *
	 * @return int
	 */
	public function getLiveWorkspaceUid() {
		return $this->_getLiveUIDIfWorkspace ( $this->row );
	}
	
	/**
	 * Returns an description of the element.
	 *
	 * @return string
	 */
	public function getInformativeDescription() {
		if (! $this->isLanguageSetToDefault ()) {
			return 'this content element is already a translated version therefore content overlays are not suppoted';
		} else {
			return 'this is a normal content element (translations are managed with overlay records)';
		}
	}
	
	/** 
	 * This method is used to determine the visibility of the element. Technically it merges the visibility of
	 * the default language record and the overlay record and returns the visibility. The visibility in the overlayrecord
	 * can overwrite the visibility of its own language.
	 * 
	 * @return string
	 **/
	public function getLocalVisibilitySetting($languageid) {
		$overlayVisibility = $this->getVisibilitySettingStoredInOverlayRecord($languageid);
		if($overlayVisibility  != '' && $overlayVisibility  != '-'){
			$res = $overlayVisibility ;
		}else{
			$local 	= $this->getVisibilitySettingStoredInDefaultRecord($languageid);		
			$res 	= $local;
		}
		
		return $res;
	}
	
	/**
	 * Returns the global visibility setting for the element (saved in the overlay)
	 * 
	 * @return string
	 */
	public function getVisibilitySettingStoredInOverlayRecord($languageid){
		//if global visibility has not been determined, determine and cache it
		
		if(!isset($this->overlayVisibilitySetting [$languageid])){
			$overlay 					= $this->getOverLayRecordForCertainLanguage($languageid);
			$overlayVisibilitySettings 	= @unserialize ($overlay ['tx_languagevisibility_visibility'] );
						
			if(is_array($overlayVisibilitySettings)){
				$this->overlayVisibilitySetting [$languageid] = $overlayVisibilitySettings[$languageid];
			}else{
				$this->overlayVisibilitySetting [$languageid] = '';
			}
		}
				
		return $this->overlayVisibilitySetting [$languageid];
	}
	
	/**
	 * This method is only need to display the visibility setting in the backend.
	 *
	 * @param int $languageid
	 * @return string
	 */
	public function getVisibilitySettingStoredInDefaultRecord($languageid){
		return $this->localVisibilitySetting [$languageid];
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param string $table
	 * @param string $olrow
	 * @return array
	 */
	protected function getContextIndependentWorkspaceOverlay($table,$olrow){
		if (is_object($GLOBALS['TSFE']->sys_page)) {
			$GLOBALS['TSFE']->sys_page->versionOL($table,$olrow);
		}
		else {
			t3lib_BEfunc::workspaceOL($table,$olrow);
		}
		
		return $olrow;
	}
	

	/**
	 * Returns all VisibilitySetting for this element.
	 *
	 * @todo we need to decide if local and global settings need to be merged
	 * @return array
	 */
	#function getAllVisibilitySettings() {
	#	return $this->allVisibilitySetting;
	#}
	
		
	/**
	 * receive relevant fallbackOrder
	 */
	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrder ();
	}
	
	/**
	 * Uses the abstract function getTable to get all Workspaceversion-UIDs of this
	 * record.
	 *
	 * @return array
	 */
	function getWorkspaceVersionUids() {
		$uids = array ();
		
		if ($this->isLiveWorkspaceElement ()) {
			$table = $this->getTable ();
			$uid = $this->row ['uid'];
			if ($table != '' && $uid != 0) {
				$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'uid', $table, 't3ver_oid=' . $GLOBALS ['TYPO3_DB']->fullQuoteStr ( $this->row ['uid'], $table ) . ' AND uid !=' . $GLOBALS ['TYPO3_DB']->fullQuoteStr ( $this->row ['uid'], $table ) . t3lib_BEfunc::deleteClause ( $table ) );
				
				while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {
					$uids [] = $row ['uid'];
				}
			}
		}
		
		return $uids;
	}
	
	################
	# STATE METHODS
	################
	/**
	 * Check if the element is set to the default language
	 * 
	 * @return boolean
	 */
	function isLanguageSetToDefault() {
		return $this->row ['sys_language_uid'] == '0';
	}
	
	/**
	 * Determines if the elements is a original or a overlay-element
	 *
	 * @return boolean
	 */
	protected function isOrigElement() {
		return ($this->row ['l18n_parent'] == '0');
	}
	
	/**
	 * Checks if the current record is set to language all (that is typically used to indicate that per default this element is visible in all langauges)
	 *
	 * @return unknown
	 */
	function isLanguageSetToAll() {
		if ($this->row ['sys_language_uid'] == '-1')
			return true;
		
		return false;
	}
	
	/**
	 * Method to check the element is an Workspaceelement or not.
	 *
	 * @return boolean
	 */
	function isLiveWorkspaceElement() {
		return ($this->row ['pid'] != - 1);
	}
	
	/**
	 * Determines whether the element is a translated original record ...
	 *
	 * @return boolean
	 */
	function isMonolithicTranslated() {
		/*
		 * Timo: this does not work with pages because pages do not have the field 'sys_language_uid'
		 * and the languagevisibility_pages class only represent elements from the table pages not
		 * from page_language_overlay
		 */
		return (! $this->isLanguageSetToDefault ()) && $this->isOrigElement ();
	}
	
	/**
	 * Compare element-language and foreign language
	 *
	 * @return boolean
	 */
	public function languageEquals(tx_languagevisibility_language $language) {
		return $this->row ['sys_language_uid'] == $language->getUid ();
	}
	
	/**
	 * Checks if this element has a translation, therefor several DB accesses are required
	 * 
	 * @return boolean
	 **/
	public function hasTranslation($languageid) {
		if (! is_numeric ( $languageid )) {
			return false;
		}
		
		//check if overlay exist:
		if ($languageid == 0) {
			return true;
		}
		
		$hasOverlay = $this->_hasOverlayRecordForLanguage ( $languageid );
		if ($hasOverlay) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Checks if this Element has a Translation in any workspace.
	 *
	 * @return boolean
	 */
	public function hasAnyTranslationInAnyWorkspace() {
		if ($this->hasOverLayRecordForAnyLanguageInAnyWorkspace ()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * This method can be used to determine if an overlay for a language exists.
	 * 
	 * @return boolean
	 * @param int $langid
	 */
	protected function _hasOverlayRecordForLanguage($langid) {
		$row = $this->getOverLayRecordForCertainLanguage ( $langid, true );
		
		if ($row ['uid'] != '')
			return true;
		else
			return false;
	}
	
	/**
	 * This method is used to determine the row of the live workspace, if the given row
	 * is a row from any workspace.
	 * 
	 * @return array
	 * @param array $row
	 * @param string $table
	 */
	protected function _getLiveRowIfWorkspace($row, $table) {
		if (! isset ( $row ['pid'] ) || ! isset ( $row ['uid'] )) {
			return false;
		}
		if ($row ['pid'] == - 1) {
			return t3lib_BEfunc::getLiveVersionOfRecord ( $table, $row ['uid'] );
		}
		
		return $row;
	}
	
	/**
	 * Method is used to determine only the live uid of a row if it is a workspace version.
	 * if this is a unversiond record it returns false.
	 * 
	 * @return mixed int if uid can be determined boolean false if not
	 * @param array $row
	 */
	protected function _getLiveUIDIfWorkspace($row) {
		if (! isset ( $row ['pid'] ) || ! isset ( $row ['t3ver_oid'] ) || ! isset ( $row ['uid'] )) {
			return false;
		}
		return $row ['t3ver_oid'];
	}
	
	/**
	 * Returns which field in the language should be used to read the default visibility
	 *
	 *@return string (blank=default / page=page)
	 **/
	public function getFieldToUseForDefaultVisibility() {
		return '';
	}
	
	/**
	 * Method to get a short description  of the elementtype. 
	 * An extending class should overwrite this method.
	 *
	 * @return string
	 */
	public function getElementDescription() {
		return 'TYPO3 Element';
	}
	
	################
	# ABSTRACT METHODS
	################
	
	/**
	 * Abstract method to determine if there exsists any translation in any workspace.
	 * 
	 * @return boolean
	 */
	abstract public function hasOverLayRecordForAnyLanguageInAnyWorkspace();
	
	/**
	 * Enter description here...
	 *
	 * @param int $languageId
	 * @param int $onlyUid
	 */
	abstract public function getOverLayRecordForCertainLanguage($languageId, $onlyUid = FALSE);
	
	/**
	 * Abstract method to determine the table, where the element is located in the database
	 *
	 * @return string
	 */
	abstract protected function getTable();
}

?>