<?php

require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_languagerepository.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_elementFactory.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/class.tx_languagevisibility_visibilityService.php');
require_once (t3lib_extMgm::extPath ( "languagevisibility" ) . 'classes/dao/class.tx_languagevisibility_daocommon.php');

/**
 * exceptions are not handled here.
 * This class just provides simple services and uses the domainmodel in classes directory!
 *
 * Methods can be used uninstanciated
 **/
class tx_languagevisibility_feservices {

	public static function checkVisiblityForElement($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		$element = $elementfactory->getElementForTable ( $table, $uid );
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language = $languageRep->getLanguageById ( $lUid );

		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );

		return $visibility->isVisible ( $language, $element );
	}

	public static function getElement($uid, $table) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		$element = $elementfactory->getElementForTable ( $table, $uid );
		return $element;
	}

	public static function getOverlayLanguageIdForElement($element, $lUid) {
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language = $languageRep->getLanguageById ( $lUid );

		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		return $visibility->getOverlayLanguageIdForLanguageAndElement ( $language, $element );
	}

	public static function getOverlayLanguageIdForElementRecord($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		$element = $elementfactory->getElementForTable ( $table, $uid );
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language = $languageRep->getLanguageById ( $lUid );

		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		return $visibility->getOverlayLanguageIdForLanguageAndElement ( $language, $element );
	}

	public static function getOverlayLanguageIdForElementRecordForced($uid, $table, $lUid) {
		$dao = t3lib_div::makeInstance ( 'tx_languagevisibility_daocommon' );
		$elementfactoryName = t3lib_div::makeInstanceClassName ( 'tx_languagevisibility_elementFactory' );
		$elementfactory = new $elementfactoryName ( $dao );
		$element = $elementfactory->getElementForTable ( $table, $uid );
		$languageRep = t3lib_div::makeInstance ( 'tx_languagevisibility_languagerepository' );
		$language = $languageRep->getLanguageById ( $lUid );

		$visibility = t3lib_div::makeInstance ( 'tx_languagevisibility_visibilityService' );
		$visibility->isVisible ( $language, $element );
		return $visibility->getLastRelevantOverlayLanguageId ();
	}
}

?>