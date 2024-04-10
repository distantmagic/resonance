<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\PriorityQueue;
use Ds\Set;

readonly class HttpMiddlewareAggregate
{
    /**
     * @var Map<HttpResponderInterface,Set<HttpMiddlewareAttribute>>
     */
    public Map $middlewares;

    /**
     * @var Map<HttpResponderInterface,PriorityQueue<HttpMiddlewareAttribute>>
     */
    private Map $unsorted;

    public function __construct()
    {
        $this->middlewares = new Map();
        $this->unsorted = new Map();
    }

    public function registerPreprocessor(
        Attribute $attribute,
        HttpMiddlewareInterface $httpMiddleware,
        HttpResponderInterface $httpResponder,
        int $priority,
    ): void {
        if (!$this->unsorted->hasKey($httpResponder)) {
            $this->middlewares->put($httpResponder, new Set());

            /**
             * @var PriorityQueue<HttpMiddlewareAttribute>
             */
            $unsortedQueue = new PriorityQueue();

            $this->unsorted->put($httpResponder, $unsortedQueue);
        }

        $this->unsorted->get($httpResponder)->push(
            new HttpMiddlewareAttribute(
                $httpMiddleware,
                $attribute,
            ),
            $priority,
        );
    }

    public function sortPreprocessors(): void
    {
        foreach ($this->unsorted as $httpResponder => $processorAttributes) {
            foreach ($processorAttributes as $processorAttribute) {
                $this->middlewares->get($httpResponder)->add($processorAttribute);
            }
        }
    }
}
