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
 * @author	Daniel Pötzinger
 */
class tx_languagevisibility_beUser {
	private $be_user;

	public function __construct() {
		$this->be_user = $GLOBALS['BE_USER'];
	}

	/**
	 * This Method determines if the option allow_movecutdelete_foroverlays has been
	 * set. It
	 *
	 * @return unknown
	 */
	public function allowCutCopyMoveDelete() {
		$res = FALSE;
		if (is_array($this->be_user->userGroups)) {
			foreach ( $this->be_user->userGroups as $group ) {
				if ($group['tx_languagevisibility_allow_movecutdelete_foroverlays']) {
					$res = TRUE;
				}
			}
		}
		return $res;
	}

	public function isAdmin() {
		return $this->be_user->isAdmin();
	}

	/**
	 * This method returns the userId of the current backend user.
	 *
	 * @return int userId of the backend user
	 */
	public function getUid() {
		return $this->be_user->user['uid'];
	}
}
