<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains a class with "Page functions" mainly for the frontend
 *
 * $Id: class.t3lib_page.php 2470 2007-08-29 15:52:38Z typo3 $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML-trans compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */


require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_feservices.php');



class ux_t3lib_pageSelect extends t3lib_pageSelect {
	
	
		
	/**
	 * Returns the relevant page overlay record fields
	 *
	 * @param	mixed		If $pageInput is an integer, it's the pid of the pageOverlay record and thus the page overlay record is returned. If $pageInput is an array, it's a page-record and based on this page record the language record is found and OVERLAYED before the page record is returned.
	 * @param	integer		Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
	 * @return	array		Page row which is overlayed with language_overlay record (or the overlay record alone)
	 */
	function getPageOverlay($pageInput,$lUid=-1)	{
				
			if ($lUid<0)	{
				$lUid = $this->sys_language_uid;
			}
			
		
			/*echo t3lib_div::debug_trail();
			echo '<b>'.$pageInput.'##'.$pageInput['uid'].'</b>';
			echo '+++'.$lUid;
			echo '<hr>';
			*/
			
			
			if (is_array($pageInput))	{				
				$page_id = $pageInput['uid'];				
			} else {
				return parent::getPageOverlay($pageInput,$lUid);
			}
			
		 			
			//call service to know if element is visible and which overlay language to use
			$overlayLanguage=tx_languagevisibility_feservices::getOverlayLanguageIdForElementRecord($page_id,'pages',$lUid);
	    
	   
	    if ($overlayLanguage===false) {	    	 	    	
    		//not visible, but this function is also used for rootline fillings -> so just mark this page as not visible!
    		$pageInput['_NOTVISIBLE'] = TRUE;
    		// $pageInput['title'].=' [[not visible]]';    		
    		return $pageInput;
    	}
    	else {
    		//$pageInput['title'].='-allowed- '.$lUid.'-'.$page_id.'-'.$overlayLanguage;    		
    		
				return parent::getPageOverlay($pageInput,$overlayLanguage);				
			}		
			return $passed;
	}

	/**
	 * Creates language-overlay for records in general (where translation is found in records from the same table)
	 *
	 * @param	string		Table name
	 * @param	array		Record to overlay. Must containt uid, pid and $table]['ctrl']['languageField']
	 * @param	integer		Pointer to the sys_language uid for content on the site.
	 * @param	string		Overlay mode. If "hideNonTranslated" then records without translation will not be returned un-translated but unset (and return value is false)
	 * @return	mixed		Returns the input record, possibly overlaid with a translation. But if $OLmode is "hideNonTranslated" then it will return false if no translation is found.
	 */
	function getRecordOverlay($table,$row,$sys_language_content,$OLmode='')	{
		//echo $table.'--'.$row['uid'].'--'.$sys_language_content.'--'.$OLmode;
		//echo '<hr>';
		//return parent::getRecordOverlay($table,$row,$sys_language_content,$OLmode);
	
		global $TCA;
		//echo $row['uid'].'-';  //39348
		
		//unset olmode
		$OLmode='';
	//	die('ההה');
		//call service to know if element is visible and which overlay language to use
		try {
			$element=tx_languagevisibility_feservices::getElement($row['uid'],$table);
	  	$overlayLanguage=tx_languagevisibility_feservices::getOverlayLanguageIdForElement($element,$sys_language_content);
	  	
	  }
	  catch (Exception $e) {
	  	//for any other tables:
	  	return parent::getRecordOverlay($table,$row,$sys_language_content,$OLmode);
	  }
		//debug($overlayLanguage);
    if ($overlayLanguage===false) {  
    	//echo 'unset  '.$table.'  / '.$row['uid'];
    	//not visible:
    	unset($row);
    	return $row;
    }
    else {
    	//visible:    	
    	if ($overlayLanguage!=0) {    			
    		
    		if ($element instanceof tx_languagevisibility_fceelement) {    			
    			//for FCE the overlay processing is handled by templavoila module, so mark the row with additional infos:
    			$languageRep=t3lib_div::makeInstance('tx_languagevisibility_languagerepository');	
    			$overlayLanguageObj=$languageRep->getLanguageById($overlayLanguage);  
    			$row['_OVERLAYLANGUAGEISOCODE']=$overlayLanguageObj->getIsoCode();
    			return $row;
    		}
    		elseif ($element instanceof tx_languagevisibility_fceoverlayelement) {
    			//now its getting tricky: we need to return overlay record with merged XML
    			$olrow=$this->_getDatabaseTranslationOverlayRecord('tt_content',$row,$overlayLanguage);
    			if ($GLOBALS['TSFE']) {
						$GLOBALS['TSFE']->includeTCA('tt_content');		
					}
    			//parse fce xml, and where a xml field is empty in olrow -> use default one
    			$flexObj = t3lib_div::makeInstance('t3lib_flexformtools');	    			
    			$this->_callbackVar_defaultXML=t3lib_div::xml2array($row['tx_templavoila_flex']);
    			$this->_callbackVar_overlayXML=t3lib_div::xml2array($olrow['tx_templavoila_flex']);
					$return=$flexObj->traverseFlexFormXMLData('tt_content','tx_templavoila_flex',$row,$this,'_callback_checkXMLFieldsForFallback');
					
					$row=parent::getRecordOverlay($table,$row,$overlayLanguage,$OLmode);
					$row['tx_templavoila_flex']=t3lib_div::array2xml($this->_callbackVar_overlayXML);
					return $row;
    		}
    		else {
    			//for default elements just do TYPO3 default overlay    			
    			return parent::getRecordOverlay($table,$row,$overlayLanguage,$OLmode);
    		}
    	}
    	else {
    		return $row;
    	}    	
    }    
	}


