<?php

declare(strict_types=1);

namespace Resonance;

use LogicException;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class StaticPageFileException extends LogicException
{
    public function __construct(
        public SplFileInfo $splFileInfo,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            previous: $previous,
        );
    }
}
