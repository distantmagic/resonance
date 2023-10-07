<?php

declare(strict_types=1);

namespace Resonance\StaticPageFileException;

use Resonance\StaticPageFileException;
use Symfony\Component\Finder\SplFileInfo;

class FrontMatterValidationException extends StaticPageFileException
{
    public function __construct(SplFileInfo $splFileInfo, string $validationErrorMessage)
    {
        parent::__construct(
            message: 'Frant Matter is invalid: '.$validationErrorMessage,
            splFileInfo: $splFileInfo,
        );
    }
}
