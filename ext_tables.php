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

t3lib_extMgm::addTCAcolumns('sys_language', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('sys_language', 'tx_languagevisibility_defaultvisibility, tx_languagevisibility_fallbackorder;;;;1-1-1,tx_languagevisibility_complexfallbacksetting, tx_languagevisibility_defaultvisibilityttnewsel, tx_languagevisibility_fallbackorderttnewsel;;;;1-1-1, tx_languagevisibility_defaultvisibilityel, tx_languagevisibility_fallbackorderel;;;;1-1-1');
$tempColumnsElements = array(
	'tx_languagevisibility_visibility' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility',
		'config' => array(
			'type' => 'user',
			'size' => '30',
			'userFunc' => 'tx_languagevisibility_fieldvisibility->user_fieldvisibility',
		)
	)
);

$tempColumnsPages = array(
	'tx_languagevisibility_visibility' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility',
		'config' => array(
			'type' => 'user',
			'size' => '30',
			'userFunc' => 'tx_languagevisibility_fieldvisibility->user_fieldvisibility',
		)
	),
	'tx_languagevisibility_inheritanceflag_original' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_inheritanceflag_original',
		'config' => array(
			'type' => 'check',
			'default' => '0'
		)
	),
	'tx_languagevisibility_inheritanceflag_overlayed' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_inheritanceflag_overlayed',
		'config' => array(
			'type' => 'check',
			'default' => '0'
		)
	),
);

t3lib_extMgm::addTCAcolumns('pages', $tempColumnsPages, 1);
t3lib_extMgm::addToAllTCAtypes('pages', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1', '', 'after:php_tree_stop');

t3lib_extMgm::addTCAcolumns('pages_language_overlay', $tempColumnsPages,1);
t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1', '', '');

t3lib_extMgm::addTCAcolumns('tt_news', $tempColumnsElements,1);
t3lib_extMgm::addToAllTCAtypes('tt_news', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1');

t3lib_extMgm::addTCAcolumns('tt_content', $tempColumnsElements,1);
t3lib_extMgm::addToAllTCAtypes('tt_content', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1,sys_language_uid,l18n_parent', '', 'before:--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.extended');

$GLOBALS['TCA']['tt_content']['palettes']['general']['showitem'] = str_replace('sys_language_uid;LLL:EXT:cms/locallang_ttc.xml:sys_language_uid_formlabel', '', $GLOBALS['TCA']['tt_content']['palettes']['general']['showitem']);

$GLOBALS['TCA']['tt_content']['ctrl']['dividers2tabs'] = TRUE;

$tempColumns = array(
	'tx_languagevisibility_allow_movecutdelete_foroverlays' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:be_groups.allow_movecutdelete_foroverlays:',
		'config' => array(
			'type' => 'check'
		)
	),
);

t3lib_extMgm::addTCAcolumns('be_groups', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('be_groups', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_allow_movecutdelete_foroverlays;;;;1-1-1');

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_languagevisibility_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY) . 'modfunc1/class.tx_languagevisibility_modfunc1.php',
		'LLL:EXT:languagevisibility/locallang_db.xml:moduleFunction.tx_languagevisibility_modfunc1'
	);
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['configuration'][] = 'tx_languagevisibility_reports_ConfigurationStatus';
