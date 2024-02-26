<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\EsbuildMetaPreloadsRenderer;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigEsbuildContext;
use Distantmagic\Resonance\TwigFunction;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class EsbuildRenderPreloads extends TwigFunction
{
    public function __construct(private TwigEsbuildContext $esbuildContext) {}

    public function __invoke(ServerRequestInterface $request): string
    {
        $entryPoints = $this->esbuildContext->getEntryPoints($request);
        $renderer = new EsbuildMetaPreloadsRenderer($entryPoints);

        return $renderer->render();
    }

    public function getName(): string
    {
        return 'esbuild_render_preloads';
    }
}
