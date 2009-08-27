<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_element.php');

class tx_languagevisibility_recordelement extends tx_languagevisibility_element {

	/**
	 * Variable to store the tablename of the record element. 
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Method to set the tablename of the recordelement.
	 *
	 * @param string $table
	 */
	function setTable($table) {
		$this->table=$table;
	}
	
	
	/**
	 * Method to get the tablename
	 *
	 * @return string
	 */
	public function getTable(){
		return $this->table;
	}

	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getElementDescription(){
		return 'TYPO3-Record';
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
			$olrow = $this->getContextIndependentWorkspaceOverlay($table,$olrow);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			
			return 	$olrow;
	}


	/**
	 * This method is used to check if this element has any translation in any workspace.
	 *
	 * @return boolean
	 */
	function hasOverLayRecordForAnyLanguageInAnyWorkspace(){
		global $TCA;
		$table=$this->table;

		if($this->isOrigElement()){
			//get live record of workspace record
			$row=$this->_getLiveRowIfWorkspace($this->row,$table);
			$fields = 'count(*) as ANZ';

			$where = 'deleted = 0 AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.$row['uid'];
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$table,$where);

			return ($res[0]['ANZ'] > 0);
		}else{
			//if this is a translation is clear that an overlay must exist
			return true;
		}
	}

	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrderElement();
	}

}

?>