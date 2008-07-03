<?php

class ux_tx_templavoila_module1 extends tx_templavoila_module1 {
	function init() {
		parent::init();
		$this->translatorMode=FALSE;
	}
	
	
	/**
	 * Renders the display framework of a single sheet. Calls itself recursively
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$languageKey: Language key for the display
	 * @param	string		$sheet: The sheet key of the sheet which should be rendered
	 * @param	array		$parentPointer: Flexform pointer to parent element
	 * @param	array		$parentDsMeta: Meta array from parent DS (passing information about parent containers localization mode)
	 * @return	string		HTML
	 * @access protected
	 * @see	render_framework_singleSheet()
	 */
	function render_framework_singleSheet($contentTreeArr, $languageKey, $sheet, $parentPointer=array(), $parentDsMeta=array()) {
		global $LANG, $TYPO3_CONF_VARS;

		$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;

		$canEditPage = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit');
		$canEditContent = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'editcontent');

		// Prepare the record icon including a content sensitive menu link wrapped around it:
		$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'').' style="text-align: center; vertical-align: middle;" width="18" height="16" border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" />';
		$menuCommands = array();
		if ($GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new')) {
			$menuCommands[] = 'new';
		}
		if ($canEditContent) {
			$menuCommands[] = 'copy,cut,pasteinto,pasteafter,delete';
		}
		$titleBarLeftButtons = $this->translatorMode ? $recordIcon : (count($menuCommands) == 0 ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), implode(',', $menuCommands)));
		unset($menuCommands);

			// Prepare table specific settings:
		switch ($contentTreeArr['el']['table']) {

			case 'pages' :
				$wrap='|';
				$titleBarLeftButtons .= $this->translatorMode || !$canEditPage ? '' : $this->link_edit('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')).'" alt="" style="text-align: center; vertical-align: middle; border:0;" />',$contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
				$titleBarRightButtons = '';

				$addGetVars = ($this->currentLanguageUid?'&L='.$this->currentLanguageUid:'');
				$viewPageOnClick = 'onclick= "'.htmlspecialchars(t3lib_BEfunc::viewOnClick($contentTreeArr['el']['uid'], $this->doc->backPath, t3lib_BEfunc::BEgetRootLine($contentTreeArr['el']['uid']),'','',$addGetVars)).'"';
				$viewPageIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.showPage',1).'" hspace="3" alt="" style="text-align: center; vertical-align: middle;" />';
				$titleBarLeftButtons .= '<a href="#" '.$viewPageOnClick.'>'.$viewPageIcon.'</a>';
			break;

			case 'tt_content' :
				$wrap='<div style="border: 1px solid #FFD8A9">|</div>';
 				$elementTitlebarColor = ($elementBelongsToCurrentPage ? $this->doc->bgColor5 : $this->doc->bgColor6);
				$elementTitlebarStyle = 'background-color: '.$elementTitlebarColor;

				$languageUid = $contentTreeArr['el']['sys_language_uid'];

				if (!$this->translatorMode && $canEditContent) {
						// Create CE specific buttons:
					$linkMakeLocal = !$elementBelongsToCurrentPage ? $this->link_makeLocal('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/makelocalcopy.gif','').' title="'.$LANG->getLL('makeLocal').'" border="0" alt="" />', $parentPointer) : '';
					$linkUnlink = $this->link_unlink('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','').' title="'.$LANG->getLL('unlinkRecord').'" border="0" alt="" />', $parentPointer, FALSE);
					if ($GLOBALS['BE_USER']->recordEditAccessInternals('tt_content', $contentTreeArr['previewData']['fullRow'])) {
						$linkEdit = ($elementBelongsToCurrentPage ? $this->link_edit('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' title="'.$LANG->getLL('editrecord').'" border="0" alt="" />',$contentTreeArr['el']['table'],$contentTreeArr['el']['uid']) : '');
					}
					else {
						$linkEdit = '';
					}
					$titleBarRightButtons = $linkEdit . $this->clipboardObj->element_getSelectButtons($parentPointer) . $linkMakeLocal . $linkUnlink;
				}
				else {
					$titleBarRightButtons = '';
				}
			break;
		}

			// Prepare the language icon:
		$languageLabel = htmlspecialchars ($this->allAvailableLanguages[$contentTreeArr['el']['sys_language_uid']]['title']);
		$languageIcon = $this->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img src="'.$this->allAvailableLanguages[$languageUid]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" style="text-align: center; vertical-align: middle;" />' : ($languageLabel && $languageUid ? '['.$languageLabel.']' : '');

			// If there was a language icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the  flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && $languageUid>0 && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && $contentTreeArr['el']['table']==='tt_content')	{
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $contentTreeArr['el']['uid'], TRUE);
		}

			// Create warning messages if neccessary:
		$warnings = '';
		if ($this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']] > 1 && $this->rootElementLangParadigm !='free') {
			$warnings .= '<br/>'.$this->doc->icons(2).' <em>'.htmlspecialchars(sprintf($LANG->getLL('warning_elementusedmorethanonce',''), $this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']], $contentTreeArr['el']['uid'])).'</em>';
		}

			// Displaying warning for container content (in default sheet - a limitation) elements if localization is enabled:
		$isContainerEl = count($contentTreeArr['sub']['sDEF']);
		if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning'] && $this->rootElementLangParadigm !='free' && $isContainerEl && $contentTreeArr['el']['table'] === 'tt_content' && $contentTreeArr['el']['CType'] === 'templavoila_pi1' && !$contentTreeArr['ds_meta']['langDisable'])	{
			if ($contentTreeArr['ds_meta']['langChildren'])	{
				if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning_warningOnly']) {
					$warnings .= '<br/>'.$this->doc->icons(2).' <b>'.$LANG->getLL('warning_containerInheritance').'</b>';
				}
			} else {
				$warnings .= '<br/>'.$this->doc->icons(3).' <b>'.$LANG->getLL('warning_containerSeparate').'</b>';
			}
		}

			// Preview made:
		$previewContent = $this->render_previewData($contentTreeArr['previewData'], $contentTreeArr['el'], $contentTreeArr['ds_meta'], $languageKey, $sheet);

			// Wrap workspace notification colors:
		if ($contentTreeArr['el']['_ORIG_uid'])	{
			$previewContent = '<div class="ver-element">'.($previewContent ? $previewContent : '<em>[New version]</em>').'</div>';
		}


