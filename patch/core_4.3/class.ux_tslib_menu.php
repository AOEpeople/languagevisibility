<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

interface tslib_menu_filterMenuPagesHook {
	public function tslib_menu_filterMenuPagesHook (array &$data, array $banUidArray, $spacer, tslib_tmenu $obj);
}


class ux_tslib_tmenu extends tslib_tmenu {

	/**
	 * Checks if a page is OK to include in the final menu item array. Pages can be excluded if the doktype is wrong, if they are hidden in navigation, have a uid in the list of banned uids etc.
	 *
	 * @param	array		Array of menu items
	 * @param	array		Array of page uids which are to be excluded
	 * @param	boolean		If set, then the page is a spacer.
	 * @return	boolean		Returns true if the page can be safely included.
	 */
	function filterMenuPages(&$data,$banUidArray,$spacer)	{
		$includePage = TRUE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof tslib_menu_filterMenuPagesHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface tslib_menu_filterMenuPagesHook', 1251476766);
				}

				$includePage = $includePage && $hookObject->tslib_menu_filterMenuPagesHook($data, $banUidArray, $spacer, $this);
			}
		}
		if (!$includePage) {
			return false;
		} else {
			return parent::filterMenuPages($data,$banUidArray,$spacer);
		}
	}

}

class ux_tslib_gmenu extends tslib_gmenu {

	function filterMenuPages(&$data,$banUidArray,$spacer)	{
		$includePage = TRUE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof tslib_menu_filterMenuPagesHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface tslib_menu_filterMenuPagesHook', 1251476766);
				}

				$includePage = $includePage && $hookObject->tslib_menu_filterMenuPagesHook($data, $banUidArray, $spacer, $this);
			}
		}
		if (!$includePage) {
			return false;
		} else {
			return parent::filterMenuPages($data,$banUidArray,$spacer);
		}
	}

}

class ux_tslib_imgmenu extends tslib_imgmenu {

	function filterMenuPages(&$data,$banUidArray,$spacer)	{
		$includePage = TRUE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof tslib_menu_filterMenuPagesHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface tslib_menu_filterMenuPagesHook', 1251476766);
				}

				$includePage = $includePage && $hookObject->tslib_menu_filterMenuPagesHook($data, $banUidArray, $spacer, $this);
			}
		}
		if (!$includePage) {
			return false;
		} else {
			return parent::filterMenuPages($data,$banUidArray,$spacer);
		}
	}

}
class ux_tslib_jsmenu extends tslib_jsmenu {

	function filterMenuPages(&$data,$banUidArray,$spacer)	{
		$includePage = TRUE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof tslib_menu_filterMenuPagesHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface tslib_menu_filterMenuPagesHook', 1251476766);
				}

				$includePage = $includePage && $hookObject->tslib_menu_filterMenuPagesHook($data, $banUidArray, $spacer, $this);
			}
		}
		if (!$includePage) {
			return false;
		} else {
			return parent::filterMenuPages($data,$banUidArray,$spacer);
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['languagevisibility/class.ux_tslib_menu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['languagevisibility/class.ux_tslib_menu.php']);
}

?>