	/** It a callbackfunction (see getRecordOverlay)
		 function traverses default row XML and checks for fields with 'mergeIfNotBlank' l10n_mode.
		then in the overlay record XML this field is replaced by default one.
		TO-DO: replace in fallbackOrder
	**/
	
	function _callback_checkXMLFieldsForFallback($dsArr, $dataValue, $PA, $structurePath, &$pObj) {		
		if ($dataValue!='' && ($dsArr['TCEforms']['l10n_mode']=='mergeIfNotBlank' || $dsArr['TCEforms']['l10n_mode']=='exclude')) {			
			//echo 'check '.$structurePath;
			if ($dsArr['TCEforms']['l10n_mode']=='exclude') {
				$pObj->setArrayValueByPath($structurePath,$this->_callbackVar_overlayXML,$dataValue);
			}
			else {
				$overlayValue = $pObj->getArrayValueByPath($structurePath, $this->_callbackVar_overlayXML);
				if ($overlayValue=='' && $dsArr['TCEforms']['l10n_mode']=='mergeIfNotBlank') {				
					$pObj->setArrayValueByPath($structurePath,$this->_callbackVar_overlayXML,$dataValue);			
				}
			}
		}		
	}
	
	function _getDatabaseTranslationOverlayRecord($table,$row,$languageId) {
		global $TCA;
		// Select overlay record:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					$table,
					'pid='.intval($row['pid']).
						' AND '.$TCA[$table]['ctrl']['languageField'].'='.intval($languageId).
						' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
						$this->enableFields($table),
					'',
					'',
					'1'
				);
				$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$this->versionOL($table,$olrow);
				return $olrow;
	}















	/*******************************************
	 *
	 * Page related: Menu, Domain record, Root line
	 *
	 ******************************************/

	/**
	 * Returns an array with pagerows for subpages with pid=$uid (which is pid here!). This is used for menus.
	 * If there are mount points in overlay mode the _MP_PARAM field is set to the corret MPvar.
	 * If the $uid being input does in itself require MPvars to define a correct rootline these must be handled externally to this function.
	 *
	 * @param	integer		The page id for which to fetch subpages (PID)
	 * @param	string		List of fields to select. Default is "*" = all
	 * @param	string		The field to sort by. Default is "sorting"
	 * @param	string		Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 * @param	boolean		check if shortcuts exist, checks by default
	 * @return	array		Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
	 * @see tslib_fe::getPageShortcut(), tslib_menu::makeMenu(), tx_wizardcrpages_webfunc_2, tx_wizardsortpages_webfunc_2
	 */
	function getMenu($uid,$fields='*',$sortField='sorting',$addWhere='',$checkShortcuts=1)	{

		$output = Array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'pages', 'pid='.intval($uid).$this->where_hid_del.$this->where_groupAccess.' '.$addWhere, '', $sortField);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->versionOL('pages',$row,TRUE);
			if (is_array($row))	{
					// Keep mount point:
				$origUid = $row['uid'];
				$mount_info = $this->getMountPointInfo($origUid, $row);	// $row MUST have "uid", "pid", "doktype", "mount_pid", "mount_pid_ol" fields in it
				if (is_array($mount_info) && $mount_info['overlay'])	{	// There is a valid mount point.
					$mp_row = $this->getPage($mount_info['mount_pid']);		// Using "getPage" is OK since we need the check for enableFields AND for type 2 of mount pids we DO require a doktype < 200!
					if (count($mp_row))	{
						$row = $mp_row;
						$row['_MP_PARAM'] = $mount_info['MPvar'];
					} else unset($row);	// If the mount point could not be fetched with respect to enableFields, unset the row so it does not become a part of the menu!
				}

					// if shortcut, look up if the target exists and is currently visible
				if ($row['doktype'] == 4 && ($row['shortcut'] || $row['shortcut_mode']) && $checkShortcuts)	{
					if ($row['shortcut_mode'] == 0)	{
						$searchField = 'uid';
						$searchUid = intval($row['shortcut']);
					} else { // check subpages - first subpage or random subpage
						$searchField = 'pid';
							// If a shortcut mode is set and no valid page is given to select subpags from use the actual page.
						$searchUid = intval($row['shortcut'])?intval($row['shortcut']):$row['uid'];
					}
					$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', $searchField.'='.$searchUid.$this->where_hid_del.$this->where_groupAccess.' '.$addWhere, '', $sortField);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res2))	{
						unset($row);
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($res2);
				} elseif ($row['doktype'] == 4 && $checkShortcuts)	{
						// Neither shortcut target nor mode is set. Remove the page from the menu.
					unset($row);
				}

					// Add to output array after overlaying language:
				if (is_array($row))	{
					$output[$origUid] = $this->getPageOverlay($row);
				}
			}
		}
		return $output;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.ux_t3lib_page.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.ux_t3lib_page.php']);
}
?>
