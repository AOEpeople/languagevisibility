<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Kasper Ligaard (ligaard@daimi.au.dk)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 *
 * @author	Tolleiv Nietsch
 */

require_once(t3lib_extMgm::extPath("languagevisibility").'tests/tx_languagevisibility_databaseTestcase.php');

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_language.php');

require_once (PATH_t3lib.'class.t3lib_tcemain.php');

class tx_visibilityServiceDB_testcase extends tx_languagevisibility_databaseTestcase {

	/**
	 * Check the visibility of a regular content element
	 *
	 * @test
	 * @param void
	 * @return void
	 * @see tx_languagevisibility_visibilityService
	 */
	function visibility_ce() {
		$language = $this->_getLang(1);
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		$fixturesWithoutOverlay = array('tt_content'=>1,'pages'=>1);

		foreach($fixturesWithoutOverlay as $table=>$uid) {
			$element = $this->_getContent($table,$uid);
			$this->assertEquals('-', $element->getLocalVisibilitySetting(1), "setting d expected");
			$this->assertEquals('f', $visibility->getVisibilitySetting($language,$element), "setting f expected (because default is used)");
			$this->assertEquals(true, $visibility->isVisible($language,$element), "default lang should be visible");
			$this->assertEquals(0, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), sprintf("default should be overlay table:%s uid:%d",$table,$uid));
		}
	}

	/**
	 * Check the visibility of some content elements with overlay-records
	 *
	 * @test
	 * @param void
	 * @return void
	 * @see tx_languagevisibility_visibilityService
	 */
	function visibility_overlayCe() {
		$element = $this->_getContent('tt_content',2 /* element with L1 overlay */);
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		$expectedResults=array(1=>1,2=>1,3=>0,4=>1);
		foreach($expectedResults as $langUid=>$expectedResult) {
			$language = $this->_getLang($langUid);

			$this->assertEquals(true, $visibility->isVisible($language,$element), "element should be visible in lang ".$expectedResult);
			$this->assertEquals($expectedResult, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), sprintf("Element Overlay used wrong fallback - language %d - should be %d ",$langUid,$expectedResult));
		}
	}

	/**
	 * Check the visibility of some content elements with overlay-records
	 *
	 * @test
	 * @param void
	 * @return void
	 * @see tx_languagevisibility_visibilityService
	 */
	function visibility_hiddenOverlayCe() {

		if(version_compare(TYPO3_version,'4.3','>') && !t3lib_extMgm::isLoaded('version')) {
			$this->markTestSkipped('Not relevant if "version" is not installed');
		}

			/* @var $element tx_languagevisibility_element */
		$element = $this->_getContent('tt_content',15 /* element with L1 overlay */);
			/* @var $visibility tx_languagevisibility_visibilityService */
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

			//Test language 4 to see that this is working if something exists
		$language = $this->_getLang( 4 );
		$this->assertEquals('t', $visibility->getVisibilitySetting($language,$element));
		$this->assertTrue($visibility->isVisible($language,$element), 'There\'s an overlay for this language - therefore it should be visible');
		$this->assertTrue($element->hasTranslation( 4 ));

		$language = $this->_getLang( 5 );
		$this->assertEquals('t', $visibility->getVisibilitySetting($language,$element));
		$this->assertFalse($visibility->isVisible($language,$element), 'This one shouldn\'t be visible because there\'s no valid overlay');
		$this->assertFalse($element->hasTranslation( 5 ));

		$this->_fakeWorkspaceContext(5);
		$language = $this->_getLang( 5 );
		$this->assertEquals('t', $visibility->getVisibilitySetting($language,$element));
		$this->assertTrue($visibility->isVisible($language,$element), 'This one should be visible because there\'s a valid overlay in the workspace (5)');
		$this->assertTrue($element->hasTranslation( 5 ));
	}

	function test_visibility_overlayPage() {
		$language = $this->_getLang(1);
		$element = $this->_getContent('pages','2');

		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		$this->assertEquals(true, $visibility->isVisible($language,$element), "page should be visible");
		$this->assertEquals(1, $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), "Page-Overlay should be defined for lang 1 ...");
	}

	function test_visibility_complexOverlay() {
		$language = $this->_getLang(3);
		$visibility=t3lib_div::makeInstance('tx_languagevisibility_visibilityService');

		$fixtures = array(	'tt_content'	=>array('uid'=>2,'result'=>0),
						 	'pages'		=>array('uid'=>2,'result'=>1)
						 );
		foreach($fixtures as $table=>$tableFixtures) {
			$element = $this->_getContent($table,$tableFixtures['uid']);
			$this->assertEquals($tableFixtures['result'], $visibility->getOverlayLanguageIdForLanguageAndElement($language,$element), sprintf("Element Overlay used wrong fallback - language 2  table %s:%d- should be %d ",$table,$tableFixtures['uid'],$tableFixtures['result']));
		}
	}

	/**
	 * As discussed in issue 6863 an editor should be able to set the languagevisibility right
	 * "force to no" in the overlay record.
	 *
	 * @param void
	 * @return void
	 * @see tx_languagevisibility_visibilityService
	 * @author Timo Schmidt
	 */
	function test_visibility_ttcontentOverlayForceToNoAffectsVisibility(){
		$language = $this->_getLang(1);

		/**
		 * The xml structure is used to to create a fixture tt_content element
		 * with the visibility "yes" for all languages. For the same element
		 * an overlay in language 1 exists with the setting "force to no".
		 * In this case the "force to no" setting in the overlay should overwrite
		 * the "yes" setting in the content element. Therefore the element should not be
		 * visible.
		 */

		$element 			= $this->_getContent('tt_content',4);

		//set visibility to true to ensure it is set to false by the service call
		$visibilityResult 	= true;

		$service			= new tx_languagevisibility_visibilityService();

		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'tt-content element is visible, but should not be visible');
	}

	/**
	 * This testcase does exactly the same as the previos testcase (test_visibility_ttcontentOverlayForceToNoAffectsVisibility)
	 * but uses page elements.
	 *
	 * @param void
	 * @return void
	 * @see tx_languagevisibility_visibilityService
	 * @return
	 */
	function test_visibility_pagesOverlayForceToNoAffectsVisibility(){
		$language 			= $this->_getLang(1);
		$element			= $this->_getContent('pages',4);
		$visibilityResult	= true;

		$service			= new tx_languagevisibility_visibilityService();
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'page element is visible, but should not be visible');
	}

	/**
	 * This testcase is used to test if an "force to no"-setting  in an overlay record in the workspace
	 * affects the original element in the workspace.
	 *
	 */
	function test_visibility_ttcontentOverlayForceToNoAffectsVisibilityAlsoInWorkspaces(){

		if(version_compare(TYPO3_version,'4.3','>') && !t3lib_extMgm::isLoaded('version')) {
			$this->markTestSkipped('Not relevant if "version" is not installed');
		}

		$this->_fakeWorkspaceContext(4711);

		$language 			= $this->_getLang(1);
		$element			= $this->_getContent('tt_content',8);
		$visibilityResult	= true;
		$service			= new tx_languagevisibility_visibilityService();

		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element is visible, but should not be visible');
	}


	/**
	 * The visibility setting in an overlay should only overwrite the visibility
	 * when it is set to "force to no" a "force to yes" setting should not affect the orginal record.
	 *
	 */
	function test_visibility_ttcontentOverlayForceToYesNotAffectsVisibility(){
		$language = $this->_getLang(1);
		$element 			= $this->_getContent('tt_content',10);

		//set visibility to true to ensure it is set to false by the service call
		$visibilityResult 	= true;

		$service			= new tx_languagevisibility_visibilityService();

		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'visibility setting in overlay makes orginal element visible');
	}

	function test_visibility_ttcontentOverlayCorruptedNotAffectsVisibilits(){
		$language = $this->_getLang(1);
		$element 			= $this->_getContent('tt_content',12);

		//set visibility to true to ensure it is set to false by the service call
		$visibilityResult 	= false;

		$service			= new tx_languagevisibility_visibilityService();

		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'corrupted element forces visibility to no');

	}

	function test_visibility_ttcontentHasTranslationInAnyWorkspace(){
		$element 			= $this->_getContent('tt_content',14);

		$hasTranslation 	= true;
		$hasTranslation		= $element->hasAnyTranslationInAnyWorkspace();

		$this->assertFalse($element->supportsInheritance());
		$this->assertFalse($hasTranslation,'Element without translation is determined as element with translation.');
	}

	/**
	 * When an element has configured -1 as sys_language_uid it is configured to be
	 * visible in all languages. This testcase should ensure that this is evaluated
	 * correctly.
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function canDetermineCorrectVisiblityForContentelementWithLanguageSetToAll(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canDetermineCorrectVisiblityForContentelementWithLanguageSetToAll.xml');
		$service			= new tx_languagevisibility_visibilityService();

		$language 			= $this->_getLang(1);

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;
		/* @var $element tx_languagevisibility_pageelement  */
		$element  = $factory->getElementForTable('tt_content',1111);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'An element with language set to all is not visible');
		$this->assertTrue($element->isLanguageSetToAll());
		$this->assertFalse( $element->isLanguageSetToDefault());
		$this->assertTrue($element->isLiveWorkspaceElement());
	}

	/**
	 * This testcase ensures that the state "force to no inherited" affects the visibility of a page in
	 * it's rootline.
	 *
	 * We have the following pages
	 *
	 * uid: 5 (has n+ for the language uk)
	 * uid 6 (pid 6) simple page for fixture rootline
	 * uid 7 (pid 7) is used to evaluate the visibility	and has no local visibility
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function inheritanceForceToNoAffectsSubpage(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/inheritanceForceToNoAffectsSubpage.xml');
		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();


		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;

		/* @var $element tx_languagevisibility_pageelement */
		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($element->supportsInheritance());
		$this->assertFalse($visibilityResult,'element should be invisible because  a page in the rootline has an inherited no+ setting');

		$element  = $factory->getElementForTable('pages',7);
		$this->assertTrue($element->supportsInheritance());
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element should be invisible because a page in the rootline has an inherited no+ setting');
	}

	/**
	 * This testcase ensures that the state "force to no inherited" affects the visibility of a page in
	 * it's rootline.
	 *
	 * We have the following pages
	 *
	 * uid: 5 (has n+ for the language uk)
	 * uid 6 (pid 6) simple page for fixture rootline
	 * uid 7 (pid 7) is used to evaluate the visibility	and has no local visibility
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function inheritanceForceToNoInOverlayAffectsSubpage(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/inheritanceForceToNoInOverlayAffectsSubpage.xml');
		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;

		/* @var $element tx_languagevisibility_pageelement */
		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($element->supportsInheritance());
		$this->assertFalse($visibilityResult,'element should be invisible because  a page in the rootline has an inherited no+ setting');

		$element  = $factory->getElementForTable('pages',7);
		$this->assertTrue($element->supportsInheritance());
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element should be invisible because a page in the rootline has an inherited no+ setting');
	}


	/**
	 * The force to no inheritance (no+) setting should only affect subpages if
	 * the flag is also set without the flag the setting should not be evaluated.
	 *
	 * We have the following pages
	 *
	 * uid: 5 (has n+ for the language uk) BUT NO inheritance flag
	 * uid 6 (pid 6) simple page for fixture rootline
	 * uid 7 (pid 7) is used to evaluate the visibility	and has no local visibility
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function inheritanceForceToNoDoesNotAffectSubpageWithoutAGivenInheritanceFlag(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/inheritanceForceToNoDoesNotAffectSubpageWithoutAGivenInheritanceFlag.xml');

		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;

		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'element should be visible because  a page in the rootline has an inherited no+ setting but no inheritance flag');

		$element  = $factory->getElementForTable('pages',7);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'element should be visible because a page in the rootline has an inherited no+ setting but no inheritance flag');
	}

	/**
	 * The no+ should also only affect pages in the language it has been configured for in the following
	 * testcase we have a page with a no+ setting for the australian language but we evaluate it for uk
	 * therefore the no+ setting should not have any impact on the visibility of the element.
	 * We have the following pages
	 *
	 * uid: 5 (has n+ for the language aus) and also the inheritance flag
	 * uid 6 (pid 6) simple page for fixture rootline
	 * uid 7 (pid 7) is used to evaluate the visibility	and has no local visibility
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function inheritanceForceToNoInOtherLanguageDoesNotAffectRecordInCurrentLanguage(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/inheritanceForceToNoInOtherLanguageDoesNotAffectRecordInCurrentLanguage.xml');

		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;

		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'element should be visible because  a page in the rootline has an inherited no+ setting but in another language');

		$element  = $factory->getElementForTable('pages',7);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'element should be visible because  a page in the rootline has an inherited no+ setting but in another language');
	}

	/**
	 * When an element has the setting yes and an element in the rootline has the setting no+ (inherited no)
	 * the element should be visible (rootline should not be evaluated for inherited settings).
	 *
	 * uid: 5 (has n+ for the language uk) and also the inheritance flag
	 * uid 6 (pid 6) simple page for fixture rootline
	 * uid 7 (pid 7) is used to evaluate the visibility and has the visibility setting "yes"
	 *
	 * @test
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function yesInPageAnnulatesInheritedForceToNoOfRootlineRecord(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/yesInPageAnnulatesInheritedForceToNoOfRootlineRecord.xml');

		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		$visibilityResult 	= true;

		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element should be invisible because  a page in the rootline has an inherited no+ setting there is no local overwriting setting');

		$element  = $factory->getElementForTable('pages',7);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertTrue($visibilityResult,'element should be visible because  a page in the rootline has an inherited no+ setting but the local setting is forced to yes');
	}

	/**
	 * The inheritance of the languagevisibility is controlled by a visibility flag
	 *
	 * @test
	 */
	public function overlayOverwritesInheritingVisibilityOfPageElements(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/overlayOverwritesInheritingVisibilityOfPageElements.xml');

		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);


		$element  = $factory->getElementForTable('pages',6);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element should be invisible because overlay overwrites inheriting visibility on page 5');

		$element  = $factory->getElementForTable('pages',7);
		$visibilityResult 	= $service->isVisible($language,$element);

		$this->assertFalse($visibilityResult,'element should be invisible because overlay overwrites inheriting visibility on page 5');
	}

	/**
	 * Every element can be tested if it is visible for a given language. In addition a
	 * description can be delivered why an element is visible or not.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt <schmidt@aoemedia.de>
	 * @test
	 */
	public function canGetCorrectVisiblityDescriptionForElementWithInheritedVisibility(){
		$this->importDataSet(dirname(__FILE__).'/fixtures/canGetCorrectVisiblityDescriptionForElementWithInheritedVisibility.xml');

		$language 			= $this->_getLang(1);
		$service			= new tx_languagevisibility_visibilityService();
		$service->setUseInheritance();

		$dao				= new tx_languagevisibility_daocommon;

		$factory			= new tx_languagevisibility_elementFactory($dao);

		/* @var $element tx_languagevisibility_pageelement*/
		$element  				= $factory->getElementForTable('pages',7);
		$visibilityDescription 	= $service->getVisibilityDescription($language,$element);

		$this->assertEquals('force to no (inherited from uid 5)',$visibilityDescription,'invalid visibility description of element with inheritance');
	}


	function _loadWorkspaces(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/dbDefaultWorkspaces.xml');
	}

	function _fakeWorkspaceContext($uid){
		$GLOBALS['BE_USER']->workspace = $uid;
	}

	function _getLang($uid) {
		if(!$this->_langImport) {
			$this->_langImport=true;
			$this->importDataSet(dirname(__FILE__). '/fixtures/dbDefaultLangs.xml');
		}
		$languageRep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');
		return $languageRep->getLanguageById($uid);
	}

	function _getContent($table,$uid) {
		if(!$this->_ceImport) {
			$this->_ceImport=true;
			$this->importDataSet(dirname(__FILE__). '/fixtures/dbContentWithVisibilityTestdata.xml');
		}
		$dao=new tx_languagevisibility_daocommon;

		$factory=new tx_languagevisibility_elementFactory($dao);
		return $factory->getElementForTable($table,$uid);
	}

	public function setUp() {
		parent::setUp();
		$this->_loadWorkspaces();

	}
}