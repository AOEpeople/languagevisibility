<?php

require_once t3lib_extMgm::extPath('languagevisibility') . 'classes/class.tx_languagevisibility_cacheManager.php';

class tx_languagevisibility_daocommon {

	protected static $recordCache;
	
	protected static  $cacheLimit = 2000;
	
	/**
	 * Returns a record by table and uid.
	 *
	 * @param $uid
	 * @param $table
	 * @return array
	 */
	public static function getRecord($uid, $table) {
		$cacheManager 		= tx_languagevisibility_cacheManager::getInstance();
		
		$isCacheEnabled		= $cacheManager->isCacheEnabled();
		self::$recordCache 	= $cacheManager->get('daoRecordCache');		
	
		if($isCacheEnabled){
			if(!isset(self::$recordCache[$table][$uid])){
					//!TODO we're still running two queries - this can be reduced to one with a tricky search criteria
				$row = self::getRequestedRecord($uid,$table);

				if($row){
					self::$recordCache[$table][$uid] = $row;
					if(count(self::$recordCache) < self::$cacheLimit){
						self::loadSimilarRecordsIntoCache($row,$table);
					}
				}
			}
			
			$cacheManager->set('daoRecordCache',self::$recordCache);
			
			$result = self::$recordCache[$table][$uid];
		}else{
			$result = self::getRequestedRecord($uid,$table);
		}

		return $result;
	}

	/**
	 * Returns the single Requested Record
	 *
	 * @param $uid
	 * @param $table
	 * @return array
	 */
	protected static function getRequestedRecord($uid,$table){
		// fix settings
		$fields = '*';
		$table = $table;
		$groupBy = null;
		$orderBy = '';
		$where = 'uid=' . intval ( $uid );

		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $fields, $table, $where, $groupBy, $orderBy );
		$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result );
		$GLOBALS['TYPO3_DB']-> sql_free_result($result);

		return $row;
	}

	/**
	 * Method trys to load similar records into the cache which will maybe requested in the future.
	 * Requires more memory usage, but reduces the amount of querys.
	 *
	 * @param $row
	 * @param $table
	 * @return void
	 */
	protected function loadSimilarRecordsIntoCache($row,$table){		
		
		if($row['pid'] > 0){
			$fields 		= '*';
			$tablename 		= $table;
			$orderBy 		= '';
			$groupBy		= null;
	
			$uidsInCache 	= implode(',',array_keys(self::$recordCache[$table]));
			//get deleted hidden and workspace field from tca
	
			global $TCA;
			t3lib_div::loadTCA($table);
	
			if(is_array($TCA[$table]['ctrl'])){
				$deleteField = $TCA[$table]['ctrl']['delete'];
			}
	
			$where 	= 'uid !='.$row['uid'].' AND pid = '.$row['pid'].' AND uid NOT IN ('.$uidsInCache.')';
			$where	.= array_key_exists('hidden', $row)?' AND hidden=0':'';
					
			if($deleteField != ''){
				$where .= ' AND '.$deleteField.'=0';
			}
	
			$limit 			= 500;
			$result 		= $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $fields, $tablename, $where, $groupBy, $orderBy, $limit );
	
			while($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result )){
				self::$recordCache[$table][$row['uid']] = $row;
			}
	
			$GLOBALS['TYPO3_DB']-> sql_free_result($result);
		}
	}
}

?>