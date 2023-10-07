<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Resonance\Attribute\Singleton;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\BadRequest as BadRequestEntity;
use Resonance\HttpResponder\Error;

#[Singleton]
final readonly class BadRequest extends Error
{
    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        BadRequestEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }
}
