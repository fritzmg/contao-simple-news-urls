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

// set new news reader
$GLOBALS['FE_MOD']['news']['newsreader']  = 'ModuleNewsReaderRedirect';

// set hooks
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('SimpleNewsUrls','getPageIdFromUrl');
$GLOBALS['TL_HOOKS']['generateFrontendUrl'][] = array('SimpleNewsUrls','generateFrontendUrl');
