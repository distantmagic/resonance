<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class HttpInterceptorAggregate
{
    /**
     * @var Map<
     *     class-string<HttpInterceptableInterface>,
     *     HttpInterceptorInterface
     * >
     */
    public Map $interceptors;

    public function __construct()
    {
        $this->interceptors = new Map();
    }
}
