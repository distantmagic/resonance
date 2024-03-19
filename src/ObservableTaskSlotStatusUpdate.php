<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TData
 */
readonly class ObservableTaskSlotStatusUpdate
{
    /**
     * @param ObservableTaskStatusUpdate<TData> $observableTaskStatusUpdate
     */
    public function __construct(
        public string $slotId,
        public ObservableTaskStatusUpdate $observableTaskStatusUpdate,
    ) {}
}
