<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\PriorityQueue;
use Ds\Stack;
use Generator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<DialogueResponseInterface>
 */
readonly class DialogueResponseSortedIterator implements IteratorAggregate
{
    /**
     * @param iterable<DialogueResponseInterface> $responses
     */
    public function __construct(
        private iterable $responses,
    ) {}

    /**
     * @return Generator<DialogueResponseInterface>
     */
    public function getIterator(): Generator
    {
        /**
         * @var PriorityQueue<DialogueResponseInterface> $responsesPriorityQueue
         */
        $responsesPriorityQueue = new PriorityQueue();

        foreach ($this->responses as $response) {
            $responsesPriorityQueue->push(
                $response,
                $response->getCost(),
            );
        }

        /**
         * @var Stack<DialogueResponseInterface> $sortedResponses
         */
        $sortedResponses = new Stack();

        foreach ($responsesPriorityQueue as $response) {
            $sortedResponses->push($response);
        }

        foreach ($sortedResponses as $response) {
            yield $response;
        }
    }
}
