<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Resonance\HttpResponder\Error\BadRequest;
use Resonance\HttpResponder\Error\Forbidden;
use Resonance\HttpResponder\Error\PageNotFound;


#[Singleton]
readonly class ControllerDependencies
{
    public function __construct(
        public BadRequest $badRequest,
        public Forbidden $forbidden,
        public Gatekeeper $gatekeeper,
        public HttpRouteMatchRegistry $routeMatchRegistry,
        public HttpRouteParameterBinderAggregate $routeParameterBinderAggregate,
        public PageNotFound $pageNotFound,
    ) {}
}
