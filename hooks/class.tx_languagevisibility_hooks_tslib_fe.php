<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 AOE media (dev@aoemedia.de)
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
 * @author	Daniel Poetzinger <poetzinger@aoemedia.de>
 * @author	Tolleiv Nietsch <nietsch@aoemedia.de>
 */
class tx_languagevisibility_hooks_tslib_fe {

	/**
	 *
	 * @param tslib_fe $ref
	 * @return void
	 */
	public function settingLanguage_preProcess($params, &$ref) {

			// Get values from TypoScript:
		$lUid=intval($ref->config['config']['sys_language_uid']);

			//works only with "ignore" setting
			//need to check access for current page and show error:
		if (!tx_languagevisibility_feservices::checkVisiblityForElement($ref->page['uid'],'pages',$lUid)) {
			$GLOBALS['TSFE']->pageNotFoundAndExit('Page is not visible in requested language ['.$lUid.'/'.$ref->page['uid'].']');
		}
	}
}

?>