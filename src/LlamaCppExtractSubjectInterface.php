<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Generator;

interface LlamaCppExtractSubjectInterface
{
    public function extract(
        string $input,
        string $topic,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): LlamaCppExtractSubjectResult;

    /**
     * @return Generator<mixed,LlmCompletionProgressInterface,mixed,LlamaCppExtractSubjectResult>
     */
    public function extractWithProgress(
        string $input,
        string $topic,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): Generator;
}
