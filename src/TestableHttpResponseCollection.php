<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\TestableHttpResponse;
use Ds\Map;
use Ds\Set;

readonly class TestableHttpResponseCollection
{
    /**
     * @var Map<HttpResponderInterface,Set<TestableHttpResponse>>
     */
    public Map $httpResponder;

    /**
     * @var Map<TestableHttpResponse,Map<int,RespondsWith>>
     */
    public Map $testableHttpResponse;

    public function __construct()
    {
        $this->httpResponder = new Map();
        $this->testableHttpResponse = new Map();
    }

    public function registerTestableHttpResponse(
        HttpResponderInterface $httpResponder,
        TestableHttpResponse $testableHttpResponse,
        RespondsWith $respondsWith,
    ): void {
        if (!$this->httpResponder->hasKey($httpResponder)) {
            $this->httpResponder->put($httpResponder, new Set());
        }

        $this->httpResponder->get($httpResponder)->add($testableHttpResponse);

        if (!$this->testableHttpResponse->hasKey($testableHttpResponse)) {
            $this->testableHttpResponse->put($testableHttpResponse, new Map());
        }

        $this->testableHttpResponse->get($testableHttpResponse)->put(
            $respondsWith->status,
            $respondsWith,
        );
    }
}
