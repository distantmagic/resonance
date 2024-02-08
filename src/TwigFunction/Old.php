<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TemplateFilters;
use Distantmagic\Resonance\TwigFunction;
use Swoole\Http\Request;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class Old extends TwigFunction
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

    public function getName(): string
    {
        return 'old';
    }
}
