<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_element.php');


class tx_languagevisibility_pageelement extends tx_languagevisibility_element {

	function isOrigElement() {
	/*	if(is_array($this->row)){
			$is_overlay =  array_key_exists('l18n_diffsource',$this->row);
			
			return !$is_overlay;
		}else{
			
			return true;
		}*/
		return true;
	}
	
	protected function getTable(){
		return 'pages';	
	}
	
	function isLanguageSetToDefault() {
		return  true;
	}
		
	function getInformativeDescription() {
		return 'this is a normal page element (translations are managed with seperate overlay records)';
	}

	function getOverLayRecordForCertainLanguage($id,$onlyUid=FALSE) {
		$row=$this->row;
		$useUid=$row['uid'];


		if ($row['pid']==-1) {
			//	$liveRow=t3lib_BEfunc::getLiveVersionOfRecord('pages',$useUid);
				//print_r($row);
				$useUid=$row['t3ver_oid'];
		}
		if ($GLOBALS['BE_USER']->workspace==0) {
				$addWhere=' AND t3ver_state!=1'; //// Shadow state for new items MUST be ignored
			}
		$where='deleted = 0 AND hidden = 0 AND sys_language_uid='.intval($id).' AND pid='.intval($useUid).	$addWhere;
		$fields='*';
		if ($onlyUid)
			$fields='uid';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'pages_language_overlay', $where, '','');
		//echo $GLOBALS['TYPO3_DB']->SELECTquery($fields, 'pages_language_overlay', $where, '','');
		
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		return $row;
	}


	/**
	 *returns which field in the language should be used to read the default visibility
	 *
	 *@return string (blank=default / page=page)
	 **/
	function getFieldToUseForDefaultVisibility() {
		return 'page';
	}
	
	function hasOverLayRecordForAnyLanguageInAnyWorkspace(){
		
		//if we handle a workspace record, we need to get it's live version
		if ($this->row ['pid'] == - 1) {
			$useUid = $this->row ['t3ver_oid'];
		}else{
			$useUid = $this->row['uid'];
		}
				
		// if a workspace record has an overlay, an overlay also exists in the livews with versionstate = 1
		// therefore we have to look for any undeleted overlays of the live version 		
		$fields = 'count(*) as anz';
		$table = 'pages_language_overlay';
		$where = 'deleted = 0 AND pid='.$useUid;

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$table,$where);				
		$anz = $rows[0]['anz'];
		
		return ($anz > 0);
	}


}

?>