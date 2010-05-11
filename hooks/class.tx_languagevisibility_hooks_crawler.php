<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007 AOE media (dev@aoemedia.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once (t3lib_extMgm::extPath('languagevisibility') . 'class.tx_languagevisibility_feservices.php');

class tx_languagevisibility_hooks_crawler {

	/**
	 * Process the prepared crawler urls and check wether these pages have the chance to get crawled or not
	 *
	 * @param array $params		the crawler result
	 * @param object $ref		the crawler_lib
	 * @return void
	 */
	public function processUrls(&$params, &$ref) {

		foreach ( $params['res'] as $cfg => $sub ) {
			$list = array();
			foreach ( $params['res'][$cfg]['URLs'] as $key => $url ) {

				list ( $id, $lang ) = self::extractIdAndLangFromUrl($url);

				if (tx_languagevisibility_feservices::checkVisiblityForElement($id, 'pages', $lang)) {
					$list[] = $url;
				} else {
					// $url not visible therefore we drop it
				}
			}
			$params['res'][$cfg]['URLs'] = $list;
		}

	}

	/**
	 *
	 * @param string $url	guess what
	 */
	protected static function extractIdAndLangFromUrl($url) {

		// retrieving the id this way is save because that part is hardcoded in the crawler
		$matches = array();
		preg_match('/\?id=(\d+)&?/', $url, $matches);
		$id = $matches[1];

		// TODO: might need domain if no "L" is given
		$matches = array();
		if (! preg_match('/L=(\d+)&?/', $url, $matches)) {
			$lang = 0;
		} else {
			$lang = $matches[1];
		}

		return array($id, $lang );
	}
}

?>