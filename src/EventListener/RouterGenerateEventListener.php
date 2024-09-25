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
use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adjusts the dynamic route for news URLs.
 */
#[AsEventListener(Events::PRE_DYNAMIC_GENERATE)]
class RouterGenerateEventListener
{
    private static $pageEnabled = [];

    public function __invoke(RouterGenerateEvent $event): void
    {
        $params = $event->getParameters();

        // Check if this route is applicable
        if (!($pageRoute = ($params['_route_object'] ?? null)) instanceof PageRoute || empty($params['parameters'])) {
            return;
        }

        // Check if we have a single parameter (the news alias)
        $fragments = explode('/', ltrim($params['parameters'], '/'));

        if (\count($fragments) > 1) {
            return;
        }

        $page = $pageRoute->getPageModel();

        // Check if page is target page for a simple URLs enabled news archive
        if (!isset(self::$pageEnabled[(int) $page->id])) {
            self::$pageEnabled[(int) $page->id] = null !== NewsArchiveModel::findBy(['jumpTo = ?', 'enable_simple_urls = 1'], [(int) $page->id]);
        }

        if (!self::$pageEnabled[(int) $page->id]) {
            return;
        }

        // Find a news via its alias
        if (!$news = NewsModel::findByAlias($fragments[0])) {
            return;
        }

        // Point to route
        $event->setRoute('tl_news.'.$news->alias);
        $event->setParameters([]);
    }
}
