<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Resonance\Attribute\Singleton;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\Forbidden as ForbiddenEntity;
use Resonance\HttpResponder\Error;

#[Singleton]
final readonly class Forbidden extends Error
{
    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        ForbiddenEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }
}
