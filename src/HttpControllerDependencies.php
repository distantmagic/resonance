<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponder\Error\ServerError;

#[Singleton]
readonly class HttpControllerDependencies
{
    public function __construct(
        public BadRequest $badRequest,
        public Forbidden $forbidden,
        public HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        public HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate,
        public PageNotFound $pageNotFound,
        public ServerError $serverError,
    ) {}
}
