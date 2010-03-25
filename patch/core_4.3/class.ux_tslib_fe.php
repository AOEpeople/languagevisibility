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

		if (is_array($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess']))	{
				$_params = array();
				foreach($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_preProcess'] as $_funcRef)	{
					t3lib_div::callUserFunction($_funcRef, $_params, $this);
				}
			}

		parent::settingLanguage();

		if (is_array($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess']))	{
			$_params = array();
			foreach($this->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess'] as $_funcRef)	{
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.ux_tslib_fe.php']);
}
?>
