<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
final class HttpRequestLanguageDetector
{
    /**
     * @var WeakMap<Request, string>
     */
    private WeakMap $languages;

    public function __construct(private TranslatorConfiguration $translatorConfiguration)
    {
        /**
         * @var WeakMap<Request, string>
         */
        $this->languages = new WeakMap();
    }

    public function detectPrimaryLanguage(Request $request): string
    {
        if ($this->languages->offsetExists($request)) {
            return $this->languages->offsetGet($request);
        }

        $language = $this->doDetectPrimaryLanguage($request);

        $this->languages->offsetSet($request, $language);

        return $language;
    }

    private function doDetectPrimaryLanguage(Request $request): string
    {
        if (!is_array($request->header)
            || !isset($request->header['accept-language'])
            || !is_string($request->header['accept-language'])
        ) {
            return $this->translatorConfiguration->defaultPrimaryLanguage;
        }

        $acceptHeader = new AcceptHeader($request->header['accept-language']);

        foreach ($acceptHeader->sorted as $language) {
            $primaryLanguage = $this->extractPrimaryLanguageFromString($language);

            if (is_string($primaryLanguage)) {
                return $primaryLanguage;
            }
        }

        return $this->translatorConfiguration->defaultPrimaryLanguage;
    }

    private function extractPrimaryLanguageFromString(string $language): ?string
    {
        $chunks = explode('-', $language);

        if (!isset($chunks[0])) {
            return null;
        }

        return $chunks[0];
    }
}
