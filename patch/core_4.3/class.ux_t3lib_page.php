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

	/*******************************************
	 *
	 * Page related: Menu, Domain record, Root line
	 *
	 ******************************************/

	/**
	 * Returns an array with pagerows for subpages with pid=$uid (which is pid here!). This is used for menus.
	 * If there are mount points in overlay mode the _MP_PARAM field is set to the corret MPvar.
	 * If the $uid being input does in itself require MPvars to define a correct rootline these must be handled externally to this function.
	 *
	 * @param	integer		The page id for which to fetch subpages (PID)
	 * @param	string		List of fields to select. Default is "*" = all
	 * @param	string		The field to sort by. Default is "sorting"
	 * @param	string		Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 * @param	boolean		check if shortcuts exist, checks by default
	 * @return	array		Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
	 * @see tslib_fe::getPageShortcut(), tslib_menu::makeMenu(), tx_wizardcrpages_webfunc_2, tx_wizardsortpages_webfunc_2
	 */
	function getMenu($uid, $fields = '*', $sortField = 'sorting', $addWhere = '', $checkShortcuts = 1) {

		$output = Array ();
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $fields, 'pages', 'pid=' . intval ( $uid ) . $this->where_hid_del . $this->where_groupAccess . ' ' . $addWhere, '', $sortField );
		while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {
			$this->versionOL ( 'pages', $row, TRUE );
			if (is_array ( $row )) {
				// Keep mount point:
				$origUid = $row ['uid'];
				$mount_info = $this->getMountPointInfo ( $origUid, $row ); // $row MUST have "uid", "pid", "doktype", "mount_pid", "mount_pid_ol" fields in it
				if (is_array ( $mount_info ) && $mount_info ['overlay']) { // There is a valid mount point.
					$mp_row = $this->getPage ( $mount_info ['mount_pid'] ); // Using "getPage" is OK since we need the check for enableFields AND for type 2 of mount pids we DO require a doktype < 200!
					if (count ( $mp_row )) {
						$row = $mp_row;
						$row ['_MP_PARAM'] = $mount_info ['MPvar'];
					} else
						unset ( $row ); // If the mount point could not be fetched with respect to enableFields, unset the row so it does not become a part of the menu!
				}

				// if shortcut, look up if the target exists and is currently visible
				if ($row ['doktype'] == 4 && ($row ['shortcut'] || $row ['shortcut_mode']) && $checkShortcuts) {
					if ($row ['shortcut_mode'] == 0) {
						$searchField = 'uid';
						$searchUid = intval ( $row ['shortcut'] );
					} else { // check subpages - first subpage or random subpage
						$searchField = 'pid';
						// If a shortcut mode is set and no valid page is given to select subpags from use the actual page.
						$searchUid = intval ( $row ['shortcut'] ) ? intval ( $row ['shortcut'] ) : $row ['uid'];
					}
					$res2 = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'uid', 'pages', $searchField . '=' . $searchUid . $this->where_hid_del . $this->where_groupAccess . ' ' . $addWhere, '', $sortField );
					if (! $GLOBALS ['TYPO3_DB']->sql_num_rows ( $res2 )) {
						unset ( $row );
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ( $res2 );
				} elseif ($row ['doktype'] == 4 && $checkShortcuts) {
					// Neither shortcut target nor mode is set. Remove the page from the menu.
					unset ( $row );
				}

				// Add to output array after overlaying language:
				if (is_array ( $row )) {
					$output [$origUid] = $this->getPageOverlay ( $row );
				}
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $output;
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['t3lib/class.ux_t3lib_page.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['t3lib/class.ux_t3lib_page.php']);
}
?>