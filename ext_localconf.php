<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_tcemain.php:tx_languagevisibility_hooks_t3lib_tcemain';


	// assuming that we get our patch into the TYPO3 core
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_page.php:tx_languagevisibility_hooks_t3lib_page';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_t3lib_userauthgroup.php:tx_languagevisibility_hooks_t3lib_userauthgroup->checkFullLanguagesAccess';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_tslib_fe.php:tx_languagevisibility_hooks_tslib_fe->settingLanguage_preProcess';

$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_alt_doc.php:tx_languagevisibility_hooks_alt_doc->makeEditForm_accessCheck';
$TYPO3_CONF_VARS['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages']['languagevisility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_tslib_menu.php:tx_languagevisibility_hooks_tslib_menu';


	// overriding option because this is done by languagevisibility and will not work if set
$TYPO3_CONF_VARS['FE']['hidePagesIfNotTranslatedByDefault'] = 0;

	//adding inheriatance flag to the addRootlineField
$rootlinefields = &$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'];
$newRootlinefields = 'tx_languagevisibility_inheritanceflag_original, tx_languagevisibility_inheritanceflag_overlayed';
$rootlinefields .= (empty($rootlinefields))? $newRootlinefields : ',' . $newRootlinefields;

	// adding the inheritance flag to the pageOverlayFields
$pagesOverlayfields = &$GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'];
$newPagesOverlayfields = 'tx_languagevisibility_inheritanceflag_overlayed';
$pagesOverlayfields .= (empty($pagesOverlayfields)) ? $newPagesOverlayfields : ',' . $newPagesOverlayfields;

/**
 * Extension-Hooks
 */
if (t3lib_extMgm::isLoaded('templavoila')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['pi1']['renderElementClass']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_templavoila_pi1.php:tx_languagevisibility_hooks_templavoila_pi1';
}
if (t3lib_extMgm::isLoaded('crawler')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['processUrls']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_crawler.php:tx_languagevisibility_hooks_crawler->processUrls';
}
if (t3lib_extMgm::isLoaded('phpunit')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles']['languagevisibility'] = 'EXT:languagevisibility/ext_tables.sql';
}
if (t3lib_extMgm::isLoaded('aoe_wspreview')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['aoe_wspreview/system/class.tx_aoewspreview_system_workspaceService.php']['createDiff']['languagevisibility'] = 'EXT:languagevisibility/hooks/class.tx_languagevisibility_hooks_aoe_wspreview.php:tx_languagevisibility_hooks_aoe_wspreview->aoewspreview_createDiff';
}
