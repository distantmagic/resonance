<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Throwable;

class MissingPhpExtension extends DependencyInjectionContainerException
{
    /**
     * @param class-string     $className
     * @param non-empty-string $extensionName
     */
    public function __construct(
        string $className,
        string $extensionName,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'To use "%s" you need to install "%s" PHP extension.',
                $className,
                $extensionName,
            ),
            $previous
        );
    }
}
