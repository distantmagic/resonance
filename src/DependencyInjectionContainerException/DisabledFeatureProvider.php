<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Distantmagic\Resonance\DependencyStack;
use Distantmagic\Resonance\FeatureInterface;
use Throwable;

class DisabledFeatureProvider extends DependencyInjectionContainerException
{
    /**
     * @param class-string $className
     */
    public function __construct(
        string $className,
        FeatureInterface $feature,
        DependencyStack $stack,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                "Enable '%s' feature to use this provider:\n-> %s\nDependency stack:\n-> %s\n",
                $feature->getName(),
                $className,
                $stack->join("\n-> "),
            ),
            $previous
        );
    }
}
