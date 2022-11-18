<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Simple News URLs extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoSimpleNewsUrlsBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use InspiredMinds\ContaoSimpleNewsUrlsBundle\ContaoSimpleNewsUrlsBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoSimpleNewsUrlsBundle::class)
                ->setLoadAfter([ContaoNewsBundle::class]),
        ];
    }
}
