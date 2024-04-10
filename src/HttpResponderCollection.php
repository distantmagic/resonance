<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class HttpResponderCollection
{
    /**
     * @var Map<non-empty-string,HttpResponderWithAttribute> $httpResponders
     */
    public Map $httpResponders;

    public function __construct()
    {
        $this->httpResponders = new Map();
    }
}
