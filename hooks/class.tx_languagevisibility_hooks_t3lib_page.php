<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Tolleiv
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

interface t3lib_pageSelect_getPageOverlayHook {

	/**
	 *
	 * @param array $pageInput
	 * @param integer $lUid
	 * @param t3lib_pageSelect $parent
	 * @return void
	 */
	public function getPageOverlay_preProcess(&$pageInput, &$lUid, t3lib_pageSelect $parent);
}

/**
 *
 * @author	 Tolleiv
 * @package	 TYPO3
 * @version $Id:$
 */
class tx_languagevisibility_hooks_t3lib_page implements t3lib_pageSelect_getPageOverlayHook {

	/**
	 * This function has various possible results:
	 * 1)	$lUid unchanged -
	 * 			there was nothing to do for langvis and the overlay is requested is fine
	 * 2)	$lUid == null
	 * 			is relevant if we did the overlay ourselfs and the getPageOverlay function is not relevant anymore
	 * 3)	$lUid changed
	 * 			is relevant if we just changed the target-languge but require getPageOverlay to proceed with the overlay-chrunching
	 *
	 * (non-PHPdoc)
	 * @see hooks/t3lib_pageSelect_getPageOverlayHook#getPageOverlay_preProcess($pageInput, $lUid, $parent)
	 */
	public function getPageOverlay_preProcess(&$pageInput, &$lUid,t3lib_pageSelect $parent) {
		if(!is_array($pageInput) || !isset($pageInput['uid'])) {
			return;
		}
		$page_id = $pageInput ['uid'];

			//call service to know if element is visible and which overlay language to use
		$overlayLanguage = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecord ( $page_id, 'pages', $lUid );
		if ($overlayLanguage === false) {
			$overlayLanguageForced = tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecordForced ( $page_id, 'pages', $lUid );
				// avoid recursion if the language wasn't changed
			if($overlayLanguageForced != $lUid) {
				// $pageInput = $parent->getPageOverlay ( &$pageInput, $overlayLanguageForced );
				$pageInput ['_NOTVISIBLE'] = TRUE;
				$lUid = $overlayLanguageForced;
			}
		} elseif($overlayLanguage != $lUid) {	// avoid recursion if the language wasn't changed
			// $pageInput = $parent->getPageOverlay ( &$pageInput, $overlayLanguage );
			$lUid = $overlayLanguage;
		}
	}
}
