<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media (dev@aoemedia.de)
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 *
 * @author	Daniel Pötzinger <poetzinger@aoemedia.de>
 * @author	Tolleiv Nietsch <nietsch@aoemedia.de>
 */

class tx_languagevisibility_hooks_templavoila_pi1 {


	/**
	 * Modify languagekey to enable proper fallsbacks etc...
	 *
	 * @param array $row
	 * @param string $table
	 * @param string $lKey
	 * @param boolean $langDisable
	 * @param boolean $langChildren
	 * @param object $object
	 * @return string
	 */
	public function renderElement_preProcessLanguageKey($row, $table, $lKey, $langDisabled, $langChildren, &$object) {
		if ($row['_OVERLAYLANGUAGEISOCODE'] && !$langDisabled && !$langChildren) {
			$lKey='l'.$row['_OVERLAYLANGUAGEISOCODE'];
		}
		return $lKey;
	}

	/**
	 * Modify valuekey to enable proper fallback etc...
	 *
	 * @param array $row
	 * @param string $table
	 * @param string $vKey
	 * @param boolean $langDisable
	 * @param boolean $langChildren
	 * @param object $object
	 * @return string
	 */
	public function renderElement_preProcessValueKey($row, $table, $vKey, $langDisabled, $langChildren, &$object) {
		if ($row['_OVERLAYLANGUAGEISOCODE'] && !$langDisabled && $langChildren) {
			$vKey='v'.$row['_OVERLAYLANGUAGEISOCODE'];
		}
		return $vKey;
	}

}

?>
