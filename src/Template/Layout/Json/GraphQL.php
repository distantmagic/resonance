<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\Layout\Json;

use JsonSerializable;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\GraphQLExecutionPromise;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\Template\Layout\Json;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton]
readonly class GraphQL extends Json
{
    /**
     * @var WeakMap<Request,GraphQLExecutionPromise> $responseData
     */
    public WeakMap $executionPromises;

    public function __construct(SecurityPolicyHeaders $securityPolicyHeaders)
    {
        parent::__construct($securityPolicyHeaders);

        /**
         * @var WeakMap<Request,GraphQLExecutionPromise>
         */
        $this->executionPromises = new WeakMap();
    }

    protected function renderJson(Request $request, Response $response): JsonSerializable
    {
        return $this->executionPromises->offsetGet($request);
    }
}
