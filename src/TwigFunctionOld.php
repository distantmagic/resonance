<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionOld
{
    public function __construct(private TemplateFilters $filters) {}

    public function __invoke(
        Request $request,
        string $fieldName,
        ?string $defaultValue = null,
    ): string {
        return $this->filters->old(
            $request->post,
            $fieldName,
            $defaultValue,
        );
    }
}
