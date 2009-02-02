<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008  <>
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

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_visibilityService.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon.php');
require_once (PATH_t3lib . 'class.t3lib_extobjbase.php');
/**
 * Module extension (addition to function menu) 'Language Visibility Overview' for the 'testtt' extension.
 *
 * @author     <Daniel Pötzinger>
 * @package    TYPO3
 * @subpackage    tx_languagevisibility
 */
class tx_languagevisibility_modfunc1 extends t3lib_extobjbase {
	/**
	 * Returns the menu array
	 *
	 * @return	array
	 */
	function modMenu() {
		global $LANG;
		
		$menuArray = array ('depth' => array (0 => $LANG->getLL ( 'depth_0' ), 1 => $LANG->getLL ( 'depth_1' ), 2 => $LANG->getLL ( 'depth_2' ), 3 => $LANG->getLL ( 'depth_3' ) ) );
		
		// Languages:
		

		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languageList = $languageRep->getLanguages ();
		$menuArray ['lang'] = array (0 => '[All]' );
		foreach ( $languageList as $language ) {
			$menuArray ['lang'] [$language->getUid ()] = $language->getTitle ();
		}
		
		return $menuArray;
	}
	
	/**
	 * MAIN function for page information of localization
	 *
	 * @return	string		Output HTML for the module.
	 */
	function main() {
		global $BACK_PATH, $LANG, $SOBE;
		
		if ($this->pObj->id) {
			$theOutput = '';
			
			// Depth selector:
			$h_func = t3lib_BEfunc::getFuncMenu ( $this->pObj->id, 'SET[depth]', $this->pObj->MOD_SETTINGS ['depth'], $this->pObj->MOD_MENU ['depth'], 'index.php' );
			$h_func .= t3lib_BEfunc::getFuncMenu ( $this->pObj->id, 'SET[lang]', $this->pObj->MOD_SETTINGS ['lang'], $this->pObj->MOD_MENU ['lang'], 'index.php' );
			$theOutput .= $h_func;
			
			// Add CSH:
			$theOutput .= t3lib_BEfunc::cshItem ( '_MOD_web_info', 'lang', $GLOBALS ['BACK_PATH'], '|<br/>' );
			
			// Showing the tree:
			// Initialize starting point of page tree:
			$treeStartingPoint = intval ( $this->pObj->id );
			$treeStartingRecord = t3lib_BEfunc::getRecordWSOL ( 'pages', $treeStartingPoint );
			$depth = $this->pObj->MOD_SETTINGS ['depth'];
			
			// Initialize tree object:
			$tree = t3lib_div::makeInstance ( 't3lib_pageTree' );
			$tree->init ( 'AND ' . $GLOBALS ['BE_USER']->getPagePermsClause ( 1 ) );
			$tree->addField ( 'l18n_cfg' );
			
			// Creating top icon; the current page
			$HTML = t3lib_iconWorks::getIconImage ( 'pages', $treeStartingRecord, $GLOBALS ['BACK_PATH'], 'align="top"' );
			$tree->tree [] = array ('row' => $treeStartingRecord, 'HTML' => $HTML );
			
			// Create the tree from starting point:
			if ($depth)
				$tree->getTree ( $treeStartingPoint, $depth, '' );
				#debug($tree->tree);
			

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
			$this->pObj->content = str_replace ( $marker, $css_content . chr ( 10 ) . $marker, $this->pObj->content );
			
			// Render information table:
			$theOutput .= $this->renderL10nTable ( $tree );
		}
		
		return $theOutput;
	}
	
