<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\CSPNonceManager;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class CSPNonce extends TwigFunction
{
    public function __construct(private CSPNonceManager $cspNonceManager) {}

    public function __invoke(ServerRequestInterface $request): string
    {
        return $this->cspNonceManager->getRequestNonce($request);
    }

    public function getName(): string
    {
        return 'csp_nonce';
    }
}
