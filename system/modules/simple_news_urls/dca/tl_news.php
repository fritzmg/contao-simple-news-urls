<?php

$GLOBALS['TL_DCA']['tl_news']['fields']['alias']['save_callback'][] = array('tl_news_simplenewsurls', 'checkAlias');


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Fritz Michael Gschwantner <https://github.com/fritzmg>
 */
class tl_news_simplenewsurls extends Backend
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
		// get the news archive first
		if( ( $objArchive = \NewsArchiveModel::findById( $dc->activeRecord->pid ) ) !== null )
		{
			// get the redirect page
			if( ( $objTarget = \PageModel::findById( $objArchive->jumpTo ) ) !== null )
			{
				// check if there is a page with the same alias
				if( ( $objPage = \PageModel::findByAlias( $varValue ) ) !== null ) 
				{
					// load the details
					$objTarget->current()->loadDetails();
					$objPage->current()->loadDetails();

					// check if page is on the same domain and language
					if( $objPage->domain == $objTarget->domain && ( !\Config::get('addLanguageToUrl') || $objPage->rootLanguage == $objTarget->rootLanguage ) )
					{
						// append id
						$varValue.= '-' . $dc->id;
					}
				}
			}
		}

		// return the alias
		return $varValue;
	}

}
