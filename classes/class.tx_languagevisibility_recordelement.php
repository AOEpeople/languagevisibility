<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_element.php');

class tx_languagevisibility_recordelement extends tx_languagevisibility_element {
	
	protected $table;
	
	function setTable($table) {
		$this->table=$table;
	}
	
	
	/** 
	* workspace aware check of overlay records for tt_content
	**/
	function getOverLayRecordForCertainLanguage($languageId,$onlyUid=FALSE) {
			global $TCA;
			$table=$this->table;
			//actual row in live WS			
			
			$row=$this->_getLiveRowIfWorkspace($this->row,$table);
			if ($row===false) {
				return false;
			}						
			
			$useUid=$row['uid'];
			$usePid=$row['pid'];			
			
			if ($GLOBALS['BE_USER']->workspace==0) {
				// Shadow state for new items MUST be ignored	in workspace
				$addWhere=' AND t3ver_state!=1'; 
			}
			else {
				//else search get workspace version
				$addWhere=' AND (t3ver_wsid=0 OR t3ver_wsid='.$GLOBALS['BE_USER']->workspace.')';
			}
			
			// Select overlay record:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					$table,
							$TCA[$table]['ctrl']['languageField'].'='.intval($languageId).
						' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($useUid).
						' AND hidden=0 AND deleted=0'.$addWhere,	 
					'',
					'',
					'1'
				);
				
				$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if (is_object($GLOBALS['TSFE']->sys_page)) {
					$GLOBALS['TSFE']->sys_page->versionOL($table,$olrow);
				}
				else {
					t3lib_BEfunc::workspaceOL($table,$olrow);					
				}	
				return 	$olrow;						
	}	
	
	
}

?>