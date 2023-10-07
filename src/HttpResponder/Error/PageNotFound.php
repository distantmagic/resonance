<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\PageNotFound as PageNotFoundEntity;
use Distantmagic\Resonance\HttpResponder\Error;

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
