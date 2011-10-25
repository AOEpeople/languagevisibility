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
 * Abstract basis class for all elements (elements are any translateable records in the system)
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_languagerepository.php');

require_once (t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_cacheManager.php');

require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/exceptions/class.tx_languagevisibility_InvalidRowException.php');

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
	 *
	 * @param $row
	 * @return void
	 */
	public function __construct($row) {

		if (! $this->isRowOriginal($row)) {
			throw new tx_languagevisibility_InvalidRowException();
		}

		$this->row = $row;
		$this->localVisibilitySetting = @unserialize($this->row['tx_languagevisibility_visibility']);

		if (! is_array($this->localVisibilitySetting)) {
			$this->localVisibilitySetting = array();
		}

		if (! is_array($this->overlayVisibilitySetting)) {
			$this->overlayVisibilitySetting = array();
		}

		$this->initialisations();
	}

	/**
	 * Method to set the tablename of the recordelement.
	 *
	 * @param string $table
	 */
	function setTable($table) {
		$this->table = $table;
	}

	/**
	 * Method to get the tablename
	 *
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Method to deternmine that an Element will not be instanciated with
	 * data of an overlay.
	 */
	protected function isRowOriginal($row) {
		return $row['l18n_parent'] == 0;
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
		return $this->row['uid'];
	}

	/**
	 * Returns the pid of the Element
	 *
	 * @return int
	 */
	public function getPid() {
		return $this->row['pid'];
	}

	/**
	 * Return the content of the title field
	 *
	 * @return unknown
	 */
	public function getTitle() {
		return $this->row['title'];
	}

	/**
	 * Returns the uid of the original element. This method will only return
	 * a non zero value if the element is an overlay;
	 *
	 * @return int
	 */
	public function getOrigElementUid() {
		return $this->row['l18n_parent'];
	}

	/**
	 * Returns the workspace uid of an element.
	 *
	 * @return unknown
	 */
	public function getWorkspaceUid() {
		return $this->row['t3ver_wsid'];
	}

	/**
	 * Returns an description of the element.
	 *
	 * @return string
	 */
	public function getInformativeDescription() {
		if (! $this->isLanguageSetToDefault()) {
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
		$localVisibility = $this->getVisibilitySettingStoredInDefaultRecord($languageid);

		if ($overlayVisibility == 'no+' || $localVisibility == 'no+') {
			$res = 'no+';
		} elseif ($overlayVisibility == 'no') {
			$res = $overlayVisibility;
		} else {
			$res = $localVisibility;
		}

		return $res;
	}

	//make protected?
	/**
	 * Returns the global visibility setting for the element (saved in the overlay)
	 *
	 * @return string
	 */
	public function getVisibilitySettingStoredInOverlayRecord($languageid) {
		//if global visibility has not been determined, determine and cache it


		if (is_array($this->overlayVisibilitySetting)) {
			if (! isset($this->overlayVisibilitySetting[$languageid])) {
				$overlay = $this->getOverLayRecordForCertainLanguage($languageid);
				$overlayVisibilitySettings = @unserialize($overlay['tx_languagevisibility_visibility']);

				if (is_array($overlayVisibilitySettings)) {
					$this->overlayVisibilitySetting[$languageid] = $overlayVisibilitySettings[$languageid];
				} else {
					$this->overlayVisibilitySetting[$languageid] = '-';
				}
			}
		}

		return $this->overlayVisibilitySetting[$languageid];
	}

	/**
	 * This method is only need to display the visibility setting in the backend.
	 *
	 * @param int $languageid
	 * @return string
	 */
	public function getVisibilitySettingStoredInDefaultRecord($languageid) {
		return $this->localVisibilitySetting[$languageid];
	}

	/**
	 * This method returns an overlay of a record, independent from
	 * a frontend or backend context
	 *
	 * @param string $table
	 * @param string $olrow
	 * @return array
	 */
	protected function getContextIndependentWorkspaceOverlay($table, $olrow) {
		if (is_object($GLOBALS['TSFE']->sys_page)) {
			$GLOBALS['TSFE']->sys_page->versionOL($table, $olrow);
		} else {
			t3lib_BEfunc::workspaceOL($table, $olrow);
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
		return $language->getFallbackOrder($this);
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
		return $this->row['sys_language_uid'] == '0';
	}

	/**
	 * Determines if the elements is a original or a overlay-element
	 *
	 * @return boolean
	 */
	protected function isOrigElement() {
		return ($this->row['l18n_parent'] == '0');
	}

	/**
	 * Checks if the current record is set to language all (that is typically used to indicate that per default this element is visible in all langauges)
	 *
	 * @return unknown
	 */
	function isLanguageSetToAll() {
		if ($this->row['sys_language_uid'] == '-1') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method to check the element is an Workspaceelement or not.
	 *
	 * @return boolean
	 */
	function isLiveWorkspaceElement() {
		return ($this->row['pid'] != - 1);
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
		return (! $this->isLanguageSetToDefault()) && $this->isOrigElement();
	}

	/**
	 * Compare element-language and foreign language.
	 *
	 * @todo make this method work for pages
	 * @return boolean
	 */
	public function languageEquals(tx_languagevisibility_language $language) {
		return $this->row['sys_language_uid'] == $language->getUid();
	}

	/**
	 * Checks if this element has a translation, therefor several DB accesses are required
	 *
	 * @return boolean
	 **/
	public function hasTranslation($languageid) {

		$result = FALSE;
		if (! is_numeric($languageid)) {
			$result = FALSE;
		} else if ($languageid == 0) {
			$result = TRUE;
		} else if ($this->_hasOverlayRecordForLanguage($languageid)) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Checks if this Element has a Translation in any workspace.
	 *
	 * @return boolean
	 */
	public function hasAnyTranslationInAnyWorkspace() {
		if ($this->hasOverLayRecordForAnyLanguageInAnyWorkspace()) {
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
		$row = $this->getOverLayRecordForCertainLanguage($langid, true);
		return $row['uid'] != '';
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

	/**
	 * By default no element supports inheritance
	 *
	 * @param void
	 * @return boolean
	 */
	public function supportsInheritance() {
		return false;
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
	 * This method is used to retrieve an overlay record of a given record.
	 *
	 * @param $languageId
	 * @param $onlyUid
	 * @return array
	 */
	public function getOverLayRecordForCertainLanguage($languageId, $onlyUid = false) {
		//get caching hints
		$table = $this->getTable();
		$uid = $this->getUid();
		$workspace = intval($GLOBALS['BE_USER']->workspace);

		$cacheManager = tx_languagevisibility_cacheManager::getInstance();

		$cacheData = $cacheManager->get('overlayRecordCache');
		$isCacheEnabled = $cacheManager->isCacheEnabled();
		if (! $isCacheEnabled || ! isset($cacheData[$table][$uid][$languageId][$workspace])) {
			$cacheData[$table][$uid][$languageId][$workspace] = $this->getOverLayRecordForCertainLanguageImplementation($languageId);
			$cacheManager->set('overlayRecordCache', $cacheData);
		}

		return $cacheData[$table][$uid][$languageId][$workspace];
	}

	/**
	 * Check the records enableColumns
	 *
	 * @param  $row
	 * @return bool
	 */
	protected function getEnableFieldResult($row) {
		$ctrl = $GLOBALS['TCA'][$this->table]['ctrl'];
		$enabled = TRUE;
		if (is_array($ctrl['enablecolumns'])) {
			if ($ctrl['enablecolumns']['disabled']) {
				$enabled = $row[$ctrl['enablecolumns']['disabled']] == 0;
			}
			if ($ctrl['enablecolumns']['starttime']) {
				$enabled &= $row[$ctrl['enablecolumns']['starttime']] <= $GLOBALS['SIM_ACCESS_TIME'];
			}
			if ($ctrl['enablecolumns']['endtime']) {
				$endtime = $row[$ctrl['enablecolumns']['endtime']];
				$enabled &= $endtime == 0 || $endtime > $GLOBALS['SIM_ACCESS_TIME'];
			}

			if ($ctrl['enablecolumns']['fe_group'] && is_object($GLOBALS['TSFE'])) {
				$fe_group = $row[$ctrl['enablecolumns']['fe_group']];
				if ($fe_group) {
					$currentUserGroups = t3lib_div::intExplode(',', $GLOBALS['TSFE']->gr_list);
					$recordGroups = t3lib_div::intExplode(',', $fe_group);
					$sharedGroups = array_intersect($recordGroups, $currentUserGroups);
					$enabled &= count($sharedGroups) > 0;
				}
			}
		}
		return $enabled;
	}

	/**
	 * This method should provide the implementation to get the overlay of an element for a
	 * certain language. The result is cached be the method getOverLayRecordForCertainLanguage.
	 *
	 * @param int $languageId
	 * @param int $onlyUid
	 */
	abstract protected function getOverLayRecordForCertainLanguageImplementation($languageId);
}
?>