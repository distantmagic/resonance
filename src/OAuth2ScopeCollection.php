<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ProvidesOAuth2Scope;
use Ds\Map;

final readonly class OAuth2ScopeCollection
{
    /**
     * @var Map<ProvidesOAuth2Scope,class-string<OAuth2ScopeInterface>> $scopes
     */
    public Map $scopes;

    public function __construct()
    {
        $this->scopes = new Map();
    }

    /**
     * @param class-string<OAuth2ScopeInterface> $scopeClass
     */
    public function addScope(ProvidesOAuth2Scope $attribute, string $scopeClass): void
    {
        $this->scopes->put($attribute, $scopeClass);
    }
}
