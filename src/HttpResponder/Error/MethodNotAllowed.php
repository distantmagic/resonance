<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\MethodNotAllowed as MethodNotAllowedEntity;
use Distantmagic\Resonance\HttpResponder\Error;

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
