<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;

readonly class SingletonDependencyMap
{
    /**
     * @param Map<class-string,Set<class-string>> $dependencyMap
     * @param Map<class-string,class-string>      $providers
     */
    public function __construct(
        public Map $dependencyMap,
        public Map $providers,
    ) {}
}
