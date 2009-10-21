<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_recordelement.php');


class tx_languagevisibility_ttnewselement extends tx_languagevisibility_recordelement {

	/**
	 * Overwritten method to initialized
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_element#initialisations()
	 */
	protected function initialisations(){}

	/**
	* returns which field in the language should be used to read the default visibility
	*
	* @param void
	* @return string (blank=default / page=page)
	**/
	function getFieldToUseForDefaultVisibility() {
		return 'tt_news';
	}
	
	/**
	 * Returns a formal description for this element type.
	 * 
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_recordelement#getElementDescription()
	 */
	public function getElementDescription(){
		return 'tt-news Record';
	}
	
	/**
	 * Returns the fallback order for news elements.
	 * 
	 * (non-PHPdoc)
	 * @see classes/tx_languagevisibility_recordelement#getFallbackOrder($language)
	 */
	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrderTTNewsElement();
	}
}
?>