	/**
	 * Rendering the localization information table.
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the localization information table.
	 */
	function renderL10nTable(&$tree) {
		global $LANG;
		
		// Title length:
		$titleLen = $GLOBALS ['BE_USER']->uc ['titleLen'];
		
		// Put together the TREE:
		$output = '';
		$newOL_js = array ();
		$langRecUids = array ();
		
		//Init DDD
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$languageList = $languageRep->getLanguages ();
		
		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		
		//traverse Tree:
		foreach ( $tree->tree as $data ) {
			$tCells = array ();
			
			$element = $elementfactory->getElementForTable ( 'pages', $data ['row'] ['uid'] );
			
			//first cell (tree):
			// Page icons / titles etc.
			$tCells [] = '<td' . ($data ['row'] ['_CSSCLASS'] ? ' class="' . $data ['row'] ['_CSSCLASS'] . '"' : '') . '>' . $data ['HTML'] . htmlspecialchars ( t3lib_div::fixed_lgd_cs ( $data ['row'] ['title'], $titleLen ) ) . (strcmp ( $data ['row'] ['nav_title'], '' ) ? ' [Nav: <em>' . htmlspecialchars ( t3lib_div::fixed_lgd_cs ( $data ['row'] ['nav_title'], $titleLen ) ) . '</em>]' : '') . '</td>';
			//language cells:
			foreach ( $languageList as $language ) {
				$info = '';
				$editUid = $data ['row'] ['uid'];
				$params = '&edit[pages][' . $editUid . ']=edit';
				$langId = $language->getUid ();
				if ($visibility->isVisible ( $language, $element )) {
					$isVisible = TRUE;
					$statusVis = 'c-visible';
				} else {
					$isVisible = FALSE;
					$statusVis = 'c-notvisible';
				}
				if ($element->hasTranslation ( $langId )) {
					$statusTrans = 'c-translated';
				} else {
					$statusTrans = 'c-nottranslated';
				}
				
				if ($language->getUid () == 0) {
					//Default
					//"View page" link is created:
					$viewPageLink = '<a href="#" onclick="' . htmlspecialchars ( t3lib_BEfunc::viewOnClick ( $data ['row'] ['uid'], $GLOBALS ['BACK_PATH'], '', '', '', '&L=###LANG_UID###' ) ) . '">' . '<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/zoom.gif', 'width="12" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_viewPage', '1' ) . '" border="0" alt="" />' . '</a>';
					$info .= '<a href="#" onclick="' . htmlspecialchars ( t3lib_BEfunc::editOnClick ( $params, $GLOBALS ['BACK_PATH'] ) ) . '">' . '<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_editDefaultLanguagePage', '1' ) . '" border="0" alt="" />' . '</a>';
					$info .= '<a href="#" onclick="' . htmlspecialchars ( 'top.loadEditId(' . intval ( $data ['row'] ['uid'] ) . ',"&SET[language]=0"); return false;' ) . '">' . '<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/edit_page.gif', 'width="12" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_editPage', '1' ) . '" border="0" alt="" />' . '</a>';
					$info .= str_replace ( '###LANG_UID###', '0', $viewPageLink );
					$info .= $data ['row'] ['l18n_cfg'] & 1 ? '<span title="' . $LANG->sL ( 'LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg.I.1', '1' ) . '">D</span>' : '&nbsp;';
					// Put into cell:
					$tCells [] = '<td class="' . $statusTrans . ' c-leftLine">' . $info . '</td>';
					$tCells [] = '<td class="' . $statusTrans . '" title="' . $LANG->getLL ( 'lang_renderl10n_CEcount', '1' ) . '" align="center">' . $this->getContentElementCount ( $data ['row'] ['uid'], 0 ) . '</td>';
				
				} else {
					//Normal Language:
					if ($element->hasTranslation ( $langId )) {
						
						$status = 'c-ok';
						$overLayRow = $element->getOverLayRecordForCertainLanguage ( $langId );
						//add uid of overlay to list of editable records:
						$langRecUids [$langId] [] = $overLayRow ['uid'];
						$icon = t3lib_iconWorks::getIconImage ( 'pages_language_overlay', $overLayRow, $GLOBALS ['BACK_PATH'], 'align="top" class="c-recIcon"' );
						
						$info = $icon . htmlspecialchars ( t3lib_div::fixed_lgd_cs ( $overLayRow ['title'], $titleLen ) ) . (strcmp ( $overLayRow ['nav_title'], '' ) ? ' [Nav: <em>' . htmlspecialchars ( t3lib_div::fixed_lgd_cs ( $overLayRow ['nav_title'], $titleLen ) ) . '</em>]' : '') . ($row ['_COUNT'] > 1 ? '<div>' . $LANG->getLL ( 'lang_renderl10n_badThingThereAre', '1' ) . '</div>' : '');
						$tCells [] = '<td class="' . $statusTrans . ' c-leftLine">' . $info . '</td>';
						
						// Edit whole record:
						$info = '';
						$editUid = $overLayRow ['uid'];
						$params = '&edit[pages_language_overlay][' . $editUid . ']=edit';
						$info .= '<a href="#" onclick="' . htmlspecialchars ( t3lib_BEfunc::editOnClick ( $params, $GLOBALS ['BACK_PATH'] ) ) . '">' . '<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_editLanguageOverlayRecord', '1' ) . '" border="0" alt="" />' . '</a>';
						
						$info .= '<a href="#" onclick="' . htmlspecialchars ( 'top.loadEditId(' . intval ( $data ['row'] ['uid'] ) . ',"&SET[language]=' . $langId . '"); return false;' ) . '">' . '<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/edit_page.gif', 'width="12" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_editPageLang', '1' ) . '" border="0" alt="" />' . '</a>';
						$info .= str_replace ( '###LANG_UID###', $langId, $viewPageLink );
						
