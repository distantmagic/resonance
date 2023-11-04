<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Currently just a proxy in order to unambigously inject the twig loader.
 */
readonly class TwigChainLoader implements LoaderInterface
{
    private ChainLoader $baseChainLoader;

    public function __construct()
    {
        $this->baseChainLoader = new ChainLoader();
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->baseChainLoader->addLoader($loader);
    }

    public function exists(string $name)
    {
        return $this->baseChainLoader->exists($name);
    }

    public function getCacheKey(string $name): string
    {
        return $this->baseChainLoader->getCacheKey($name);
    }

    public function getSourceContext(string $name): Source
    {
        return $this->baseChainLoader->getSourceContext($name);
    }

    public function isFresh(string $name, int $time): bool
    {
        return $this->baseChainLoader->isFresh($name, $time);
    }
}
