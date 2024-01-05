<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class OAuth2ScopePattern
{
    /**
     * @var array<OAuth2ScopePatternChunk> $patternChunks
     */
    private array $patternChunks;

    /**
     * @param non-empty-string $separator
     */
    public function __construct(
        public string $pattern,
        private string $separator,
    ) {
        $patternChunks = explode($separator, $pattern);
        $patternProcessedChunks = [];

        foreach ($patternChunks as $patternChunk) {
            $patternProcessedChunks[] = new OAuth2ScopePatternChunk($patternChunk);
        }

        $this->patternChunks = $patternProcessedChunks;
    }

    public function match(string $identifier): ?OAuth2ScopePatternMatch
    {
        $identifierChunks = explode($this->separator, $identifier);

        if (count($this->patternChunks) !== count($identifierChunks)) {
            return null;
        }

        $oAuth2ScopePatternMatch = new OAuth2ScopePatternMatch();

        foreach ($this->patternChunks as $index => $patternChunk) {
            if (!array_key_exists($index, $identifierChunks) || !isset($identifierChunks[$index])) {
                return null;
            }

            $identifierChunk = $identifierChunks[$index];

            if ($patternChunk->isVariable()) {
                $oAuth2ScopePatternMatch->parameters->put(
                    $patternChunk->basename,
                    $identifierChunk,
                );
            } elseif ($patternChunk->basename !== $identifierChunk) {
                return null;
            }
        }

        return $oAuth2ScopePatternMatch;
    }

    /**
     * @param Map<string,string> $variables
     */
    public function withVariables(Map $variables): string
    {
        $ret = [];

        foreach ($this->patternChunks as $patternChunk) {
            if ($patternChunk->isVariable()) {
                $ret[] = $variables->get($patternChunk->basename);
            } else {
                $ret[] = $patternChunk->basename;
            }
        }

        return implode($this->separator, $ret);
    }
}
