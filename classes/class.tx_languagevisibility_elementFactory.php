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
	* @params $table	table
	* @params $uid	identifier
	*
	* @throws Unknown_Element_Exception 
	**/	
	function getElementForTable($table,$uid) {
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
				if (is_object($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->workspace !=0) {				
					//$uid=t3lib_BEfunc::wsMapId($table,$uid);						
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
		
		
		switch ($table) {
				case 'pages':
					require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_pageelement.php');
					$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_pageelement');					
					$element=new $elementclass($row);
					return $element;
				break;
				case 'tt_news':
					require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_recordelement.php');
					$elementclass=t3lib_div::makeInstanceClassName('tx_languagevisibility_recordelement');					
					$element=new $elementclass($row);
					$element->setTable($table);
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
	
	function _getTVDS($srcPointer) {
	
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