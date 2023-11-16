<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\TranslationException\LanguageNotFoundException;
use Distantmagic\Resonance\TranslationException\MissingTranslationParameterException;
use Distantmagic\Resonance\TranslationException\PhraseNotFoundException;
use Ds\Map;
use Ds\Vector;
use Swoole\Http\Request;

readonly class TranslatorBridge
{
    /**
     * @var Map<string, Map<string, string>>
     */
    public Map $translations;

    /**
     * @var Map<string, Vector<string>>
     */
    protected Map $labels;

    public function __construct(private HttpRequestLanguageDetector $languageDetector)
    {
        $this->translations = new Map();
        $this->labels = new Map();
    }

    /**
     * @param array<string, string> $parameters
     */
    public function trans(Request $request, string $phrase, array $parameters = []): string
    {
        $language = $this->languageDetector->detectPrimaryLanguage($request);

        if (!$this->translations->hasKey($language)) {
            throw new LanguageNotFoundException($language);
        }

        $phrases = $this->translations->get($language);

        if (!$phrases->hasKey($phrase)) {
            throw new PhraseNotFoundException($language, $phrase);
        }

        return $this->fillParameters(
            $phrases->get($phrase),
            $parameters,
        );
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function fillParameters(string $phrase, array $parameters): string
    {
        if (!$this->labels->hasKey($phrase)) {
            $labels = new Vector($this->resolveLabels($phrase));

            $this->labels->put($phrase, $labels);
        }

        /** @var null|Vector<string> $labels */
        $labels ??= $this->labels->get($phrase, new Vector());

        foreach ($labels ?? [] as $label) {
            if (!isset($parameters[$label])) {
                throw new MissingTranslationParameterException($phrase, $label);
            }

            [$label => $parameter] = $parameters;

            $phrase = str_replace(':'.$label, $parameter, $phrase);
        }

        return $phrase;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveLabels(string $phrase): array
    {
        preg_match_all('/:([a-zA-Z_]+)/m', $phrase, $matches);

        [1 => $labels] = $matches;

        return array_unique($labels);
    }
}
