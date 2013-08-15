<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2010 Tolleiv
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
 * @author	 Tolleiv
 * @package	 TYPO3
 * @version $Id:$
 */
class tx_languagevisibility_hooks_tslib_menu implements tslib_menu_filterMenuPagesHook {

	/**
	 * Checks if a page is OK to include in the final menu item array.
	 *
	 * @param	array		Array of menu items
	 * @param	array		Array of page uids which are to be excluded
	 * @param	boolean		If set, then the page is a spacer.
	 * @param	tslib_menu	The menu object
	 * @return	boolean		Returns TRUE if the page can be safely included.
	 */
	public function tslib_menu_filterMenuPagesHook(array &$data, array $banUidArray, $spacer, tslib_menu $obj) {
		if ($data['_NOTVISIBLE']) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Checks if a page is OK to include in the final menu item array.
	 *
	 * @param	array		Array of menu items
	 * @param	array		Array of page uids which are to be excluded
	 * @param	boolean		If set, then the page is a spacer.
	 * @param	tslib_menu	The menu object
	 * @return	boolean		Returns TRUE if the page can be safely included.
	 */
	public function processFilter(array &$data, array $banUidArray, $spacer, tslib_menu $obj) {
		return $this->tslib_menu_filterMenuPagesHook($data, $banUidArray, $spacer, $obj);
	}
}