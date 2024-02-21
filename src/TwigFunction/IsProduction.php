<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class IsProduction extends TwigFunction
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
    ) {}

    public function __invoke(): bool
    {
        return Environment::Production === $this->applicationConfiguration->environment;
    }

    public function getName(): string
    {
        return 'is_production';
    }
}
