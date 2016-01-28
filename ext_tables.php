<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['palettes']['general']['showitem'] = str_replace('sys_language_uid;LLL:EXT:cms/locallang_ttc.xml:sys_language_uid_formlabel', '', $GLOBALS['TCA']['tt_content']['palettes']['general']['showitem']);

$GLOBALS['TCA']['tt_content']['ctrl']['dividers2tabs'] = TRUE;


if (TYPO3_MODE=='BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_languagevisibility_modfunc1',
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'modfunc1/class.tx_languagevisibility_modfunc1.php',
		'LLL:EXT:languagevisibility/locallang_db.xml:moduleFunction.tx_languagevisibility_modfunc1'
	);
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['configuration'][] = 'tx_languagevisibility_reports_ConfigurationStatus';
