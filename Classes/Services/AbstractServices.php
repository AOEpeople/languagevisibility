<?php

namespace AOE\Languagevisibility\Services;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 AOE GmbH <dev@aoe.com>
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

use AOE\Languagevisibility\Services\VisibilityService;

abstract class AbstractServices {

	/**
	 * Method to check if records of a given table support the language-visibility feature.
	 *
	 * @param  string $table
	 * @return boolean
	 */
	public static function isSupportedTable($table) {
		$tableSupportStatus = in_array($table, VisibilityService::getSupportedTables());
		return $tableSupportStatus;
	}
}
