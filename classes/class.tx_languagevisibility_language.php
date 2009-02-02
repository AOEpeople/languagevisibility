<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_languagerepository.php');

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/dao/class.tx_languagevisibility_daocommon.php');


class tx_languagevisibility_language {
	var $row;

	function setData($row)    {
		$this->row=$row;
	}

	function getFallbackOrder() {
		//unfortunatly defaultlangauge is 999 instead of 0 (reason in formrendering of typo3):
		$tx_languagevisibility_fallbackorder=str_replace('999','0',$this->row['tx_languagevisibility_fallbackorder']);
		return t3lib_div::trimExplode(',',$tx_languagevisibility_fallbackorder);
	}

	function getFallbackOrderElement() {
        if($this->usesComplexFallbackSettings()) {
		    $tx_languagevisibility_fallbackorderel=str_replace('999','0',$this->row['tx_languagevisibility_fallbackorderel']);
		    return t3lib_div::trimExplode(',',$tx_languagevisibility_fallbackorderel);
        } else {
            return $this->getFallbackOrder();
        }
	}

	function getFallbackOrderTTNewsElement() {
        if($this->usesComplexFallbackSettings()) {
		    $tx_languagevisibility_fallbackorderttnewel=str_replace('999','0',$this->row['tx_languagevisibility_fallbackorderttnewsel']);
		    return t3lib_div::trimExplode(',',$tx_languagevisibility_fallbackorderttnewel);
        } else {
            return $this->getFallbackOrder();
        }
	}
    
    function usesComplexFallbackSettings() {
        return intval($this->row['tx_languagevisibility_complexfallbacksetting']) > 0;
    }

	function getDefaultVisibilityForPage() {
		return $this->row['tx_languagevisibility_defaultvisibility'];
	}

	function getDefaultVisibilityForElement() {
        return $this->row['tx_languagevisibility_defaultvisibilityel'];
	}

	function getDefaultVisibilityForTTNewsElement() {
        return $this->row['tx_languagevisibility_defaultvisibilityttnewsel'];
	}
    
	function getUid() {
		return $this->row['uid'];
	}
	function getIsoCode() {
		// Finding the ISO code:
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_iso_2', 'static_languages', 'uid='.intval($this->row['static_lang_isocode']),'','');
    $static_languages_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		return $static_languages_row['lg_iso_2'];

	}
	function getTitle($pidForDefault='') {
		if ($this->getUid()=='0') {
			if ($pidForDefault=='')
				$pidForDefault=$this->_guessCurrentPid();
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig($pidForDefault, 'mod.SHARED');

			return strlen ($sharedTSconfig['properties']['defaultLanguageLabel']) ? $sharedTSconfig['properties']['defaultLanguageLabel'] : 'Default';
		}
		else {
			return $this->row['title'];
		}
	}

	function _guessCurrentPid() {
		return t3lib_div::_GP('id');
	}

	/**
	* @param  Optional the pid of the page. This can be used to get the correct flag for default language (which is set in tsconfig)
	**/
	function getFlagImg($pidForDefault='') {
		global $BACK_PATH;
		return '<img src="'.$this->getFlagImgPath($pidForDefault).'" title="'.$this->getTitle($pidForDefault).'-'.$this->getIsoCode().' ['.$this->getUid().']">';
	}


	/**
	* @param Optional the pid of the page. This can be used to get the correct flagpath for default language (which is set in tsconfig)
	**/
	function getFlagImgPath($pidForDefault='') {
		$flagAbsPath = t3lib_div::getFileAbsFileName($GLOBALS['TCA']['sys_language']['columns']['flag']['config']['fileFolder']);
		$flagIconPath = $BACK_PATH.'../'.substr($flagAbsPath, strlen(PATH_site));
		if ($this->getUid()=='0') {
			if ($pidForDefault=='') {
				$pidForDefault=$this->_guessCurrentPid();
			}
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig($pidForDefault, 'mod.SHARED');
			$img=strlen($sharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath.$sharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath.$sharedTSconfig['properties']['defaultLanguageFlag'] : null;
		}
		else {
			$path=$flagIconPath.$this->row['flag'];
		}
		return $path;
	}

	/**
	* checks if the given languageid is part of the fallback of this language
	* (used for permission options in the backend)
	* 
	* @param int uid
	* @return boolean
	**/
	function isLanguageUidInFallbackOrder($uid) {
		$fallbacks=$this->getFallbackOrder();
		return in_array($uid,$fallbacks);
	}
}

?>