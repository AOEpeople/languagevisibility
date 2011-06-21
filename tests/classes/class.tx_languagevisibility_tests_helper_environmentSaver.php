<?php

/**
 * 
 */
class tx_languagevisibility_tests_helper_environmentHelper {
	/**
	 * @var array holds the restored addRootlineFields array which will be stored in setUp and restored in tearDown.
	 */
	protected $restoredAddRootlineFields;

	/**
	 * @var array holds the restored pageOverlayFields array which will be stored in setUp and restored in tearDown
	 */
	protected $restoredPageOverlayFields;

	/**
	 * @var array holds the restored sc options
	 */
	protected $restoredSCOptions;


	/**
	 * @var
	 */
	protected $restoredBeUser;

	/**
	 * @return void
	 */
	public function save(){
		/* save current enviroment values */
		$this->restoredAddRootlineFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
		$this->restoredPageOverlayFields = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"];
		$this->restoredSCOptions = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'];
		$this->restoredBeUser = $GLOBALS['BE_USER'];
	}

	/**
	 * @return void
	 */
	public function restore() {
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"] = $this->restoredAddRootlineFields;
		$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"] = $this->restoredPageOverlayFields;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['languagevisibility'] = $this->restoredSCOptions;
		$GLOBALS['BE_USER'] = $this->restoredBeUser;
	}
}
