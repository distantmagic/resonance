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
     * @var WeakMap<Request, SupportedLanguageCodeInterface>
     */
    private WeakMap $languages;

    public function __construct(
        private SupportedLanguageCodeRepositoryInterface $supportedLanguageCodeRepository,
    ) {
        /**
         * @var WeakMap<Request, SupportedLanguageCodeInterface>
         */
        $this->languages = new WeakMap();
    }

    public function detectPrimaryLanguage(Request $request): SupportedLanguageCodeInterface
    {
        if ($this->languages->offsetExists($request)) {
            return $this->languages->offsetGet($request);
        }

        $language = $this->doDetectPrimaryLanguage($request);

        $this->languages->offsetSet($request, $language);

        return $language;
    }

    private function doDetectPrimaryLanguage(Request $request): SupportedLanguageCodeInterface
    {
        if (!is_array($request->header)
            || !isset($request->header['accept-language'])
            || !is_string($request->header['accept-language'])
        ) {
            return $this->supportedLanguageCodeRepository->getDefault();
        }

        $acceptHeader = new AcceptHeader($request->header['accept-language']);

        foreach ($acceptHeader->sorted as $language) {
            $primaryLanguage = $this->extractPrimaryLanguageFromString($language);

            if ($primaryLanguage instanceof SupportedLanguageCodeInterface) {
                return $primaryLanguage;
            }
        }

        return $this->supportedLanguageCodeRepository->getDefault();
    }

    private function extractPrimaryLanguageFromString(string $language): ?SupportedLanguageCodeInterface
    {
        $chunks = explode('-', $language);

        if (!isset($chunks[0])) {
            return null;
        }

        return $this->supportedLanguageCodeRepository->tryFrom($chunks[0]);
    }
}
