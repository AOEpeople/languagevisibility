<?php


require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_recordelement.php');

class tx_languagevisibility_celement extends tx_languagevisibility_recordelement {	
	/**
	* init table
	**/
	protected function initialisations() {
		$this->setTable('tt_content');
	}

}

?>