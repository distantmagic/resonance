<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\PriorityQueue;
use Ds\Set;

readonly class HttpPreprocessorAggregate
{
    /**
     * @var Map<
     *     class-string<HttpResponderInterface>,
     *     Set<HttpPreprocessorAttribute>
     * >
     */
    public Map $preprocessors;

    /**
     * @var Map<
     *     class-string<HttpResponderInterface>,
     *     PriorityQueue<HttpPreprocessorAttribute>
     * >
     */
    private Map $unsorted;

    public function __construct()
    {
        $this->preprocessors = new Map();
        $this->unsorted = new Map();
    }

    /**
     * @param class-string<HttpResponderInterface> $httpResponderClassName
     */
    public function registerPreprocessor(
        HttpPreprocessorInterface $httpPreprocessor,
        string $httpResponderClassName,
        Attribute $attribute,
        int $priority,
    ): void {
        if (!$this->unsorted->hasKey($httpResponderClassName)) {
            $this->preprocessors->put($httpResponderClassName, new Set());

            /**
             * @var PriorityQueue<HttpPreprocessorAttribute>
             */
            $unsortedQueue = new PriorityQueue();

            $this->unsorted->put($httpResponderClassName, $unsortedQueue);
        }

        $this->unsorted->get($httpResponderClassName)->push(
            new HttpPreprocessorAttribute(
                $httpPreprocessor,
                $attribute,
            ),
            $priority,
        );
    }

    public function sortPreprocessors(): void
    {
        foreach ($this->unsorted as $httpResponderClassName => $processorAttributes) {
            foreach ($processorAttributes as $processorAttribute) {
                $this->preprocessors->get($httpResponderClassName)->add($processorAttribute);
            }
        }
    }
}
