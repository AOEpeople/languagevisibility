<?php
class tx_languagevisibility_visibility{
	
	/**
	 * Holds the visibility string (-,yes,no,f,t)
	 */
	protected $visibilityString;
	
	/**
	 * Holds a description for the visiblitiy
	 */
	protected $visibilityDescription;
	/**
	 * @return string
	 */
	public function getVisibilityDescription() {
		return $this->visibilityDescription;
	}
	
	/**
	 * Returns the visibility string (-,no,t,f)
	 * @return string
	 */
	public function getVisibilityString() {
		return $this->visibilityString;
	}
	
	/**
	 * Method to set the visibility string, chainable because it returns itself
	 * 
	 * @param string $visibilityDescription
	 * @return tx_languagevisibility_visibility
	 * 	 */
	public function setVisibilityDescription($visibilityDescription) {
		$this->visibilityDescription = $visibilityDescription;
		
		return $this;
	}
	
	/**
	 * Method to set the visibility string, chainable because it returns itself
	 * 
	 * @param string $visibilityString
	 * @return tx_languagevisibility_visibility
	 */
	public function setVisibilityString($visibilityString) {
		$this->visibilityString = $visibilityString;
		
		return $this;
	}	
}
?>