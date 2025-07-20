<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoSimpleNewsUrls;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoSimpleNewsUrlsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
