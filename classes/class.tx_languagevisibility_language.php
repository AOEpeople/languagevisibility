<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007 AOE media (dev@aoemedia.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 *
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @coauthor Tolleiv Nietsch <nietsch@aoemedia.de>
 * @coauthor Timo Schmidt <schmidt@aoemedia.de>
 */
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath("languagevisibility") . 'classes/dao/class.tx_languagevisibility_daocommon.php');

/**
 * @author timo
 *
 */
class tx_languagevisibility_language {
	private $row;

	protected static $flagCache;

	/**
	 * Holds the exploded fallBackOrderArray
	 *
	 * @var array
	 */
	protected $defaultFallBackOrderArray;

	/**
	 * Holds the exploded elementFallBackOrderArray
	 *
	 * @var array
	 */
	protected $elementFallBackOrderArray;

	/**
	 * Holds the exploded newFallBackOrderArray
	 *
	 * @var array
	 */
	protected $newsFallBackOrderArray;

	/**
	 * @var holds the lg_iso_2 isocode
	 */
	protected $lg_iso_2;

	public function setData($row) {
		$this->row = $row;
	}

	/**
	 * Returns the fallback order of this language as array.
	 *
	 * @param tx_languagevisibility_element $contextElement
	 * @return array
	 */
	public function getFallbackOrder(tx_languagevisibility_element $contextElement) {
		//determine and explode only once
		if (! isset($this->defaultFallBackOrderArray)) {
			//unfortunatly defaultlangauge is 999 instead of 0 (reason in formrendering of typo3):
			$tx_languagevisibility_fallbackorder = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorder']);
			$this->defaultFallBackOrderArray = t3lib_div::trimExplode(',', $tx_languagevisibility_fallbackorder);
		}
		return $this->triggerFallbackHooks('getFallbackOrder', $this->defaultFallBackOrderArray, $contextElement);
	}

	/**
	 * Returns the fallback order for this language for elements
	 *
	 * @param tx_languagevisibility_element $contextElement
	 * @return array
	 */
	public function getFallbackOrderElement(tx_languagevisibility_element $contextElement) {
		//determine and explode only once
		if (! isset($this->elementFallBackOrderArray)) {
			if ($this->usesComplexFallbackSettings()) {
				$tx_languagevisibility_fallbackorderel = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorderel']);
				$this->elementFallBackOrderArray = t3lib_div::trimExplode(',', $tx_languagevisibility_fallbackorderel);
			} else {
				$this->elementFallBackOrderArray = $this->getFallbackOrder($contextElement);
			}
		}

		return $this->triggerFallbackHooks('getFallbackOrderElement', $this->elementFallBackOrderArray, $contextElement);
	}

	/**
	 * Returns the fallback order for news elements as array
	 *
	 * @param tx_languagevisibility_element $contextElement
	 * @return array
	 */
	public function getFallbackOrderTTNewsElement(tx_languagevisibility_element $contextElement) {
		//determine and explode only once
		if (! isset($this->newsFallBackOrderArray)) {
			if ($this->usesComplexFallbackSettings()) {
				$tx_languagevisibility_fallbackorderttnewel = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorderttnewsel']);
				$this->newsFallBackOrderArray = t3lib_div::trimExplode(',', $tx_languagevisibility_fallbackorderttnewel);
			} else {
				$this->newsFallBackOrderArray = $this->getFallbackOrder($contextElement);
			}
		}

		return $this->triggerFallbackHooks('getFallbackOrderTTNewsElement', $this->newsFallBackOrderArray, $contextElement);
	}

