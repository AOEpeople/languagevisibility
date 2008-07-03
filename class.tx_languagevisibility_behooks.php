<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Daniel P?tzinger (poetzinger@aoemedia.de)
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
/** 
 * Class/Function which manipulates the item-array for the  listing (see piFlexform).
 *
 * @author	Daniel P?tzinger <poetzinger@aoemedia.de>
 */

 
/**
 * SELECT box processing
 * 
 */
class tx_languagevisibility_behooks {

	
	
	/*
	* store:
	* 
	*/
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$reference) {
			$data=$incomingFieldArray;
			
			
			
			if(!is_array($data)) return; /* some strange DB situation */
			
		/*	if ($id=="NEW")
						return;			
			*/						
			switch ($table) {
				case 'pages': case 'tt_content': case 'tt_news':
					if (isset($incomingFieldArray['tx_languagevisibility_visibility'])) {						
						$incomingFieldArray['tx_languagevisibility_visibility']=serialize($incomingFieldArray['tx_languagevisibility_visibility']);
					}
					else {
						//force visibility setting according to permissions						
						if ($id=="NEW") {
							require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_beservices.php');
							//print_r(tx_languagevisibility_beservices::getDefaultVisibilityArray());
							$incomingFieldArray['tx_languagevisibility_visibility']=serialize(tx_languagevisibility_beservices::getDefaultVisibilityArray());
						}		
						else {
							//die($id);
						}				
					}
				break;
				default:
					return;
				break;
			}
			
			
			
		}
	
	/*
	//	function processDatamap_postProcessFieldArray ($status, $table, $id, $fieldArray, &$reference) {
	*/
}



?>
