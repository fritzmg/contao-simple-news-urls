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
    private static $routes = [];
    private static $allLoaded = false;

    public function __construct(Connection $db, ContaoFramework $framework)
    {
        $this->db = $db;
        $this->framework = $framework;
    }

    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        $collection = new RouteCollection();
        $alias = substr($request->getPathInfo(), 1);

        // We are only interested in URLs with one fragment
        if (count(explode('/', $alias)) > 1) {
            return $collection;
        }

        $name = 'tl_news.'.rawurldecode($alias);

        try {
            $route = $this->getRouteByName($name);

            $collection->add($name, $route);
        } catch (RouteNotFoundException $e) {
            // Do nothing
        }

        return $collection;
    }

    public function getRouteByName($name): Route
    {
        if (0 !== strncmp($name, 'tl_news.', 8)) {
            throw new RouteNotFoundException('Route name is not a news entry.');
        }

        if (isset(self::$routes[$name])) {
            return self::$routes[$name];
        }

        if (self::$allLoaded) {
            throw new RouteNotFoundException('Route "'.$name.'" not found');
        }

        $news = $this->getEnabledNews([$name]);

        if (empty($news)) {
            throw new RouteNotFoundException('Route name does not match a news entry.');
        }

        $this->addRouteForNews($news[0]);

        if (!\array_key_exists($name, self::$routes)) {
            throw new RouteNotFoundException('Route "'.$name.'" not found');
        }

        return self::$routes[$name];
    }

    public function getRoutesByNames(?array $names = null): iterable
    {
        if (null === $names) {
            if (self::$allLoaded) {
                return self::$routes;
            }

            $news = $this->getEnabledNews();

            foreach ($news as $entry) {
                $this->addRouteForNews($entry);
            }

            self::$allLoaded = true;

            return self::$routes;
        }

        $routes = [];

        foreach ($names as $name) {
            if (!isset(self::$routes[$name])) {
                $news = $this->getEnabledNews($names);

                foreach ($news as $entry) {
                    $this->addRouteForNews($entry);
                }
            }

            if (isset(self::$routes[$name])) {
                $routes[$name] = self::$routes[$name];
            }
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

    private function addRouteForNews(array $news): void
    {
        if (!$news['alias']) {
            return;
        }

        $name = 'tl_news.'.$news['alias'];

        if (isset(self::$routes[$name])) {
            return;
        }

        $this->framework->initialize(true);

        $page = PageModel::findByPk($news['archiveJumpTo']);

        if (null === $page) {
            return;
        }

        // Register a new page route for this page under the news alias
        $route = new NewsRoute($page, '/'.$news['alias'], ['_canonical_route' => $name]);

        // News URLs are supposed to be example.com/<news-alias>
        $route->setUrlPrefix('');
        $route->setUrlSuffix('');

        self::$routes[$name] = $route;
    }
}
