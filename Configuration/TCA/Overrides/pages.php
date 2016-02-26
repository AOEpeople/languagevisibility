<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumnsPages = array(
	'tx_languagevisibility_visibility' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility',
		'config' => array(
			'type' => 'user',
			'size' => '30',
			'userFunc' => 'AOE\\Languagevisibility\\FieldVisibility->user_fieldvisibility',
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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumnsPages, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1', '', 'after:php_tree_stop');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $tempColumnsPages,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages_language_overlay', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1', '', '');

unset($tempColumnsPages);