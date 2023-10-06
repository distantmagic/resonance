<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionEsbuildPreloads
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
