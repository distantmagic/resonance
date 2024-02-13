<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use RuntimeException;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
readonly class TwigEsbuildContext
{
    /**
     * @var WeakMap<Request,EsbuildMetaEntryPoints>
     */
    private WeakMap $entryPoints;

    private ?EsbuildMeta $esbuildMeta;

    public function __construct(
        ApplicationConfiguration $applicationConfiguration,
        EsbuildMetaBuilder $esbuildMetaBuilder,
    ) {
        /**
         * @var WeakMap<Request,EsbuildMetaEntryPoints>
         */
        $this->entryPoints = new WeakMap();
        $this->esbuildMeta = is_string($applicationConfiguration->esbuildMetafile)
            ? $esbuildMetaBuilder->build($applicationConfiguration->esbuildMetafile)
            : null;
    }

    public function getEntryPoints(Request $request): EsbuildMetaEntryPoints
    {
        if (is_null($this->esbuildMeta)) {
            throw new RuntimeException("You need to provide application's esbuild metafile to use esbuild in Twig");
        }

        if (!$this->entryPoints->offsetExists($request)) {
            $this->entryPoints->offsetSet($request, new EsbuildMetaEntryPoints($this->esbuildMeta));
        }

        return $this->entryPoints->offsetGet($request);
    }
}
