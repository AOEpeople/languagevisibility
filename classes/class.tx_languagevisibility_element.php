<?php

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
	
	function isLanguageSetToAll() {
		if ($this->row['sys_language_uid']	== '-1')
			return true;
		
		return false;		
	}
	
	function getInformativeDescription() {
		return 'this is a normal content element (translations are managed with overlay records)';		
	}
	
	/**
	* returns the local settings for this element (saved in the element itself)
	**/
	function getLocalVisibilitySetting($languageid) {
		return $this->visibilitySetting[$languageid];
		
	}
	
	/** 
	* checks if this element has a translation, therefor several DB accesses are required	
	**/	
	function hasTranslation($languageid) {
		if (!is_numeric($languageid)) 
			return false;
		//check if overlay exist:
		if ($languageid==0)
			return true;
			
		if ($this->_hasOverlayRecordForLanguage($languageid)) {
			return true;
		}
		else {
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
	
}

?>