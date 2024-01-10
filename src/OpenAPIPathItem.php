<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Ds\Set;
use ReflectionClass;

readonly class OpenAPIPathItem
{
    /**
     * @param ReflectionClass<HttpResponderInterface> $reflectionClass
     * @param Set<OAuth2ScopeInterface>               $requiredOAuth2Scopes
     */
    public function __construct(
        public OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
        public ReflectionClass $reflectionClass,
        public Set $requiredOAuth2Scopes,
        public RespondsToHttp $respondsToHttp,
    ) {}
}
