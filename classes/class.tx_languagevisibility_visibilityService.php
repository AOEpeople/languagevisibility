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
require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_visibility.php';
require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_cacheManager.php';

class tx_languagevisibility_visibilityService {
	/**
	 * @var boolean holds the state if inheritance is enabled or not
	 */
	protected static $useInheritance;

	/**
	 * Constructor of the service, used to initialize the service with the usage
	 * of the inheritance feature.
	 *
	 * @return void
	 */
	public function __construct() {
		if (! isset(self::$useInheritance)) {
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
			if (is_array($confArr) && $confArr['inheritanceEnabled']) {
				self::setUseInheritance();
			}
		}
	}

	/**
	 * This method returns the configuration of the inheritance flag. If an inheritance flag is set
	 * this method can be used to read it.
	 *
	 * @return boolean
	 */
	public static function getUseInheritance() {
		return self::$useInheritance;
	}

	/**
	 * Function to configure the visibilityService to use inherited settings.
	 *
	 * @param boolean $useInheritance
	 */
	public static function setUseInheritance($useInheritance = true) {
		self::$useInheritance = $useInheritance;
	}

	/**
	 * returns relevant languageid for overlay record or false if element is not visible for guven language
	 *
	 * @param tx_languagevisibility_language $language
	 * @param tx_languagevisibility_element $element
	 * @return mixed
	 **/
	function getOverlayLanguageIdForLanguageAndElement(tx_languagevisibility_language $language, tx_languagevisibility_element $element) {
		if ($this->isVisible($language, $element)) {
			return $this->_relevantOverlayLanguageId;
		} else {
			return false;
		}
	}

	/**
	 * currently used to get correct r
	 * page rootline - also if a page in rootline is not vivible
	 *
	 * @todo can this resolved diffrent? the relevantOverlayLanguageId is set in isVisible
	 * @return int
	 */
	function getLastRelevantOverlayLanguageId() {
		return $this->_relevantOverlayLanguageId;
	}

