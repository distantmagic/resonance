<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Generator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<DialogueResponseInterface>
 */
readonly class DialogueResponseSortedIterator implements IteratorAggregate
{
    /**
     * @param Set<DialogueResponseInterface> $responses
     */
    public function __construct(
        private Set $responses,
    ) {}

    /**
     * @return Generator<DialogueResponseInterface>
     */
    public function getIterator(): Generator
    {
        $responses = $this->responses->toArray();

        usort($responses, $this->compareResponses(...));

        foreach ($responses as $response) {
            yield $response;
        }
    }

    private function compareResponses(DialogueResponseInterface $a, DialogueResponseInterface $b): int
    {
        return $a->getCost() <=> $b->getCost();
    }
}
