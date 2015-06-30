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


// load classes
ClassLoader::addClasses(array
(
	'SimpleNewsUrls'           => 'system/modules/simple_news_urls/classes/SimpleNewsUrls.php',
	'ModuleNewsReaderRedirect' => 'system/modules/simple_news_urls/modules/ModuleNewsReaderRedirect.php'
));
