<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



$tempColumns = Array (
	"tx_languagevisibility_fallbackorder" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorder",
		'l10n_display'=>'hideDiff',
		"config" => Array (
			"type" => "select",
			"foreign_table" => "sys_language",
			"foreign_table_where" => " ORDER BY sys_language.title",
			"items" => Array (
				Array("default", "999"),
			),
			"size" => 10,
			"minitems" => 0,
			"maxitems" => 10,
		)
	),
	"tx_languagevisibility_fallbackorderel" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorderel",
        "displayCond" => "FIELD:tx_languagevisibility_complexfallbacksetting:>=:1",
		'l10n_display'=>'hideDiff',
		"config" => Array (
			"type" => "select",
			"foreign_table" => "sys_language",
			"foreign_table_where" => " ORDER BY sys_language.title",
			"items" => Array (
				Array("default", "999"),
			),
			"size" => 10,
			"minitems" => 0,
			"maxitems" => 10,
		)
	),
	"tx_languagevisibility_fallbackorderttnewsel" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_fallbackorderttnewsel",
        "displayCond" => "FIELD:tx_languagevisibility_complexfallbacksetting:>=:1",
		'l10n_display'=>'hideDiff',
		"config" => Array (
			"type" => "select",
			"foreign_table" => "sys_language",
			"foreign_table_where" => " ORDER BY sys_language.title",
			"items" => Array (
				Array("default", "999"),
			),
			"size" => 10,
			"minitems" => 0,
			"maxitems" => 10,
		)
	),
	"tx_languagevisibility_defaultvisibility" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibility",
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array('',''),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes", "yes"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no", "no"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t", "t"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f", "f"),
			),
			'default'=>'f',
			"size" => 1,
			"maxitems" => 1,
		)
	),
	"tx_languagevisibility_defaultvisibilityel" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibilityel",
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array('',''),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes", "yes"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no", "no"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t", "t"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f", "f"),
			),
			'default'=>'f',
			"size" => 1,
			"maxitems" => 1,
		)
	),
	"tx_languagevisibility_defaultvisibilityttnewsel" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_defaultvisibilityttnewsel",
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array('',''),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.yes", "yes"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.no", "no"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.t", "t"),
				Array("LLL:EXT:languagevisibility/locallang_db.xml:tx_languagevisibility_visibility.I.f", "f"),
			),
			'default'=>'f',
			"size" => 1,
			"maxitems" => 1,
		)
	),
	"tx_languagevisibility_complexfallbacksetting" => Array (
		"exclude" => 0,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:sys_language.tx_languagevisibility_complexfallbacksetting",
		"config" => Array (
			"type" => "check"
		)
	),

);


t3lib_div::loadTCA("sys_language");
t3lib_extMgm::addTCAcolumns("sys_language",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("sys_language","tx_languagevisibility_defaultvisibility, tx_languagevisibility_fallbackorder;;;;1-1-1,tx_languagevisibility_complexfallbacksetting, tx_languagevisibility_defaultvisibilityttnewsel, tx_languagevisibility_fallbackorderttnewsel;;;;1-1-1, tx_languagevisibility_defaultvisibilityel, tx_languagevisibility_fallbackorderel;;;;1-1-1");
$tempColumnsElements = Array (
	"tx_languagevisibility_visibility" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility",
		"config" => Array (
			"type" => "user",
			"size" => "30",
			"userFunc" => 'tx_languagevisibility_fieldvisibility->user_fieldvisibility',
		)
	)
);


$tempColumnsPages = Array (
	"tx_languagevisibility_visibility" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility",
		"config" => Array (
			"type" => "user",
			"size" => "30",
			"userFunc" => 'tx_languagevisibility_fieldvisibility->user_fieldvisibility',
		)
	),
	"tx_languagevisibility_inheritanceflag_original" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:l10nmgr/locallang_db.xml:pages.tx_languagevisibility_visibility",
		"config" => Array (
			"type" => "check",
			"default" => "0"
		)
	),
	"tx_languagevisibility_inheritanceflag_overlayed" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:l10nmgr/locallang_db.xml:pages.tx_languagevisibility_visibility",
		"config" => Array (
			"type" => "check",
			"default" => "0"
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumnsPages,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_languagevisibility_visibility;;;;1-1-1", '', 'after:l18n_cfg');
t3lib_extMgm::addToAllTCAtypes("pages","--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname", '', 'before:l18n_cfg');

t3lib_div::loadTCA("pages_language_overlay");
t3lib_extMgm::addTCAcolumns("pages_language_overlay",$tempColumnsPages,1);
t3lib_extMgm::addToAllTCAtypes("pages_language_overlay","tx_languagevisibility_visibility;;;;1-1-1", '', 'after:l18n_cfg');
t3lib_extMgm::addToAllTCAtypes("pages_language_overlay","--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname", '', 'before:l18n_cfg');

t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumnsElements,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1");


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumnsElements,1);
t3lib_extMgm::addToAllTCAtypes('tt_content', "--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1,sys_language_uid,l18n_parent", '', 'before:sys_language_uid');

//remove language related fields from pallete (instead show them in language tab)
$GLOBALS['TCA']['tt_content']['palettes']['4']['showitem'] = str_replace('sys_language_uid,','',$GLOBALS['TCA']['tt_content']['palettes']['4']['showitem']);
$GLOBALS['TCA']['tt_content']['palettes']['4']['showitem'] = str_replace('l18n_parent,','',$GLOBALS['TCA']['tt_content']['palettes']['4']['showitem']);
$GLOBALS['TCA']['tt_content']['ctrl']['dividers2tabs']=TRUE;

$tempColumns = Array(
	"tx_languagevisibility_allow_movecutdelete_foroverlays" => Array(
		"exclude" => 1,
		"label" => "LLL:EXT:languagevisibility/locallang_db.xml:be_groups.allow_movecutdelete_foroverlays:",
		"config" => Array(
			"type" => "check"
		)
	),
);

t3lib_div::loadTCA("be_groups");
t3lib_extMgm::addTCAcolumns("be_groups",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("be_groups","--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_allow_movecutdelete_foroverlays;;;;1-1-1");

if (TYPO3_MODE=="BE")    {
    t3lib_extMgm::insertModuleFunction(
        "web_info",
        "tx_languagevisibility_modfunc1",
        t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_languagevisibility_modfunc1.php",
        "LLL:EXT:languagevisibility/locallang_db.xml:moduleFunction.tx_languagevisibility_modfunc1"
    );
}
?>