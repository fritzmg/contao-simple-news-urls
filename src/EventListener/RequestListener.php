<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoSimpleNewsUrls\EventListener;

use Contao\Input;
use InspiredMinds\ContaoSimpleNewsUrls\Routing\NewsRoute;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
class RequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_route_object') instanceof NewsRoute) {
            return;
        }

        // Manually set the auto_item to the news alias, so that the newsreader module still works
        [, $alias] = explode('.', (string) $request->attributes->get('_canonical_route'));
        Input::setGet('auto_item', $alias);
    }
}
