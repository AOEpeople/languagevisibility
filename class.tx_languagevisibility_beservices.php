<?php

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_visibilityService.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'patch/lib/class.tx_languagevisibility_beUser.php');

class tx_languagevisibility_beservices {
	
	function getVisibleFlagsForElement($uid, $table) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		try {
			$element = $elementfactory->getElementForTable ( $table, $uid );
		} catch ( Exception $e ) {
			return '-';
		}
		
		$languageRep 	= t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languageList 	= $languageRep->getLanguages ();
		$visibility 	= t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		
		$visibleFlags = array ();
		foreach ( $languageList as $language ) {
			if ($visibility->isVisible ( $language, $element )) {
				$visibleFlags [] = $language->getFlagImg ( $this->pageId );
			}
		}
		
		return implode ( '', $visibleFlags );
	
	}
	
	/**
	 * Helper function to get an element by uid and tablename.
	 *
	 * @param int $uid
	 * @param string $table
	 * @param boolean $overlay_ids
	 * @return tx_languagevisibility_element
	 */
	public static function getElement($uid,$table,$overlay_ids = true) {
		$dao=t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactoryName= t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');		
		$elementfactory=new $elementfactoryName($dao);		
    	$element=$elementfactory->getElementForTable($table,$uid,$overlay_ids);        
    	return $element;
	}
		
	/**
	 * Helper function to check i an element with a given uid and tablename is visible for a languageid.
	 *
	 * @param int $uid
	 * @param string $table
	 * @param int $languageUid
	 * @return boolean
	 */
	public static function isVisible($uid, $table, $languageUid) {
		$rep 		= t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language 	= $rep->getLanguageById ( $languageUid );
		
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		
		try {
			$element = $elementfactory->getElementForTable ( $table, $uid );
		} catch ( Exception $e ) {
			return false;
		}
		
		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		
		return $visibility->isVisible ( $language, $element );
	}
	
	/**
	 * Helper function to check if the current backend user has rights to cut,copy or delete
	 *
	 * @return boolean
	 */
	public static function canCurrrentUserCutCopyMoveDelete(){
		//current element is no overlay -> if user has rights to cutMoveDelete or is an admin don't filter commants
		$be_user 		= t3lib_div::makeInstance('tx_languagevisibility_beUser');
		if($be_user->allowCutCopyMoveDelete() || $be_user->isAdmin() ){				
			return true;								
		}else{
			return false;
		}
	}

	/**
	 * Helper function to check if a record from a given table in an overlayrecord
	 *
	 * @param array $row
	 * @param string $table
	 * @return boolean
	 */
	public static function isOverlayRecord($row, $table) {
		switch($table){
			case 'pages_language_overlay':
				return true;
			break;
			case 'pages':
				return false;	
			break;
			
			case 'tt_news':
			case 'tt_content':
				global $TCA;	 		
				t3lib_div::loadTCA($table);
								
				$tanslationIdField = $TCA[$table]['ctrl']['transOrigPointerField'];

				if($tanslationIdField != ''){
					//if the field which points to the orginal of the translation is 
					//not 0 a translation exists and we have an overlay record
					
					return $row[$tanslationIdField] != 0;
				}else{
					//if no translation field exists this is not an overlay
					return false;
				}
			break;
		}
	}
	
	/**
	 * Method to check if records of a given table support the languagevisibility feature
	 *
	 * @param string $table
	 * @return boolean
	 */
	function isSupportedTable($table) {
		$supported = array ('tt_news', 'tt_content', 'pages' );
		if (in_array ( $table, $supported )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Static service method to determine if an record has a translation in any language
	 *
	 * @param int $uid
	 * @param string $table
	 * @return boolean.
	 */
	function hasTranslationInAnyLanguage($uid, $table) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		
		try{
			$element = $elementfactory->getElementForTable ( $table, $uid );
			$rep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
			$languages = $rep->getLanguages ();
			
			foreach ( $languages as $language ) {
				//skip default language
				if ($language->getUid () != 0) {
					if ($element->hasTranslation ( $language->getUid () )) {
						return true;
					}
				}
			}
		}catch(UnexpectedValueException $e){
			//the element can not be handeld by language visibility
			return false;
		}
		return false;
	}
	
	/**
	 * checks if the current BE_USER has access to the page record:
	 *  that is the case if:
	 *			a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 *			b) edit page record: only if the record is only visible in languages where the user has access to
	 *			b.1) also if the languages taht are visibile and falls back to allowed languages
	 *			c) delete: same as for edit (only if user has access to all visible languages)
	 **/
	function hasUserAccessToPageRecord($id, $cmd = 'edit') {
		
		global $BE_USER;
		if ($cmd == 'new') {
			return true;
		}
		if (! is_numeric ( $id )) {
			return false;
		}
		$rep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languages = $rep->getLanguages ();
		foreach ( $languages as $language ) {
			//echo 'check '.$language->getUid();
			if ($this->isVisible ( $id, 'pages', $language->getUid () )) {
				if (! $BE_USER->checkLanguageAccess ( $language->getUid () )) {
					//no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder ();
					foreach ( $fallbacks as $lId ) {
						if ($GLOBALS ['BE_USER']->checkLanguageAccess ( $lId )) {
							$isInFallback = TRUE;
							continue;
						}
					}
					if (! $isInFallback)
						return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * checks if the current BE_USER has access to a record:
	 *  that is the case if:
	 *			a) new page created -> always because then the languagevisibility is set to never for all languages where the user has no access
	 *			b) edit page record: only if the record is only visible in languages where the user has access to
	 **/
	function hasUserAccessToEditRecord($table, $id) {
		global $BE_USER;
	
		if (! is_numeric ( $id )) {
			return false;
		}
		if (! $this->isSupportedTable ( $table )) {
			return true;
		}

		//check if overlay record:
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$row = $dao->getRecord ( $id, $table );
		
		//@TODO check TCA for languagefield
		if ($this->isOverlayRecord ( $row, $table )) {
		
			if ($BE_USER->checkLanguageAccess ( $row ['sys_language_uid'] ))
				return true;
			else
				return false;
		}
		
		$rep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languages = $rep->getLanguages ();
		foreach ( $languages as $language ) {
			if (tx_languagevisibility_beservices::isVisible ( $id, $table, $language->getUid () )) {
				if (! $BE_USER->checkLanguageAccess ( $language->getUid () )) {
					//no access to a visible language: check fallbacks
					$isInFallback = FALSE;
					$fallbacks = $language->getFallbackOrder ();
					foreach ( $fallbacks as $lId ) {
						if ($GLOBALS ['BE_USER']->checkLanguageAccess ( $lId )) {
							$isInFallback = TRUE;
							continue;
						}
					}
					if (! $isInFallback)
						return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * returns array with the visibility options that are allowed for the current user.
	 *
	 * @param tx_languagevisibility_language $language
	 * @return array
	 */
	function getAvailableOptionsForLanguage(tx_languagevisibility_language $language, $isOverlay=false) {
		$uid = $language->getUid ();
		$select = array ();
		
		if(!$isOverlay){
			if ($uid == 0) {
				$select ['-'] = '-';
				$select ['yes'] = 'yes';
				$select ['no'] = 'no';
			} else {
				$select ['-'] = '-';
				$select ['yes'] = 'yes';
				$select ['no'] = 'no';
				$select ['t'] = 't';
				$select ['f'] = 'f';
			
			}
	
			//check permissions, if user has no permission only no for the language is allowed
			// if the user has permissions for languages that act as fallbacklanguage: then the languages that falls back can have "-" in the options!
			if (! $GLOBALS ['BE_USER']->checkLanguageAccess ( $uid )) {
				//check if the language falls back to one of the languages the user has permissions:
				$isInFallback = FALSE;
				$fallbacks = $language->getFallbackOrder ();
				foreach ( $fallbacks as $lId ) {
					if ($GLOBALS ['BE_USER']->checkLanguageAccess ( $lId )) {
						$isInFallback = TRUE;
						continue;
					}
				}
				$select = array ();
				if ($isInFallback) {
					$select ['-'] = '-';
				}
				
				$select ['no'] = 'no';
			}
		}else{
			//overlays elements can only have "force to no"
			$select ['-'] = '-';
			$select ['no'] = 'no';
		}

		/**
		 * Get translations of labels from the locallang file
		 */
		if (is_object ( $GLOBALS ['LANG'] )) {
			//get value from locallang:
			foreach ( $select as $k => $v ) {
				$select [$k] = $GLOBALS ['LANG']->sl ( 'LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.' . $v );
			}
		}		
		
		return $select;
	}
	
	/**
	 * This method is used to create an visibility array with the default settings
	 * for all languages.
	 *
	 * @return array
	 */
	function getDefaultVisibilityArray() {
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languageList = $languageRep->getLanguages ();
		$default = array ();
		foreach ( $languageList as $language ) {
			$options = tx_languagevisibility_beservices::getAvailableOptionsForLanguage ( $language );
			$default [$language->getUid ()] = array_shift ( array_keys($options) );
		
		}
		return $default;
	}

	/**
	 * This method is used to get the table where original elements of the
	 * given table are stored.
	 *
	 * @param string $table
	 * @return string
	 */
	public static function getOriginalTableOfTranslation($table){
		global $TCA;	 		
		t3lib_div::loadTCA($table);
		
		$translationTable = $TCA[$table]['ctrl']['transOrigPointerTable'];
		if($translationTable != ''){
			return $translationTable;
		}else{
			return $table;
		}
	}
	
	/**
	 * This method is used to determine the original uid of a translation
	 *
	 * @param array $row
	 * @param string $table
	 * @return string
	 */
	public static function getOriginalUidOfTranslation($row,$table){
		global $TCA;	 		
		t3lib_div::loadTCA($table);
		
		return $row[$TCA[$table]['ctrl']['transOrigPointerField']];
	}
}
?>