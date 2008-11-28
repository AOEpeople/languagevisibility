<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_recordelement.php');

class tx_languagevisibility_ttnewselement extends tx_languagevisibility_recordelement {
	/**
	* init table
	**/
	protected function initialisations() {
		$this->setTable('tt_news');
	}

	/**
	*returns which field in the language should be used to read the default visibility
	*
	*@return string (blank=default / page=page)
	**/
	function getFieldToUseForDefaultVisibility() {
		return 'tt_news';
	}
	
	public function getElementDescription(){
		return 'tt-news Record';
	}
	
	function getFallbackOrder(tx_languagevisibility_language $language) {
		return $language->getFallbackOrderTTNewsElement();
	}

}

?>
