<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @see Distantmagic\Resonance\LlamaCppExtractYesNoMaybe
 */
enum YesNoMaybe: string
{
    case Maybe = 'maybe';
    case No = 'no';
    case Yes = 'yes';

    public function isCertain(): bool
    {
        return self::Maybe !== $this;
    }

    public function isNo(): bool
    {
        return self::No === $this;
    }

    public function isYes(): bool
    {
        return self::Yes === $this;
    }
}
