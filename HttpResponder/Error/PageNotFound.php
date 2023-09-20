<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Resonance\Attribute\Singleton;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\PageNotFound as PageNotFoundEntity;
use Resonance\HttpResponder\Error;

#[Singleton]
final readonly class PageNotFound extends Error
{
    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        PageNotFoundEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }
}
