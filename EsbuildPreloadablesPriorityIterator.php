<?php

declare(strict_types=1);

namespace Resonance;

use Ds\PriorityQueue;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<EsbuildPreloadable>
 */
readonly class EsbuildPreloadablesPriorityIterator implements IteratorAggregate
{
    public function __construct(private EsbuildPreloadablesIterator $preloadables) {}

    /**
     * @return PriorityQueue<EsbuildPreloadable>
     */
    public function getIterator(): PriorityQueue
    {
        /**
         * @var PriorityQueue<EsbuildPreloadable> $sorted
         */
        $sorted = new PriorityQueue();

        foreach ($this->preloadables as $type => $pathname) {
            $preloadable = new EsbuildPreloadable($pathname, $type);

            $sorted->push($preloadable, match ($type) {
                EsbuildPreloadableType::JavaScriptModule => 10,
                EsbuildPreloadableType::Image => 20,
                EsbuildPreloadableType::Stylesheet => 30,
                EsbuildPreloadableType::Font => 40,
            });
        }

        return $sorted;
    }
}
