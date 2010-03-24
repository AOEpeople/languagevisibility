<?php

/*
 adds patch to disallow page editing without access to default language
*/
class ux_SC_alt_doc extends SC_alt_doc {


	/***************************
	 *
	 * Sub-content functions, rendering specific parts of the module content.
	 *
	 ***************************/

	/**
	 * Creates the editing form with TCEforms, based on the input from GPvars.
	 *
	 * @return	string		HTML form elements wrapped in tables
	 */
	function makeEditForm()	{
		global $BE_USER,$LANG,$TCA;

			// Initialize variables:
		$this->elementsData=array();
		$this->errorC=0;
		$this->newC=0;
		$thePrevUid='';
		$editForm='';

			// Traverse the GPvar edit array
		foreach($this->editconf as $table => $conf)	{	// Tables:
			if (is_array($conf) && $TCA[$table] && $BE_USER->check('tables_modify',$table))	{

					// Traverse the keys/comments of each table (keys can be a commalist of uids)
				foreach($conf as $cKey => $cmd)	{
					if ($cmd=='edit' || $cmd=='new')	{

							// Get the ids:
						$ids = t3lib_div::trimExplode(',',$cKey,1);

							// Traverse the ids:
						foreach($ids as $theUid)	{

								// Checking if the user has permissions? (Only working as a precaution, because the final permission check is always down in TCE. But it's good to notify the user on beforehand...)
								// First, resetting flags.
							$hasAccess = 1;
							$deniedAccessReason = '';
							$deleteAccess = 0;
							$this->viewId = 0;

								// If the command is to create a NEW record...:
							if ($cmd=='new')	{
								if (intval($theUid))	{		// NOTICE: the id values in this case points to the page uid onto which the record should be create OR (if the id is negativ) to a record from the same table AFTER which to create the record.

										// Find parent page on which the new record reside
									if ($theUid<0)	{	// Less than zero - find parent page
										$calcPRec=t3lib_BEfunc::getRecord($table,abs($theUid));
										$calcPRec=t3lib_BEfunc::getRecord('pages',$calcPRec['pid']);
									} else {	// always a page
										$calcPRec=t3lib_BEfunc::getRecord('pages',abs($theUid));
									}

										// Now, calculate whether the user has access to creating new records on this position:
									if (is_array($calcPRec))	{
										$CALC_PERMS = $BE_USER->calcPerms($calcPRec);	// Permissions for the parent page
										if ($table=='pages')	{	// If pages:
											$hasAccess = $CALC_PERMS&8 ? 1 : 0;
											$this->viewId = $calcPRec['pid'];
										} else {
											$hasAccess = $CALC_PERMS&16 ? 1 : 0;
											$this->viewId = $calcPRec['uid'];
										}
									}
								}
								$this->dontStoreDocumentRef=1;		// Don't save this document title in the document selector if the document is new.
							} else {	// Edit:
								$calcPRec = t3lib_BEfunc::getRecord($table,$theUid);
								t3lib_BEfunc::fixVersioningPid($table,$calcPRec);
								if (is_array($calcPRec))	{
									if ($table=='pages')	{	// If pages:
										$CALC_PERMS = $BE_USER->calcPerms($calcPRec);
										$hasAccess = $CALC_PERMS&2 ? 1 : 0;
										$deleteAccess = $CALC_PERMS&4 ? 1 : 0;
										$this->viewId = $calcPRec['uid'];
									} else {
										$CALC_PERMS = $BE_USER->calcPerms(t3lib_BEfunc::getRecord('pages',$calcPRec['pid']));	// Fetching pid-record first.
										$hasAccess = $CALC_PERMS&16 ? 1 : 0;
										$deleteAccess = $CALC_PERMS&16 ? 1 : 0;
										$this->viewId = $calcPRec['pid'];

											// Adding "&L=xx" if the record being edited has a languageField with a value larger than zero!
										if ($TCA[$table]['ctrl']['languageField'] && $calcPRec[$TCA[$table]['ctrl']['languageField']]>0)	{
											$this->viewId_addParams = '&L='.$calcPRec[$TCA[$table]['ctrl']['languageField']];
										}
									}

										// Check internals regarding access:
									if ($hasAccess)	{										
										$hasAccess = $BE_USER->recordEditAccessInternals($table, $calcPRec);
										$deniedAccessReason = $BE_USER->errorMsg;
									}
								} else $hasAccess = 0;
							}

							// AT THIS POINT we have checked the access status of the editing/creation of records and we can now proceed with creating the form elements:
							
							//danielp: additional permission check:
							// if user wants to edit/create page record but has no access to default language!
							if ($table == 'pages' && !$BE_USER->checkLanguageAccess(0)) {
								if (t3lib_extMgm::isLoaded('languagevisibility')) {
									require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');									
									$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
									if (!$visibilityservice->hasUserAccessToPageRecord($theUid,$cmd)) {
										$hasAccess=FALSE;
									}									
								}	
							}

							if ($hasAccess)	{
								$prevPageID = is_object($trData)?$trData->prevPageID:'';
								$trData = t3lib_div::makeInstance('t3lib_transferData');
								$trData->addRawData = TRUE;
								$trData->defVals = $this->defVals;
								$trData->lockRecords=1;
								$trData->disableRTE = $this->MOD_SETTINGS['disableRTE'];
								$trData->prevPageID = $prevPageID;
								$trData->fetchRecord($table,$theUid,$cmd=='new'?'new':'');	// 'new'
								reset($trData->regTableItems_data);
								$rec = current($trData->regTableItems_data);
								$rec['uid'] = $cmd=='new' ? uniqid('NEW') : $theUid;
								if ($cmd=='new')	{
									$rec['pid'] = $theUid=='prev'?$thePrevUid:$theUid;
								}
								$this->elementsData[]=array(
									'table' => $table,
									'uid' => $rec['uid'],
									'pid' => $rec['pid'],
									'cmd' => $cmd,
									'deleteAccess' => $deleteAccess
								);

									// Now, render the form:
								if (is_array($rec))	{

										// Setting visual path / title of form:
									$this->generalPathOfForm = $this->tceforms->getRecordPath($table,$rec);
									if (!$this->storeTitle)	{
										$this->storeTitle = $this->recTitle ? htmlspecialchars($this->recTitle) : t3lib_BEfunc::getRecordTitle($table,$rec,TRUE);
									}

										// Setting variables in TCEforms object:
									$this->tceforms->hiddenFieldList = '';
									$this->tceforms->globalShowHelp = $this->disHelp ? 0 : 1;
									if (is_array($this->overrideVals[$table]))	{
										$this->tceforms->hiddenFieldListArr = array_keys($this->overrideVals[$table]);
									}

										// Register default language labels, if any:
									$this->tceforms->registerDefaultLanguageData($table,$rec);

										// Create form for the record (either specific list of fields or the whole record):
									$panel = '';
									if ($this->columnsOnly)	{
										if(is_array($this->columnsOnly)){
											$panel.= $this->tceforms->getListedFields($table,$rec,$this->columnsOnly[$table]);
										} else {
											$panel.= $this->tceforms->getListedFields($table,$rec,$this->columnsOnly);
										}
									} else {
										$panel.= $this->tceforms->getMainFields($table,$rec);
									}
									$panel = $this->tceforms->wrapTotal($panel,$rec,$table);

										// Setting the pid value for new records:
									if ($cmd=='new')	{
										$panel.= '<input type="hidden" name="data['.$table.']['.$rec['uid'].'][pid]" value="'.$rec['pid'].'" />';
										$this->newC++;
									}

										// Display "is-locked" message:
									if ($lockInfo = t3lib_BEfunc::isRecordLocked($table,$rec['uid']))	{
										$lockIcon = '

											<!--
											 	Warning box:
											-->
											<table border="0" cellpadding="0" cellspacing="0" class="warningbox">
												<tr>
													<td><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/recordlock_warning3.gif','width="17" height="12"').' alt="" /></td>
													<td>'.htmlspecialchars($lockInfo['msg']).'</td>
												</tr>
											</table>
										';
									} else $lockIcon = '';

										// Combine it all:
									$editForm.= $lockIcon.$panel;
								}

								$thePrevUid = $rec['uid'];
							} else {
								$this->errorC++;
								$editForm.=$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.noEditPermission',1).'<br /><br />'.
											($deniedAccessReason ? 'Reason: '.htmlspecialchars($deniedAccessReason).'<br/><br/>' : '');
							}
						}
					}
				}
			}
		}

		return $editForm;
	}

}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_doc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_doc.php']);
}






?>
