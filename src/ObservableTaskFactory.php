<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;

/**
 * @psalm-import-type TIterableTaskCallback from ObservableTask
 */
final readonly class ObservableTaskFactory
{
    public static function withTimeout(
        callable $iterableTask,
        float $inactivityTimeout = 5.0,
        string $name = '',
        string $category = '',
    ): ObservableTask {
        return new ObservableTask(
            iterableTask: new ObservableTaskTimeoutIterator(
                iterableTask: static function () use ($iterableTask): Generator {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Running, null);

                    yield from $iterableTask();
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Finished, null);
                },
                inactivityTimeout: $inactivityTimeout,
            ),
            name: $name,
            category: $category,
        );
    }

    /**
     * @psalm-suppress UnusedConstructor this class is just a wrapper around
     *     functions
     */
    private function __construct() {}
}
