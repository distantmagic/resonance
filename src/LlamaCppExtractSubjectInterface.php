<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;

interface LlamaCppExtractSubjectInterface
{
    public function extract(
        string $input,
        string $topic,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): LlamaCppExtractSubjectResult;
}
