<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

/**
 * @template TData
 */
readonly class ObservableTaskStatusUpdate implements JsonSerializable
{
    /**
     * @param TData $data
     */
    public function __construct(
        public ObservableTaskStatus $status,
        public mixed $data
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status->value,
            'data' => $this->data,
        ];
    }
}
