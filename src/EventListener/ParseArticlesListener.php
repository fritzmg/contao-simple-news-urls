<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoSimpleNewsUrls\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\ModuleNewsReader;
use Contao\NewsArchiveModel;
use InspiredMinds\ContaoSimpleNewsUrls\Routing\NewsRoute;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Creates a 301 redirect to the canonical simple URL of a news entry, if applicable.
 */
#[AsHook('parseArticles')]
class ParseArticlesListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(FrontendTemplate $template, array $newsEntry, Module $module): void
    {
        if (!$module instanceof ModuleNewsReader) {
            return;
        }

        $archive = NewsArchiveModel::findById($newsEntry['pid']);

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
