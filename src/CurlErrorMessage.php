<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use Stringable;

readonly class CurlErrorMessage implements Stringable
{
    public function __construct(private CurlHandle $curlHandle) {}

    public function __toString(): string
    {
        return sprintf(
            'curl request failed because of error: (%d)"%s"',
            curl_errno($this->curlHandle),
            curl_error($this->curlHandle),
        );
    }
}
