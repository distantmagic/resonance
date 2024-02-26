<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteActionInterface;
use Distantmagic\Resonance\TwigFunction;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class GatekeeperCan extends TwigFunction
{
    public function __construct(private Gatekeeper $gatekeeper) {}

    public function __invoke(
        ServerRequestInterface $request,
        SiteActionInterface|string $siteAction,
    ): bool {
        if (!is_string($siteAction)) {
            return $this->gatekeeper->withRequest($request)->can($siteAction);
        }

        $resolvedAction = constant(sprintf('App\\SiteAction::%s', $siteAction));

        if (!($resolvedAction instanceof SiteActionInterface)) {
            throw new RuntimeException(sprintf('Expected "%s"', SiteActionInterface::class));
        }

        return $this->gatekeeper->withRequest($request)->can($resolvedAction);
    }

    public function getName(): string
    {
        return 'can';
    }
}
