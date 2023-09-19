<?php

declare(strict_types=1);

namespace Resonance;

use App\SupportedPrimaryLanguageCode;
use Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
final class HttpRequestLanguageDetector
{
    /**
     * @var WeakMap<Request, SupportedPrimaryLanguageCode>
     */
    private WeakMap $languages;

    public function __construct()
    {
        /**
         * @var WeakMap<Request, SupportedPrimaryLanguageCode>
         */
        $this->languages = new WeakMap();
    }

    public function detectPrimaryLanguage(Request $request): SupportedPrimaryLanguageCode
    {
        if ($this->languages->offsetExists($request)) {
            return $this->languages->offsetGet($request);
        }

        $language = $this->doDetectPrimaryLanguage($request);

        $this->languages->offsetSet($request, $language);

        return $language;
    }

    public function getDefault(): SupportedPrimaryLanguageCode
    {
        return SupportedPrimaryLanguageCode::EN;
    }

    private function doDetectPrimaryLanguage(Request $request): SupportedPrimaryLanguageCode
    {
        if (!is_array($request->header)
            || !isset($request->header['accept-language'])
            || !is_string($request->header['accept-language'])
        ) {
            return $this->getDefault();
        }

        $acceptHeader = new AcceptHeader($request->header['accept-language']);

        foreach ($acceptHeader->sorted as $language) {
            $primaryLanguage = $this->extractPrimaryLanguageFromString($language);

            if ($primaryLanguage instanceof SupportedPrimaryLanguageCode) {
                return $primaryLanguage;
            }
        }

        return $this->getDefault();
    }

    private function extractPrimaryLanguageFromString(string $language): ?SupportedPrimaryLanguageCode
    {
        $chunks = explode('-', $language);

        if (!isset($chunks[0])) {
            return null;
        }

        return SupportedPrimaryLanguageCode::tryFrom($chunks[0]);
    }
}
