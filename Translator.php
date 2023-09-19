<?php

declare(strict_types=1);

namespace Resonance;

use App\SupportedPrimaryLanguageCode;
use Ds\Map;
use Resonance\TranslationException\LanguageNotFoundException;
use Resonance\TranslationException\PhraseNotFoundException;
use Swoole\Http\Request;

readonly class Translator
{
    /**
     * @var Map<SupportedPrimaryLanguageCode, Map<string, string>>
     */
    public Map $translations;

    public function __construct(private HttpRequestLanguageDetector $languageDetector)
    {
        $this->translations = new Map();
    }

    public function trans(Request $request, string $phrase): string
    {
        $language = $this->languageDetector->detectPrimaryLanguage($request);

        if (!$this->translations->hasKey($language)) {
            throw new LanguageNotFoundException($language);
        }

        $phrases = $this->translations->get($language);

        if (!$phrases->hasKey($phrase)) {
            throw new PhraseNotFoundException($language, $phrase);
        }

        return $phrases->get($phrase);
    }
}
