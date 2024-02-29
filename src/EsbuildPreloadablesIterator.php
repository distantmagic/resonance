<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use RuntimeException;

/**
 * @template-implements IteratorAggregate<EsbuildPreloadableType,string>
 */
readonly class EsbuildPreloadablesIterator implements IteratorAggregate
{
    public function __construct(private EsbuildMetaEntryPoints $esbuildMetaEntryPoints) {}

    /**
     * @return Generator<EsbuildPreloadableType,string>
     */
    public function getIterator(): Generator
    {
        foreach ($this->esbuildMetaEntryPoints->preloadables as $pathname) {
            yield $this->getType($pathname) => $pathname;
        }
    }

    private function getType(string $pathname): EsbuildPreloadableType
    {
        $extension = pathinfo($pathname, PATHINFO_EXTENSION);

        return match ($extension) {
            'css' => EsbuildPreloadableType::Stylesheet,
            'jpg', 'png', 'svg', 'webp' => EsbuildPreloadableType::Image,
            'js' => EsbuildPreloadableType::JavaScriptModule,
            'otf', 'ttf', 'woff2' => EsbuildPreloadableType::Font,
            default => throw new RuntimeException('Non-preloadable resource requested: '.$pathname),
        };
    }
}
