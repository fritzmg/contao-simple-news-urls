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

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\ModuleNewsReader;
use Contao\NewsArchiveModel;
use InspiredMinds\ContaoSimpleNewsUrls\Routing\NewsRoute;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Creates a 301 redirect to the canonical simple URL of a news entry, if applicable.
 *
 * @Hook("parseArticles")
 */
class ParseArticlesListener
{
    private $requestStack;
    private $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function __invoke(FrontendTemplate $template, array $newsEntry, Module $module): void
    {
        if (!$module instanceof ModuleNewsReader) {
            return;
        }

        $archive = NewsArchiveModel::findByPk($newsEntry['pid']);

        // Simple URLs not enabled for this archive
        if (null === $archive || !$archive->enable_simple_urls) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        // We are on the canonical route
        if ($request->attributes->get('_route_object') instanceof NewsRoute) {
            return;
        }

        // Redirect to canonical route
        throw new RedirectResponseException($this->router->generate('tl_news.'.$newsEntry['alias']), 301);
    }
}
