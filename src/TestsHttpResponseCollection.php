<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\TestsHttpResponse;
use Ds\Map;
use Ds\Set;

readonly class TestsHttpResponseCollection
{
    /**
     * @var Map<HttpResponderInterface,Set<TestsHttpResponse>>
     */
    public Map $httpResponder;

    /**
     * @var Map<TestsHttpResponse,Map<int,RespondsWith>>
     */
    public Map $testsHttpResponse;

    public function __construct()
    {
        $this->httpResponder = new Map();
        $this->testsHttpResponse = new Map();
    }

    public function registerTestableHttpResponse(
        HttpResponderInterface $httpResponder,
        TestsHttpResponse $testsHttpResponse,
        RespondsWith $respondsWith,
    ): void {
        if (!$this->httpResponder->hasKey($httpResponder)) {
            $this->httpResponder->put($httpResponder, new Set());
        }

        $this->httpResponder->get($httpResponder)->add($testsHttpResponse);

        if (!$this->testsHttpResponse->hasKey($testsHttpResponse)) {
            $this->testsHttpResponse->put($testsHttpResponse, new Map());
        }

        $this->testsHttpResponse->get($testsHttpResponse)->put(
            $respondsWith->status,
            $respondsWith,
        );
    }
}
