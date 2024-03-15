<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class LlmPromptTemplate
{
    abstract public function getPromptTemplateContent(): string;

    /**
     * @return list<non-empty-string>
     */
    abstract public function getStopWords(): array;
}
