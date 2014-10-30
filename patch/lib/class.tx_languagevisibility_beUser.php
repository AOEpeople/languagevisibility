<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 AOE GmbH <dev@aoe.com>
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
 * @author	Daniel Pötzinger
 *
 * Class tx_languagevisibility_beUser
 */
class tx_languagevisibility_beUser {

	private $beUser;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->beUser = $GLOBALS['BE_USER'];
	}

	/**
	 * This Method determines if the option allow_movecutdelete_foroverlays has been
	 * set. It
	 *
	 * @return bool
	 */
	public function allowCutCopyMoveDelete() {
		$res = FALSE;
		if (is_array($this->beUser->userGroups)) {
			foreach ( $this->beUser->userGroups as $group ) {
				if ($group['tx_languagevisibility_allow_movecutdelete_foroverlays']) {
					$res = TRUE;
				}
			}
		}
		return $res;
	}

	/**
	 * @return mixed
	 */
	public function isAdmin() {
		return $this->beUser->isAdmin();
	}

	/**
	 * This method returns the userId of the current backend user.
	 *
	 * @return int userId of the backend user
	 */
	public function getUid() {
		return $this->beUser->user['uid'];
	}
}
