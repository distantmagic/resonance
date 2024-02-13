<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Throwable;

class MissingPhpExtensions extends DependencyInjectionContainerException
{
    /**
     * @param class-string                     $className
     * @param non-empty-list<non-empty-string> $extensionNames
     */
    public function __construct(
        string $className,
        array $extensionNames,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'To use "%s" you need to install "%s" PHP %s.',
                $className,
                implode('", "', $extensionNames),
                1 === count($extensionNames) ? 'extension' : 'extensions',
            ),
            $previous
        );
    }
}
