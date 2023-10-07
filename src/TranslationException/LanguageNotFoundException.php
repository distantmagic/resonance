<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TranslationException;

use Distantmagic\Resonance\SupportedLanguageCodeInterface;
use Distantmagic\Resonance\TranslationException;

class LanguageNotFoundException extends TranslationException
{
    public function __construct(SupportedLanguageCodeInterface $language)
    {
        parent::__construct('Translations are not loaded for language: '.$language->getName());
    }
}
