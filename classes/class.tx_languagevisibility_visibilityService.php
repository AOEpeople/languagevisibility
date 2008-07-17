<?php




class tx_languagevisibility_visibilityService {
	
	/**
	* returns relevant languageid for overlay record or false if element is not visible for guven language
	**/
	function getOverlayLanguageIdForLanguageAndElement(tx_languagevisibility_language $language,$element) {
		if ($this->isVisible($language,$element)) {			
			return $this->_relevantOverlayLanguageId;
		}
		else {				
			return false;
		}		
	}
	
	
	/**
	* Returns true or false wether the element is visible in the certain language.
	*  (sets for internal access only $this->_relevantOverlayLanguageId which holds the overlay languageid)
	**/
	function isVisible(tx_languagevisibility_language $language,$element)    {
			$this->_relevantOverlayLanguageId=$language->getUid();
			$visibility=$this->getVisibilitySetting($language,$element);
			
			if ($visibility=='yes') {
					return true;
			}
			elseif ($visibility=='no') {
				return false;
			}
			elseif ($visibility=='t') {
				if ($element->hasTranslation($language->getUid())) {
					return true;
				}
				else {
					return false;
				}
			}
			elseif ($visibility=='f') {
				if ($element->hasTranslation($language->getUid())) {
					return true;
				}
				else {
					$fallBackOrder=$language->getFallbackOrder();
					//echo 'checking..'.$element->row['uid'];
					foreach ($fallBackOrder as $languageid) {
						if ($element->hasTranslation($languageid)) {
							//echo 'found for !'.$languageid;
							$this->_relevantOverlayLanguageId=$languageid;
							return true;
						}
					}
					/*
					$languageRep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');	    			
					foreach ($fallBackOrder as $languageid) {
						$fallbackLanguage=$languageRep->getById($languageid);
						if ($this->isVisible($fallbackLanguage,$element)) {
							return true;
						}
					}
					*/
					return false;
				}
			}
			else {
				//no setting or default:
				if ($language->getUid() == '0') 
					return true;
				else
					return false;
			}
	}
	
	/**
	* return the accumulated visibility setting: reads default for language then reads local for element and merges them.
	*  if local is default, then the global is used or it is forced to be "yes" if the language was set to all.
	*	 If nothing is set the hardcoded default "t" (translated) is returned
	*/	
	function getVisibilitySetting(tx_languagevisibility_language $language,$element) {		
		$local=$element->getLocalVisibilitySetting($language->getUid());
		if ($local !='' && $local !='-') {
			return $local;
		}
		else {
			if ($element->isLanguageSetToAll()) {
				return 'yes';	
			}
			
			if ($element->getFieldToUseForDefaultVisibility()=='page') {
				$global=$language->getDefaultVisibilityForPage();
			}
			elseif($element->getFieldToUseForDefaultVisibility()=='tt_news'){
				$global=$language->getDefaultVisibilityForTTNewsElement();
			}
			else {
				$global=$language->getDefaultVisibilityForElement();
			}
			if ($global =='')
				return 't';
			else
				return $global;
		}		
	}
	
	
}

?>