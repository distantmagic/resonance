<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionEsbuild
{
    public function __construct(private TwigEsbuildContext $esbuildContext) {}

    public function __invoke(Request $request, string $asset): string
    {
        $esbuildMetaEntryPoints = $this->esbuildContext->getEntryPoints($request);

        return '/'.$esbuildMetaEntryPoints->resolveEntryPointPath($asset);
    }
}
