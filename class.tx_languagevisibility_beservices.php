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
		
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languageList = $languageRep->getLanguages ();
		
		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		
		$desc = $element->getInformativeDescription ();
		$visibleFlags = array ();
		foreach ( $languageList as $language ) {
			if ($visibility->isVisible ( $language, $element )) {
				$visibleFlags [] = $language->getFlagImg ( $this->pageId );
			}
		}
		
		return implode ( '', $visibleFlags );
	
	}
	
	function getElement($uid,$table) {
		$dao=t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		$elementfactoryName= t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');		
		$elementfactory=new $elementfactoryName($dao);		
    	$element=$elementfactory->getElementForTable($table,$uid);        
    	return $element;
	}
		
	function isVisible($uid, $table, $languageUid) {
		$rep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language = $rep->getLanguageById ( $languageUid );
		
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
	
	function canCurrrentUserCutCopyMoveDelete(){
		//current element is no overlay -> if user has rights to cutMoveDelete or is an admin don't filter commants
		$be_user 		= t3lib_div::makeInstance('tx_languagevisibility_beUser');
		if($be_user->allowCutCopyMoveDelete() || $be_user->isAdmin() ){				
			return true;								
		}else{
			return false;
		}
	}
	
	//@TODO: check TCA and get correct l18n_parent
	function isOverlayRecord($row, $table) {
		
		if ($row['l18n_parent'] != '' && $row['l18n_parent']!='0')
			return true;
		else
			return false;
	}
	
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
	function getAvailableOptionsForLanguage(tx_languagevisibility_language $language) {
		$uid = $language->getUid ();
		$select = array ();
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
		if (is_object ( $GLOBALS ['LANG'] )) {
			//get value from locallang:
			foreach ( $select as $k => $v ) {
				$select [$k] = $GLOBALS ['LANG']->sl ( 'LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.' . $v );
			}
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
		return $select;
	}
	
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
	
}

?>