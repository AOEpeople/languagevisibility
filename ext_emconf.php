<?php

########################################################################
# Extension Manager/Repository config file for ext "languagevisibility".
#
# Auto generated 26-02-2012 13:46
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Multilanguage Projects: Language Visibility',
	'description' => 'Enables multilevel fallback and introduces the languagevisibility concept',
	'category' => 'fe',
	'author' => 'Daniel Pötzinger, Tolleiv Nietsch, Timo Schmidt - AOE media GmbH',
	'author_email' => 'dev@aoemedia.de',
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
	'author_company' => 'AOE media GmbH',
	'version' => '0.9.1',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
			'danp_languagefallback_patch' => '',
		),
		'suggests' => array(
		),
	),
);
