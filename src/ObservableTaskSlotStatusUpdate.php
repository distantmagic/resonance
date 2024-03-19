<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

/**
 * @template TData
 */
readonly class ObservableTaskSlotStatusUpdate implements JsonSerializable
{
    /**
     * @param ObservableTaskStatusUpdate<TData> $observableTaskStatusUpdate
     */
    public function __construct(
        public string $slotId,
        public ObservableTaskStatusUpdate $observableTaskStatusUpdate,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'slot_id' => $this->slotId,
            'observable_task_status_update' => $this->observableTaskStatusUpdate,
        ];
    }
}
