<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TranslationException;

use Distantmagic\Resonance\TranslationException;

class PhraseNotFoundException extends TranslationException
{
    public function __construct(
        string $language,
        string $phrase,
    ) {
        parent::__construct('Phrase is not defined: '.$language.'/'.$phrase);
    }
}
