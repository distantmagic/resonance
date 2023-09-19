<?php

declare(strict_types=1);

namespace Resonance\ErrorBag;

use Nette\Schema\ValidationException;
use Resonance\ErrorBag;

readonly class NetteValidationErrorBag extends ErrorBag
{
    public function __construct(ValidationException $exception)
    {
        $errors = [];

        foreach ($exception->getMessageObjects() as $message) {
            $errors[implode('.', $message->path)] = $message->toString();
        }

        parent::__construct($errors);
    }
}
