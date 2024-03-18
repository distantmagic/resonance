<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class ObservableTaskConfiguration
{
    /**
     * @psalm-taint-source system_secret $maxTasks
     */
    public function __construct(
        #[SensitiveParameter]
        public int $maxTasks,
        public int $serializedStatusSize,
    ) {}
}
