<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class SQLiteVSSConfiguration
{
    /**
     * @param non-empty-string $extensionVector0
     * @param non-empty-string $extensionVss0
     */
    public function __construct(
        #[SensitiveParameter]
        public string $extensionVector0,
        #[SensitiveParameter]
        public string $extensionVss0,
    ) {}
}
