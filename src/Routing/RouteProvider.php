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
use Contao\Input;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
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

    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = new RouteCollection();
        $alias = substr($request->getPathInfo(), 1);
        $name = 'tl_news.'.$alias;
        $news = $this->getEnabledNews([$name]);

        if (empty($news)) {
            return $collection;
        }

        $routes = [];
        $this->addRouteForNews($news[0], $routes);
        $collection->add($name, $routes[$name]);

        Input::setGet('auto_item', $alias);

        return $collection;
    }

    public function getRouteByName($name): Route
    {
        $news = $this->getEnabledNews([$name]);

        if (empty($news)) {
            throw new RouteNotFoundException('Route name does not match a news entry.');
        }

        $routes = [];

        $this->addRouteForNews($news[0], $routes);

        if (!\array_key_exists($name, $routes)) {
            throw new RouteNotFoundException('Route "'.$name.'" not found');
        }

        return $routes[$name];
    }

    public function getRoutesByNames($names): array
    {
        $news = $this->getEnabledNews($names);

        $routes = [];

        foreach ($news as $entry) {
            $this->addRouteForNews($entry, $routes);
        }

        return $routes;
    }

    private function getEnabledNews(array $names = null): array
    {
        $values = [];
        $query = '
            SELECT tl_news.*, tl_news_archive.jumpTo AS archiveJumpTo
              FROM tl_news, tl_news_archive
             WHERE tl_news.pid = tl_news_archive.id
               AND tl_news_archive.enable_simple_urls = 1
        ';

        if ($names) {
            foreach ($names as $name) {
                if (0 !== strncmp($name, 'tl_news.', 8)) {
                    continue;
                }

                [, $alias] = explode('.', $name);

                $query .= ' AND tl_news.alias = ?';
                $values[] = $alias;
            }
        }

        return $this->db->fetchAllAssociative($query, $values);
    }

    private function addRouteForNews(array $news, array &$routes): void
    {
        $this->framework->initialize(true);

        $page = PageModel::findByPk($news['archiveJumpTo']);

        if (null === $page) {
            return;
        }

        $route = new PageRoute($page, '/'.$news['alias']);
        $route->setUrlPrefix('');
        $route->getPageModel()->requireItem = false;

        $routes['tl_news.'.$news['alias']] = $route;
    }
}
