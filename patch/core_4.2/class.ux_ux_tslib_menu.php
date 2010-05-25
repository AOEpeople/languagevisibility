<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009 AOE media <dev@aoemedia.de>
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
 *
 * @author	Daniel Pötzinger
 * @author	Tolleiv Nietsch
 */

class ux_ux_tslib_tmenu extends ux_tslib_tmenu {

	/**
	 * Checks if a page is OK to include in the final menu item array. Pages can be excluded if the doktype is wrong, if they are hidden in navigation, have a uid in the list of banned uids etc.
	 *
	 * @param	array		Array of menu items
	 * @param	array		Array of page uids which are to be excluded
	 * @param	boolean		If set, then the page is a spacer.
	 * @return	boolean		Returns true if the page can be safely included.
	 */
	function filterMenuPages(&$data, $banUidArray, $spacer) {

		if ($data['_NOTVISIBLE']) {
			return false;
		} else {
			return parent::filterMenuPages($data, $banUidArray, $spacer);
		}
	}

}

class ux_ux_tslib_gmenu extends ux_tslib_gmenu {

	function filterMenuPages(&$data, $banUidArray, $spacer) {

		if ($data['_NOTVISIBLE']) {
			return false;
		} else {
			return parent::filterMenuPages($data, $banUidArray, $spacer);
		}
	}

}

class ux_ux_tslib_imgmenu extends ux_tslib_imgmenu {

	function filterMenuPages(&$data, $banUidArray, $spacer) {

		if ($data['_NOTVISIBLE']) {
			return false;
		} else {
			return parent::filterMenuPages($data, $banUidArray, $spacer);
		}
	}

}
class ux_ux_tslib_jsmenu extends ux_tslib_jsmenu {

	function filterMenuPages(&$data, $banUidArray, $spacer) {

		if ($data['_NOTVISIBLE']) {
			return false;
		} else {
			return parent::filterMenuPages($data, $banUidArray, $spacer);
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['languagevisibility/class.ux_ux_tslib_menu.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['languagevisibility/class.ux_ux_tslib_menu.php']);
}

?>
