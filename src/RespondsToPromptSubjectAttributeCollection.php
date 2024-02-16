<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToPromptSubject;
use Ds\Set;

readonly class RespondsToPromptSubjectAttributeCollection
{
    /**
     * @var Set<RespondsToPromptSubject> $attributes
     */
    public Set $attributes;

    public function __construct()
    {
        $this->attributes = new Set();
    }
}
