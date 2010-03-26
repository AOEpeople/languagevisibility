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
	 * @param	boolean		Set, if testing a deleted record array.
	 * @param	boolean		Set, whenever access to all translations of the record is required
	 * @return	boolean		True if OK, otherwise false
	 */
	function recordEditAccessInternals($table, $idOrRow, $newRecord = FALSE, $deletedRecord = FALSE, $checkFullLanguageAccess = FALSE) {
		global $TCA;

		if (isset($TCA[$table]))	{
			t3lib_div::loadTCA($table);

				// Always return true for Admin users.
			if ($this->isAdmin())	return TRUE;

				// Fetching the record if the $idOrRow variable was not an array on input:
			if (!is_array($idOrRow))	{
				if ($deletedRecord) {
					$idOrRow = t3lib_BEfunc::getRecord($table, $idOrRow, '*', '', FALSE);
				} else {
					$idOrRow = t3lib_BEfunc::getRecord($table, $idOrRow);
				}
				if (!is_array($idOrRow))	{
					$this->errorMsg = 'ERROR: Record could not be fetched.';
					return FALSE;
				}
			}

				// Checking languages:
			if ($TCA[$table]['ctrl']['languageField'])	{
				if (isset($idOrRow[$TCA[$table]['ctrl']['languageField']]))	{	// Language field must be found in input row - otherwise it does not make sense.
					if (!$this->checkLanguageAccess($idOrRow[$TCA[$table]['ctrl']['languageField']]))	{
							//original content of this block
						//$this->errorMsg = 'ERROR: Language was not allowed.';
						//return FALSE;

							//modifed content of this block ----------------------------------- begin
							//TODO this needs to be moved into some kind of hook
						$skipLanguageErrorMessage=FALSE;
							//danielp allow default language for creating new elements as well as editing if languagevisibility allows it

						if ($idOrRow[$TCA[$table]['ctrl']['languageField']] == 0) {
							$editingIsAllowed=FALSE;


							$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
							if ($visibilityservice->hasUserAccessToEditRecord($table,$idOrRow['uid'])) {
								$editingIsAllowed=TRUE;
							}
							if ($newRecord OR $editingIsAllowed) {
								$skipLanguageErrorMessage=TRUE;
							}
						}
						if (!$skipLanguageErrorMessage) {
							$this->errorMsg = 'ERROR: Language ( ' . $idOrRow[$TCA[$table]['ctrl']['languageField']] . ') was not allowed.';
							return FALSE;
						}

							//modifed content of this block ----------------------------------- end

					} elseif ($checkFullLanguageAccess && $idOrRow[$TCA[$table]['ctrl']['languageField']]==0 && !$this->checkFullLanguagesAccess($table, $idOrRow)) {
						$this->errorMsg = 'ERROR: Related/affected language was not allowed.';
						return FALSE;
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


	/**
	 * Check if user has access to all existing localizations for a certain record
	 *
	 * @param string 	the table
	 * @param array 	the current record
	 * @return boolean
	 */
	function checkFullLanguagesAccess($table, $record) {
		$recordLocalizationAccess = $this->checkLanguageAccess(0);
		if ($recordLocalizationAccess && t3lib_BEfunc::isTableLocalizable($table)) {

			$pointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];

			$recordLocalizations = t3lib_BEfunc::getRecordsByField(
				$table,
				$pointerField,
				$record[$pointerField] > 0 ? $record[$pointerField] : $record['uid'],
				'',
				'',
				'',
				'1'
			);

			if (is_array($recordLocalizations)) {
				foreach($recordLocalizations as $localization) {
					$recordLocalizationAccess = $recordLocalizationAccess && $this->checkLanguageAccess($localization[$GLOBALS['TCA'][$table]['ctrl']['languageField']]);
					if (!$recordLocalizationAccess) {
						break;
					}
				}
			}
		}


		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess']))	{
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['checkFullLanguagesAccess'] as $_funcRef)	{
				$_params = array(
					'table' => $table,
					'row' => $record,
					'recordLocalizationAccess' => $recordLocalizationAccess
				);
				$recordLocalizationAccess = t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
		return $recordLocalizationAccess;
	}
}