						$tCells [] = '<td class="' . $statusTrans . '">' . $info . '</td>';
						$tCells [] = '<td class="' . $statusTrans . '" title="' . $LANG->getLL ( 'lang_renderl10n_CEcount', '1' ) . '" align="center">' . $this->getContentElementCount ( $data ['row'] ['uid'], $langId ) . '</td>';
					} else {
						$status = t3lib_div::hideIfNotTranslated ( $data ['row'] ['l18n_cfg'] ) || $data ['row'] ['l18n_cfg'] & 1 ? 'c-blocked' : 'c-fallback';
						$tCells [] = '<td class="' . $statusTrans . ' c-leftLine">&nbsp;</td>';
						$tCells [] = '<td class="' . $statusTrans . '">&nbsp;</td>';
						//add to JS
						$infoCell = '<input type="checkbox" name="newOL[' . $langId . '][' . $data ['row'] ['uid'] . ']" value="1" />';
						$newOL_js [$langId] .= '
								+(document.webinfoForm[\'newOL[' . $langId . '][' . $data ['row'] ['uid'] . ']\'].checked ? \'&edit[pages_language_overlay][' . $data ['row'] ['uid'] . ']=new\' : \'\')
							';
						$tCells [] = '<td class="' . $statusTrans . '">' . $infoCell . '</td>';
					
					}
				}
				//last cell show status
				$tCells [] = '<td class="' . $statusVis . '">' . $this->_getStatusImage ( $isVisible ) . '</td>';
			}
			$output .= '
			<tr class="bgColor5">
				' . implode ( '
				', $tCells ) . '
			</tr>';
		}
		
		//first ROW:
		//****************
		$firstRowCells [] = '<td>' . $LANG->getLL ( 'lang_renderl10n_page', '1' ) . ':</td>';
		foreach ( $languageList as $language ) {
			$langId = $language->getUid ();
			if ($this->pObj->MOD_SETTINGS ['lang'] == 0 || ( int ) $this->pObj->MOD_SETTINGS ['lang'] === ( int ) $langId) {
				$firstRowCells [] = '<td class="c-leftLine">' . $language->getTitle () . $language->getFlagImg () . '</td>';
				if ($langId == 0) {
					$firstRowCells [] = '<td></td>';
					$firstRowCells [] = '<td></td>';
				} else {
					// Title:
					

					// Edit language overlay records:
					if (is_array ( $langRecUids [$langId] )) {
						$params = '&edit[pages_language_overlay][' . implode ( ',', $langRecUids [$langId] ) . ']=edit&columnsOnly=title,nav_title,hidden';
						$firstRowCells [] = '<td><a href="#" onclick="' . htmlspecialchars ( t3lib_BEfunc::editOnClick ( $params, $GLOBALS ['BACK_PATH'] ) ) . '">
							<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_renderl10n_editLangOverlays', '1' ) . '" border="0" alt="" />
							</a></td>';
					} else {
						$firstRowCells [] = '<td>&nbsp;</td>';
					}
					
					// Create new overlay records:
					$params = "'" . $newOL_js [$langId] . "+'&columnsOnly=title,hidden,sys_language_uid&defVals[pages_language_overlay][sys_language_uid]=" . $langId;
					$firstRowCells [] = '<td><a href="#" onclick="' . htmlspecialchars ( t3lib_BEfunc::editOnClick ( $params, $GLOBALS ['BACK_PATH'] ) ) . '">
						<img' . t3lib_iconWorks::skinImg ( $GLOBALS ['BACK_PATH'], 'gfx/new_el.gif', 'width="11" height="12"' ) . ' title="' . $LANG->getLL ( 'lang_getlangsta_createNewTranslationHeaders', '1' ) . '" border="0" alt="" />
						</a></td>';
					$firstRowCells [] = '<td></td>';
				}
			}
		}
		
		$output = '
			<tr class="bgColor4">
				' . implode ( '
				', $firstRowCells ) . '
			</tr>' . $output;
		
		$output = '

		<table border="0" cellspacing="0" cellpadding="0" id="langTable">' . $output . '
		</table>';
		
		return $output;
	}
	
	function _getStatusImage($stat) {
		global $BACK_PATH;
		if ($stat) {
			return '<img src="' . $BACK_PATH . '../typo3conf/ext/languagevisibility/res/ok.gif">';
		} else {
			return '<img src="' . $BACK_PATH . '../typo3conf/ext/languagevisibility/res/nok.gif">';
		}
	}
	
	/**
	 * Counting content elements for a single language on a page.
	 *
	 * @param	integer		Page id to select for.
	 * @param	integer		Sys language uid
	 * @return	integer		Number of content elements from the PID where the language is set to a certain value.
	 */
	function getContentElementCount($pageId, $sysLang) {
		if ($sysLang == 0) {
			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'count(*)', 'tt_content', 'pid=' . intval ( $pageId ) . ' AND sys_language_uid=' . intval ( $sysLang ) . t3lib_BEfunc::deleteClause ( 'tt_content' ) . t3lib_BEfunc::versioningPlaceholderClause ( 'tt_content' ) );
			list ( $count ) = $GLOBALS ['TYPO3_DB']->sql_fetch_row ( $res );
		}
		
		return $count ? $count : '-';
	}
}

