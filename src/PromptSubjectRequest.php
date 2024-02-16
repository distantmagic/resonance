<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in apps
 */
readonly class PromptSubjectRequest
{
    public function __construct(
        public ?AuthenticatedUser $authenticatedUser,
    ) {}
}
