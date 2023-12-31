<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Throwable;

class AmbiguousProvider extends DependencyInjectionContainerException
{
    /**
     * @param class-string $className
     */
    public function __construct(
        string $className,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Multiple providers registered for: %s',
                $className,
            ),
            $previous
        );
    }
}
