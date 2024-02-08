<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

#[Singleton(collection: SingletonCollection::TwigExtension)]
#[TwigExtension]
readonly class TwigBridgeExtension implements ExtensionInterface
{
    public function __construct(
        private TwigFilterCollection $twigFilterCollection,
        private TwigFunctionCollection $twigFunctionCollection,
    ) {}

    public function getFilters()
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        $ret = [];

        foreach ($this->twigFilterCollection->twigFilters as $twigFilter) {
            /**
             * @psalm-suppress InvalidArgument twig filters are callable
             */
            $ret[] = new TwigFilter($twigFilter->getName(), $twigFilter, $safe);
        }

        return $ret;
    }

    public function getFunctions()
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        $ret = [];

        foreach ($this->twigFunctionCollection->twigFunctions as $twigFunction) {
            /**
             * @psalm-suppress InvalidArgument twig functions are callable
             */
            $ret[] = new TwigFunction($twigFunction->getName(), $twigFunction, $safe);
        }

        return $ret;
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
