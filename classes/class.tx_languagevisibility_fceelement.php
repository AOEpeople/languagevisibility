<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 AOE media (dev@aoemedia.de)
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
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_element.php');

require_once (PATH_t3lib . 'class.t3lib_flexformtools.php');

class tx_languagevisibility_fceelement extends tx_languagevisibility_element {

	private $langIsoCodeForFlexFormCallback = '';
	private $langChildren;
	private $langDisabled;
	private $disabledIsVisible;

	//flags which are set during processings
	private $_callBackFoundOverlay = FALSE;

	public function __construct($row, $DS) {
		parent::__construct ( $row );

		$this->langChildren = $DS ['meta'] ['langChildren'] ? 1 : 0;
		$this->langDisabled = $DS ['meta'] ['langDisable'] ? 1 : 0;
		$this->disabledIsVisible = $DS ['meta'] ['disabledIsVisible'] ? 1 : 0;
	}

	public function getElementDescription() {
		return 'FCE';
	}

	function getInformativeDescription() {
		if ($this->langDisabled == 1) {
			if ($this->disabledIsVisible == 1) {
				return 'FCE is in mode langDisabled, therefore cannot be translated. But it is configured to be handled as translated per Default.';
			} else {
				return 'FCE is in mode langDisabled, therefore cannot be translated.';
			}
		}
		if ($this->langChildren == 1) {
			return 'FCE is in mode inheritance';
		} else {
			return 'FCE is in mode seperate';
		}
	}

	function _hasOverlayRecordForLanguage($id) {
		$languageRep = tx_languagevisibility_languagerepository::makeInstance();
		$language = $languageRep->getLanguageById ( $id );
		$this->langIsoCodeForFlexFormCallback = strtoupper ( $language->getIsoCode () );
		$this->_callBackFoundOverlay = FALSE;

		// Get data structure:
		if ($this->langDisabled == 1) {
			//the FCE has langDisabled: this means there is no overlay
			if ($this->disabledIsVisible == 1) {
				return true;
			} else {
				return false;
			}
		}

		if ($GLOBALS ['TSFE']) {
			@$GLOBALS ['TSFE']->includeTCA ();
		}

		if ($this->langChildren == 1) {
			//the FCE has real overlay record


			$flexObj = t3lib_div::makeInstance ( 't3lib_flexformtools' );
			if ($this->row ['tx_templavoila_flex']) {
				$return = $flexObj->traverseFlexFormXMLData ( 'tt_content', 'tx_templavoila_flex', $this->row, $this, '_hasOverlayRecordForLanguage_Inheritance_flexFormCallBack' );
				if ($return !== TRUE && strlen ( $return ) > 0) {
					debug ( 'FCE: _hasOverlayRecordForLanguage has error:' . $return );
				}
				return $this->_callBackFoundOverlay;
			} else {
				//in case no xml yet (new created?)
				return false;
			}
		} else {
			//the FCE has no real overlay record
			$flexObj = t3lib_div::makeInstance ( 't3lib_flexformtools' );
			$flexObj->traverseFlexFormXMLData ( 'tt_content', 'tx_templavoila_flex', $this->row, $this, '_hasOverlayRecordForLanguage_Seperate_flexFormCallBack' );
			return $this->_callBackFoundOverlay;
		}
	}
	/**
	 *TODO
	 **/
	function getOverLayRecordForCertainLanguageImplementation($languageId, $onlyUid = FALSE) {
		return array ();
	}

	/**
	 * FlexForm call back function, see _hasOverlayRecordForLanguage
	 *
	 * @param	array		Data Structure of current field
	 * @param	string		Data value of current field
	 * @param	array		Various stuff in an array
	 * @param	string		path to location in flexform for current field
	 * @param	object		Reference to parent object
	 * @return	void
	 */
	public function _hasOverlayRecordForLanguage_Inheritance_flexFormCallBack($dsArr, $dataValue, $PA, $structurePath, &$pObj) {

		if ($this->langIsoCodeForFlexFormCallback == '') {
			return false;
		}

		// Only take lead from default values (since this is "Inheritance" localization we parse for)
		if (substr ( $structurePath, - 5 ) == '/vDEF') {
			$baseStructPath = substr ( $structurePath, 0, - 3 );
			$structurePath = $baseStructPath . $this->langIsoCodeForFlexFormCallback;
			$translValue = $pObj->getArrayValueByPath ( $structurePath, $pObj->traverseFlexFormXMLData_Data );
			if ($this->_isFlexFieldFilled ( $dsArr ['TCEforms'] ['config'], $translValue )) {
				$this->_callBackFoundOverlay = TRUE;
			}
		}
	}

	protected function _isFlexFieldFilled($cfg, $translValue) {
		if (($cfg ['type'] == 'check' && $translValue != 0) || ($cfg ['type'] != 'check' && $translValue != '')) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * FlexForm call back function, see _hasOverlayRecordForLanguage
	 *
	 * @param	array		Data Structure of current field
	 * @param	string		Data value of current field
	 * @param	array		Various stuff in an array
	 * @param	string		path to location in flexform for current field
	 * @param	object		Reference to parent object
	 * @return	void
	 */
	function _hasOverlayRecordForLanguage_Seperate_flexFormCallBack($dsArr, $dataValue, $PA, $structurePath, &$pObj) {
		if ($this->langIsoCodeForFlexFormCallback == '') {
			return false;
		}
		//path like: data/sDEF/lDEF/field_links/el/1/field_link/el/field_link_text/vDEF
		if (strpos ( $structurePath, '/lDEF/' )) {
			$structurePath = str_replace ( '/lDEF/', '/l' . $this->langIsoCodeForFlexFormCallback . '/', $structurePath );
			$translValue = $pObj->getArrayValueByPath ( $structurePath, $pObj->traverseFlexFormXMLData_Data );
			if ($this->_isFlexFieldFilled ( $dsArr ['TCEforms'] ['config'], $translValue )) {
				$this->_callBackFoundOverlay = TRUE;
			}

		}
	}

	function hasOverLayRecordForAnyLanguageInAnyWorkspace() {

	}

}

?>