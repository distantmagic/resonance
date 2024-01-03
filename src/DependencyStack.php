<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DependencyInjectionContainerException\DependencyCycle;
use Ds\Set;

readonly class DependencyStack
{
    /**
     * @param Set<class-string> $dependencies
     */
    public function __construct(private Set $dependencies = new Set()) {}

    /**
     * @param class-string $className
     */
    public function branch(string $className): self
    {
        if ($this->dependencies->contains($className)) {
            throw new DependencyCycle($className, $this);
        }

        $childDependencies = $this->dependencies->copy();
        $childDependencies->add($className);

        return new self($childDependencies);
    }

    public function join(string $glue): string
    {
        return $this->dependencies->join($glue);
    }
}
