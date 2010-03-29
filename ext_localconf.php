<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_tcemain.php:tx_languagevisibility_hooks_t3lib_tcemain';

if (version_compare(TYPO3_version,'4.4','>')) {
		// assuming that we get our patch into the TYPO3 core
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_userauthgroup.php:tx_languagevisibility_hooks_t3lib_userauthgroup->checkFullLanguagesAccess';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_tslib_fe.php:tx_languagevisibility_hooks_tslib_fe->settingLanguage_preProcess';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_tcemain.php:tx_languagevisibility_hooks_t3lib_tcemain';

} else if (version_compare(TYPO3_version,'4.3','>')) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_page.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_t3lib_page.php';
	include_once(t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_t3lib_page.php');

		// that's really odd - due to inheritance we've to XCLASS beuserauth to introduce functionality which is meant to exists in t3lib_userauthgroup
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_beuserauth.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_t3lib_beuserauth.php';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_userauthgroup.php:tx_languagevisibility_hooks_t3lib_userauthgroup->checkFullLanguagesAccess';

	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_fe.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_tslib_fe.php';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_tslib_fe.php:tx_languagevisibility_hooks_tslib_fe->settingLanguage_preProcess';

	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_t3lib_tcemain.php';

} else {
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'class.tx_languagevisibility_fieldvisibility.php');
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_page.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_t3lib_page.php';
	include_once(t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_t3lib_page.php');

	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_beuserauth.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_t3lib_beuserauth.php';

	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_fe.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_tslib_fe.php';
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.3/class.ux_t3lib_tcemain.php';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'EXT:languagevisibility/class.tx_languagevisibility_behooks.php:tx_languagevisibility_behooks';
}

	// overriding option because this is done by languagevisibility and will not work if set
$TYPO3_CONF_VARS['FE']['hidePagesIfNotTranslatedByDefault'] = 0;

if ($TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_menu.php']) {
	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.ux_tslib_menu.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_ux_tslib_menu.php';
}
else {
	$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_menu.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_tslib_menu.php';
}


$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/pi1/class.tx_templavoila_pi1.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/tv/class.ux_tx_templavoila_pi1.php';
//$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_api.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/tv/class.ux_tx_templavoila_api.php';

//modify permission check for creating pages
$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['typo3/alt_doc.php']=t3lib_extMgm::extPath($_EXTKEY) . 'patch/core_4.2/class.ux_SCalt_doc.php';


	//adding inheriatance flag to the addRootlineField
$rootlinefields = &$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
$NewRootlinefields = "tx_languagevisibility_inheritanceflag_original, tx_languagevisibility_inheritanceflag_overlayed";
$rootlinefields .= (empty($rootlinefields))? $NewRootlinefields : ','.$NewRootlinefields;

	// adding the inheritance flag to the pageOverlayFields
$pagesOverlayfields = &$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"];
$NewPagesOverlayfields = "tx_languagevisibility_inheritanceflag_overlayed";
$pagesOverlayfields .= (empty($pagesOverlayfields)) ? $NewPagesOverlayfields : ','.$NewPagesOverlayfields;

/**
 * Extension-Hooks
 */

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['pi1']['renderElementClass'][] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_templavoila_pi1.php:tx_languagevisibility_hooks_templavoila_pi1';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['processUrls'][] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_crawler.php:tx_languagevisibility_hooks_crawler->processUrls';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'][] = 'EXT:languagevisibility/ext_tables.sql';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['aoe_wspreview/system/class.tx_aoewspreview_system_workspaceService.php']['createDiff'][] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_aoe_wspreview.php:tx_languagevisibility_hooks_aoe_wspreview->aoewspreview_createDiff';

?>