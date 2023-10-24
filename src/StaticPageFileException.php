<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class StaticPageFileException extends LogicException
{
    public function __construct(
        SplFileInfo $splFileInfo,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: sprintf(
                '%s: "%s"',
                $message,
                $splFileInfo->getPathname(),
            ),
            previous: $previous,
        );
    }
}
