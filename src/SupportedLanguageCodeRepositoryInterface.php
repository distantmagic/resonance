<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface SupportedLanguageCodeRepositoryInterface
{
    /**
     * @return array<SupportedLanguageCodeInterface>
     */
    public function cases(): array;

    public function getDefault(): SupportedLanguageCodeInterface;

    public function tryFrom(string $languageCode): ?SupportedLanguageCodeInterface;
}
