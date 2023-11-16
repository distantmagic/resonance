<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TranslationException;

use Distantmagic\Resonance\TranslationException;

class MissingTranslationParameterException extends TranslationException
{
    public function __construct(string $phrase, string $missing)
    {
        parent::__construct("Translation parameter [{$missing}] is missing for translation string: {$phrase}");
    }
}
