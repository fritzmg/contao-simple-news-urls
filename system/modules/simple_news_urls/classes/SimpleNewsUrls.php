<?php

/**
 * Contao Open Source CMS
 *
 * Simple Contao extension to allow news URLs without its reader page fragment.
 * 
 * @copyright inspiredminds 2015
 * @package   simple_news_urls
 * @link      http://www.inspiredminds.at
 * @author    Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @license   GPL-2.0
 */

use Contao\Config;
use Contao\Controller;
use Contao\Environment;
use Contao\ModuleLoader;
use Contao\ModuleNewsReader;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;

class SimpleNewsUrls
{

	/**
	 * getPageIdFromUrl Hook
	 * checks if the URL fragment is a news alias and sets the page alias and auto item accordingly
	 * @param array the URL fragments
	 */
	public function getPageIdFromUrl( $arrFragments )
	{
		// extract alias from fragments
		$alias = null;
		// handle special case with i18nl10n (see #4)
		if( in_array( 'i18nl10n', ModuleLoader::getActive() ) && count( $arrFragments ) == 3 )
		{
			if( $arrFragments[0] == null && $arrFragments[1] == 'language' )
			{
				$alias = $arrFragments[2];
			}
		}
		// otherwise check if there is exactly only one fragment
		elseif( count( $arrFragments ) == 1 )
		{
			$alias = $arrFragments[0];
		}

		// check if an alias was extracted
		if( $alias )
		{
			// check if news item exists
			if( ( $objNews = NewsModel::findByAlias( $alias ) ) !== null )
			{
				// check if jumpTo page exists
				if( ( $objTarget = PageModel::findWithDetails( $objNews->getRelated('pid')->jumpTo ) ) !== null )
				{
					// check if target page is in the right language
					if( Config::get('addLanguageToUrl') && $objTarget->rootLanguage != \Input::get('language') )
					{
						// return fragments without change
						return $arrFragments;
					}

					// check if target page is in the right domain
					if( $objTarget->domain && stripos( Environment::get('host'), $objTarget->domain ) === false )
					{
						// return fragments without change
						return $arrFragments;
					}

					// return changed fragments
					return array( $objTarget->alias, 'auto_item', $objNews->alias );
				}
			}
		}

		// return fragments without change
		return $arrFragments;
	}


	/**
	 * generateFrontendUrl Hook
	 * checks if the parameter for the generated URL is a news alias and rewrites the URL without its page alias
	 * @param  array page data
	 * @param  string URL parameters
	 * @param  string current URL
	 * @return string
	 */
	public function generateFrontendUrl($arrRow, $strParams, $strUrl)
	{
		// no params, no action
		if( !$strParams )
		{
			return $strUrl;
		}

		// check if param is a news alias
		if (null !== ($objNews = NewsModel::findByAlias(ltrim($strParams, '/'))))
		{
			// remove the page alias from the URL
			$strUrl = str_replace($arrRow['alias'] . '/', '', $strUrl);
		}	

		// return the url
		return $strUrl;
	}


	/**
	 * Remove page path from searchable pages for news entries.
	 * @param  array   $arrPages
	 * @param  integer $intRoot
	 * @param  boolean $blnIsSitemap
	 * @return array
	 */
	public function getSearchablePages($arrPages, $intRoot = 0, $blnIsSitemap = false)
	{
		// get all news archives
		if (null !== $objArchive = NewsArchiveModel::findAll())
		{
			while ($objArchive->next())
			{
				if ($objArchive->jumpTo && null !== ($objTarget = $objArchive->getRelated('jumpTo')))
				{
					foreach ($arrPages as &$page)
					{
						$page = str_replace($objTarget->alias . '/', '', $page);
					}
				}
			}
		}

		return $arrPages;
	}


	/**
	 * parseArticles Hook to either generate a 301 redirect or a canonical URL
	 * to the simple news URL to prevent duplicate URLs
	 * @param array page data
	 * @param string news alias
	 */
	public function parseArticles($objTemplate, $arrArticle, $objModule)
	{
		// check for news module
		if (!$objModule instanceof ModuleNewsReader)
		{
			return;
		}

		// check if auto item parameter matches the article
		if (\Input::get('auto_item') != $arrArticle['alias'])
		{
			return;
		}

		// get the current request string
		$strRequest = Environment::get('requestUri');

		// remove script name
		$strRequest = preg_replace('~^'.Environment::get('scriptName').'/~', '', $strRequest);

		// remove language, if applicable
		if (Config::get('addLanguageToUrl'))
		{
			$strRequest = substr($strRequest, 3);
		}

		// check if news alias is at the beginning of url
		if (stripos(urldecode($strRequest), $arrArticle['alias']) !== 0)
		{
			/** @var PageModel $objPage */
			global $objPage;

			// generate the news URL
			$strUrl = $objPage->getAbsoluteUrl('/' . $arrArticle['alias']);

			// remove the page alias
			$strUrl = str_replace($objPage->alias . '/', '', $strUrl);

			// generate query string
			$strQuery = Environment::get('queryString') ? '?'.Environment::get('queryString') : '';

			// check for redirect
			$redirectType = Config::get('simpleNewsUrlsRedirect');
			switch ($redirectType)
			{
				// insert canonical meta tag
				case 'canonical': $GLOBALS['TL_HEAD'][] = '<link rel="canonical" href="'. $strUrl .'">'; break;

				// redirect to simple URL
				case 301:
				case 302:
				case 303: Controller::redirect($strUrl . $strQuery, $redirectType); break;
				 default: Controller::redirect($strUrl . $strQuery, 301          );
			}
		}
	}
}
