<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPersona;

use Distantmagic\Resonance\LlmPersona;

readonly class HelpfulAssistant extends LlmPersona
{
    private string $personaDescription;

    public function __construct()
    {
        $this->personaDescription = <<<'PERSONA'
        You are a helpful assistant.
        PERSONA;
    }

    public function getPersonaDescription(): string
    {
        return $this->personaDescription;
    }
}
