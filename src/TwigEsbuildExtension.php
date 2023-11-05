<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

#[Singleton(collection: SingletonCollection::TwigExtension)]
#[TwigExtension]
readonly class TwigEsbuildExtension implements ExtensionInterface
{
    public function __construct(
        private TwigFunctionEsbuild $functionEsbuild,
        private TwigFunctionEsbuildPreload $functionEsbuildPreload,
        private TwigFunctionEsbuildRenderPreloads $functionEsbuildRenderPreloads,
    ) {}

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('esbuild', $this->functionEsbuild, $safe),
            new TwigFunction('esbuild_preload', $this->functionEsbuildPreload, $safe),
            new TwigFunction('esbuild_render_preloads', $this->functionEsbuildRenderPreloads, $safe),
        ];
    }

    public function getNodeVisitors()
    {
        return [];
    }

    public function getOperators()
    {
        return [
            [],
            [],
        ];
    }

    public function getTests()
    {
        return [];
    }

    public function getTokenParsers()
    {
        return [];
    }
}