	/**
	 * Returns true or false wether the element is visible in the certain language.
	 * (sets for internal access only $this->_relevantOverlayLanguageId which holds the overlay languageid)
	 *
	 * @param tx_languagevisibility_language $language
	 * @param tx_languagevisibility_element
	 * @param boolean
	 * @return boolean
	 **/
	public function isVisible(tx_languagevisibility_language $language, tx_languagevisibility_element $element, $omitLocal=false) {
		$this->_relevantOverlayLanguageId = $language->getUid();

		$visibility = $this->getVisibilitySetting($language, $element, $omitLocal);

		if ($visibility == 'yes') {
			if (!$element->hasTranslation($language->getUid())) {
				$this->_relevantOverlayLanguageId = 0;
			}
			$result = true;
		} elseif ($visibility == 'no+') {
			$result = false;
		} elseif ($visibility == 'no') {
			$result = false;
		} elseif ($visibility == 't') {
			if ($element->hasTranslation($language->getUid())) {
				$result = true;
			} else {
				$result = false;
			}
		} elseif ($visibility == 'f') {
			if ($element->hasTranslation($language->getUid())) {
				$result = true;
			} else {
				$result = false;

				//there is no direct translation for this element, therefore check languages in fallback
				$fallBackOrder = $element->getFallbackOrder($language);
				if (! is_array($fallBackOrder))
					throw new Exception(print_r($element, true));

				foreach ( $fallBackOrder as $languageid ) {
					if ($element->hasTranslation($languageid)) {
						$this->_relevantOverlayLanguageId = $languageid;
						$result = true;
						break;
					}
				}
			}
		} else {
			//no setting or default:
			if ($language->getUid() == '0') {
				$result = true;
			} else {
				$result = false;
			}
		}

		return $result;
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
	protected function getInheritedVisibility(tx_languagevisibility_language $language, tx_languagevisibility_element $element) {

		$dao = t3lib_div::makeInstance('tx_languagevisibility_daocommon');
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$elementfactoryName = t3lib_div::makeInstanceClassName('tx_languagevisibility_elementFactory');
			$elementfactory = new $elementfactoryName($dao);
		} else {
			$elementfactory = t3lib_div::makeInstance('tx_languagevisibility_elementFactory', $dao);
		}

		$elements = $elementfactory->getParentElementsFromElement($element, $language);

		if (is_array($elements) && count($elements) > 0) {
			foreach ( $elements as $element ) {
				/* @var $element tx_languagevisibility_pageelement */
				$visibility = new tx_languagevisibility_visibility();
				$visibility->setVisibilityString($element->getLocalVisibilitySetting($language->getUid()));
				//is the setting a inheritable setting:
				if ($visibility->getVisibilityString() == 'no+' || $visibility->getVisibilityString() == 'yes+') {
					$visibility->setVisibilityDescription('inherited from uid ' . $element->getUid());
					return $visibility;
				}
			}
		}

		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['getInheritedVisibility'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility']['getInheritedVisibility'] as $classRef) {
				$hookObj = t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, 'getInheritedVisibility')) {
					$visibility = $hookObj->getInheritedVisibility($language, $elements, $element);
					if (substr($visibility->getVisibilityString(),-1)=='+') {
						return $visibility;
					}
				}
			}
		}

		$visibility = new tx_languagevisibility_visibility();
		$visibility->setVisibilityString('-');

		return $visibility;
	}

	/**
	 * return the accumulated visibility setting: reads default for language then reads local for element and merges them.
	 * if local is default, then the global is used or it is forced to be "yes" if the language was set to all.
	 * if the element itself is a translated original record the element is only visible in the specific language
	 * If nothing is set the hardcoded default "t" (translated) is returned
	 *
	 * @param tx_languagevisibility_language $language
	 * @param tx_languagevisibility_element $element
	 * @param boolean
	 * @return string
	 */
	public function getVisibilitySetting(tx_languagevisibility_language $language, tx_languagevisibility_element $element, $omitLocal=false) {
		$cacheManager = tx_languagevisibility_cacheManager::getInstance();
		$cacheData = $cacheManager->get('visibilitySettingCache');
		$isCacheEnabled = $cacheManager->isCacheEnabled();

		$elementTable = $element->getTable();
		$elementUid = $element->getUid();
		$languageUid = $language->getUid();

		$cacheKey = $languageUid . '_' . $elementUid . '_' . $elementTable.'_'.$omitLocal;
		if (! $isCacheEnabled || ! isset($cacheData[$cacheKey])) {
			$cacheData[$cacheKey] = $this->getVisibility($language, $element, $omitLocal)->getVisibilityString();
			$cacheManager->set('visibilitySettingCache', $cacheData);
		}

		return $cacheData[$cacheKey];
	}

	/**
	 * This method can be used to retrieve an informal description for the visibility of an element
	 *
	 * @param tx_languagevisibility_language $language
	 * @param tx_languagevisibility_element $element
	 * @return string
	 */
	public function getVisibilityDescription(tx_languagevisibility_language $language, tx_languagevisibility_element $element) {
		return $this->getVisibility($language, $element)->getVisibilityDescription();
	}

	/**
	 * Create a visiblity object for an element for a given language.

	 * @param tx_languagevisibility_language $language
	 * @param tx_languagevisibility_element $element
	 * @param boolean $omitLocal
	 * @return tx_languagevisibility_visibility
	 */
	protected function getVisibility(tx_languagevisibility_language $language, tx_languagevisibility_element $element, $omitLocal=false) {
		$visibility = new tx_languagevisibility_visibility();
		$local = $element->getLocalVisibilitySetting($language->getUid());

		if (!$omitLocal && ($local != '' && $local != '-')) {
			$visibility->setVisibilityString($local)->setVisibilityDescription('local setting ' . $local);
			return $visibility;
		} else {
			if ($element->isLanguageSetToAll()) {
				$visibility->setVisibilityString('yes')->setVisibilityDescription('language configured to all');
				return $visibility;
			}

			if ($element->isMonolithicTranslated()) {
				if ($element->languageEquals($language)) {
					$visibility->setVisibilityString('yes')->setVisibilityDescription('');
				} else {
					$visibility->setVisibilityString('no')->setVisibilityDescription('');
				}

				return $visibility;
			}

			if ($element->getFieldToUseForDefaultVisibility() == 'page') {
				if ($this->getUseInheritance()) {
					// gibt es in der rootline das visibiklitysetting no+ für die sprache dann return 'no'
					$inheritedVisibility = $this->getInheritedVisibility($language, $element);

					switch ($inheritedVisibility->getVisibilityString()) {
						case 'no+' :
							//if no+ is found it means the current element should be threated as if it has no set
							$visibility->setVisibilityString('no')->setVisibilityDescription('force to no (' . $inheritedVisibility->getVisibilityDescription() . ')');
							break;
						case 'yes+' :
							$visibility->setVisibilityString('yes')->setVisibilityDescription('force to yes (' . $inheritedVisibility->getVisibilityDescription() . ')');
							break;
						default :
							$setting = $language->getDefaultVisibilityForPage($element);
							$visibility->setVisibilityString($setting)->setVisibilityDescription('default visibility  for page (' . $setting . ')');
							break;
					}
				} else {
						//inheritance is disabled
					$setting = $language->getDefaultVisibilityForPage($element);
					$visibility->setVisibilityString($setting)->setVisibilityDescription('default visibility  for page (' . $setting . ')');
				}
			} elseif ($element->getFieldToUseForDefaultVisibility() == 'tt_news') {
					$setting = $language->getDefaultVisibilityForTTNewsElement($element);
					$visibility->setVisibilityString($setting)->setVisibilityDescription('default visibility  for news (' . $setting . ')');
			} else {
				$setting = $language->getDefaultVisibilityForElement($element);
				$visibility->setVisibilityString($setting)->setVisibilityDescription('default visibility  for element (' . $setting . ')');
			}

			if ($visibility->getVisibilityString() == '') {
				$visibility->setVisibilityString('t')->setVisibilityDescription('no visibility configured using default setting "t"');
			}

			return $visibility;
		}
	}
}

?>