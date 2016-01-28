<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumns = array(
	'tx_languagevisibility_allow_movecutdelete_foroverlays' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:languagevisibility/locallang_db.xml:be_groups.allow_movecutdelete_foroverlays:',
		'config' => array(
			'type' => 'check'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', '--div--;LLL:EXT:languagevisibility/locallang_db.xml:tabname,tx_languagevisibility_allow_movecutdelete_foroverlays;;;;1-1-1');

unset($tempColumns);