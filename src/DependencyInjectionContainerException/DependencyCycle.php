<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Distantmagic\Resonance\DependencyStack;
use Throwable;

class DependencyCycle extends DependencyInjectionContainerException
{
    /**
     * @param class-string $className
     */
    public function __construct(
        string $className,
        DependencyStack $stack,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                "Dependency injection cycle:\n-> %s\n-> %s\n",
                $stack->join("\n-> "),
                $className,
            ),
            $previous
        );
    }
}
