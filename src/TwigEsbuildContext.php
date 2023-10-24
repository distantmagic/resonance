<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
readonly class TwigEsbuildContext
{
    /**
     * @var WeakMap<Request,EsbuildMetaEntryPoints>
     */
    private WeakMap $entryPoints;

    private EsbuildMeta $esbuildMeta;

    public function __construct(
        ApplicationConfiguration $applicationConfiguration,
        EsbuildMetaBuilder $esbuildMetaBuilder,
    ) {
        /**
         * @var WeakMap<Request,EsbuildMetaEntryPoints>
         */
        $this->entryPoints = new WeakMap();
        $this->esbuildMeta = $esbuildMetaBuilder->build($applicationConfiguration->esbuildMetafile);
    }

    public function getEntryPoints(Request $request): EsbuildMetaEntryPoints
    {
        if (!$this->entryPoints->offsetExists($request)) {
            $this->entryPoints->offsetSet($request, new EsbuildMetaEntryPoints($this->esbuildMeta));
        }

        return $this->entryPoints->offsetGet($request);
    }
}
