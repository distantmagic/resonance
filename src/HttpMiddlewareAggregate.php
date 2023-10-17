<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\PriorityQueue;
use Ds\Set;

readonly class HttpMiddlewareAggregate
{
    /**
     * @var Map<
     *     class-string<HttpInterceptableInterface|HttpResponderInterface>,
     *     Set<HttpMiddlewareAttribute>
     * >
     */
    public Map $middlewares;

    /**
     * @var Map<
     *     class-string<HttpInterceptableInterface|HttpResponderInterface>,
     *     PriorityQueue<HttpMiddlewareAttribute>
     * >
     */
    private Map $unsorted;

    public function __construct()
    {
        $this->middlewares = new Map();
        $this->unsorted = new Map();
    }

    /**
     * @param class-string<HttpInterceptableInterface|HttpResponderInterface> $httpResponderClassName
     */
    public function registerPreprocessor(
        HttpMiddlewareInterface $httpMiddleware,
        string $httpResponderClassName,
        Attribute $attribute,
        int $priority,
    ): void {
        if (!$this->unsorted->hasKey($httpResponderClassName)) {
            $this->middlewares->put($httpResponderClassName, new Set());

            /**
             * @var PriorityQueue<HttpMiddlewareAttribute>
             */
            $unsortedQueue = new PriorityQueue();

            $this->unsorted->put($httpResponderClassName, $unsortedQueue);
        }

        $this->unsorted->get($httpResponderClassName)->push(
            new HttpMiddlewareAttribute(
                $httpMiddleware,
                $attribute,
            ),
            $priority,
        );
    }

    public function sortPreprocessors(): void
    {
        foreach ($this->unsorted as $httpResponderClassName => $processorAttributes) {
            foreach ($processorAttributes as $processorAttribute) {
                $this->middlewares->get($httpResponderClassName)->add($processorAttribute);
            }
        }
    }
}
