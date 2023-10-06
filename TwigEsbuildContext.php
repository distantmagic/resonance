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

    private EsbuildMeta $esbuildMeta;

    public function __construct()
    {
        $builder = new EsbuildMetaBuilder();

        $this->esbuildMeta = $builder->build(DM_ROOT.'/esbuild-meta-app.json');

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
