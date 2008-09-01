<?php

class ux_t3lib_TCEmain extends t3lib_TCEmain	{

	/**
	 * Used to evaluate if a page can be deleted
	 *
	 * @param	integer		Page id
	 * @return	mixed		If array: List of page uids to traverse and delete (means OK), if string: error code.
	 */
	function canDeletePage($uid)	{
		$return = parent::canDeletePage($uid);
		if (is_array($return)) {			
			if (t3lib_extMgm::isLoaded('languagevisibility')) {
					require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');									
					$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
					if (!$visibilityservice->hasUserAccessToPageRecord($uid,'delete')) {
						return 'Attempt to delete records without access to the visible languages';
					}									
				}	
		}
		return $return;
	}
	
	/**
	 * Checks if user may update a record with uid=$id from $table
	 *
	 * @param	string		Record table
	 * @param	integer		Record UID
	 * @return	boolean		Returns true if the user may update the record given by $table and $id
	 */
	function checkRecordUpdateAccess($table,$id)	{
		global $TCA;
		$res = 0;
		if ($TCA[$table] && intval($id)>0)	{
			if (isset($this->recUpdateAccessCache[$table][$id]))	{	// If information is cached, return it
				return $this->recUpdateAccessCache[$table][$id];
				// Check if record exists and 1) if 'pages' the page may be edited, 2) if page-content the page allows for editing
			} elseif ($this->doesRecordExist($table,$id,'edit'))	{
				$res = 1;
				if (t3lib_extMgm::isLoaded('languagevisibility')) {
					require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');									
					$visibilityservice=t3lib_div::makeInstance('tx_languagevisibility_beservices');
					if ($table=='pages') {
						if (!$visibilityservice->hasUserAccessToPageRecord($id,'edit')) {
							$res = 0;
						}
					}
					else {
						if (!$visibilityservice->hasUserAccessToEditRecord($table,$id)) {
							$res=0;									
						}
					}
				}
				
			}
			$this->recUpdateAccessCache[$table][$id]=$res;	// Cache the result
		}
		return $res;
	}

	/**
	 * Wrapper function for process_cmdmap in tce main, checks if the user wants 
	 * to move or copy a page
	 *
	 */
//	function process_cmdmap(){
//		if (t3lib_extMgm::isLoaded('languagevisibility')) {
//	
//			require_once(t3lib_extMgm::extPath("languagevisibility").'patch/lib/class.tx_languagevisibility_commandMap.php');
//			require_once(t3lib_extMgm::extPath("languagevisibility").'patch/lib/class.tx_languagevisibility_beUser.php');
//			
//			$be_user 		= t3lib_div::makeInstance('tx_languagevisibility_beUser');
//	
//			if($be_user->allowCutMoveDelete() || $be_user->isAdmin() ){				
//				//nothing to user hase rights to move, cut or delete items
//
//			}else{
//				//user has no rights to cut move copy or delete, therefore the commands need to be filtered
//				$command_map 	= t3lib_div::makeInstance('tx_languagevisibility_commandMap');		
//				$command_map->setMap($this->cmdmap);
//
//				$elements = $command_map->getElementsByCommands(array('cut','move','copy','delete'));
//				if(is_array($elements)){
//					foreach($elements as $element){						
//
//						require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');
//						$hastranslation = tx_languagevisibility_beservices::hasElementTranslationInAnyLanguageAndAnyWorkspace($element['uid'],$element['table']);
//
//						if($hastranslation){
//							$command_map->removeElement($element);	
//							$this->newlog('You have no rights to apply the command '.$element['cmd'].' on elements with overlays',1);								
//						}
//					}
//				}
//				//overwrite the internal map an process the base tce_main method
//				$this->cmdmap = $command_map->getMap();
//			}
//
//		}
//				
//		parent::process_cmdmap();
//	}
	
	function process_cmdmap(){
		if (t3lib_extMgm::isLoaded('languagevisibility')) {
	
			require_once(t3lib_extMgm::extPath("languagevisibility").'patch/lib/class.tx_languagevisibility_commandMap.php');
			require_once(t3lib_extMgm::extPath("languagevisibility").'patch/lib/class.tx_languagevisibility_beUser.php');
			
			//user has no rights to cut move copy or delete, therefore the commands need to be filtered
			$command_map 	= t3lib_div::makeInstance('tx_languagevisibility_commandMap');		
			$command_map->setMap($this->cmdmap);

			$command_elements = $command_map->getElementsByCommands(array('cut','move','copy','delete'));
			if(is_array($command_elements)){
				foreach($command_elements as $command_element){							
					try{
						$elementObj = tx_languagevisibility_beservices::getElement($command_element['uid'],$command_element['table']);
						$command = $command_element['cmd'];
						 
						if(!$elementObj->isOrigElement()){
							//current element is an overlay -> restrict cut copy and move in general -> filter the command map
							if($command == 'move' || $command == 'cut'|| $command == 'copy'){
								$this->newlog('The command '.$command.' can not be applied on overlays',1);	
								//overlay records should no be move,copy or cutable but it should be possible to delete them
								$command_map->removeElement($command_element);	
							}	
						}else{
							//current element is no overlay -> if user has rights to cutMoveDelete or is an admin don't filter commants
							$be_user 		= t3lib_div::makeInstance('tx_languagevisibility_beUser');
							if($be_user->allowCutMoveDelete() || $be_user->isAdmin() ){				
								//nothing to do ->  user hase rights to move, cut or delete items
							
							}else{
								//if the record has any translation disallow move, cut, copy and delete

								if($elementObj->hasAnyTranslationInAnyWorkspace()){
									$command_map->removeElement($command_element);	
									$this->newlog('You have no rights to apply the command '.$command.' on elements with overlays',1);								
								}
							}
						}	
					}catch(Exception $e){
						//element not supported by language visibility	
					}	
				}
			}
			
			//overwrite the internal map an process the base tce_main method
			$this->cmdmap = $command_map->getMap();	
		}
				
		parent::process_cmdmap();
	}
	
	function isOverlay($element){
		$record = t3lib_BEfunc::getRecord($element['table'],$element['uid'],$fields='l18n_parent',$where='',$useDeleteClause=true);
		return ($record['l18n_parent'] != 0);
	}
	
}

?>
