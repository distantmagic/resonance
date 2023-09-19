<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Vector;
use PhpToken;

readonly class PHPTokenSequenceMatcher
{
    /**
     * @var Vector<PhpToken>
     */
    public Vector $matchingTokens;

    public function __construct(private array $pattern)
    {
        /**
         * @var Vector<PhpToken>
         */
        $this->matchingTokens = new Vector();
    }

    public function isMatching(): bool
    {
        if ($this->matchingTokens->count() !== count($this->pattern)) {
            return false;
        }

        foreach ($this->matchingTokens as $index => $matchingToken) {
            if ($this->pattern[$index] !== $matchingToken->getTokenName()) {
                return false;
            }
        }

        return true;
    }

    public function pushToken(PhpToken $token): void
    {
        $this->matchingTokens->push($token);

        if ($this->matchingTokens->count() > count($this->pattern)) {
            $this->matchingTokens->shift();
        }
    }
}
