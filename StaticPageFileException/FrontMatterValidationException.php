<?php

declare(strict_types=1);

namespace Resonance\StaticPageFileException;

use Nette\Schema\ValidationException;
use Resonance\StaticPageFileException;
use Symfony\Component\Finder\SplFileInfo;

class FrontMatterValidationException extends StaticPageFileException
{
    public function __construct(SplFileInfo $splFileInfo, ValidationException $validationException)
    {
        parent::__construct(
            message: 'Frant Matter is invalid: '.$validationException->getMessage(),
            previous: $validationException,
            splFileInfo: $splFileInfo,
        );
    }
}
