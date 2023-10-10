<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;

#[Singleton]
readonly class HttpControllerDependencies
{
    public function __construct(
        public BadRequest $badRequest,
        public Forbidden $forbidden,
        public HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate,
        public PageNotFound $pageNotFound,
    ) {}
}
