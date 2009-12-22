<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 AOE media (dev@aoemedia.de)
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
 * A visibility object represents the visibility of an element.
 * It contains a visibilityString(-,yes,no,f,t) and a visibility
 * description. The visibility description can be used to indicate,
 * why an element is visible or not.
 *
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 */
class tx_languagevisibility_visibility{

	/**
	 * Holds the visibility string (-,yes,no,f,t).
	 *
	 * @var string
	 */
	protected $visibilityString;

	/**
	 * Holds a description for the visiblitiy.
	 *
	 * @var string
	 */
	protected $visibilityDescription;

	/**
	 * Returns a description why the visibility string is as it is.
	 *
	 * @return string
	 */
	public function getVisibilityDescription() {
		return $this->visibilityDescription;
	}

	/**
	 * Returns the visibility string (-,no,t,f)
	 *
	 * @return string
	 */
	public function getVisibilityString() {
		return $this->visibilityString;
	}

	/**
	 * Method to set the visibility string, chainable because it returns itself
	 *
	 * @param string $visibilityDescription
	 * @return tx_languagevisibility_visibility
	 * 	 */
	public function setVisibilityDescription($visibilityDescription) {
		$this->visibilityDescription = $visibilityDescription;
		return $this;
	}

	/**
	 * Method to set the visibility string, chainable because it returns itself
	 *
	 * @param string $visibilityString
	 * @return tx_languagevisibility_visibility
	 */
	public function setVisibilityString($visibilityString) {
		$this->visibilityString = $visibilityString;
		return $this;
	}
}
?>