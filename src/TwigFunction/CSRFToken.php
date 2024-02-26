<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use Psr\Http\Message\ServerRequestInterface;

#[GrantsFeature(Feature::HttpSession)]
#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class CSRFToken extends TwigFunction
{
    public function __construct(private CSRFManager $csrfManager) {}

    public function __invoke(ServerRequestInterface $request): string
    {
        return $this->csrfManager->prepareSessionToken($request);
    }

    public function getName(): string
    {
        return 'csrf_token';
    }
}
