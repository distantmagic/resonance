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
     * @param array<string> $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->directives = new Set($defaults);
    }

    public function __toString(): string
    {
        return $this->directives->join(' ');
    }

    public function add(string $directive): void
    {
        $this->directives->add($directive);
    }
}
