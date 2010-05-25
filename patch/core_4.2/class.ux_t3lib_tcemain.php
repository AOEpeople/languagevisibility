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

class ux_t3lib_TCEmain extends t3lib_TCEmain {

	/**
	 * Used to evaluate if a page can be deleted
	 *
	 * @param	integer		Page id
	 * @return	mixed		If array: List of page uids to traverse and delete (means OK), if string: error code.
	 */
	function canDeletePage($uid) {
		$return = parent::canDeletePage($uid);
		if (is_array($return)) {
			if (t3lib_extMgm::isLoaded('languagevisibility')) {
				require_once (t3lib_extMgm::extPath("languagevisibility") . 'class.tx_languagevisibility_beservices.php');
				$visibilityservice = t3lib_div::makeInstance('tx_languagevisibility_beservices');
				if (! $visibilityservice->hasUserAccessToPageRecord($uid, 'delete')) {
					return 'Attempt to delete records without access to the visible languages';
				}
			}
		}
		return $return;
	}

	/**
	 * Checks if user may update a record with uid=$id from $table
	 *
	 * @param	string		Record table
	 * @param	integer		Record UID
	 * @return	boolean		Returns true if the user may update the record given by $table and $id
	 */
	function checkRecordUpdateAccess($table, $id) {

		global $TCA;
		$res = 0;
		if ($TCA[$table] && intval($id) > 0) {
			if (isset($this->recUpdateAccessCache[$table][$id])) { // If information is cached, return it


				return $this->recUpdateAccessCache[$table][$id];
				// Check if record exists and 1) if 'pages' the page may be edited, 2) if page-content the page allows for editing
			} elseif ($this->doesRecordExist($table, $id, 'edit')) {
				$res = 1;
				if (t3lib_extMgm::isLoaded('languagevisibility')) {
					require_once (t3lib_extMgm::extPath("languagevisibility") . 'class.tx_languagevisibility_beservices.php');
					$visibilityservice = t3lib_div::makeInstance('tx_languagevisibility_beservices');
					if ($table == 'pages') {
						if (! $visibilityservice->hasUserAccessToPageRecord($id, 'edit')) {
							$res = 0;
						}
					} else {
						if (! $visibilityservice->hasUserAccessToEditRecord($table, $id)) {
							$res = 0;
						}
					}
				}
			}

			$this->recUpdateAccessCache[$table][$id] = $res; // Cache the result
		}

		return $res;
	}

	/**
	 * Method to check if cut copy or move is restricted for overlays
	 *
	 * @return boolean
	 */
	protected function isCutCopyAndMoveRestrictedForOverlays() {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
		if (is_array($confArr)) {
			return ($confArr['restrictCutCopyMoveForOverlays'] == 1);
		} else {
			return false;
		}
	}

	/**
	 * This method is used to extend the tce_main process_cmdmap function. It provides the functionallity to
	 * disallow users to move, cut or copy any element which has an overlay. Moving and Cutting of elements
	 * with overlays is dangerous because the extisting overlays may also need to be moved.
	 *
	 *
	 */
	function process_cmdmap() {
		if (t3lib_extMgm::isLoaded('languagevisibility')) {
			require_once (t3lib_extMgm::extPath("languagevisibility") . 'patch/lib/class.tx_languagevisibility_commandMap.php');

			//user has no rights to cut move copy or delete, therefore the commands need to be filtered
			$command_map = t3lib_div::makeInstance('tx_languagevisibility_commandMap');
			$command_map->setMap($this->cmdmap);

			$command_elements = $command_map->getElementsByCommands(array('cut', 'move', 'copy', 'delete' ));
			if (is_array($command_elements)) {
				foreach ( $command_elements as $command_element ) {
					try {
						//get row
						$table = $command_element['table'];
						$uid = $command_element['uid'];
						$row = tx_languagevisibility_daocommon::getRecord($uid, $table);
						$command = $command_element['cmd'];

						if (tx_languagevisibility_beservices::isOverlayRecord($row, $table)) {
							//current element is an overlay -> restrict cut copy and move in general -> filter the command map
							if (($command == 'move' || $command == 'cut' || $command == 'copy') && $this->isCutCopyAndMoveRestrictedForOverlays()) {

								$this->newlog('The command ' . $command . ' can not be applied on overlays', 1);
								//overlay records should no be move,copy or cutable but it should be possible to delete them
								//therefore we remove all elements which have the comment cut, copy or move
								$command_map->removeElement($command_element);
							}
						} else {
							//current element is no overlay
							if (! tx_languagevisibility_beservices::canCurrrentUserCutCopyMoveDelete()) {
								//if the record has any translation disallow move, cut, copy and delete
								$elementObj = tx_languagevisibility_beservices::getElement($uid, $table);

								if ($elementObj->hasAnyTranslationInAnyWorkspace()) {
									$command_map->removeElement($command_element);
									$this->newlog('You have no rights to apply the command ' . $command . ' on elements with overlays', 1);
								}
							}
						}
					} catch ( Exception $e ) {
						//element not supported by language visibility
					}
				}
			}

			//overwrite the internal map an process the base tce_main method
			$this->cmdmap = $command_map->getMap();
		}

		//process parent method to use basic functionallity
		parent::process_cmdmap();
	}
}

?>