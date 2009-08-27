<?php

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_language.php');

class tx_languagevisibility_languagerepository {
	
	protected static $instance;
	
	protected $languageCache;
	
	/**
	 * This method returns an array with all available language objects in the system.
	 *
	 * @see tx_languagevisibility_language
	 * @return array
	 */
	function getLanguages() {
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'sys_language', '', '', '', '' );
		$return = array ();
		$return [] = $this->getDefaultLanguage ();
		while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {
			$language = t3lib_div::makeInstance ( 'tx_languagevisibility_language' );
			$language->setData ( $row );
			$return [] = $language;
		}
		
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $return;
	}
	
	function getLanguagesForBEUser() {
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'sys_language', '', '', '', '' );
		$return = array ();
		if ($GLOBALS ['BE_USER']->checkLanguageAccess ( 0 )) {
			$return [] = $this->getDefaultLanguage ();
		}
		
		while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {
			if ($GLOBALS ['BE_USER']->checkLanguageAccess ( $row ['uid'] )) {
				$language = t3lib_div::makeInstance ( 'tx_languagevisibility_language' );
				$language->setData ( $row );
				$return [] = $language;
			}
		}
		
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		
		return $return;
	}
	
	/**
	 * Retruns an instance of the language object for the default language.
	 * 
	 * @return tx_languagevisibility_language
	 */
	function getDefaultLanguage() {
		$language = t3lib_div::makeInstance ( 'tx_languagevisibility_language' );
		$row ['uid'] = 0;
		$row ['title'] = 'Default';
		
		$language->setData ( $row );
		return $language;
	}
	
	/**
	 * Returns an instance for a language by the id.
	 * Note: since the language is an value object all languages can be cached in the 
	 * repository propoerty $languageCache.
	 * 
	 * @param $id
	 * @return tx_languagevisibility_language
	 */
	function getLanguageById($id) {
		if(!isset($this->languageCache[$id])){			
			if ($id == 0) {
				$this->languageCache[$id] = $this->getDefaultLanguage ();
			} else {
				$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'sys_language', 'uid=' . intval ( $id ), '', '', '' );
				$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res );
				$language = t3lib_div::makeInstance ( 'tx_languagevisibility_language' );
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				$language->setData ( $row );
				$this->languageCache[$id] = $language;
			}
		}
		return $this->languageCache[$id];
	}
	
	/**
	 * @return returns an instance of the language repository as singleton.
	 * 
	 * @param void
	 * @return tx_languagevisibility_languagerepository
	 */
	public static function makeInstance(){
		if(!self::$instance instanceof tx_languagevisibility_languagerepository) {
			self::$instance	= t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		}
		
		return self::$instance;
	}
}
?>