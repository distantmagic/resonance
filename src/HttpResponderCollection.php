<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class HttpResponderCollection
{
    /**
     * @var Map<class-string<HttpResponderInterface>,HttpResponderInterface> $httpResponders
     */
    public Map $httpResponders;

    public function __construct()
    {
        $this->httpResponders = new Map();
    }
}
