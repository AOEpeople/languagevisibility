<?php



class tx_languagevisibility_daocommon {	
	
	
	function getRecord($uid,$table) {
		// fix settings
    $fields = '*';
    $table = $table;
    $groupBy = null;
    $orderBy = '';
    $where = 'uid='.intval($uid);
    
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, $groupBy, $orderBy);
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
    return $row;
		
	}
	
	
}

?>