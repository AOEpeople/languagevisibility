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
 * Wrapperclass for the command map in tce_main
 *
 * @author	Daniel P�tzinger
 * @author	Tolleiv Nietsch
 */
class tx_languagevisibility_commandMap {
	private $cmd_map;

	public function setMap($map) {
		$this->cmdmap = $map;
	}

	/**
	 * Return an array of elements with the command, uid, and table
	 *
	 * example:
	 * getElementsByCommand('delete');
	 *
	 * returns all elements in the map it the command delete
	 * @param array $commands
	 * @return unknown
	 */
	public function getElementsByCommands($commands) {
		$elements = array();

		foreach ( $this->cmdmap as $table => $entry ) {
			//traverse records in table with commands
			foreach ( $entry as $uid => $cmds ) {
				//traverse command for record in table
				foreach ( $cmds as $cmd => $active ) {
					if (( bool ) $active && in_array($cmd, $commands)) {
						//create element instance and append it to the result array


						$elements[] = array('cmd' => $cmd, 'uid' => $uid, 'table' => $table );
					}
				}
			}
		}

		return $elements;
	}

	/**
	 * removes an element from the command map
	 *
	 * @param unknown_type $element
	 */
	public function removeElement($element) {
		unset($this->cmdmap[$element['table']][$element['uid']][$element['cmd']]);
	}

	public function getMap() {
		return $this->cmdmap;
	}
}
?>