<?php


require_once(PATH_t3lib.'class.t3lib_page.php');

class tx_languagevisibility_elementFactory {
	
	var $dao;
	
	/**
	*	Dependency is injected, this object needs a simple Data Access Object (can be replaced in testcase)
	*/
	function tx_languagevisibility_elementFactory($dao) {
		$this->dao=$dao;		
	}
	
	/**
	* Returns ready initialised "element" object. Depending on the element the correct element class is used. (e.g. page/content/fce)
	*
	* @param $table	table
	* @param $uid	identifier
	* @param $overlay_ids boolean parameter to overlay uids if the user is in workspace context 
	*
	* @throws Unknown_Element_Exception 
	**/	
	function getElementForTable($table,$uid,$overlay_ids = true) {
		/*	
		echo $uid.'-';
		echo $uid=t3lib_BEfunc::wsMapId($table,$uid);
		echo '<hr>';		
		*/
		if (!is_numeric($uid)) {
			//no uid => maybe NEW element in BE
			$row=array();
		}
		else {				
			/***
			** WORKSPACE NOTE
			* the diffrent usecases has to be defined and checked..
			**/
			if (is_object($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->workspace !=0 && $overlay_ids) {				
				
				$row=$this->dao->getRecord($uid,$table);						
				if ($row['pid']!=-1) {
					$uid=t3lib_BEfunc::wsMapId($table,$uid);
					$row=$this->dao->getRecord($uid,$table);							
				}
			}
			else {
				$row=$this->dao->getRecord($uid,$table);
			}
		}

		//@todo isSupported table
		
		switch ($table) {
				case 'pages':
					require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_pageelement.php');
					$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_pageelement');					
					$element=new $elementclass($row);
					return $element;
				break;
				case 'tt_news':
					require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_ttnewselement.php');
					$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_ttnewselement');					
					$element=new $elementclass($row);

					return $element;
				break;
				case 'tt_content':					
					if ($row['CType']=='templavoila_pi1') {
						//read DS:
						$srcPointer = $row['tx_templavoila_ds'];
						$DS=$this->_getTVDS($srcPointer);
						if (is_array($DS)) {
							if ($DS['meta']['langDisable']==1 && $DS['meta']['langDatabaseOverlay']==1) {
								//handle as special FCE with normal tt_content overlay:
								require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_fceoverlayelement.php');
								$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_fceoverlayelement');					
								$element=new $elementclass($row);
							}
							else {
								require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_fceelement.php');
								$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_fceelement');
								$element=new $elementclass($row,$DS);
							}
						}
						else {
							throw new UnexpectedValueException($table.' uid:'.$row['uid'].' has no valid Datastructure ', 1195039394);
						}
					}
					else {
						require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_celement.php');
						$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_celement');					
						$element=new $elementclass($row);
					}					
					return $element;
					
				break;
				default: 
					throw new UnexpectedValueException($table.' not supported ', 1195039394);
				break;
		}
	}
	
	/**
	 * This method is used to retrieve all parent elements (an parent elements needs to
	 * have the flag 'tx_languagevisibility_inheritanceflag_original' or
	 * needs to be orverlayed with a record, that has the field 'tx_languagevisibility_inheritanceflag_overlayed'
	 * configured 
	 * 
	 * @param tx_languagevisibility_element $element
	 * @return array $elements (collection of tx_languagevisibility_element)
	 */
	public function getParentElementsFromElement(tx_languagevisibility_element $element,$language){
		$elements = array();

		if($element instanceof tx_languagevisibility_pageelement){
			/* @var $sys_page t3lib_pageSelect */
			$rootline = $this->getOverlayedRootLine($element->getUid(),$language->getUid());
			
			if(is_array($rootline)){
				foreach($rootline as $rootlineElement){			
					if(	$rootlineElement['tx_languagevisibility_inheritanceflag_original'] == 1 ||
						$rootlineElement['tx_languagevisibility_inheritanceflag_overlayed'] == 1){
						$elements[] = self::getElementForTable('pages',$rootlineElement['uid']);
					}
				}
			}
		}	
				
		return $elements;
	}	
	
	/**
	 * This method is needed because the getRootline method from t3lib_pageSelect causes an error when
	 * getRootline is called be cause getRootline internally uses languagevisibility to determine the
	 * visibility during the rootline calculation. This results in an unlimited recursion.
	 * 
	 * @param	integer		The page uid for which to seek back to the page tree root.
	 * @see tslib_fe::getPageAndRootline()
	 */
	function getOverlayedRootLine($uid,$languageid) {
		$sys_page=t3lib_div::makeInstance('t3lib_pageSelect');
		$sys_page->sys_language_uid = $languageid;
		
		$uid = intval($uid);

			// Initialize:
		$selFields = t3lib_div::uniqueList('pid,uid,t3ver_oid,t3ver_wsid,t3ver_state,t3ver_swapmode,title,alias,nav_title,media,layout,hidden,starttime,endtime,fe_group,extendToSubpages,doktype,TSconfig,storage_pid,is_siteroot,mount_pid,mount_pid_ol,fe_login_mode,'.$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);

		$loopCheck = 0;
		$theRowArray = Array();

		while ($uid!=0 && $loopCheck<20)	{	// Max 20 levels in the page tree.
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selFields, 'pages', 'uid='.intval($uid).' AND pages.deleted=0 AND pages.doktype!=255');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			if ($row)	{
				$sys_page->versionOL('pages',$row, FALSE, TRUE);
				$sys_page->fixVersioningPid('pages',$row);
	
				if (is_array($row))	{
					// Mount Point page types are allowed ONLY a) if they are the outermost record in rootline and b) if the overlay flag is not set:
					$uid = $row['pid'];	// Next uid
				}
					// Add row to rootline with language overlaid:

				$theRowArray[] = $sys_page->_original_getPageOverlay($row,$languageid);
			} else {
				return array();	// broken rootline.
			}

			$loopCheck++;
		}

			// Create output array (with reversed order of numeric keys):
		$output = Array();
		$c = count($theRowArray);
		foreach($theRowArray as $key => $val)	{
			$c--;
			$output[$c] = $val;
		}

		return $output;	
	}		
	
	protected function _getTVDS($srcPointer) {
	
		$sys_page=t3lib_div::makeInstance('t3lib_pageSelect');		
		$DS=array();
		if (t3lib_div::testInt($srcPointer))	{	// If integer, then its a record we will look up:
			$DSrec=$sys_page->getRawRecord('tx_templavoila_datastructure',$srcPointer,'dataprot');			
			$DS = t3lib_div::xml2array($DSrec['dataprot']);			
		} else {	// Otherwise expect it to be a file:
			$file = t3lib_div::getFileAbsFileName($srcPointer);
			if ($file && @is_file($file))	{
				$DS = t3lib_div::xml2array(t3lib_div::getUrl($file));
			}
		}
		return $DS;
		
	}	
}

?>