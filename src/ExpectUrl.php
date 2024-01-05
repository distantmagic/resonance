<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ExpectUrl
{
    public function __invoke(string $pattern): bool
    {
        // if user puts here something like gopher://abc it's on them
        return false !== filter_var($pattern, FILTER_VALIDATE_URL);
    }
}
