<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DependencyInjectionContainerException;

use Distantmagic\Resonance\DependencyInjectionContainerException;
use Distantmagic\Resonance\DependencyStack;
use Distantmagic\Resonance\FeatureInterface;
use Ds\Set;
use Throwable;

class DisabledFeatureProvider extends DependencyInjectionContainerException
{
    /**
     * @param class-string          $className
     * @param Set<FeatureInterface> $features
     */
    public function __construct(
        string $className,
        Set $features,
        DependencyStack $stack,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                "Enable '%s' %s to use this provider:\n-> %s\nDependency stack:\n-> %s\n",
                $this->serializeFeatures($features)->join("', '"),
                1 === $features->count() ? 'feature' : 'features',
                $className,
                $stack->join("\n-> "),
            ),
            $previous
        );
    }

    /**
     * @param Set<FeatureInterface> $features
     *
     * @return Set<non-empty-string>
     */
    private function serializeFeatures(Set $features): Set
    {
        /**
         * @var Set<non-empty-string>
         */
        $ret = new Set();

        foreach ($features as $feature) {
            $ret->add($feature->getName());
        }

        return $ret;
    }
}
