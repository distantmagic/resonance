<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\BadRequest as BadRequestEntity;
use Distantmagic\Resonance\HttpResponder\Error;

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
