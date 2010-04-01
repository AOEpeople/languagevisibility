<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

class tx_cachedWorkflow_testcase extends tx_languagevisibility_databaseTestcase{
	
	
	/**
	 * Situation:
	 * 
	 * We have a page structure like the following:
	 * 
	 *  a (uid 1)
	 *  |
	 *  +--- b (uid 2)
	 *       | 
	 *       +---- c (uid 3)
	 *       
	 * Page a has the inherited force to no setting (no +) for the 
	 * language 3. Therefor the element should not be visible.
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @test
	 */
	public function canDetermineInheritedVisibility(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new tx_languagevisibility_language();
		$fixtureLanguage->setData($fixtureLanguageRow);
		
		$dao				= new tx_languagevisibility_daocommon();
		$elementFactory 	= new tx_languagevisibility_elementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages',3);
		
		$visibilityService 	= new tx_languagevisibility_visibilityService();
		$visibilityService->setUseInheritance();
		$visibilityResult	= true;
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);	
		
		$this->assertFalse($visibilityResult,'The element should not be visibile because of the inherited force to no setting');
	}
	
	/**
	 * We have the same situation as before but in this
	 * testcase we test the visibility of page c and change
	 * the pid afterward. Because the visibility is forced
	 * to no by inheritance, it should normaly be visible,
	 * but the result of the visibility is chached in
	 * that situation and the visibility will only change afterwards
	 * when the cache was flushed. 
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @test
	 */
	public function changeOfPidDoesNotInfluenceCachedResult(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetermineInheritedVisibility.xml');

		$fixtureLanguageRow = array('uid' => 1, 'tx_languagevisibility_defaultvisibility' => 't');
		$fixtureLanguage 	= new tx_languagevisibility_language();
		$fixtureLanguage->setData($fixtureLanguageRow);
		
		$dao				= new tx_languagevisibility_daocommon();
		$elementFactory 	= new tx_languagevisibility_elementFactory($dao);
		$fixtureElement 	= $elementFactory->getElementForTable('pages',3);
		
		$visibilityService 	= new tx_languagevisibility_visibilityService();
		$visibilityResult	= true;
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);	
		$this->assertFalse($visibilityResult,'The element should not be visibile because of the inherited force to no setting');		
		
		$db = $GLOBALS['TYPO3_DB'];	
		/* @var  $db t3lib_db */
		$db->exec_UPDATEquery('pages','pid=2',array('pid' => 0));
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);		
		$this->assertFalse($visibilityResult,'The element should not still not be visible because the visibility result is cached');
		
		tx_languagevisibility_cacheManager::getInstance()->flushAllCaches();
		
		$visibilityResult 	= $visibilityService->isVisible($fixtureLanguage,$fixtureElement);		
		$this->assertTrue($visibilityResult,'Now the element should be visible because the cache was flushed');
	}
}
?>