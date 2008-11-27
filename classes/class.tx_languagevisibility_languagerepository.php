<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_language.php');



class tx_languagevisibility_languagerepository {
	
	
	function getLanguages()    {

  		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','sys_language','','','','');
  		$return=array();
  		$return[]=$this->getDefaultLanguage();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {				
				$language= t3lib_div::makeInstance('tx_languagevisibility_language');	
				$language->setData($row);
				$return[]=$language;
			}
			return $return;
	}
	
	function getLanguagesForBEUser()    {
  		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','sys_language','','','','');
  		$return=array();
  		if ($GLOBALS['BE_USER']->checkLanguageAccess(0)) {
  			$return[]=$this->getDefaultLanguage();
  		}
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {				
				if ($GLOBALS['BE_USER']->checkLanguageAccess($row['uid'])) {
					$language= t3lib_div::makeInstance('tx_languagevisibility_language');	
					$language->setData($row);
					$return[]=$language;
				}
			}
			return $return;
	}
	
	
	function getDefaultLanguage() {
		$language= t3lib_div::makeInstance('tx_languagevisibility_language');	
		$row['uid']=0;
		$row['title']='Default';
		
		$language->setData($row);
		return $language;
		
	}
	
	function getLanguageById($id) {
		if ($id==0) {
			return $this->getDefaultLanguage();
		}
		else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','sys_language','uid='.intval($id),'','','');  		
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);			
			$language= t3lib_div::makeInstance('tx_languagevisibility_language');	
			$language->setData($row);
			return $language;		
		}
	}
	
	
}

?>