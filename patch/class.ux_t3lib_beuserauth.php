<?php

class ux_t3lib_beUserAuth extends t3lib_beUserAuth {


	/**
	 * Checking if a user has editing access to a record from a $TCA table.
	 * The checks does not take page permissions and other "environmental" things into account. It only deal with record internals; If any values in the record fields disallows it.
	 * For instance languages settings, authMode selector boxes are evaluated (and maybe more in the future).
	 * It will check for workspace dependent access.
	 * The function takes an ID (integer) or row (array) as second argument.
	 *
	 * @param	string		Table name
	 * @param	mixed		If integer, then this is the ID of the record. If Array this just represents fields in the record.
	 * @param	boolean		Set, if testing a new (non-existing) record array. Will disable certain checks that doesn't make much sense in that context.
	 * @return	boolean		True if OK, otherwise false
	 */
	function recordEditAccessInternals($table,$idOrRow,$newRecord=FALSE)	{
		global $TCA;

		if (isset($TCA[$table]))	{
			t3lib_div::loadTCA($table);

				// Always return true for Admin users.
			if ($this->isAdmin())	return TRUE;

				// Fetching the record if the $idOrRow variable was not an array on input:
			if (!is_array($idOrRow))	{
				$idOrRow = t3lib_BEfunc::getRecord($table, $idOrRow);
				if (!is_array($idOrRow))	{
					$this->errorMsg = 'ERROR: Record could not be fetched.';
					return FALSE;
				}
			}

				// Checking languages:
			if ($TCA[$table]['ctrl']['languageField'])	{
				
				if (isset($idOrRow[$TCA[$table]['ctrl']['languageField']]))	{	// Language field must be found in input row - otherwise it does not make sense.					
					$skipLanguageErrorMessage=FALSE;
					//danielp allow default language for creating new elements as well as editing if languagevisibility allows it
					if (!$this->checkLanguageAccess($idOrRow[$TCA[$table]['ctrl']['languageField']])) {
						if ($idOrRow[$TCA[$table]['ctrl']['languageField']] ==0) {		
												
							$editingIsAllowed=FALSE;
							require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');	
							$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');							
							if ($visibilityservice->hasUserAccessToEditRecord($table,$idOrRow['uid'])) {
								$editingIsAllowed=TRUE;
								
							}												
							if ($newRecord OR $editingIsAllowed) {
								$skipLanguageErrorMessage=TRUE;
							
							}
						}
						if (!$skipLanguageErrorMessage) {
							$this->errorMsg = 'ERROR: Language was not allowed.';
							return FALSE;
						}
					}
				} else {
					$this->errorMsg = 'ERROR: The "languageField" field named "'.$TCA[$table]['ctrl']['languageField'].'" was not found in testing record!';
					return FALSE;
				}
			}

				// Checking authMode fields:
			if (is_array($TCA[$table]['columns']))	{
				foreach($TCA[$table]['columns'] as $fN => $fV)	{
					if (isset($idOrRow[$fN]))	{	//
						if ($fV['config']['type']=='select' && $fV['config']['authMode'] && !strcmp($fV['config']['authMode_enforce'],'strict')) {
							if (!$this->checkAuthMode($table,$fN,$idOrRow[$fN],$fV['config']['authMode']))	{
								$this->errorMsg = 'ERROR: authMode "'.$fV['config']['authMode'].'" failed for field "'.$fN.'" with value "'.$idOrRow[$fN].'" evaluated';
								return FALSE;
							}
						}
					}
				}
			}

				// Checking "editlock" feature (doesn't apply to new records)
			if (!$newRecord && $TCA[$table]['ctrl']['editlock'])	{
				if (isset($idOrRow[$TCA[$table]['ctrl']['editlock']]))	{
					if ($idOrRow[$TCA[$table]['ctrl']['editlock']])	{
						$this->errorMsg = 'ERROR: Record was locked for editing. Only admin users can change this state.';
						return FALSE;
					}
				} else {
					$this->errorMsg = 'ERROR: The "editLock" field named "'.$TCA[$table]['ctrl']['editlock'].'" was not found in testing record!';
					return FALSE;
				}
			}

				// Checking record permissions
			// THIS is where we can include a check for "perms_" fields for other records than pages...

				// Process any hooks
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['recordEditAccessInternals']))	{
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['recordEditAccessInternals'] as $funcRef)	{
					$params = array(
						'table' => $table,
						'idOrRow' => $idOrRow,
						'newRecord' => $newRecord
					);
					if (!t3lib_div::callUserFunction($funcRef, $params, $this)) {
						return FALSE;
					}
				}
			}

				// Finally, return true if all is well.
			return TRUE;
		}
	}
}


?>
