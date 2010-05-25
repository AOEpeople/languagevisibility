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

class ux_t3lib_TCEmain extends t3lib_TCEmain {

	/**
	 * Used to evaluate if a page can be deleted
	 *
	 * The function is patched according to #13941
	 *
	 * @param	integer		Page id
	 * @return	mixed		If array: List of page uids to traverse and delete (means OK), if string: error code.
	 */
	function canDeletePage($uid) {
		if ($this->doesRecordExist('pages', $uid, 'delete')) { // If we may at all delete this page
			if ($this->deleteTree) {
				$brExist = $this->doesBranchExist('', $uid, $this->pMap['delete'], 1); // returns the branch
				if ($brExist != - 1) { // Checks if we had permissions
					if ($this->noRecordsFromUnallowedTables($brExist . $uid)) {
						$pagesInBranch = t3lib_div::trimExplode(',', $brExist . $uid, 1);
						foreach ( $pagesInBranch as $pageInBranch ) {
							if (! $this->BE_USER->recordEditAccessInternals('pages', $pageInBranch, FALSE, FALSE, TRUE)) {
								return 'Attempt to delete page which has prohibited localizations.';
							}
						}
						return $pagesInBranch;
					} else
						return 'Attempt to delete records from disallowed tables';
				} else
					return 'Attempt to delete pages in branch without permissions';
			} else {
				$brExist = $this->doesBranchExist('', $uid, $this->pMap['delete'], 1); // returns the branch
				if ($brExist == '') { // Checks if branch exists
					if ($this->noRecordsFromUnallowedTables($uid)) {
						if ($this->BE_USER->recordEditAccessInternals('pages', $uid, FALSE, FALSE, TRUE)) {
							return array($uid );
						} else
							return 'Attempt to delete page which has prohibited localizations.';
					} else
						return 'Attempt to delete records from disallowed tables';
				} else
					return 'Attempt to delete page which has subpages';
			}
		} else
			return 'Attempt to delete page without permissions';
	}

	/**
	 * Checks if user may update a record with uid=$id from $table
	 *
	 * @param	string		Record table
	 * @param	integer		Record UID
	 * @param	array		Record data
	 * @param	array		Hook objects
	 * @return	boolean		Returns true if the user may update the record given by $table and $id
	 */
	function checkRecordUpdateAccess($table, $id, $data = false, &$hookObjectsArr = false) {
		global $TCA;
		/**
		 * These two blocks are splitted because this patch is a copy of what I'm about to ask for #485 (bugs.typo3.org)
		 * But to avoid that this XCLASS covers the process_datamap() aswell this hookObj initialization is inlined in this version
		 */
		if ($hookObjectsArr === false) {
			$hookObjectsArr = array();
			if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'])) {
				foreach ( $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] as $classRef ) {
					$hookObjectsArr[] = t3lib_div::getUserObj($classRef);
				}
			}
		}

		/**
		 * This part comes from #485 (bugs.typo3.org)
		 */
		$res = null;
		if (is_array($hookObjectsArr)) {
			foreach ( $hookObjectsArr as $hookObj ) {
				if (method_exists($hookObj, 'checkRecordUpdateAccess')) {
					$res = $hookObj->checkRecordUpdateAccess($table, $id, $data, $res, $this);
				}
			}
		}
		if ($res === 1 || $res === 0) {
			return $res;
		} else {
			$res = 0;
		}

		if ($TCA[$table] && intval($id) > 0) {
			if (isset($this->recUpdateAccessCache[$table][$id])) { // If information is cached, return it
				return $this->recUpdateAccessCache[$table][$id];
				// Check if record exists and 1) if 'pages' the page may be edited, 2) if page-content the page allows for editing
			} elseif ($this->doesRecordExist($table, $id, 'edit')) {
				$res = 1;
			}
			$this->recUpdateAccessCache[$table][$id] = $res; // Cache the result
		}

		return $res;
	}
}

?>