<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;
use Throwable;

/**
 * @psalm-type TIterableTaskCallback = callable():iterable<ObservableTaskStatusUpdate>
 */
readonly class ObservableTask implements ObservableTaskInterface
{
    /**
     * @var Closure():iterable<ObservableTaskStatusUpdate>
     */
    private Closure $iterableTask;

    /**
     * @param TIterableTaskCallback $iterableTask
     */
    public function __construct(
        callable $iterableTask,
        private string $name = '',
        private string $category = '',
    ) {
        $this->iterableTask = Closure::fromCallable($iterableTask);
    }

    public function getCategory(): string
    {
        return $this->category;
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

    public function getName(): string
    {
        return $this->name;
    }
}
