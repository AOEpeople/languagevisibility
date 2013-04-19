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
	'dependencies' => 'cms',
	'conflicts' => 'danp_languagefallback_patch',
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
	'version' => '0.8.dev',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
			'danp_languagefallback_patch' => '',
		),
		'suggests' => array(
			'ext_languagevisibility'
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);

?>
