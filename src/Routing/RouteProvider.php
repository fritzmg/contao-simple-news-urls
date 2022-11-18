<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Simple News URLs extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoSimpleNewsUrls\Routing;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Page\PageRoute;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteProvider implements RouteProviderInterface
{
    private $db;
    private $framework;

    public function __construct(Connection $db, ContaoFramework $framework)
    {
        $this->db = $db;
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = new RouteCollection();

        /** @var array $archives */
        $archives = $this->db->fetchAllAssociative('SELECT id, jumpTo FROM tl_news_archive WHERE enable_simple_urls = 1');

        if (empty($archives)) {
            return $collection;
        }

        $archiveIds = array_map(static function (array $archive): int {
            return (int) $archive['id'];
        }, $archives);

        $alias = substr($request->getPathInfo(), 1);
        $news = $this->db->fetchAssociative('
                SELECT tl_news.*, tl_news_archive.jumpTo
                FROM tl_news, tl_news_archive
                WHERE tl_news.alias = ? AND tl_news.pid = tl_news_archive.id
                    AND tl_news_archive.id IN ?
                LIMIT 1
            ', [$alias, $archiveIds], [Types::STRING, Types::SIMPLE_ARRAY]
        );

        if (false === $news) {
            return $collection;
        }

        $this->framework->initialize(true);

        $page = PageModel::findByPk($news['jumpTo']);

        if (null === $page) {
            return $collection;
        }

        $route = new PageRoute($page, $request->getPathInfo());
        $collection->add('tl_news.'.$alias, $route);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name): Route
    {
        throw new RouteNotFoundException('This router does not support routes by name');
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names): array
    {
        return [];
    }
}
