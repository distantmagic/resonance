<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Resonance\Attribute\Singleton;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\MethodNotAllowed as MethodNotAllowedEntity;
use Resonance\HttpResponder\Error;

#[Singleton]
final readonly class MethodNotAllowed extends Error
{
    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        MethodNotAllowedEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }
}
