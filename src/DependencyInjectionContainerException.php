<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

class DependencyInjectionContainerException extends Exception implements ContainerExceptionInterface
{
    public function __construct(
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
