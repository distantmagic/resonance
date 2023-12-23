<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ProvidesOAuth2Scope;
use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;

#[Singleton]
readonly class OAuth2ScopePatternMatcher
{
    /**
     * @return null|Map<string,string>
     */
    public function match(ProvidesOAuth2Scope $attribute, string $identifier): ?Map
    {
        if ($attribute->pattern === $identifier) {
            return new Map();
        }

        $identifierChunks = explode($attribute->separator, $identifier);
        $patternChunks = explode($attribute->separator, $attribute->pattern);

        if (count($identifierChunks) !== count($patternChunks)) {
            return null;
        }

        /**
         * @var Map<string,string> $parameters
         */
        $parameters = new Map();

        foreach ($patternChunks as $index => $patternChunk) {
            if (str_starts_with($patternChunk, '{') && str_ends_with($patternChunk, '}')) {
                $patternName = trim($patternChunk, '{}');

                $parameters->put($patternName, $identifierChunks[$index]);
            } elseif ($identifierChunks[$index] !== $patternChunk) {
                return null;
            }
        }

        return $parameters;
    }
}
