<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Stringable;

readonly class ContentSecurityPolicyDirectives implements Stringable
{
    /**
     * @var Set<string>
     */
    private Set $directives;

    /**
     * @param array<string> $initial
     * @param array<string> $fallback
     */
    public function __construct(
        array $initial = [],
        private array $fallback = [],
    ) {
        $this->directives = new Set($initial);
    }

    public function __toString(): string
    {
        if ($this->directives->isEmpty()) {
            return implode(' ', $this->fallback);
        }

        return $this->directives->join(' ');
    }

    public function add(string $directive): void
    {
        $this->directives->add($directive);
    }
}
