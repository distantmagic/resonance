<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use RuntimeException;

class CurlException extends RuntimeException
{
    public function __construct(CurlHandle $ch)
    {
        parent::__construct((string) new CurlErrorMessage($ch));
    }
}
