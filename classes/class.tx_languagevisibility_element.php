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
require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_languagerepository.php');

abstract class tx_languagevisibility_element {
	private $visibilitySetting;

	public function __construct($row)    {
		$this->row=$row;
		$this->visibilitySetting=@unserialize($this->row['tx_languagevisibility_visibility']);
		if (!is_array($this->visibilitySetting)) {
				$this->visibilitySetting=array();
		}
		$this->initialisations();
	}

	/**
	* possibility to add inits in subclasses
	**/
	protected function initialisations() {

	}

	/**
	 * Returns the Uid of the Element
	 *
	 * @return int
	 */
	public function getUid(){
		return $this->row['uid'];
	}
	
	/**
	 * Returns the pid of the Element
	 *
	 * @return int
	 */
	public function getPid(){
		return $this->row['pid'];
	}
	
	public function getTitle(){
		return $this->row['title'];
	}
	
	/**
	 * Checks if the current record is set to language all (that is typically used to indicate that per default this element is visible in all langauges)
	 *
	 * @return unknown
	 */
	function isLanguageSetToAll() {
		if ($this->row['sys_language_uid']	== '-1')
			return true;

		return false;
	}


	/**
	 * Check if the element is set to the default language
	 */
	function isLanguageSetToDefault() {
		return  $this->row['sys_language_uid']	== '0';
	}


	/**
	 * Compare element-language and foreign language
	 *
	 * @return boolean
	 */
	function languageEquals(tx_languagevisibility_language $language) {
		return $this->row['sys_language_uid'] == $language->getUid();
	}


	/**
	 * Determines if the elements is a original or a overlay-element
	 *
	 * @return boolean
	 */
	function isOrigElement() {
		return  ($this->row['l18n_parent'] == '0');
	}
	

	/**
	 * Returns the uid of the original element. This method will only return 
	 * a non zero value if the element is an overlay;
	 *
	 * @return int
	 */
	function getOrigElementUid(){
		return  $this->row['l18n_parent']; 
	}

	/**
	 * Method to check the element is an Workspaceelement or not.
	 *
	 * @return boolean
	 */
	function isLiveWorkspaceElement(){
		return ($this->row['t3ver_wsid'] == 0);
	}
	
	function getWorkspaceUid(){
		return $this->row['t3ver_wsid'];
	}
	
	function getWorkspaceName(){
		
	}
	
	/**
	 * Returns the uid or the LiveWorkspace Record
	 *
	 * @return int
	 */
	function getLiveWorkspaceUid(){
		return $this->_getLiveUIDIfWorkspace($this->row);
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
		return (!$this->isLanguageSetToDefault()) && $this->isOrigElement();
	}


	function getInformativeDescription() {
		if(!$this->isLanguageSetToDefault()) {
			return 'this content element is already a translated version therefore content overlays are not suppoted';
		} else {
			return 'this is a normal content element (translations are managed with overlay records)';
		}
	}

	/**
	* returns the local settings for this element (saved in the element itself)
	**/
	function getLocalVisibilitySetting($languageid) {
		return $this->visibilitySetting[$languageid];

	}
	
	/**
	 * Returns all VisibilitySetting for this element.
	 *
	 * @return array
	 */
	function getAllVisibilitySettings(){
		return $this->visibilitySetting;
	}

	/**
	 * receive relevant fallbackOrder
	 */
	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrder();
	}

	/**
	* checks if this element has a translation, therefor several DB accesses are required
	**/
	function hasTranslation($languageid) {
		if (!is_numeric($languageid)){
			return false;
		}
				
		//check if overlay exist:
		if($languageid==0){
			return true;
		}

		$hasOverlay = $this->_hasOverlayRecordForLanguage($languageid);		
		if ($hasOverlay) {
			return true;
		}
		else {
				
			return false;
		}
	}

	function hasAnyTranslationInAnyWorkspace(){
		if($this->hasOverLayRecordForAnyLanguageInAnyWorkspace()){
			return true;
		}else{
			return false;
		}
	}

	protected function _hasOverlayRecordForLanguage($langid) {
		$row=$this->getOverLayRecordForCertainLanguage($langid,true);
		
		if ($row['uid'] != '')
    		return true;
    	else
    		return false;
	}

	abstract function hasOverLayRecordForAnyLanguageInAnyWorkspace();


	abstract function getOverLayRecordForCertainLanguage($languageId,$onlyUid=FALSE);

	protected function _getLiveRowIfWorkspace($row,$table) {
		if (!isset($row['pid']) || !isset($row['uid'])) {
			return false;
		}
		if ($row['pid']==-1) {
					return t3lib_BEfunc::getLiveVersionOfRecord($table,$row['uid']);
		}
		return $row;

	}
	protected function _getLiveUIDIfWorkspace($row) {
		if (!isset($row['pid']) ||!isset($row['t3ver_oid']) || !isset($row['uid'])) {
			return false;
		}
		return $row['t3ver_oid'];
	}

	/**
	*returns which field in the language should be used to read the default visibility
	*
	*@return string (blank=default / page=page)
	**/
	function getFieldToUseForDefaultVisibility() {
		return '';
	}
	
	/**
	 * Abstract method to determine the table, where the element is located in the database
	 *
	 * @return string
	 */
	abstract protected function getTable();
	
	/**
	 * Method to get a short description  of the elementtype. 
	 * An extending class should overwrite this method.
	 *
	 * @return string
	 */
	public function getElementDescription(){
		return 'TYPO3 Element';
	}
	
	
	/**
	 * Uses the abstract function getTable to get all Workspaceversion-UIDs of this
	 * record.
	 *
	 * @return array
	 */
	function getWorkspaceVersionUids(){
		$table 	= $this->getTable();
		$uids 	= array();
					
		if($table != ''){
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid',
							$table,
							't3ver_oid='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->row['uid'], $table).
							' AND uid !='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->row['uid'], $table).t3lib_BEfunc::deleteClause($table));

			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$uids[] = $row['uid'];
			}
		}
		
		return $uids;	
	}
}

?>