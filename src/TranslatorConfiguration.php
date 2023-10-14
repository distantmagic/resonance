<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class TranslatorConfiguration
{
    public function __construct(
        public string $baseDirectory,
        public string $defaultPrimaryLanguage,
    ) {}
}
