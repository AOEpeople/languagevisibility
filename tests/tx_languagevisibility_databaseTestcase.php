<?php
abstract class tx_languagevisibility_databaseTestcase extends tx_phpunit_database_testcase{

	/** 
	 * @var array holds the restored addRootlineFields array which will be stored in setUp and restored in tearDown.
	 */
	protected $restoredAddRootlineFields;
	
	/**
	 * @var array holds the restored pageOverlayFields array which will be stored in setUp and restored in tearDown
	 */
	protected $restoredPageOverlayFields;
	
	/**
	 *
	 */
	function setUp() {
		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		/* save current enviroment values */
		$this->restoredAddRootlineFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
		$this->restoredPageOverlayFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"];
		
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = "tx_languagevisibility_inheritanceflag_original,tx_languagevisibility_inheritanceflag_overlayed";
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = "uid,title,subtitle,nav_title,media,keywords,description,abstract,author,author_email,sys_language_uid,tx_languagevisibility_inheritanceflag_overlayed";

		// order of extension-loading is important !!!!
		$this->importExtensions(array('cms','languagevisibility'));

		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
	}

	/**
	 *
	 */
	function tearDown() {
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = $this->restoredAddRootlineFields;
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = $this->restoredPageOverlayFields;
				
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}
}
?>