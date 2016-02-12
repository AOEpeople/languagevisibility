<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Language Visibility',
	'description' => 'Enables multi level language fallback and introduces the languagevisibility concept',
	'category' => 'fe',
	'author' => 'Daniel Pötzinger, Tolleiv Nietsch, Timo Schmidt, Stefan Rotsch, Tomas Norre Mikkelsen',
	'author_company' => 'AOE GmbH',
	'author_email' => 'dev@aoe.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '1.0.dev',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '6.2.0-7.9.99',
		),
		'conflicts' => array(
			'danp_languagefallback_patch' => '',
		),
		'suggests' => array(
		),
	),
);
