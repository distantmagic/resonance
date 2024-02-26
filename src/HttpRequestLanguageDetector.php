<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

#[Singleton]
final readonly class HttpRequestLanguageDetector
{
    /**
     * @var WeakMap<ServerRequestInterface,string>
     */
    private WeakMap $languages;

    public function __construct(private TranslatorConfiguration $translatorConfiguration)
    {
        /**
         * @var WeakMap<ServerRequestInterface,string>
         */
        $this->languages = new WeakMap();
    }

    public function detectPrimaryLanguage(ServerRequestInterface $request): string
    {
        if ($this->languages->offsetExists($request)) {
            return $this->languages->offsetGet($request);
        }

        $language = $this->doDetectPrimaryLanguage($request);

        $this->languages->offsetSet($request, $language);

        return $language;
    }

    private function doDetectPrimaryLanguage(ServerRequestInterface $request): string
    {
        $acceptLanguage = $request->getHeaderLine('accept-language');

        if (empty($acceptLanguage)) {
            return $this->translatorConfiguration->defaultPrimaryLanguage;
        }

        $acceptHeader = new AcceptHeader($acceptLanguage);

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