/**
 * Module extension (addition to function menu) 'Language Visibility Overview' for the 'testtt' extension.
 *
 * @author     <Daniel Pötzinger>
 * @package    TYPO3
 * @subpackage    tx_languagevisibility
 */
class sstx_languagevisibility_modfunc1 extends t3lib_extobjbase {
	
	/**
	 * Returns the module menu
	 *
	 * @return    Array with menuitems
	 */
	function modMenu() {
		global $LANG;
		
		return Array ("tx_languagevisibility_modfunc1_check" => "" );
	}
	
	/**
	 * Main method of the module
	 *
	 * @return    HTML
	 */
	function main() {
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE, $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		
		$theOutput .= $this->pObj->doc->spacer ( 5 );
		$theOutput .= $this->pObj->doc->section ( $LANG->getLL ( "title" ), "Dummy content here...", 0, 1 );
		
		$menu = array ();
		$menu [] = t3lib_BEfunc::getFuncCheck ( $this->wizard->pObj->id, "SET[tx_languagevisibility_modfunc1_check]", $this->wizard->pObj->MOD_SETTINGS ["tx_languagevisibility_modfunc1_check"] ) . $LANG->getLL ( "checklabel" );
		$theOutput .= $this->pObj->doc->spacer ( 5 );
		$theOutput .= $this->pObj->doc->section ( "Menu", implode ( " - ", $menu ), 0, 1 );
		
		return $theOutput;
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/languagevisibility/modfunc1/class.tx_languagevisibility_modfunc1.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/languagevisibility/modfunc1/class.tx_languagevisibility_modfunc1.php']);
}

?>