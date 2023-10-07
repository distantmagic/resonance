<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Swoole\Http\Request;

readonly class ContentTypeResponder
{
    /**
     * @var Set<ContentType>
     */
    public Set $responders;

    public function __construct()
    {
        /**
         * @var Set<ContentType>
         */
        $this->responders = new Set();
    }

    public function best(Request $request): ?ContentType
    {
        $acceptHeader = AcceptHeader::mime($request);

        $best = null;
        $bestValue = -1;

        foreach ($this->responders as $responderContentType) {
            $quality = $acceptHeader->getQuality($responderContentType->value);

            if (is_int($quality) && $quality > $bestValue) {
                $best = $responderContentType;
                $bestValue = $quality;
            }
        }

        return $best;
    }
}