//visibility
				require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');
				$service=t3lib_div::makeInstance('tx_languagevisibility_beservices');
				$flags=$elementContentTreeArr['el']['uid'].$service->getVisibleFlagsForElement($contentTreeArr['el']['uid'],$contentTreeArr['el']['table']);
				
			// Finally assemble the table:
		$finalContent ='
		
			<table cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid black; margin:5px;">
			<tr class="languagevisibility" style="background: #FFE3C1; ">
					<td style="vertical-align:top; padding: 2px; color: #4B3B27;"  colspan="2">'.
						'<span class="nobr">Visibility:'.
						$flags.
						'</span>'.						
					'</td>					
				</tr>
				<tr style="background: #FFAC4B; ">
					<td style="vertical-align:top;">'.
						'<span class="nobr">'.
						$languageIcon.
						$titleBarLeftButtons.
						($elementBelongsToCurrentPage?'':'<em>').htmlspecialchars($contentTreeArr['el']['title']).($elementBelongsToCurrentPage ? '' : '</em>').
						'</span>'.
						$warnings.
					'</td>
					<td nowrap="nowrap" style="text-align:right; vertical-align:top;">'.
						$titleBarRightButtons.
					'</td>
				</tr>
				<tr>
					<td colspan="2">'.
						$this->render_framework_subElements($contentTreeArr, $languageKey, $sheet).
						$previewContent.
						'<div style="margin-left: 10px">'.$this->render_localizationInfoTable($contentTreeArr, $parentPointer, $parentDsMeta).'</div>'.
					'</td>
				</tr>
			</table>
		
		';

		return str_replace('|',$finalContent,$wrap);
	}
}

?>