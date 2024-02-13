<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ConfigurationFile
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(public array $config) {}
}
