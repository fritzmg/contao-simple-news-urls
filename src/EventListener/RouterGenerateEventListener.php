<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Simple News URLs extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoSimpleNewsUrls\EventListener;

use Contao\CoreBundle\Routing\Page\PageRoute;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * Adjusts the dynamic route for news URLs.
 *
 * @ServiceTag("kernel.event_listener", event="cmf_routing.pre_dynamic_generate")
 */
class RouterGenerateEventListener
{
    private static $pageEnabled = [];

    public function __invoke(RouterGenerateEvent $event): void
    {
        $params = $event->getParameters();

        // Check if this route is applicable
        if (!isset($params['_content']) || !$params['_content'] instanceof PageModel || empty($params['parameters'])) {
            return;
        }

        // Check if we have a single parameter (the news alias)
        $paramFragments = explode('/', mb_substr($params['parameters'], 1));

        if (\count($paramFragments) > 1) {
            return;
        }

        $page = $params['_content'];

        // Check if page is target page for a simple URLs enabled news archive
        if (!isset(self::$pageEnabled[(int) $page->id])) {
            self::$pageEnabled[(int) $page->id] = null !== NewsArchiveModel::findBy(['jumpTo = ?', 'enable_simple_urls = 1'], [(int) $page->id]);
        }

        if (!self::$pageEnabled[(int) $page->id]) {
            return;
        }

        // Find a news via its alias
        $news = NewsModel::findByAlias($paramFragments[0]);

        if (null === $news) {
            return;
        }

        // Adjust the route
        $route = new PageRoute($params['_content'], '/'.$news->alias);
        $route->setUrlPrefix('');
        $route->setUrlSuffix('');

        $event->setRoute($route);
        $event->setParameters([]);
    }
}
