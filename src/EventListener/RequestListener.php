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

use Contao\Input;
use InspiredMinds\ContaoSimpleNewsUrls\Routing\NewsRoute;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener")
 */
class RequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_route_object') instanceof NewsRoute) {
            return;
        }

        // Manually set the auto_item to the news alias, so that the newsreader module still works
        [, $alias] = explode('.', $request->attributes->get('_canonical_route'));
        Input::setGet('auto_item', $alias);
    }
}
