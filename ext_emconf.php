<?php

########################################################################
# Extension Manager/Repository config file for ext: "languagevisibility"
#
# Auto generated 03-07-2008 11:28
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Multilanguage Projects: Language Visibility',
	'description' => 'Enables multilevel fallback and introduces the languagevisibility concept',
	'category' => 'fe',
	'author' => 'Daniel Pötzinger, Tolleiv Nietsch, Timo Schmidt AOE media GmbH',
	'author_email' => '',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => 'danp_languagefallback_patch',
	'priority' => '',
	'module' => '',
	'state' => '',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'AOE media GmbH',
	'version' => '0.3.11',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
			'danp_languagefallback_patch' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:50:{s:9:"ChangeLog";s:4:"4dd9";s:10:"README.txt";s:4:"fc7c";s:39:"class.tx_languagevisibility_behooks.php";s:4:"1dc0";s:42:"class.tx_languagevisibility_beservices.php";s:4:"04e0";s:42:"class.tx_languagevisibility_feservices.php";s:4:"c562";s:47:"class.tx_languagevisibility_fieldvisibility.php";s:4:"857a";s:21:"ext_conf_template.txt";s:4:"bfdc";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"3933";s:14:"ext_tables.php";s:4:"8bf6";s:14:"ext_tables.sql";s:4:"0d80";s:16:"locallang_db.xml";s:4:"6a54";s:48:"classes/class.tx_languagevisibility_celement.php";s:4:"6ed0";s:47:"classes/class.tx_languagevisibility_element.php";s:4:"1577";s:54:"classes/class.tx_languagevisibility_elementFactory.php";s:4:"02e6";s:50:"classes/class.tx_languagevisibility_fceelement.php";s:4:"f681";s:57:"classes/class.tx_languagevisibility_fceoverlayelement.php";s:4:"5b1f";s:48:"classes/class.tx_languagevisibility_language.php";s:4:"3fc9";s:58:"classes/class.tx_languagevisibility_languagerepository.php";s:4:"b586";s:51:"classes/class.tx_languagevisibility_pageelement.php";s:4:"ac9d";s:53:"classes/class.tx_languagevisibility_recordelement.php";s:4:"9db4";s:57:"classes/class.tx_languagevisibility_visibilityService.php";s:4:"9ba7";s:53:"classes/dao/class.tx_languagevisibility_daocommon.php";s:4:"756d";s:58:"classes/dao/class.tx_languagevisibility_daocommon_stub.php";s:4:"5249";s:19:"doc/wizard_form.dat";s:4:"ecbf";s:20:"doc/wizard_form.html";s:4:"5a09";s:49:"modfunc1/class.tx_languagevisibility_modfunc1.php";s:4:"76c2";s:22:"modfunc1/locallang.xml";s:4:"25f7";s:28:"patch/class.ux_SCalt_doc.php";s:4:"7ab2";s:35:"patch/class.ux_t3lib_beuserauth.php";s:4:"f7f8";s:29:"patch/class.ux_t3lib_page.php";s:4:"b2c5";s:32:"patch/class.ux_t3lib_tcemain.php";s:4:"1ff7";s:27:"patch/class.ux_tslib_fe.php";s:4:"0e9f";s:29:"patch/class.ux_tslib_menu.php";s:4:"dab7";s:32:"patch/class.ux_ux_tslib_menu.php";s:4:"d859";s:40:"patch/tv/class.ux_tx_templavoila_api.php";s:4:"62f7";s:44:"patch/tv/class.ux_tx_templavoila_module1.php";s:4:"68bc";s:40:"patch/tv/class.ux_tx_templavoila_pi1.php";s:4:"6ece";s:11:"res/nok.gif";s:4:"ce40";s:10:"res/ok.gif";s:4:"4705";s:36:"tests/tx_elementFactory_testcase.php";s:4:"ef14";s:29:"tests/tx_element_testcase.php";s:4:"78e0";s:30:"tests/tx_language_testcase.php";s:4:"f4fc";s:40:"tests/tx_languagerepository_testcase.php";s:4:"fdca";s:39:"tests/tx_visibilityService_testcase.php";s:4:"4326";s:41:"tests/fixtures/fce_2col_datastructure.xml";s:4:"d349";s:42:"tests/fixtures/fce_2col_templateobject.dat";s:4:"18ee";s:47:"tests/fixtures/fce_buttonelement_contentxml.xml";s:4:"8776";s:50:"tests/fixtures/fce_buttonelement_datastructure.xml";s:4:"e3b3";s:61:"tests/fixtures/fce_buttonelement_datastructure_useOverlay.xml";s:4:"2c43";}',
	'suggests' => array(
	),
);

?>