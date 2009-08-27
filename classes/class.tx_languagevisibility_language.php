<?php

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon.php');

class tx_languagevisibility_language {
	private $row;
	
	protected static $flagCache;
	
	function setData($row) {
		$this->row = $row;
	}
	
	/**
	 * Returns the fallback order of this language as array.
	 *
	 * @return array
	 */
	public function getFallbackOrder() {
		//unfortunatly defaultlangauge is 999 instead of 0 (reason in formrendering of typo3):
		$tx_languagevisibility_fallbackorder = str_replace ( '999', '0', $this->row ['tx_languagevisibility_fallbackorder'] );
		return t3lib_div::trimExplode ( ',', $tx_languagevisibility_fallbackorder );
	}
	
	/**
	 * Returns the fallback order for this language for elements
	 *
	 * @return array
	 */
	public function getFallbackOrderElement() {
		if ($this->usesComplexFallbackSettings ()) {
			$tx_languagevisibility_fallbackorderel = str_replace ( '999', '0', $this->row ['tx_languagevisibility_fallbackorderel'] );
			return t3lib_div::trimExplode ( ',', $tx_languagevisibility_fallbackorderel );
		} else {
			return $this->getFallbackOrder ();
		}
	}
	
	/**
	 * Returns the fallback order for news elements as array
	 *
	 * @return array
	 */
	public function getFallbackOrderTTNewsElement() {
		if ($this->usesComplexFallbackSettings ()) {
			$tx_languagevisibility_fallbackorderttnewel = str_replace ( '999', '0', $this->row ['tx_languagevisibility_fallbackorderttnewsel'] );
			return t3lib_div::trimExplode ( ',', $tx_languagevisibility_fallbackorderttnewel );
		} else {
			return $this->getFallbackOrder ();
		}
	}
	
	/**
	 * Method to check if complex fallback settings should be used.
	 *
	 * @return boolean
	 */
	public function usesComplexFallbackSettings() {
		return intval ( $this->row ['tx_languagevisibility_complexfallbacksetting'] ) > 0;
	}
	
	/**
	 * Method to read the defaultVisibility setting of pages.
	 *
	 * @return string
	 */
	public function getDefaultVisibilityForPage() {
		return $this->row ['tx_languagevisibility_defaultvisibility'];
	}
	
	/**
	 * Method to read the defaultVisibility for elements
	 *
	 * @return string
	 */
	public function getDefaultVisibilityForElement() {
		return $this->row ['tx_languagevisibility_defaultvisibilityel'];
	}
	
	/**
	 * Method to read the visibility for tt news Elements.
	 *
	 * @return boolean
	 */
	function getDefaultVisibilityForTTNewsElement() {
		return $this->row ['tx_languagevisibility_defaultvisibilityttnewsel'];
	}
	
	/**
	 * Method to get the primary key of the language record.
	 *
	 * @return int
	 */
	function getUid() {
		return $this->row ['uid'];
	}
	
	function getIsoCode() {
		// Finding the ISO code:
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'lg_iso_2', 'static_languages', 'uid=' . intval ( $this->row ['static_lang_isocode'] ), '', '' );
		$static_languages_row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result );
		return $static_languages_row ['lg_iso_2'];
	}
	
	function getTitle($pidForDefault = '') {
		if ($this->getUid () == '0') {
			if ($pidForDefault == '')
				$pidForDefault = $this->_guessCurrentPid ();
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig ( $pidForDefault, 'mod.SHARED' );
			
			return strlen ( $sharedTSconfig ['properties'] ['defaultLanguageLabel'] ) ? $sharedTSconfig ['properties'] ['defaultLanguageLabel'] : 'Default';
		} else {
			return $this->row ['title'];
		}
	}
	
	function _guessCurrentPid() {
		return t3lib_div::_GP ( 'id' );
	}
	
	/**
	 * @param  Optional the pid of the page. This can be used to get the correct flag for default language (which is set in tsconfig)
	 **/
	function getFlagImg($pidForDefault = '') {
		global $BACK_PATH;
		
		$cache_key = 'pid:'.$pidForDefault.'uid:'.$this->getUid();
		if(!isset(self::$flagCache[$cache_key])){
			self::$flagCache[$cache_key] = '<img src="' . $this->getFlagImgPath ( $pidForDefault, $BACK_PATH ) . '" title="' . $this->getTitle ( $pidForDefault ) . '-' . $this->getIsoCode () . ' [' . $this->getUid () . ']">';
		}
		
		return self::$flagCache[$cache_key];
	}
	
	/**
	 * @param Optional the pid of the page. This can be used to get the correct flagpath for default language (which is set in tsconfig)
	 **/
	function getFlagImgPath($pidForDefault = '', $BACK_PATH = '') {
		
		$flagAbsPath = t3lib_div::getFileAbsFileName ( $GLOBALS ['TCA'] ['sys_language'] ['columns'] ['flag'] ['config'] ['fileFolder'] );
		
		$flagIconPath = $BACK_PATH . '../' . substr ( $flagAbsPath, strlen ( PATH_site ) );
		if ($this->getUid () == '0') {
			if ($pidForDefault == '') {
				$pidForDefault = $this->_guessCurrentPid ();
			}
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig ( $pidForDefault, 'mod.SHARED' );
			$path = strlen ( $sharedTSconfig ['properties'] ['defaultLanguageFlag'] ) && @is_file ( $flagAbsPath . $sharedTSconfig ['properties'] ['defaultLanguageFlag'] ) ? $flagIconPath . $sharedTSconfig ['properties'] ['defaultLanguageFlag'] : null;
		} else {
			$path = $flagIconPath . $this->row ['flag'];
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
		$fallbacks = $this->getFallbackOrder ();
		return in_array ( $uid, $fallbacks );
	}
}

?>