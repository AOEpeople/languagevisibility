<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_element.php');



class tx_languagevisibility_pageelement extends tx_languagevisibility_element {

	function isOrigElement() {
		return false;
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

}

?>