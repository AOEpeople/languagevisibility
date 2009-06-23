<?php

require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_visibility.php';


class tx_languagevisibility_visibilityService {
	/**
	 * @var boolean holds the state if inheritance is enabled or not
	 */
	protected $useInheritance;
	
	
	public function __construct(){
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
		if($confArr['inheritanceEnabled']){
			$this->setUseInheritance();	
		}
	}
	
	/**
	 * @return boolean
	 */
	public function getUseInheritance() {
		return $this->useInheritance;
	}
	
	/**
	 * @param boolean $useInheritance
	 */
	public function setUseInheritance($useInheritance=true) {
		$this->useInheritance = $useInheritance;
	}
	
	/**
	* returns relevant languageid for overlay record or false if element is not visible for guven language
	* 
	* @param tx_languagevisibility_language $language
	* @param tx_languagevisibility_element $element
	* @return mixed 
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
	 * currently used to get correct r
	 * page rootline - also if a page in rootline is not vivible
	 *
	 * @return unknown
	 */
	function getLastRelevantOverlayLanguageId() {
		return $this->_relevantOverlayLanguageId;
	}


	/**
	* Returns true or false wether the element is visible in the certain language.
	*  (sets for internal access only $this->_relevantOverlayLanguageId which holds the overlay languageid)
	**/
	public function isVisible(tx_languagevisibility_language $language,$element)    {
			$this->_relevantOverlayLanguageId=$language->getUid();
			$visibility=$this->getVisibilitySetting($language,$element);

			if ($visibility=='yes') {
					return true;
			}
			elseif($visibility == 'no+'){
				return false;
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
					//there is no direct translation for this element, therefore check languages in fallback
					$fallBackOrder=$element->getFallbackOrder($language);
					if(!is_array($fallBackOrder)) throw new Exception(print_r($element,true));

					foreach ($fallBackOrder as $languageid) {
						if ( $element->hasTranslation($languageid) ) {							
							$this->_relevantOverlayLanguageId=$languageid;
							return true;
						}
					}

					return false;
				}
			}
			else {
				//no setting or default:		
				if ($language->getUid() == '0'){
					return true;
				}else{
					return false;
				}
			}
	}
	
	/**
	 * This method is used to get all bequeathing elements of an element (makes only sence for pages)
	 * it checks if there is any element in the rootline which has any inherited visibility setting (like no+, yes+)  as configured visibility.
	 * 
	 * @param tx_languagevisibility_language
	 * @param tx_languagevisibility_element
	 * 
	 * @return tx_languagevisibility_visibility $visibility
	 */
	protected function getInheritedVisibility(tx_languagevisibility_language $language, $element){
		
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
;
		$elements = $elementfactory->getParentElementsFromElement($element,$language);

		if(is_array($elements) && count($elements) > 0){
			foreach($elements as $element){
				/* @var $element tx_languagevisibility_pageelement */
				$visibility = new tx_languagevisibility_visibility();
				$visibility->setVisibilityString($element->getLocalVisibilitySetting($language->getUid()));
				//is the setting a inheritable setting:
				if($visibility->getVisibilityString() == 'no+' || $visibility->getVisibilityString()== 'yes+'){
					$visibility->setVisibilityDescription('inherited from uid '.$element->getUid());
					return $visibility;
				}
			}
		}
		$visibility = new tx_languagevisibility_visibility();
		$visibility->setVisibilityString('-');
		
		return $visibility;
	}

	/**
	* return the accumulated visibility setting: reads default for language then reads local for element and merges them.
	*  if local is default, then the global is used or it is forced to be "yes" if the language was set to all.
	*  if the element itself is a translated original record the element is only visible in the specific language
	*	 If nothing is set the hardcoded default "t" (translated) is returned
	* 
	* @param tx_languagevisibility_language $language
	* @param tx_languagevisibility_element $element
	* @return string
	*/
	public function getVisibilitySetting(tx_languagevisibility_language $language,$element) {
		return $this->getVisibility($language,$element)->getVisibilityString();
	}
	
	
	/**
	 * This method can be used to retrieve an informal description for the visibility of an element
	 * 
	* @param tx_languagevisibility_language $language
	* @param tx_languagevisibility_element $element
	* @return string
	 */
	public function getVisibilityDescription(tx_languagevisibility_language $language,$element){
		return $this->getVisibility($language,$element)->getVisibilityDescription();
	}
	
	/**
	 * Create a visiblity object for an element for a given language.
	
	* @param tx_languagevisibility_language $language
	* @param tx_languagevisibility_element $element
	* @return tx_languagevisibility_visibility
	*/
	protected function getVisibility(tx_languagevisibility_language $language,$element){

		$visibility 	= new tx_languagevisibility_visibility();
		$local			=	$element->getLocalVisibilitySetting($language->getUid());
			
		if ($local !='' && $local !='-') {
			$visibility->setVisibilityString($local)->setVisibilityDescription('local setting '.$local);
			return $visibility;
		}
		else {
			if ($element->isLanguageSetToAll()) {
				$visibility->setVisibilityString('yes')->setVisibilityDescription('language configured to all');
				return $visibility;
			}

			if ($element->isMonolithicTranslated()) {				
				if( $element->languageEquals($language)){
					$visibility->setVisibilityString('yes')->setVisibilityDescription('');
				}else{
					$visibility->setVisibilityString('no')->setVisibilityDescription('');
				}
					
				return $visibility;
			}

			if ($element->getFieldToUseForDefaultVisibility()=='page') {
				if($this->getUseInheritance()){
					// gibt es in der rootline das visibiklitysetting no+ für die sprache dann return 'no'
					$inheritedVisibility=$this->getInheritedVisibility($language,$element);
	
					switch ($inheritedVisibility->getVisibilityString()) {
						case 'no+':
							//if no+ is found it means the current element should be threated as if it has no set
							$visibility->setVisibilityString('no')->setVisibilityDescription('force to no ('.$inheritedVisibility->getVisibilityDescription().')');
						break;
						case 'yes+':
							$visibility->setVisibilityString('yes')->setVisibilityDescription('force to yes ('.$inheritedVisibility->getVisibilityDescription().')');
						break;
						default: 
							$visibility->setVisibilityString($language->getDefaultVisibilityForPage())->setVisibilityDescription('default visibility for page');
						break;
					}
				}else{
					//inheritance is disabled 
					$visibility->setVisibilityString($language->getDefaultVisibilityForPage())->setVisibilityDescription('default visibility for page');
				}
			}elseif($element->getFieldToUseForDefaultVisibility()=='tt_news'){
				$visibility->setVisibilityString($language->getDefaultVisibilityForTTNewsElement())->setVisibilityDescription('default visibility for news');
			}else {
				$visibility->setVisibilityString($language->getDefaultVisibilityForElement())->setVisibilityDescription('default visibility for element');
			}
			
			if ($visibility->getVisibilityString() ==''){
				$visibility->setVisibilityString('t')->setVisibilityDescription('no visibility configured using default setting "t"');
			}
			
			return $visibility;
		}		
	}
}

?>