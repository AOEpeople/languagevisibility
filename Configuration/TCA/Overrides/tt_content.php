<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumnsElements = array(
	'tx_languagevisibility_visibility' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:pages.tx_languagevisibility_visibility',
		'config' => array(
			'type' => 'user',
			'size' => '30',
			'userFunc' => 'AOE\\Languagevisibility\\FieldVisibility->user_fieldvisibility',
		)
	)
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_news', $tempColumnsElements,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_news', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumnsElements,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_visibility;;;;1-1-1,sys_language_uid,l18n_parent', '', 'before:--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.extended');

unset($tempColumnsElements);