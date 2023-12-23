<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Throwable;

class AmbiguousProvider extends DependencyInjectionContainerException
{
    /**
     * @param class-string        $className
     * @param array<class-string> $providers
     */
    public function __construct(
        string $className,
        array $providers,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                "Multiple providers registered for: %s\n- %s\n",
                $className,
                implode("\n- ", $providers),
            ),
            $previous
        );
    }
}
