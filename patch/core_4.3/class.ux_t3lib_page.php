<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 1999-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
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

interface t3lib_pageSelect_getRecordOverlayHook {

	public function getRecordOverlay_preProcess($table, &$row, &$sys_language_content, $OLmode, t3lib_pageSelect $parent);
	public function getRecordOverlay_postProcess($table, &$row, &$sys_language_content, $OLmode, t3lib_pageSelect $parent);

}


/**
 * Contains a class with "Page functions" mainly for the frontend
 *
 * $Id: class.t3lib_page.php 2470 2007-08-29 15:52:38Z typo3 $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML-trans compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */

class ux_t3lib_pageSelect extends t3lib_pageSelect {

	/**
	 * Returns the relevant page overlay record fields
	 *
	 * @param	mixed		If $pageInput is an integer, it's the pid of the pageOverlay record and thus the page overlay record is returned. If $pageInput is an array, it's a page-record and based on this page record the language record is found and OVERLAYED before the page record is returned.
	 * @param	integer		Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
	 * @return	array		Page row which is overlayed with language_overlay record (or the overlay record alone)
	 */
	function getPageOverlay($pageInput,$lUid=-1)	{

			// Initialize:
		if ($lUid<0)	$lUid = $this->sys_language_uid;
		$row = NULL;

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof t3lib_pageSelect_getPageOverlayHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface t3lib_pageSelect_getPageOverlayHook', 1251476766);
				}

				$hookObject->getPageOverlay_preProcess($pageInput, $lUid, $this);
			}
		}

			// If language UID is different from zero, do overlay:
		if ($lUid)	{
			$fieldArr = explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields']);
			if (is_array($pageInput))	{
				$page_id = $pageInput['uid'];	// Was the whole record
				$fieldArr = array_intersect($fieldArr,array_keys($pageInput));		// Make sure that only fields which exist in the incoming record are overlaid!
			} else {
				$page_id = $pageInput;	// Was the id
			}

			if (count($fieldArr))	{
				/*
					NOTE to enabledFields('pages_language_overlay'):
					Currently the showHiddenRecords of TSFE set will allow pages_language_overlay records to be selected as they are child-records of a page.
					However you may argue that the showHiddenField flag should determine this. But that's not how it's done right now.
				*/

					// Selecting overlay record:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							implode(',',$fieldArr),
							'pages_language_overlay',
							'pid='.intval($page_id).'
								AND sys_language_uid='.intval($lUid).
								$this->enableFields('pages_language_overlay'),
							'',
							'',
							'1'
						);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				$this->versionOL('pages_language_overlay',$row);

				if (is_array($row))	{
					$row['_PAGES_OVERLAY'] = TRUE;

						// Unset vital fields that are NOT allowed to be overlaid:
					unset($row['uid']);
					unset($row['pid']);
				}
			}
		}

			// Create output:
		if (is_array($pageInput))	{
			return is_array($row) ? array_merge($pageInput,$row) : $pageInput;	// If the input was an array, simply overlay the newfound array and return...
		} else {
			return is_array($row) ? $row : array();	// always an array in return
		}
	}


	/**
	 * Creates language-overlay for records in general (where translation is found in records from the same table)
	 *
	 * @param	string		Table name
	 * @param	array		Record to overlay. Must containt uid, pid and $table]['ctrl']['languageField']
	 * @param	integer		Pointer to the sys_language uid for content on the site.
	 * @param	string		Overlay mode. If "hideNonTranslated" then records without translation will not be returned un-translated but unset (and return value is false)
	 * @return	mixed		Returns the input record, possibly overlaid with a translation. But if $OLmode is "hideNonTranslated" then it will return false if no translation is found.
	 */
	function getRecordOverlay($table,$row,$sys_language_content,$OLmode='')	{
		global $TCA;

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof t3lib_pageSelect_getRecordOverlayHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface t3lib_pageSelect_getRecordOverlayHook', 1251476766);
				}
				$hookObject->getRecordOverlay_preProcess($table,$row,$sys_language_content,$OLmode, $this);
			}
		}

		if ($row['uid']>0 && $row['pid']>0)	{
			if ($TCA[$table] && $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'])	{
				if (!$TCA[$table]['ctrl']['transOrigPointerTable'])	{	// Will not be able to work with other tables (Just didn't implement it yet; Requires a scan over all tables [ctrl] part for first FIND the table that carries localization information for this table (which could even be more than a single table) and then use that. Could be implemented, but obviously takes a little more....)

						// Will try to overlay a record only if the sys_language_content value is larger than zero.
					if ($sys_language_content>0)	{

							// Must be default language or [All], otherwise no overlaying:
						if ($row[$TCA[$table]['ctrl']['languageField']]<=0)	{

								// Select overlay record:
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								$table,
								'pid='.intval($row['pid']).
									' AND '.$TCA[$table]['ctrl']['languageField'].'='.intval($sys_language_content).
									' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
									$this->enableFields($table),
								'',
								'',
								'1'
							);
							$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
							$GLOBALS['TYPO3_DB']->sql_free_result($res);
							$this->versionOL($table,$olrow);

								// Merge record content by traversing all fields:
							if (is_array($olrow))	{
								foreach($row as $fN => $fV)	{
									if ($fN!='uid' && $fN!='pid' && isset($olrow[$fN]))	{

										if ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$fN]!='exclude'
												&& ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$fN]!='mergeIfNotBlank' || strcmp(trim($olrow[$fN]),'')))	{
											$row[$fN] = $olrow[$fN];
										}
									} elseif ($fN=='uid')	{
										$row['_LOCALIZED_UID'] = $olrow['uid'];
									}
								}
							} elseif ($OLmode==='hideNonTranslated' && $row[$TCA[$table]['ctrl']['languageField']]==0)	{	// Unset, if non-translated records should be hidden. ONLY done if the source record really is default language and not [All] in which case it is allowed.
								unset($row);
							}

							// Otherwise, check if sys_language_content is different from the value of the record - that means a japanese site might try to display french content.
						} elseif ($sys_language_content!=$row[$TCA[$table]['ctrl']['languageField']])	{
							unset($row);
						}
					} else {
							// When default language is displayed, we never want to return a record carrying another language!
						if ($row[$TCA[$table]['ctrl']['languageField']]>0)	{
							unset($row);
						}
					}
				}
			}
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof t3lib_pageSelect_getRecordOverlayHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface t3lib_pageSelect_getRecordOverlayHook', 1251476766);
				}
				$hookObject->getRecordOverlay_postProcess($table,$row,$sys_language_content,$OLmode, $this);
			}
		}
		return $row;
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['t3lib/class.ux_t3lib_page.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['t3lib/class.ux_t3lib_page.php']);
}
?>