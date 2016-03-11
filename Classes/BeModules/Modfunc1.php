<?php

namespace AOE\Languagevisibility\BeModules;
/***************************************************************
 * Copyright notice
 *
 * (c) 2008  <>
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

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * Module extension (addition to function menu) 'Language Visibility Overview' for the 'testtt' extension.
 *
 * @author     <Daniel P�tzinger>
 * @package    TYPO3
 * @subpackage    tx_languagevisibility
 */
class Modfunc1 extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	/**
	 * Returns the menu array
	 *
	 * @return	array
	 */
	public function modMenu() {
		$menuArray = array(
			'depth' => array(
				0 => $GLOBALS['LANG']->getLL('depth_0'),
				1 => $GLOBALS['LANG']->getLL('depth_1'),
				2 => $GLOBALS['LANG']->getLL('depth_2'),
				3 => $GLOBALS['LANG']->getLL('depth_3')
			)
		);

			// Languages:
		$languageRep = GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$languageList = $languageRep->getLanguages();
		$menuArray['lang'] = array(0 => '[All]' );
		foreach ( $languageList as $language ) {
			$menuArray['lang'][$language->getUid()] = $language->getTitle();
		}

		return $menuArray;
	}

	/**
	 * MAIN function for page information of localization
	 *
	 * @return	string		Output HTML for the module.
	 */
	public function main() {
		$theOutput = '';
		if ($this->pObj->id) {

				// Depth selector:
			$h_func = BackendUtility::getFuncMenu($this->pObj->id, 'SET[depth]', $this->pObj->MOD_SETTINGS['depth'], $this->pObj->MOD_MENU['depth'], 'index.php');
			$h_func .= BackendUtility::getFuncMenu($this->pObj->id, 'SET[lang]', $this->pObj->MOD_SETTINGS['lang'], $this->pObj->MOD_MENU['lang'], 'index.php');
			$theOutput .= $h_func;

				// Add CSH:
			$theOutput .= BackendUtility::cshItem('_MOD_web_info', 'lang', $GLOBALS['BACK_PATH'], '|<br/>');

				// Showing the tree:
				// Initialize starting point of page tree:
			$treeStartingPoint = intval($this->pObj->id);
			$treeStartingRecord = BackendUtility::getRecordWSOL('pages', $treeStartingPoint);
			$depth = $this->pObj->MOD_SETTINGS['depth'];

				// Initialize tree object:
			$tree = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\View\\PageTreeView');
			$tree->init('AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1));
			$tree->addField('l18n_cfg');

				// Creating top icon; the current page
			$HTML = IconUtility::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'], 'align="top"');
			$tree->tree[] = array('row' => $treeStartingRecord, 'HTML' => $HTML );

				// Create the tree from starting point:
			if ($depth) {
				$tree->getTree($treeStartingPoint, $depth, '');
			}

				// Add CSS needed:
			$css_content = '
				TABLE#langTable {
					margin-top: 10px;
				}
				TABLE#langTable TR TD {
					padding-left : 2px;
					padding-right : 2px;
					white-space: nowrap;
				}
				TD.c-notvisible { background-color: red; }
				TD.c-visible { background-color: #669966; }
				TD.c-translated { background-color: #A8E95C; }
				TD.c-nottranslated { background-color: #E9CD5C; }
				TD.c-leftLine {border-left: 2px solid black; }
				.bgColor5 { font-weight: bold; }
			';
			$marker = '/*###POSTCSSMARKER###*/';
			$this->pObj->content = str_replace($marker, $css_content . chr(10) . $marker, $this->pObj->content);

				// Render information table:
			$theOutput .= $this->renderL10nTable($tree);
		}

		return $theOutput;
	}

	/**
	 * Rendering the localization information table.
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the localization information table.
	 */
	public function renderL10nTable(&$tree) {
			// Title length:
		$titleLen = $GLOBALS['BE_USER']->uc['titleLen'];

			// Put together the tree:
		$output = '';
		$newOL_js = array();
		$langRecUids = array();

			// Init DAO
		$dao = GeneralUtility::makeInstance('AOE\\Languagevisibility\\Dao\DaoCommon');
		$elementfactory = GeneralUtility::makeInstance('AOE\\Languagevisibility\\ElementFactory', $dao);
		$languageRep = GeneralUtility::makeInstance('AOE\\Languagevisibility\\LanguageRepository');
		$languageList = $languageRep->getLanguages();
		$visibility = GeneralUtility::makeInstance('AOE\\Languagevisibility\\Services\\VisibilityService');

			//traverse tree:
		foreach ( $tree->tree as $data ) {
			$tCells = array();

			$element = $elementfactory->getElementForTable('pages', $data['row']['uid']);

				// first cell (tree):
				// Page icons / titles etc.
			$tCells[] = '<td' . ($data['row']['_CSSCLASS'] ? ' class="' . $data['row']['_CSSCLASS'] . '"' : '') . '>' . $data['HTML'] . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($data['row']['title'], $titleLen)) . (strcmp($data['row']['nav_title'], '') ? ' [Nav: <em>' . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($data['row']['nav_title'], $titleLen)) . '</em>]' : '') . '</td>';
				// language cells:
			foreach ( $languageList as $language ) {
				$info = '';
				$editUid = $data['row']['uid'];
				$params = '&edit[pages][' . $editUid . ']=edit';
				$langId = $language->getUid();
				if ($visibility->isVisible($language, $element)) {
					$isVisible = TRUE;
					$statusVis = 'c-visible';
				} else {
					$isVisible = FALSE;
					$statusVis = 'c-notvisible';
				}
				if ($element->hasTranslation($langId)) {
					$statusTrans = 'c-translated';
				} else {
					$statusTrans = 'c-nottranslated';
				}

				if ($language->getUid() == 0) {
						// Default
						// "View page" link is created:
					$viewPageLink = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::viewOnClick($data['row']['uid'], $GLOBALS['BACK_PATH'], '', '', '', '&L=###LANG_UID###')) . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/zoom.gif', 'width="12" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_viewPage', '1') . '" border="0" alt="" />' . '</a>';
					$info .= '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH'])) . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_editDefaultLanguagePage', '1') . '" border="0" alt="" />' . '</a>';
					$info .= '<a href="#" onclick="' . htmlspecialchars('top.loadEditId(' . intval($data['row']['uid']) . ',"&SET[language]=0"); return FALSE;') . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit_page.gif', 'width="12" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_editPage', '1') . '" border="0" alt="" />' . '</a>';
					$info .= str_replace('###LANG_UID###', '0', $viewPageLink);
					$info .= $data['row']['l18n_cfg'] & 1 ? '<span title="' . $GLOBALS['LANG']->sL('LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg.I.1', '1') . '">D</span>' : '&nbsp;';
						// Put into cell:
					$tCells[] = '<td class="' . $statusTrans . ' c-leftLine">' . $info . '</td>';
					$tCells[] = '<td class="' . $statusTrans . '" title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_CEcount', '1') . '" align="center">' . $this->getContentElementCount($data['row']['uid'], 0) . '</td>';

				} else {
						// Normal Language:
					if ($element->hasTranslation($langId)) {
						$viewPageLink = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::viewOnClick($data['row']['uid'], $GLOBALS['BACK_PATH'], '', '', '', '&L=###LANG_UID###')) . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/zoom.gif', 'width="12" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_viewPage', '1') . '" border="0" alt="" />' . '</a>';

						$overLayRow = $element->getOverLayRecordForCertainLanguage($langId);
							// add uid of overlay to list of editable records:
						$langRecUids[$langId][] = $overLayRow['uid'];
						$icon = IconUtility::getIconImage('pages_language_overlay', $overLayRow, $GLOBALS['BACK_PATH'], 'align="top" class="c-recIcon"');

						$info = $icon . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($overLayRow['title'], $titleLen)) . (strcmp($overLayRow['nav_title'], '') ? ' [Nav: <em>' . htmlspecialchars(\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($overLayRow['nav_title'], $titleLen)) . '</em>]' : '') . ($overLayRow['_COUNT'] > 1 ? '<div>' . $GLOBALS['LANG']->getLL('lang_renderl10n_badThingThereAre', '1') . '</div>' : '');
						$tCells[] = '<td class="' . $statusTrans . ' c-leftLine">' . $info . '</td>';

							// Edit whole record:
						$info = '';
						$editUid = $overLayRow['uid'];
						$params = '&edit[pages_language_overlay][' . $editUid . ']=edit';
						$info .= '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH'])) . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_editLanguageOverlayRecord', '1') . '" border="0" alt="" />' . '</a>';

						$info .= '<a href="#" onclick="' . htmlspecialchars('top.loadEditId(' . intval($data['row']['uid']) . ',"&SET[language]=' . $langId . '"); return FALSE;') . '">' . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit_page.gif', 'width="12" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_editPageLang', '1') . '" border="0" alt="" />' . '</a>';
						$info .= str_replace('###LANG_UID###', $langId, $viewPageLink);

						$tCells[] = '<td class="' . $statusTrans . '">' . $info . '</td>';
						$tCells[] = '<td class="' . $statusTrans . '" title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_CEcount', '1') . '" align="center">' . $this->getContentElementCount($data['row']['uid'], $langId) . '</td>';
					} else {
						$tCells[] = '<td class="' . $statusTrans . ' c-leftLine">&nbsp;</td>';
						$tCells[] = '<td class="' . $statusTrans . '">&nbsp;</td>';
						//add to JS
						$infoCell = '<input type="checkbox" name="newOL[' . $langId . '][' . $data['row']['uid'] . ']" value="1" />';
						$newOL_js[$langId] .= '
								+(document.webinfoForm[\'newOL[' . $langId . '][' . $data['row']['uid'] . ']\'].checked ? \'&edit[pages_language_overlay][' . $data['row']['uid'] . ']=new\' : \'\')
							';
						$tCells[] = '<td class="' . $statusTrans . '">' . $infoCell . '</td>';

					}
				}
				//last cell show status
				$tCells[] = '<td class="' . $statusVis . '">' . $this->_getStatusImage($isVisible) . '</td>';
			}
			$output .= '
			<tr class="bgColor5">
				' . implode('
				', $tCells) . '
			</tr>';
		}

			// first ROW:
		$firstRowCells = array();
		$firstRowCells[] = '<td>' . $GLOBALS['LANG']->getLL('lang_renderl10n_page', '1') . ':</td>';
		foreach ( $languageList as $language ) {
			$langId = $language->getUid();
			if ($this->pObj->MOD_SETTINGS['lang'] == 0 || ( int ) $this->pObj->MOD_SETTINGS['lang'] === ( int ) $langId) {
				$firstRowCells[] = '<td class="c-leftLine">' . $language->getTitle() . $language->getFlagImg() . '</td>';
				if ($langId == 0) {
					$firstRowCells[] = '<td></td>';
					$firstRowCells[] = '<td></td>';
				} else {
					// Title:


					// Edit language overlay records:
					if (is_array($langRecUids[$langId])) {
						$params = '&edit[pages_language_overlay][' . implode(',', $langRecUids[$langId]) . ']=edit&columnsOnly=title,nav_title,hidden';
						$firstRowCells[] = '<td><a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH'])) . '">
							<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_renderl10n_editLangOverlays', '1') . '" border="0" alt="" />
							</a></td>';
					} else {
						$firstRowCells[] = '<td>&nbsp;</td>';
					}

					// Create new overlay records:
					$params = "'" . $newOL_js[$langId] . "+'&columnsOnly=title,hidden,sys_language_uid&defVals[pages_language_overlay][sys_language_uid]=" . $langId;
					$firstRowCells[] = '<td><a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH'])) . '">
						<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/new_el.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->getLL('lang_getlangsta_createNewTranslationHeaders', '1') . '" border="0" alt="" />
						</a></td>';
					$firstRowCells[] = '<td></td>';
				}
			}
		}

		$output = '
			<tr class="bgColor4">
				' . implode('
				', $firstRowCells) . '
			</tr>' . $output;

		$output = '

		<table border="0" cellspacing="0" cellpadding="0" id="langTable">' . $output . '
		</table>';

		return $output;
	}

	protected function _getStatusImage($stat) {
		if ($stat) {
			return '<img src="' . $GLOBALS['BACK_PATH'] . '../typo3conf/ext/languagevisibility/Resources/Public/Icons/ok.gif">';
		} else {
			return '<img src="' . $GLOBALS['BACK_PATH'] . '../typo3conf/ext/languagevisibility/Resources/Public/Icons/nok.gif">';
		}
	}

	/**
	 * Counting content elements for a single language on a page.
	 *
	 * @param	integer		Page id to select for.
	 * @param	integer		Sys language uid
	 * @return	integer		Number of content elements from the PID where the language is set to a certain value.
	 */
	public function getContentElementCount($pageId, $sysLang) {
		if ($sysLang == 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', 'tt_content', 'pid=' . intval($pageId) . ' AND sys_language_uid=' . intval($sysLang) . BackendUtility::deleteClause('tt_content') . \TYPO3\CMS\Backend\Utility\BackendUtility::versioningPlaceholderClause('tt_content'));
			list ( $count ) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		}

		return $count ? $count : '-';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/languagevisibility/modfunc1/class.tx_languagevisibility_modfunc1.php']) {
	include_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/languagevisibility/modfunc1/class.tx_languagevisibility_modfunc1.php']);
}