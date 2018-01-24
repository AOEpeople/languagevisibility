<?php

namespace AOE\Languagevisibility;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 AOE GmbH <dev@aoe.com>
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
class Language {

	/**
	 * @var array
	 */
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

	/**
	 * @param  array $row
	 */
	public function setData($row) {
		$this->row = $row;
	}

	/**
	 * Returns the fallback order of this language as array.
	 *
	 * @param Element $contextElement
	 * @return array
	 */
	public function getFallbackOrder(Element $contextElement) {
			// determine and explode only once
		if (! isset($this->defaultFallBackOrderArray)) {
				// unfortunatly defaultlangauge is 999 instead of 0 (reason in formrendering of typo3):
			$tx_languagevisibility_fallbackorder = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorder']);
			$this->defaultFallBackOrderArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $tx_languagevisibility_fallbackorder);
		}
		return $this->triggerFallbackHooks('getFallbackOrder', $this->defaultFallBackOrderArray, $contextElement);
	}

	/**
	 * Returns the fallback order for this language for elements
	 *
	 * @param Element $contextElement
	 * @return array
	 */
	public function getFallbackOrderElement(Element $contextElement) {
			// determine and explode only once
		if (! isset($this->elementFallBackOrderArray)) {
			if ($this->usesComplexFallbackSettings()) {
				$tx_languagevisibility_fallbackorderel = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorderel']);
				$this->elementFallBackOrderArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $tx_languagevisibility_fallbackorderel);
			} else {
				$this->elementFallBackOrderArray = $this->getFallbackOrder($contextElement);
			}
		}

		return $this->triggerFallbackHooks('getFallbackOrderElement', $this->elementFallBackOrderArray, $contextElement);
	}

	/**
	 * Returns the fallback order for news elements as array
	 *
	 * @param Element $contextElement
	 * @return array
	 */
	public function getFallbackOrderTTNewsElement(Element $contextElement) {
			// determine and explode only once
		if (! isset($this->newsFallBackOrderArray)) {
			if ($this->usesComplexFallbackSettings()) {
				$tx_languagevisibility_fallbackorderttnewel = str_replace('999', '0', $this->row['tx_languagevisibility_fallbackorderttnewsel']);
				$this->newsFallBackOrderArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $tx_languagevisibility_fallbackorderttnewel);
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
	 * @param Element $contextElement
	 * @return array
	 */
	protected function triggerFallbackHooks($key, $fallbackorder, Element $contextElement) {
		$result = array(
			'priority' => 10,
			'fallbackorder' => $fallbackorder,
		);
		$fallback = $result;
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key])) {

			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key] as $classRef) {
				$hookObj = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);
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
	 * @param Element $contextElement
	 * @return string
	 */
	public function getDefaultVisibilityForPage(Element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForPage', $this->row['tx_languagevisibility_defaultvisibility'], $contextElement);
	}

	/**
	 * Method to read the defaultVisibility for elements
	 *
	 * @param Element $contextElement
	 * @return string
	 */
	public function getDefaultVisibilityForElement(Element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForElement', $this->row['tx_languagevisibility_defaultvisibilityel'], $contextElement);
	}

	/**
	 * Method to read the visibility for tt news Elements.
	 *
	 * @param Element $contextElement
	 * @return boolean
	 */
	public function getDefaultVisibilityForTTNewsElement(Element $contextElement) {
		return $this->triggerDefaultVisibilityHooks('getDefaultVisibilityForTTNewsElement', $this->row['tx_languagevisibility_defaultvisibilityttnewsel'], $contextElement);
	}

	/**
	 * @param  string $key
	 * @param  $visibilityDefault
	 * @param  Element $contextElement
	 * @return mixed
	 */
	protected function triggerDefaultVisibilityHooks($key, $visibilityDefault, Element $contextElement) {
		$result = array(
			'priority' => 10,
			'visibility' => $visibilityDefault,
		);
		$visibility = $result;
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key])) {

			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'][$key] as $classRef) {
				$hookObj = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);
				if (method_exists($hookObj, $key)) {
					$result = $hookObj->$key($this, $visibility, $contextElement);
					if ($result['priority'] > $visibility['priority']) {
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
			if ($pidForDefault == '') {
				$pidForDefault = $this->_guessCurrentPid();
			}
			$sharedTSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($pidForDefault, 'mod.SHARED');

			return strlen($sharedTSconfig['properties']['defaultLanguageLabel']) ? $sharedTSconfig['properties']['defaultLanguageLabel'] : 'Default';
		} else {
			return $this->row['title'];
		}
	}

	protected function _guessCurrentPid() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
	}

	/**
	 * @param  Optional the pid of the page. This can be used to get the correct flag for default language (which is set in tsconfig)
	 **/
	public function getFlagImg($pidForDefault = '') {
		$cache_key = 'pid:' . $pidForDefault . 'uid:' . $this->getUid();
		if ( !isset(self::$flagCache[$cache_key]) ) {
			/** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
			$iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
			self::$flagCache[$cache_key] = $iconFactory->getIcon($this->getFlagName($pidForDefault));
		}

		return self::$flagCache[$cache_key];
	}

	/**
	 * @param string $pidForDefault
	 * @return string
	 */
	protected function getFlagName($pidForDefault = '') {
		if ($this->getUid() == '0') {
			$sharedTSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($pidForDefault, 'mod.SHARED');
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
		$flagAbsPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($GLOBALS['TCA']['sys_language']['columns']['flag']['config']['fileFolder']);

		$flagIconPath = $BACK_PATH . '../' . substr($flagAbsPath, strlen(PATH_site));
		if ($this->getUid() == '0') {
			if ($pidForDefault == '') {
				$pidForDefault = $this->_guessCurrentPid();
			}
			$sharedTSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($pidForDefault, 'mod.SHARED');
			$path = strlen($sharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath . $sharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath . $sharedTSconfig['properties']['defaultLanguageFlag'] : NULL;
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
	 * @param Element $el
	 * @return boolean
	 */
	public function isLanguageUidInFallbackOrder($uid, Element $el) {
		$fallbacks = $this->getFallbackOrder($el);
		return in_array($uid, $fallbacks);
	}
}
