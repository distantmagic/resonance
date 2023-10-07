<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\Forbidden as ForbiddenEntity;
use Distantmagic\Resonance\HttpResponder\Error;

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
