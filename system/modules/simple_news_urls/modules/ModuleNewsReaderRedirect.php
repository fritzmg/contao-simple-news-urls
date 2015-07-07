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


class ModuleNewsReaderRedirect extends \ModuleNewsReader
{
	public function generate()
	{
		if( TL_MODE == 'FE' )
		{
			// get the request string
			$request = \Environment::get('request');

			// remove language, if applicable
			if( \Config::get('addLanguageToUrl') )
			{
				$request = substr( $request, 3 );
			}

			// check URL parameters
			if( count( explode( '/', $request ) ) > 1 )
			{
				/** @var \PageModel $objPage */
				global $objPage;

				// redirect to simple URL
				$this->redirect( \SimpleNewsUrls::buildUrl( $objPage->row(), \Input::get('auto_item') ) );
			}
		}

		return parent::generate();
	}
}
