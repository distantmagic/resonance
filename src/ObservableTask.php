<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;
use Throwable;

readonly class ObservableTask implements ObservableTaskInterface
{
    /**
     * @var Closure():iterable<ObservableTaskStatusUpdate>
     */
    private Closure $iterableTask;

    /**
     * @param callable():iterable<ObservableTaskStatusUpdate> $iterableTask
     */
    public function __construct(callable $iterableTask)
    {
        $this->iterableTask = Closure::fromCallable($iterableTask);
    }

    public function getIterator(): Generator
    {
        try {
            yield from ($this->iterableTask)();
        } catch (Throwable $throwable) {
            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Failed,
                $throwable,
            );
        }
    }
}
