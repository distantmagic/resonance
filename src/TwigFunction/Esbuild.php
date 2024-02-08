<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigEsbuildContext;
use Distantmagic\Resonance\TwigFunction;
use Swoole\Http\Request;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class Esbuild extends TwigFunction
{
    public function __construct(private TwigEsbuildContext $esbuildContext) {}

    public function __invoke(Request $request, string $asset): string
    {
        $esbuildMetaEntryPoints = $this->esbuildContext->getEntryPoints($request);

        return '/'.$esbuildMetaEntryPoints->resolveEntryPointPath($asset);
    }

    public function getName(): string
    {
        return 'esbuild';
    }
}
