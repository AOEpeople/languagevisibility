<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003, 2004 Kasper Skaarhoj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class ux_tx_templavoila_pi1 extends tx_templavoila_pi1 {


	/**
	 * Common function for rendering of the Flexible Content / Page Templates.
	 * For Page Templates the input row may be manipulated to contain the proper reference to a data structure (pages can have those inherited which content elements cannot).
	 *
	 * @param	array		Current data record, either a tt_content element or page record.
	 * @param	string		Table name, either "pages" or "tt_content".
	 * @return	string		HTML output.
	 */
	function renderElement($row,$table)	{
		global $TYPO3_CONF_VARS;

			// First prepare user defined objects (if any) for hooks which extend this function:
		$hookObjectsArr = array();
		if (is_array ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['pi1']['renderElementClass'])) {
			foreach ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['pi1']['renderElementClass'] as $classRef) {
				$hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
			}
		}

			// Hook: renderElement_preProcessRow
		foreach($hookObjectsArr as $hookObj)	{
			if (method_exists ($hookObj, 'renderElement_preProcessRow')) {
				$hookObj->renderElement_preProcessRow($row, $table, $this);
			}
		}

			// Get data structure:
		$srcPointer = $row['tx_templavoila_ds'];
		if (t3lib_div::testInt($srcPointer))	{	// If integer, then its a record we will look up:
			$DSrec = $GLOBALS['TSFE']->sys_page->checkRecord('tx_templavoila_datastructure', $srcPointer);
			$DS = t3lib_div::xml2array($DSrec['dataprot']);
		} else {	// Otherwise expect it to be a file:
			$file = t3lib_div::getFileAbsFileName($srcPointer);
			if ($file && @is_file($file))	{
				$DS = t3lib_div::xml2array(t3lib_div::getUrl($file));
			}
		}

			// If a Data Structure was found:
		if (is_array($DS))	{

				// Sheet Selector:
			if ($DS['meta']['sheetSelector'])	{
					// <meta><sheetSelector> could be something like "EXT:user_extension/class.user_extension_selectsheet.php:&amp;user_extension_selectsheet"
				$sheetSelector = &t3lib_div::getUserObj($DS['meta']['sheetSelector']);
				$renderSheet = $sheetSelector->selectSheet();
			} else {
				$renderSheet = 'sDEF';
			}

				// Initialize:
			$langChildren = $DS['meta']['langChildren'] ? 1 : 0;
			$langDisabled = $DS['meta']['langDisable'] ? 1 : 0;
			list ($dataStruct, $sheet, $singleSheet) = t3lib_div::resolveSheetDefInDS($DS,$renderSheet);

				// Data from FlexForm field:
			$data = t3lib_div::xml2array($row['tx_templavoila_flex']);

			$lKey = ($GLOBALS['TSFE']->sys_language_isocode && !$langDisabled && !$langChildren) ? 'l'.$GLOBALS['TSFE']->sys_language_isocode : 'lDEF';

				/* AOE modification not needed for TV > 1.4.2 */
			foreach($hookObjectsArr as $hookObj)	{
				if (method_exists ($hookObj, 'renderElement_preProcessLanguageKey')) {
					$lKey = $hookObj->renderElement_preProcessLanguageKey($row, $table, $lKey, $langDisabled, $langChildren, $this);
				}
			}

			$dataValues = is_array($data['data']) ? $data['data'][$sheet][$lKey] : '';
			if (!is_array($dataValues))	$dataValues = array();

				// Init mark up object.
			$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
			$this->markupObj->htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');

				// Get template record:
			if ($row['tx_templavoila_to'])	{

					// Initialize rendering type:
				if ($this->conf['childTemplate'])	{
					$renderType = $this->conf['childTemplate'];
					if (substr($renderType, 0, 9) == 'USERFUNC:') {
						$conf = array(
							'conf' => is_array($this->conf['childTemplate.']) ? $this->conf['childTemplate.'] : array(),
							'toRecord' => $row
						);
						$renderType = t3lib_div::callUserFunction(substr($renderType, 9), $conf, $this);
					}
				} else {	// Default:
					$renderType = t3lib_div::_GP('print') ? 'print' : '';
				}

					// Get Template Object record:
				$TOrec = $this->markupObj->getTemplateRecord($row['tx_templavoila_to'], $renderType, $GLOBALS['TSFE']->sys_language_uid);
				if (is_array($TOrec))	{

						// Get mapping information from Template Record:
					$TO = unserialize($TOrec['templatemapping']);
					if (is_array($TO))	{

							// Get local processing:
						$TOproc = array();
						if ($TOrec['localprocessing']) {
							$TOproc = t3lib_div::xml2array($TOrec['localprocessing']);
							if (!is_array($TOproc))	{
								// Must be a error!
								// TODO log to TT the content of $TOproc (it is a error message now)
								$TOproc = array();
							}
						}
							// Processing the data array:
						if ($GLOBALS['TT']->LR) $GLOBALS['TT']->push('Processing data');
							$vKey = ($GLOBALS['TSFE']->sys_language_isocode && !$langDisabled && $langChildren) ? 'v'.$GLOBALS['TSFE']->sys_language_isocode : 'vDEF';

								/* AOE modification not needed for TV > 1.4.2 */
							foreach($hookObjectsArr as $hookObj)	{
								if (method_exists ($hookObj, 'renderElement_preProcessValueKey')) {
									$vKey = $hookObj->renderElement_preProcessValueKey($row, $table, $vKey, $langDisabled, $langChildren, $this);
								}
							}

							$TOlocalProc = $singleSheet ? $TOproc['ROOT']['el'] : $TOproc['sheets'][$sheet]['ROOT']['el'];
								// Store the original data values before the get processed.
							$originalDataValues = $dataValues;
							$this->processDataValues($dataValues,$dataStruct['ROOT']['el'],$TOlocalProc,$vKey);

								// Hook: renderElement_postProcessDataValues
							foreach ($hookObjectsArr as $hookObj) {
								if (method_exists($hookObj, 'renderElement_postProcessDataValues')) {
									$flexformData = array(
										'table' => $table,
										'row'   => $row,
										'sheet' => $renderSheet,
										'sLang' => $lKey,
										'vLang' => $vKey
									);
									$hookObj->renderElement_postProcessDataValues($DS, $dataValues, $originalDataValues, $flexformData);
								}
							}

						if ($GLOBALS['TT']->LR) $GLOBALS['TT']->pull();

							// Merge the processed data into the cached template structure:
						if ($GLOBALS['TT']->LR) $GLOBALS['TT']->push('Merge data and TO');
								// Getting the cached mapping data out (if sheets, then default to "sDEF" if no mapping exists for the specified sheet!)
							$mappingDataBody = $singleSheet ? $TO['MappingData_cached'] : (is_array($TO['MappingData_cached']['sub'][$sheet]) ? $TO['MappingData_cached']['sub'][$sheet] : $TO['MappingData_cached']['sub']['sDEF']);
							$content = $this->markupObj->mergeFormDataIntoTemplateStructure($dataValues,$mappingDataBody,'',$vKey);
							$this->markupObj->setHeaderBodyParts($TO['MappingInfo_head'],$TO['MappingData_head_cached'],$TO['BodyTag_cached']);
						if ($GLOBALS['TT']->LR) $GLOBALS['TT']->pull();

							// Edit icon (frontend editing):
						$eIconf = array('styleAttribute'=>'position:absolute;');
						if ($table=='pages')	$eIconf['beforeLastTag']=-1;	// For "pages", set icon in top, not after.
						$content = $this->pi_getEditIcon($content,'tx_templavoila_flex','Edit element',$row,$table,$eIconf);

							// Visual identification aids:
						if ($GLOBALS['TSFE']->fePreview && $GLOBALS['TSFE']->beUserLogin && !$GLOBALS['TSFE']->workspacePreview && !$this->conf['disableExplosivePreview'])	{
							$content = $this->visualID($content,$srcPointer,$DSrec,$TOrec,$row,$table);
						}
					} else {
						$content = $this->formatError('Template Object could not be unserialized successfully.
							Are you sure you saved mapping information into Template Object with UID "'.$row['tx_templavoila_to'].'"?');
					}
				} else {
					$content = $this->formatError('Couldn\'t find Template Object with UID "'.$row['tx_templavoila_to'].'".
						Please make sure a Template Object is accessible.');
				}
			} else {
				$content = $this->formatError('You haven\'t selected a Template Object yet for table/uid "'.$table.'/'.$row['uid'].'".
					Without a Template Object TemplaVoila cannot map the XML content into HTML.
					Please select a Template Object now.');
			}
		} else {
			$content = $this->formatError('
				Couldn\'t find a Data Structure set for table/row "'.$table.':'.$row['uid'].'".
				Please select a Data Structure and Template Object first.');
		}

		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/pi1/class.ux_tx_templavoila_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/pi1/class.ux_tx_templavoila_pi1.php']);
}
?>