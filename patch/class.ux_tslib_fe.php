<?php

require_once(t3lib_extMgm::extPath("languagevisibility").'class.tx_languagevisibility_feservices.php');

 class ux_tslib_fe extends tslib_fe	{


	/**
	 * Setting the language key that'll be used by the current page.
	 * In this function it should be checked, 1) that this language exists, 2) that a page_overlay_record exists, .. and if not the default language, 0 (zero), should be set.
	 *
	 * @return	void
	 * @access private
	 */
	function settingLanguage()	{
			// Get values from TypoScript:
			$lUid=intval($this->config['config']['sys_language_uid']);
			
			//works only with "ignore" setting
			//need to check access for current page and show error:
			if (!tx_languagevisibility_feservices::checkVisiblityForElement($this->page['uid'],'pages',$lUid)) {
		   				$GLOBALS['TSFE']->pageNotFoundAndExit('Page is not visible in requested language ['.$lUid.'/'.$this->page['uid'].']');
			
			}
			
			//overlay of current page is handled in ux_t3lib_pageSelect::getPageOverlay
			parent::settingLanguage();
			
			
				
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php']);
}
?>
