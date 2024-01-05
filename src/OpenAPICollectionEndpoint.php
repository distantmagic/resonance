<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\BelongsToOpenAPICollection;
use Distantmagic\Resonance\Attribute\RequiresOAuth2Scope;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Ds\Set;
use ReflectionClass;

readonly class OpenAPICollectionEndpoint
{
    /**
     * @param ReflectionClass<HttpResponderInterface> $httpResponderReflectionClass
     * @param Set<RequiresOAuth2Scope>                $requiredOAuth2Scopes
     */
    public function __construct(
        public ReflectionClass $httpResponderReflectionClass,
        public BelongsToOpenAPICollection $belongsToOpenAPICollection,
        public Set $requiredOAuth2Scopes,
        public RespondsToHttp $respondsToHttp,
    ) {}
}
