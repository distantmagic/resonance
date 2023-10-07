<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionEsbuildPreload
{
    public function __construct(private TwigEsbuildContext $esbuildContext) {}

    public function __invoke(Request $request, string $entryPoint): void
    {
        $this->esbuildContext->getEntryPoints($request)->preloadEntryPoint($entryPoint);
    }
}
