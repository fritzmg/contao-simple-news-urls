<?php

$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][] = array('tl_page_simplenewsurls', 'checkAlias');


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Fritz Michael Gschwantner <https://github.com/fritzmg>
 */
class tl_page_simplenewsurls extends Backend
{

	/**
	 * Check the news alias for duplicates
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function checkAlias($varValue, DataContainer $dc)
	{
		// check if there is a news article with the same alias
		if( ( $objNews = \NewsModel::findByAlias( $varValue ) ) !== null ) 
		{
			// get the redirect page
			if( ( $objTarget = \PageModel::findWithDetails( $objNews->getRelated('pid')->jumpTo ) ) !== null )
			{
				// get the page
				$objPage = \PageModel::findWithDetails( $dc->id );

				// check if page is on the same domain and language
				if( $objPage->domain == $objTarget->domain && ( !\Config::get('addLanguageToUrl') || $objPage->rootLanguage == $objTarget->rootLanguage ) )
				{
					// append id
					$varValue.= '-' . $dc->id;
				}
			}
		}

		// return the alias
		return $varValue;
	}

}
