<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoSimpleNewsUrls\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsDecorator('contao_news.routing.news_resolver')]
class NewsResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly ContentUrlResolverInterface $inner,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(object $content): ContentUrlResult|null
    {
        if (!$content instanceof NewsModel) {
            return null;
        }

        if (!$archive = NewsArchiveModel::findById($content->pid)) {
            return $this->inner->resolve($content);
        }

        if (!$archive->enable_simple_urls) {
            return $this->inner->resolve($content);
        }

        try {
            return ContentUrlResult::url($this->urlGenerator->generate('tl_news.'.$content->alias));
        } catch (ExceptionInterface) {
            return $this->inner->resolve($content);
        }
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        return $this->inner->getParametersForContent($content, $pageModel);
    }
}
