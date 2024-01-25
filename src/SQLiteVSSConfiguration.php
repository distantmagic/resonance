<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class SQLiteVSSConfiguration
{
    public function __construct(
        #[SensitiveParameter]
        public string $extensionVector0,
        #[SensitiveParameter]
        public string $extensionVss0,
    ) {}
}
