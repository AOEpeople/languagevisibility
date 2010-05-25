<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2010 AOE media <dev@aoemedia.de>
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
 * @author	Tolleiv Nietsch
 */

require_once (t3lib_extMgm::extPath("languagevisibility") . 'class.tx_languagevisibility_feservices.php');

class ux_tslib_fe extends tslib_fe {

	/**
	 * Setting the language key that'll be used by the current page.
	 * In this function it should be checked, 1) that this language exists, 2) that a page_overlay_record exists, .. and if not the default language, 0 (zero), should be set.
	 *
	 * @return	void
	 * @access private
	 */
	function settingLanguage() {

		if (is_array($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess'])) {
			$_params = array();
			foreach ( $this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess'] as $_funcRef ) {
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}

		parent::settingLanguage();

		if (is_array($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'])) {
			$_params = array();
			foreach ( $this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'] as $_funcRef ) {
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php']);
}
?>