<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twig\Extra\Cache\CacheRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

#[Singleton]
readonly class TwigCacheRuntimeLoader implements RuntimeLoaderInterface
{
    private CacheRuntime $cacheRuntime;

    public function __construct()
    {
        $this->cacheRuntime = new CacheRuntime(new TagAwareAdapter(new ArrayAdapter()));
    }

    public function load(string $class): ?object
    {
        if (CacheRuntime::class === $class) {
            return $this->cacheRuntime;
        }

        return null;
    }
}
