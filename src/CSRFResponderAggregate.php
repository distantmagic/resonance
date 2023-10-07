<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class CSRFResponderAggregate
{
    /**
     * @var Map<HttpResponderInterface,RequestDataSource> $httpResponders
     */
    public Map $httpResponders;

    public function __construct()
    {
        $this->httpResponders = new Map();
    }
}
