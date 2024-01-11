<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\HttpResponder\HttpController;
use Ds\Map;

readonly class HttpControllerReflectionMethodCollection
{
    /**
     * @var Map<class-string<HttpController>,HttpControllerReflectionMethod>
     */
    public Map $reflectionMethods;

    public function __construct(
    ) {
        $this->reflectionMethods = new Map();
    }
}
