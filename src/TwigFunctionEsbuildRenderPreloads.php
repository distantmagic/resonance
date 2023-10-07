<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionEsbuildRenderPreloads
{
    public function __construct(private TwigEsbuildContext $esbuildContext) {}

    public function __invoke(Request $request): string
    {
        $entryPoints = $this->esbuildContext->getEntryPoints($request);
        $renderer = new EsbuildMetaPreloadsRenderer($entryPoints);

        return $renderer->render();
    }
}
