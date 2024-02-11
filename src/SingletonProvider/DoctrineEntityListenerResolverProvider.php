<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityListenerCollection;
use Distantmagic\Resonance\DoctrineEntityListenerResolver;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<DoctrineEntityListenerResolver>
 */
#[GrantsFeature(Feature::Doctrine)]
#[RequiresSingletonCollection(SingletonCollection::DoctrineEntityListener)]
#[Singleton(provides: DoctrineEntityListenerResolver::class)]
final readonly class DoctrineEntityListenerResolverProvider extends SingletonProvider
{
    public function __construct(
        private DoctrineEntityListenerCollection $doctrineEntityListenerCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DoctrineEntityListenerResolver
    {
        $entityListenerResolver = new DoctrineEntityListenerResolver();

        foreach ($this->doctrineEntityListenerCollection->listeners as $listeners) {
            foreach ($listeners as $listener) {
                $entityListenerResolver->register($listener);
            }
        }

        return $entityListenerResolver;
    }
}
