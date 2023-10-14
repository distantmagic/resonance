<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TranslationException;

use Distantmagic\Resonance\TranslationException;

class LanguageNotFoundException extends TranslationException
{
    public function __construct(string $language)
    {
        parent::__construct('Translations are not loaded for language: '.$language);
    }
}
