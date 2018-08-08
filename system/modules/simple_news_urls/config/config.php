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


// disable extension if auto item or alias is disabled
if( !\Config::get('useAutoItem') || \Config::get('disableAlias') )
	return;

// set hooks
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('SimpleNewsUrls','getPageIdFromUrl');
$GLOBALS['TL_HOOKS']['generateFrontendUrl'][] = array('SimpleNewsUrls','generateFrontendUrl');
$GLOBALS['TL_HOOKS']['parseArticles'][] = array('SimpleNewsUrls','parseArticles');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('SimpleNewsUrls', 'getSearchablePages');

// settings
$GLOBALS['TL_CONFIG']['simpleNewsUrlsRedirect'] = 301;
