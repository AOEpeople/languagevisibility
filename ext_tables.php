<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['palettes']['general']['showitem'] = str_replace('sys_language_uid;LLL:EXT:cms/locallang_ttc.xml:sys_language_uid_formlabel', '', $GLOBALS['TCA']['tt_content']['palettes']['general']['showitem']);

$GLOBALS['TCA']['tt_content']['ctrl']['dividers2tabs'] = TRUE;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['configuration'][] = 'tx_languagevisibility_reports_ConfigurationStatus';
