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
	'version' => '0.8.7',
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
	'_md5_values_when_last_written' => 'a:98:{s:9:"ChangeLog";s:4:"fb51";s:10:"README.txt";s:4:"40f2";s:42:"class.tx_languagevisibility_beservices.php";s:4:"879a";s:42:"class.tx_languagevisibility_feservices.php";s:4:"ec22";s:47:"class.tx_languagevisibility_fieldvisibility.php";s:4:"4220";s:16:"ext_autoload.php";s:4:"a67f";s:21:"ext_conf_template.txt";s:4:"7237";s:12:"ext_icon.gif";s:4:"f65f";s:17:"ext_localconf.php";s:4:"4038";s:14:"ext_tables.php";s:4:"67e1";s:14:"ext_tables.sql";s:4:"9842";s:16:"locallang_db.xml";s:4:"3002";s:52:"classes/class.tx_languagevisibility_cacheManager.php";s:4:"794f";s:48:"classes/class.tx_languagevisibility_celement.php";s:4:"80b4";s:47:"classes/class.tx_languagevisibility_element.php";s:4:"7d25";s:54:"classes/class.tx_languagevisibility_elementFactory.php";s:4:"928d";s:50:"classes/class.tx_languagevisibility_fceelement.php";s:4:"e188";s:57:"classes/class.tx_languagevisibility_fceoverlayelement.php";s:4:"865a";s:48:"classes/class.tx_languagevisibility_language.php";s:4:"f5c0";s:58:"classes/class.tx_languagevisibility_languagerepository.php";s:4:"7f95";s:51:"classes/class.tx_languagevisibility_pageelement.php";s:4:"d57c";s:53:"classes/class.tx_languagevisibility_recordelement.php";s:4:"f3bc";s:67:"classes/class.tx_languagevisibility_reports_ConfigurationStatus.php";s:4:"c91b";s:53:"classes/class.tx_languagevisibility_ttnewselement.php";s:4:"b6d3";s:50:"classes/class.tx_languagevisibility_visibility.php";s:4:"f7bd";s:57:"classes/class.tx_languagevisibility_visibilityService.php";s:4:"a450";s:53:"classes/dao/class.tx_languagevisibility_daocommon.php";s:4:"3676";s:58:"classes/dao/class.tx_languagevisibility_daocommon_stub.php";s:4:"f039";s:70:"classes/exceptions/class.tx_languagevisibility_InvalidRowException.php";s:4:"5491";s:14:"doc/manual.sxw";s:4:"daed";s:19:"doc/wizard_form.dat";s:4:"ecbf";s:20:"doc/wizard_form.html";s:4:"5a09";s:51:"hooks/class.tx_languagevisibility_hooks_alt_doc.php";s:4:"1adb";s:57:"hooks/class.tx_languagevisibility_hooks_aoe_wspreview.php";s:4:"1558";s:51:"hooks/class.tx_languagevisibility_hooks_crawler.php";s:4:"b898";s:54:"hooks/class.tx_languagevisibility_hooks_t3lib_page.php";s:4:"5de8";s:57:"hooks/class.tx_languagevisibility_hooks_t3lib_tcemain.php";s:4:"5b13";s:63:"hooks/class.tx_languagevisibility_hooks_t3lib_userauthgroup.php";s:4:"95af";s:59:"hooks/class.tx_languagevisibility_hooks_templavoila_pi1.php";s:4:"4c7b";s:52:"hooks/class.tx_languagevisibility_hooks_tslib_fe.php";s:4:"cf45";s:54:"hooks/class.tx_languagevisibility_hooks_tslib_menu.php";s:4:"dea0";s:49:"modfunc1/class.tx_languagevisibility_modfunc1.php";s:4:"c50c";s:22:"modfunc1/locallang.xml";s:4:"25f7";s:37:"patch/core_4.2/class.ux_SCalt_doc.php";s:4:"1380";s:44:"patch/core_4.2/class.ux_t3lib_beuserauth.php";s:4:"161a";s:38:"patch/core_4.2/class.ux_t3lib_page.php";s:4:"4554";s:41:"patch/core_4.2/class.ux_t3lib_tcemain.php";s:4:"6c2b";s:36:"patch/core_4.2/class.ux_tslib_fe.php";s:4:"6224";s:38:"patch/core_4.2/class.ux_tslib_menu.php";s:4:"18c3";s:41:"patch/core_4.2/class.ux_ux_tslib_menu.php";s:4:"6a77";s:37:"patch/core_4.3/class.ux_SCalt_doc.php";s:4:"47f4";s:44:"patch/core_4.3/class.ux_t3lib_beuserauth.php";s:4:"21b9";s:38:"patch/core_4.3/class.ux_t3lib_page.php";s:4:"9fa6";s:41:"patch/core_4.3/class.ux_t3lib_tcemain.php";s:4:"fb20";s:36:"patch/core_4.3/class.ux_tslib_fe.php";s:4:"9dbe";s:38:"patch/core_4.3/class.ux_tslib_menu.php";s:4:"902f";s:41:"patch/core_4.3/class.ux_ux_tslib_menu.php";s:4:"2e72";s:48:"patch/lib/class.tx_languagevisibility_beUser.php";s:4:"6350";s:52:"patch/lib/class.tx_languagevisibility_commandMap.php";s:4:"735e";s:40:"patch/tv/class.ux_tx_templavoila_api.php";s:4:"7aec";s:44:"patch/tv/class.ux_tx_templavoila_module1.php";s:4:"c837";s:40:"patch/tv/class.ux_tx_templavoila_pi1.php";s:4:"7e79";s:11:"res/nok.gif";s:4:"ce40";s:10:"res/ok.gif";s:4:"4705";s:17:"tests/phpunit.xml";s:4:"fe7e";s:34:"tests/tx_cacheManager_testcase.php";s:4:"5368";s:36:"tests/tx_cachedWorkflow_testcase.php";s:4:"73c0";s:36:"tests/tx_elementFactory_testcase.php";s:4:"1587";s:29:"tests/tx_element_testcase.php";s:4:"a587";s:33:"tests/tx_environment_testcase.php";s:4:"a1c5";s:32:"tests/tx_languageDB_testcase.php";s:4:"e522";s:30:"tests/tx_language_testcase.php";s:4:"3b05";s:40:"tests/tx_languagerepository_testcase.php";s:4:"4d25";s:44:"tests/tx_languagevisibility_baseTestcase.php";s:4:"c646";s:48:"tests/tx_languagevisibility_databaseTestcase.php";s:4:"0399";s:41:"tests/tx_visibilityBEService_testcase.php";s:4:"d55b";s:41:"tests/tx_visibilityServiceDB_testcase.php";s:4:"4c07";s:39:"tests/tx_visibilityService_testcase.php";s:4:"3143";s:75:"tests/classes/class.tx_languagevisibility_tests_helper_environmentSaver.php";s:4:"0f6d";s:53:"tests/fixtures/canDetectTranslationsInAnyLanguage.xml";s:4:"80c8";s:84:"tests/fixtures/canDetermineCorrectVisiblityForContentelementWithLanguageSetToAll.xml";s:4:"03be";s:50:"tests/fixtures/canDetermineInheritedVisibility.xml";s:4:"f397";s:85:"tests/fixtures/canGetCorrectVisiblityDescriptionForElementWithInheritedVisibility.xml";s:4:"356e";s:50:"tests/fixtures/dbContentWithVisibilityTestdata.xml";s:4:"0fe2";s:33:"tests/fixtures/dbDefaultLangs.xml";s:4:"b296";s:38:"tests/fixtures/dbDefaultWorkspaces.xml";s:4:"b724";s:41:"tests/fixtures/fce_2col_datastructure.xml";s:4:"d349";s:42:"tests/fixtures/fce_2col_templateobject.dat";s:4:"18ee";s:47:"tests/fixtures/fce_buttonelement_contentxml.xml";s:4:"8776";s:50:"tests/fixtures/fce_buttonelement_datastructure.xml";s:4:"e3b3";s:61:"tests/fixtures/fce_buttonelement_datastructure_useOverlay.xml";s:4:"2c43";s:58:"tests/fixtures/getLiveWorkspaceElementFromWorkspaceUid.xml";s:4:"aeae";s:53:"tests/fixtures/inheritanceForceToNoAffectsSubpage.xml";s:4:"dca5";s:87:"tests/fixtures/inheritanceForceToNoDoesNotAffectSubpageWithoutAGivenInheritanceFlag.xml";s:4:"831a";s:90:"tests/fixtures/inheritanceForceToNoInOtherLanguageDoesNotAffectRecordInCurrentLanguage.xml";s:4:"25da";s:62:"tests/fixtures/inheritanceForceToNoInOverlayAffectsSubpage.xml";s:4:"7cc8";s:70:"tests/fixtures/overlayOverwritesInheritingVisibilityOfPageElements.xml";s:4:"6177";s:71:"tests/fixtures/yesInPageAnnulatesInheritedForceToNoOfRootlineRecord.xml";s:4:"481a";}',
	'suggests' => array(
	),
);

?>
