<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionEsbuildRenderPreloads
{
    public function __construct(
        private TemplateFilters $filters,
        private TwigEsbuildContext $esbuildContext,
    ) {}

    public function __invoke(Request $request): string
    {
        $renderer = new EsbuildMetaPreloadsRenderer(
            $this->esbuildContext->getEntryPoints($request),
            $this->filters,
        );

        return $renderer->render();
    }
}
