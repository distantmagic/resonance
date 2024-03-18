<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;

/**
 * @template TTaskStatus of ObservableTaskStatusUpdate
 *
 * @template-implements ObservableTaskInterface<TTaskStatus>
 */
readonly class ObservableTask implements ObservableTaskInterface
{
    /**
     * @param Closure():Generator<TTaskStatus> $iterableTask
     */
    public function __construct(private Closure $iterableTask) {}

    public function getIterator(): Generator
    {
        yield from ($this->iterableTask)();
    }
}
