<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;

/**
 * @psalm-suppress PossiblyUnusedProperty it's used in the templates
 */
readonly class ObservableTaskTableRow
{
    public function __construct(
        public ObservableTaskStatusUpdate $observableTaskStatusUpdate,
        public string $category,
        public string $name,
        public DateTimeImmutable $modifiedAt,
    ) {}
}
