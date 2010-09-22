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
 * Class/Function which manipulates the item-array for the  listing (see piFlexform).
 *
 * @author	Fabrizio Brance
 * @author	Timo Schmidt
 */

class tx_languagevisibility_hooks_alt_doc {

	public function makeEditForm_accessCheck($params, &$ref) {
		if (! $params['hasAccess'])
			return false;
		$hasAccess = TRUE;
		// if user wants to edit/create page record but has no access to default language!
		if ($params['table'] == 'pages' && ! $GLOBALS['BE_USER']->checkLanguageAccess(0)) {
			$visibilityservice = t3lib_div::makeInstance('tx_languagevisibility_beservices');
			if (! $visibilityservice->hasUserAccessToPageRecord($params['uid'], $params['cmd'])) {
				$hasAccess = FALSE;
			}
		}
		return $hasAccess;
	}
}

?>