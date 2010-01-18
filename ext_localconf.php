<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'EXT:languagevisibility/class.tx_languagevisibility_behooks.php:tx_languagevisibility_behooks';


require_once(t3lib_extMgm::extPath($_EXTKEY) . 'class.tx_languagevisibility_fieldvisibility.php');


$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_page.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_t3lib_page.php';
include_once(t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_t3lib_page.php');
if ($TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_menu.php']) {
	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.ux_tslib_menu.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_ux_tslib_menu.php';
}
else {
	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_menu.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_tslib_menu.php';
}
if (TYPO3_MODE=='FE') {
	//include_once($TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_menu.php']);
}

$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_fe.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_tslib_fe.php';

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['languagevisibility']);
if ($confArr['applyPatchTV']==1) {
	//$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/index.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/tv/class.ux_tx_templavoila_module1.php';

}
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/pi1/class.tx_templavoila_pi1.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/tv/class.ux_tx_templavoila_pi1.php';
//$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_api.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/tv/class.ux_tx_templavoila_api.php';

//modify permission check for creating pages
$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['typo3/alt_doc.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_SCalt_doc.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_beuserauth.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_t3lib_beuserauth.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/class.ux_t3lib_tcemain.php';

//adding inheriatance flag to the rootline
$rootlinefields = &$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
$NewRootlinefields = "tx_languagevisibility_inheritanceflag_original, tx_languagevisibility_inheritanceflag_overlayed";
$rootlinefields .= (empty($rootlinefields))? $NewRootlinefields : ','.$NewRootlinefields;

$pagesOverlayfields = &$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"];
$NewPagesOverlayfields = "tx_languagevisibility_inheritanceflag_overlayed";
$pagesOverlayfields .= (empty($pagesOverlayfields)) ? $NewPagesOverlayfields : ','.$NewPagesOverlayfields;

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'][] = 'EXT:languagevisibility/ext_tables.sql';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['aoe_wspreview/system/class.tx_aoewspreview_system_workspaceService.php']['createDiff'][] = 'EXT:languagevisibility/class.tx_languagevisibility_behooks.php:tx_languagevisibility_behooks->aoewspreview_createDiff';

/**
 * Hooks
 */

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['pi1']['renderElementClass'][] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_templavoila_pi1.php:tx_languagevisibility_hooks_templavoila_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['processUrls'][] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_crawler.php:tx_languagevisibility_crawler->processUrls';
?>