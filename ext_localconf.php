<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/**
 * Register TYPO3 core hooks
 */
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['languagevisibility'] = 'tx_languagevisibility_hooks_t3lib_tcemain';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay']['languagevisility'] = 'tx_languagevisibility_hooks_t3lib_page';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay']['languagevisility'] = 'tx_languagevisibility_hooks_t3lib_page';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess']['languagevisility'] = 'tx_languagevisibility_hooks_t3lib_userauthgroup->checkFullLanguagesAccess';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess']['languagevisility'] = 'tx_languagevisibility_hooks_tslib_fe->settingLanguage_preProcess';

$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck']['languagevisility'] = 'tx_languagevisibility_hooks_alt_doc->makeEditForm_accessCheck';
$TYPO3_CONF_VARS['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages']['languagevisility'] = 'tx_languagevisibility_hooks_tslib_menu';

	// overriding option because this is done by languagevisibility and will not work if set
$TYPO3_CONF_VARS['FE']['hidePagesIfNotTranslatedByDefault'] = 0;

	// adding inheriatance flag to the addRootlineField
$rootlineFields = &$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'];
$newRootlineFields = 'tx_languagevisibility_inheritanceflag_original, tx_languagevisibility_inheritanceflag_overlayed';
$rootlineFields .= (empty($rootlineFields))? $newRootlineFields : ',' . $newRootlineFields;

	// adding the inheritance flag to the pageOverlayFields
$pagesOverlayFields = &$GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'];
$newPagesOverlayFields = 'tx_languagevisibility_inheritanceflag_overlayed';
$pagesOverlayFields .= (empty($pagesOverlayFields)) ? $newPagesOverlayFields : ',' . $newPagesOverlayFields;

/**
 * Register extension hooks
 */
if (t3lib_extMgm::isLoaded('templavoila')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['pi1']['renderElementClass']['languagevisibility'] = 'tx_languagevisibility_hooks_templavoila_pi1';
}
if (t3lib_extMgm::isLoaded('crawler')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['processUrls']['languagevisibility'] = 'tx_languagevisibility_hooks_crawler->processUrls';
}
if (t3lib_extMgm::isLoaded('phpunit')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles']['languagevisibility'] = 'EXT:languagevisibility/ext_tables.sql';
}
if (t3lib_extMgm::isLoaded('aoe_wspreview')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['aoe_wspreview/system/class.tx_aoewspreview_system_workspaceService.php']['createDiff']['languagevisibility'] = 'tx_languagevisibility_hooks_aoe_wspreview->aoewspreview_createDiff';
}

/**
 * Configure TYPO3 Caching Framework
 */
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_languagevisibility'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_languagevisibility'] = array(
		'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\TransientMemoryBackend',
		'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
	);
}
