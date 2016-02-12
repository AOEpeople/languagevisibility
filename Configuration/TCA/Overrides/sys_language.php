<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumns = array(
	'tx_languagevisibility_fallbackorder' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorder',
		'l10n_display' => 'hideDiff',
		'config' => array(
			'type' => 'select',
			'foreign_table' => 'sys_language',
			'foreign_table_where' => ' ORDER BY sys_language.title',
			'items' => array(
				array('default', '999'),
			),
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 10,
		)
	),
	'tx_languagevisibility_fallbackorderel' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorderel',
		'displayCond' => 'FIELD:tx_languagevisibility_complexfallbacksetting:>=:1',
		'l10n_display' => 'hideDiff',
		'config' => array(
			'type' => 'select',
			'foreign_table' => 'sys_language',
			'foreign_table_where' => ' ORDER BY sys_language.title',
			'items' => array(
				array('default', '999'),
			),
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 10,
		)
	),
	'tx_languagevisibility_fallbackorderttnewsel' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorderttnewsel',
		'displayCond' => 'FIELD:tx_languagevisibility_complexfallbacksetting:>=:1',
		'l10n_display' => 'hideDiff',
		'config' => array(
			'type' => 'select',
			'foreign_table' => 'sys_language',
			'foreign_table_where' => ' ORDER BY sys_language.title',
			'items' => array(
				array('default', '999'),
			),
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 10,
		)
	),
	'tx_languagevisibility_defaultvisibility' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibility',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('',''),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes', 'yes'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no', 'no'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t', 't'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f', 'f'),
			),
			'default' => 'f',
			'size' => 1,
			'maxitems' => 1,
		)
	),
	'tx_languagevisibility_defaultvisibilityel' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibilityel',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('',''),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes', 'yes'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no', 'no'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t', 't'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f', 'f'),
			),
			'default' => 'f',
			'size' => 1,
			'maxitems' => 1,
		)
	),
	'tx_languagevisibility_defaultvisibilityttnewsel' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibilityttnewsel',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('',''),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes', 'yes'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no', 'no'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t', 't'),
				array('LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f', 'f'),
			),
			'default' => 'f',
			'size' => 1,
			'maxitems' => 1,
		)
	),
	'tx_languagevisibility_complexfallbacksetting' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_complexfallbacksetting',
		'config' => array(
			'type' => 'check'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_language', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_language', 'tx_languagevisibility_defaultvisibility, tx_languagevisibility_fallbackorder;;;;1-1-1,tx_languagevisibility_complexfallbacksetting, tx_languagevisibility_defaultvisibilityttnewsel, tx_languagevisibility_fallbackorderttnewsel;;;;1-1-1, tx_languagevisibility_defaultvisibilityel, tx_languagevisibility_fallbackorderel;;;;1-1-1');

unset($tempColumns);