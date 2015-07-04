<?php

/**
 * Contao Open Source CMS
 *
 * simple extension to provide a share buttons module
 * 
 * @copyright inspiredminds 2015
 * @package   simple_news_urls
 * @link      http://www.inspiredminds.at
 * @author    Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @license   GPL-2.0
 */


class SimpleNewsUrls
{

	/**
	 * getPageIdFromUrl Hook
	 * checks if the URL fragment is a news alias and sets the page alias and auto item accordingly
	 * @param array the URL fragments
	 */
	public function getPageIdFromUrl( $arrFragments )
	{
		// check if there is exactly one fragment
		if( count( $arrFragments ) == 1 )
		{
			// check if news item exists
			if( ( $objNews = \NewsModel::findByAlias( $arrFragments[0] ) ) !== null )
			{			
				// check if jumpTo page exists
				if( ( $objTarget = \PageModel::findWithDetails( $objNews->getRelated('pid')->jumpTo ) ) !== null )
				{
					// check if target page is in the right language
					if( \Config::get('addLanguageToUrl') && $objTarget->rootLanguage != \Input::get('language') )
					{
						// return fragments without change
						return $arrFragments;
					}

					// check if target page is in the right domain
					if( $objTarget->domain && stripos( \Environment::get('host'), $objTarget->domain ) === false )
					{
						// return fragments without change
						return $arrFragments;
					}

					// set fragments
					$arrFragments[0] = $objTarget->alias;
					$arrFragments[1] = 'auto_item';
					$arrFragments[2] = $objNews->alias;
				}
			}
		}

		// return the fragments
		return $arrFragments;
	}


	/**
	 * generateFrontendUrl Hook
	 * checks if the parameter for the generated URL is a news alias and rewrites the URL without its page alias
	 * @param array page data
	 * @param string URL parameters
	 * @param string current URL
	 */
	public function generateFrontendUrl( $arrRow, $strParams, $strUrl )
	{
		// no params, no action
		if( !$strParams )
		{
			return $strUrl;
		}

		// check if param is a news alias
		if( ( $objNews = \NewsModel::findByAlias( ltrim( $strParams, '/' ) ) ) !== null )
		{
			// build url using only the news alias
			$strUrl = self::buildUrl( $arrRow, $objNews->alias );
		}			

		// return the url
		return $strUrl;
	}


	/**
	 * Helper function to build the simple news URL
	 * @param array page data
	 * @param string news alias
	 */
	public static function buildUrl( $arrPage, $strAlias )
	{
		// check for language
		$strLanguage = '';

		if( \Config::get('addLanguageToUrl') )
		{
			if( isset( $arrPage['language'] ) && $arrPage['type'] == 'root' )
			{
				$strLanguage = $arrPage['language'] . '/';
			}
			elseif( TL_MODE == 'FE' )
			{
				/** @var \PageModel $objPage */
				global $objPage;

				$strLanguage = $objPage->rootLanguage . '/';
			}
		}

		// build url using only the news alias
		$strUrl = ( \Config::get('rewriteURL') ? '' : 'index.php/' ) . $strLanguage . $strAlias . \Config::get('urlSuffix');

		// Add the domain if it differs from the current one
		if( $arrPage['domain'] != '' && $arrPage['domain'] != \Environment::get('host') )
		{
			$strUrl = ($arrPage['rootUseSSL'] ? 'https://' : 'http://') . $arrPage['domain'] . TL_PATH . '/' . $strUrl;
		}

		// return the url
		return $strUrl;		
	}
	
}