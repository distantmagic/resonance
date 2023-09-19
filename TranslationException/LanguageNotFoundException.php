<?php

declare(strict_types=1);

namespace Resonance\TranslationException;

use App\SupportedPrimaryLanguageCode;
use Resonance\TranslationException;

class LanguageNotFoundException extends TranslationException
{
    public function __construct(SupportedPrimaryLanguageCode $language)
    {
        parent::__construct('Translations are not loaded for language: '.$language->value);
    }
}
