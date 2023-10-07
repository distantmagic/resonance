<?php

declare(strict_types=1);

namespace Resonance\TranslationException;

use Resonance\SupportedLanguageCodeInterface;
use Resonance\TranslationException;

class LanguageNotFoundException extends TranslationException
{
    public function __construct(SupportedLanguageCodeInterface $language)
    {
        parent::__construct('Translations are not loaded for language: '.$language->getName());
    }
}
