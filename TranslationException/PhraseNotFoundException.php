<?php

declare(strict_types=1);

namespace Resonance\TranslationException;

use App\SupportedPrimaryLanguageCode;
use Resonance\TranslationException;

class PhraseNotFoundException extends TranslationException
{
    public function __construct(
        SupportedPrimaryLanguageCode $language,
        string $phrase,
    ) {
        parent::__construct('Phrase is not defined: '.$language->value.'/'.$phrase);
    }
}
