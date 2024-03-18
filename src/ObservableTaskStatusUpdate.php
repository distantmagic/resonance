<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TData
 */
readonly class ObservableTaskStatusUpdate
{
    /**
     * @param TData $data
     */
    public function __construct(
        public ObservableTaskStatus $status,
        public mixed $data
    ) {}
}
