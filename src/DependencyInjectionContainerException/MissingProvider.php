<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Distantmagic\Resonance\DependencyStack;
use Throwable;

class MissingProvider extends DependencyInjectionContainerException
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
                "No singleton provider is registered for:\n-> %s\nDependency stack:\n-> %s\n",
                $className,
                $stack->join("\n-> "),
            ),
            $previous
        );
    }
}
