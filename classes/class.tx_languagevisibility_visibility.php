<?php
/**
 * A visibility object represents the visibility of an element.
 * It contains a visibilityString(-,yes,no,f,t) and a visibility
 * description. The visibility description can be used to indicate,
 * why an element is visible or not.
 * 
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 */
class tx_languagevisibility_visibility{
	
	/**
	 * Holds the visibility string (-,yes,no,f,t).
	 * 
	 * @var string
	 */
	protected $visibilityString;
	
	/**
	 * Holds a description for the visiblitiy.
	 * 
	 * @var string
	 */
	protected $visibilityDescription;
	
	/**
	 * Returns a description why the visibility string is as it is.
	 * 
	 * @return string
	 */
	public function getVisibilityDescription() {
		return $this->visibilityDescription;
	}
	
	/**
	 * Returns the visibility string (-,no,t,f)
	 * 
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