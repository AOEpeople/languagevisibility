<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_element.php');

class tx_languagevisibility_recordelement extends tx_languagevisibility_element {

	/**
	 * Returns a formal description of the record element.
	 *
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_element#getElementDescription()
	 * @return string
	 */
	public function getElementDescription(){
		return 'TYPO3-Record';
	}


	/**
	 * This method is the implementation of an abstract parent method.
	 * The method should return the overlay record for a certain language.
	 *
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_element#getOverLayRecordForCertainLanguageImplementation($languageId)
	 */
	protected function getOverLayRecordForCertainLanguageImplementation($languageId) {
		global $TCA;

		$table		= $this->table;
		$uid 		= $this->row['uid'];
		$workspace	= intval($GLOBALS['BE_USER']->workspace);
		//actual row in live WS

		$row=$this->_getLiveRowIfWorkspace($this->row,$table);
		if ($row===false) {
			$result = false;
		}else{

			$useUid=$row['uid'];
			$usePid=$row['pid'];

			if ($workspace==0) {
				// Shadow state for new items MUST be ignored	in workspace
				$addWhere=' AND t3ver_state!=1 AND pid > 0 AND t3ver_wsid=0';
			}
			else {
				//else search get workspace version
				$addWhere=' AND (t3ver_wsid=0 OR t3ver_wsid='.$workspace.')';
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

			$result = $olrow;
		}

		return $result;
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

	/**
	 * Returns the fallback order of an record element.
	 *
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_element#getFallbackOrder($language)
	 */
	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrderElement();
	}
}
?>