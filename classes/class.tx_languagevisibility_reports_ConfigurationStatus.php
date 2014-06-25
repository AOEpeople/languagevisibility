<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 AOE Dev <dev@aoemedia.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class tx_languagevisibility_reports_ConfigurationStatus
 *
 * @package AOE\Languagevisibility\Reports
 */
class tx_languagevisibility_reports_ConfigurationStatus implements \TYPO3\CMS\Reports\StatusProviderInterface {

	/**
	 * Determines the Install Tool's status, mainly concerning its protection.
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$statuses = array(
			'LangMode' => $this->getLangModes(),
		);

		return $statuses;
	}

	/**
	 * Check all "root" sys_templates and try to find the value for config.sys_language_mode
	 */
	public function getLangModes() {
		$message  = '';
		$checked = array(
			'ok' => array(),
			'fail' => array(),
		);
		$value = $GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:reports.ok.value');
		$severity = tx_reports_reports_status_Status::OK;

		$rootTpls = t3lib_BEfunc::getRecordsByField('sys_template', 'root', '1', '', 'pid');

		foreach ($rootTpls as $tpl) {
			/**
			 * @var t3lib_tsparser_ext
			 */
			$tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
			$tmpl->tt_track = 0;
			$tmpl->init();

				// Gets the rootLine
			$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sys_page->getRootLine($tpl['pid']);
			$tmpl->runThroughTemplates($rootLine, $tpl['uid']);

			$tplRow = $tmpl->ext_getFirstTemplate($tpl['pid'], $tpl['uid']);
			$tmpl->generateConfig();

			if (!isset($tmpl->setup['config.']['sys_language_mode']) || $tmpl->setup['config.']['sys_language_mode'] != 'ignore') {
				$checked['fail'][] = array($tpl['pid'], $tpl['uid'], $tmpl->setup['config.']['sys_language_mode']);
			}
		}

		if (count($checked['fail'])) {
			$severity = tx_reports_reports_status_Status::WARNING;
			$value = $GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:reports.fail.value');
			$message .= $GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:reports.fail.message') . '<br/>';
			foreach ($checked['fail'] as $fail) {
				$message .= vsprintf($GLOBALS['LANG']->sL('LLL:EXT:languagevisibility/locallang_db.xml:reports.fail.message.detail'), $fail) . '<br />';
			}
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			'EXT:languagevisibility config.sys_language_mode',
			$value,
			$message,
			$severity
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/languagevisibility/classes/class.tx_languagevisibility_reports_ConfigurationStatus.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/languagevisibility/classes/class.tx_languagevisibility_reports_ConfigurationStatus.php']);
}
