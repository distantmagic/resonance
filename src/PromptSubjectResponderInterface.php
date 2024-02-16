<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface PromptSubjectResponderInterface
{
    public function respondToPromptSubject(
        PromptSubjectRequest $request,
        PromptSubjectResponse $response,
    ): void;
}
