<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use ReflectionClass;

readonly class OpenAPIPathItem
{
    /**
     * @param ReflectionClass<HttpResponderInterface> $reflectionClass
     */
    public function __construct(
        public OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
        public ReflectionClass $reflectionClass,
        public RespondsToHttp $respondsToHttp,
    ) {}
}
