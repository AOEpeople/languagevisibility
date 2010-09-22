<?php

/**
 * Public API class for proper handling of content elements and other useful TemplaVoila related functions
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class ux_tx_templavoila_api extends tx_templavoila_api {


	/**
	 * Returns information about localization of traditional content elements (non FCEs).
	 * It will be added to the content tree by getContentTree().
	 *
	 * @param	array		$contentTreeArr: Part of the content tree of the element to create the localization information for.
	 * @param	array		$tt_content_elementRegister: Array of sys_language UIDs with some information as the value
	 * @return	array		Localization information
	 * @access	protected
	 * @see	getContentTree_element()
	 */
	function getContentTree_getLocalizationInfoForElement($contentTreeArr, &$tt_content_elementRegister) {
		global $TYPO3_DB;

		$localizationInfoArr = array();
		if ($contentTreeArr['el']['table']=='tt_content' && $contentTreeArr['el']['sys_language_uid']<=0)	{

				// Finding translations of this record and select overlay record:
			$fakeElementRow = array ('uid' => $contentTreeArr['el']['uid'], 'pid' => $contentTreeArr['el']['pid']);
			t3lib_beFunc::fixVersioningPID('tt_content', $fakeElementRow);

			$res = $TYPO3_DB->exec_SELECTquery(
				'*',
				'tt_content',
				'pid='.$fakeElementRow['pid'].
					' AND sys_language_uid>0'.
					' AND l18n_parent='.intval($contentTreeArr['el']['uid']).
					t3lib_BEfunc::deleteClause('tt_content')
			);

			$attachedLocalizations = array();
			while(TRUE == ($olrow = $TYPO3_DB->sql_fetch_assoc($res)))	{
				t3lib_BEfunc::workspaceOL('tt_content',$olrow);
				if (!isset($attachedLocalizations[$olrow['sys_language_uid']]))	{
					$attachedLocalizations[$olrow['sys_language_uid']] = $olrow['uid'];
				}
			}
			$TYPO3_DB-> sql_free_result($res);
		
			
				// Traverse the available languages of the page (not default and [All])
			if (is_array($this->allSystemWebsiteLanguages) && is_array($this->allSystemWebsiteLanguages['rows'])) {
				foreach(array_keys($this->allSystemWebsiteLanguages['rows']) as $sys_language_uid)	{
					if ($sys_language_uid > 0)	{
						if (isset($attachedLocalizations[$sys_language_uid]))	{
							$localizationInfoArr[$sys_language_uid] = array();
							$localizationInfoArr[$sys_language_uid]['mode'] = 'exists';
							$localizationInfoArr[$sys_language_uid]['localization_uid'] = $attachedLocalizations[$sys_language_uid];

							$tt_content_elementRegister[$attachedLocalizations[$sys_language_uid]]++;
						} elseif ($contentTreeArr['el']['CType']!='templavoila_pi1') {	// Only localize content elements with "Default" langauge set
							if ((int)$contentTreeArr['el']['sys_language_uid']===0)	{
								$localizationInfoArr[$sys_language_uid] = array();
								$localizationInfoArr[$sys_language_uid]['mode'] = 'localize';
							}
						} elseif(!$contentTreeArr['ds_meta']['langDisable'] && ((int)$contentTreeArr['el']['sys_language_uid']===-1 ||
													(int)$contentTreeArr['el']['sys_language_uid']===0))
												{	
							$localizationInfoArr[$sys_language_uid] = array();
							$localizationInfoArr[$sys_language_uid]['mode'] = 'localizedFlexform';
						} else {
							$localizationInfoArr[$sys_language_uid] = array();
							$localizationInfoArr[$sys_language_uid]['mode'] = 'no_localization';
						}
					}
				}
			}
		}
		return $localizationInfoArr;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.ux_tx_templavoila_api.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.ux_tx_templavoila_api.php']);
}

?>