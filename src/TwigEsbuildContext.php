<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
readonly class TwigEsbuildContext
{
    /**
     * @var WeakMap<Request,EsbuildMetaEntryPoints>
     */
    private WeakMap $entryPoints;

    public function __construct(private EsbuildMeta $esbuildMeta)
    {
        /**
         * @var WeakMap<Request,EsbuildMetaEntryPoints>
         */
        $this->entryPoints = new WeakMap();
    }

    public function getEntryPoints(Request $request): EsbuildMetaEntryPoints
    {
        if (!$this->entryPoints->offsetExists($request)) {
            $this->entryPoints->offsetSet($request, new EsbuildMetaEntryPoints($this->esbuildMeta));
        }

        return $this->entryPoints->offsetGet($request);
    }
}