	/**
	 *
	 * @param unknown_type $key
	 * @param unknown_type $fallbackorder
	 * @param tx_languagevisibility_element $contextElement
	 * @return array
	 */
	protected function triggerFallbackHooks($key, $fallbackorder, tx_languagevisibility_element $contextElement) {
		$result = array(
			'priority' => 10,
			'fallbackorder' => $fallbackorder,
		);
		$fallback = $result;
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key])) {

			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key] as $classRef) {
				$hookObj = t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, $key)) {
					$result = $hookObj->$key($this, $fallback, $contextElement);
					if ($result['priority'] > $fallback['priority']) {
						$fallback = $result;
					}
				}
			}
		}
		return $fallback['fallbackorder'];
	}


	/**
	 * Method to check if complex fallback settings should be used.
	 *
	 * @return boolean
	 */
	public function usesComplexFallbackSettings() {
		return intval($this->row['tx_languagevisibility_complexfallbacksetting']) > 0;
	}

	/**
	 * Method to read the defaultVisibility setting of pages.
	 *
	 * @return string
	 */
	public function getDefaultVisibilityForPage( tx_languagevisibility_element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForPage', $this->row['tx_languagevisibility_defaultvisibility'], $contextElement);
	}

	/**
	 * Method to read the defaultVisibility for elements
	 *
	 * @return string
	 */
	public function getDefaultVisibilityForElement( tx_languagevisibility_element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForElement', $this->row['tx_languagevisibility_defaultvisibilityel'], $contextElement);
	}

	/**
	 * Method to read the visibility for tt news Elements.
	 *
	 * @return boolean
	 */
	public function getDefaultVisibilityForTTNewsElement( tx_languagevisibility_element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForTTNewsElement', $this->row['tx_languagevisibility_defaultvisibilityttnewsel'], $contextElement);
	}


	protected function triggerDefaultVisibilityHooks($key, $visibilityDefault, tx_languagevisibility_element $contextElement) {
		$result = array(
			'priority' => 10,
			'visibility' => $visibilityDefault,
		);
		$visibility = $result;
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key])) {

			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key] as $classRef) {
				$hookObj = t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, $key)) {
					$result = $hookObj->$key($this, $visibility, $contextElement);
					if ($result['priority'] > $fallback['priority']) {
						$visibility = $result;
					}
				}
			}
		}
		return $visibility['visibility'];
	}

	/**
	 * Method to get the primary key of the language record.
	 *
	 * @return int
	 */
	public function getUid() {
		return $this->row['uid'];
	}

	/**
	 * Method to determine the lg_iso_2 code from the static languages record.
	 *
	 * @return string
	 */
	public function getIsoCode() {
		if (! isset($this->lg_iso_2)) {
			// Finding the ISO code:
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_iso_2', 'static_languages', 'uid=' . intval($this->row['static_lang_isocode']), '', '');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			$this->lg_iso_2 = $row['lg_iso_2'];
		}

		return $this->lg_iso_2;
	}

	/**
	 * Returns the title of the language.
	 *
	 * @param $pidForDefault
	 * @return string
	 */
	public function getTitle($pidForDefault = '') {
		if ($this->getUid() == '0') {
			if ($pidForDefault == '')
				$pidForDefault = $this->_guessCurrentPid();
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig($pidForDefault, 'mod.SHARED');

			return strlen($sharedTSconfig['properties']['defaultLanguageLabel']) ? $sharedTSconfig['properties']['defaultLanguageLabel'] : 'Default';
		} else {
			return $this->row['title'];
		}
	}

	protected function _guessCurrentPid() {
		return t3lib_div::_GP('id');
	}

	/**
	 * @param  Optional the pid of the page. This can be used to get the correct flag for default language (which is set in tsconfig)
	 **/
	public function getFlagImg($pidForDefault = '') {
		global $BACK_PATH;

		$cache_key = 'pid:' . $pidForDefault . 'uid:' . $this->getUid();
		if ( !isset(self::$flagCache[$cache_key]) ) {
			if (version_compare(TYPO3_version,'4.5','<')) {
				$flagPath = $this->getFlagImgPath($pidForDefault, $BACK_PATH);
				if ($flagPath) {
					self::$flagCache[$cache_key] = '<img src="' . $flagPath . '" title="' . $this->getTitle($pidForDefault) . '-' . $this->getIsoCode() . ' [' . $this->getUid() . ']">';
				} else {
					self::$flagCache[$cache_key] = '';
				}
			} else {
				self::$flagCache[$cache_key] = t3lib_iconWorks::getSpriteIcon($this->getFlagName($pidForDefault));
			}
		}

		return self::$flagCache[$cache_key];
	}

	/**
	 * @return string
	 */
	protected function getFlagName($pidForDefault = '') {
		if ($this->getUid() == '0') {
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig($pidForDefault, 'mod.SHARED');
			$flag = $sharedTSconfig['properties']['defaultLanguageFlag'];
		} else {
			$flag = $this->row['flag'];
		}
		return 'flags-' . $flag;
	}

	/**
	 * @param Optional the pid of the page. This can be used to get the correct flagpath for default language (which is set in tsconfig)
	 **/
	public function getFlagImgPath($pidForDefault = '', $BACK_PATH = '') {
		$flagAbsPath = t3lib_div::getFileAbsFileName($GLOBALS['TCA']['sys_language']['columns']['flag']['config']['fileFolder']);

		$flagIconPath = $BACK_PATH . '../' . substr($flagAbsPath, strlen(PATH_site));
		if ($this->getUid() == '0') {
			if ($pidForDefault == '') {
				$pidForDefault = $this->_guessCurrentPid();
			}
			$sharedTSconfig = t3lib_BEfunc::getModTSconfig($pidForDefault, 'mod.SHARED');
			$path = strlen($sharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath . $sharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath . $sharedTSconfig['properties']['defaultLanguageFlag'] : null;
		} else {
			$path = $flagIconPath . $this->row['flag'];
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
	public function isLanguageUidInFallbackOrder($uid, tx_languagevisibility_element $el) {
		$fallbacks = $this->getFallbackOrder($el);
		return in_array($uid, $fallbacks);
	}
}

?>