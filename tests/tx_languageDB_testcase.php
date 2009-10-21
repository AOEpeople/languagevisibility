<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

class tx_languageDB_testcase extends tx_languagevisibility_databaseTestcase{
			
	/**
	 * The fallback order property is cached. This testcase
	 * should ensure that it can be read multiple times.
	 *
	 * @test
	 * @return void
	 */
	public function getFallBackOrderMultipleTimes(){
		$languageRepository = new tx_languagevisibility_languagerepository();
		
		/* @var $language tx_languagevisibility_language */
		$language = $languageRepository->getLanguageById(1);
		
		$this->assertEquals(array(0 => 0),$language->getFallbackOrder());
		$this->assertEquals(count(array(0 => 0)),1);
		$this->assertEquals(array(0 => 0),$language->getFallbackOrder());
		
		$this->assertFalse($language->isLanguageUidInFallbackOrder(22));
		$this->assertTrue($language->isLanguageUidInFallbackOrder(0));
	}
	
	/**

	 */
	function setUp(){
		parent::setUp();
		$this->importDataSet(dirname(__FILE__). '/fixtures/dbDefaultLangs.xml');
	}	
}

?>