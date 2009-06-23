<?php
require_once(t3lib_extMgm::extPath("languagevisibility").'classes/class.tx_languagevisibility_celement.php');

class tx_languagevisibility_fceoverlayelement extends tx_languagevisibility_celement {
	
	function getInformativeDescription() {
		return 'this is a flexible content element but translations are handled with overlay records.';		
	}
	
	public function getElementDescription(){
		return 'FCE-Overlay';
	}
}

?>