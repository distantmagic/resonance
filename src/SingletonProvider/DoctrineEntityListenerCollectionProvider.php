<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ListensToDoctrineEntityEvents;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityListenerCollection;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonContainerAttributeIterator;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<DoctrineEntityListenerCollection>
 */
#[GrantsFeature(Feature::Doctrine)]
#[RequiresSingletonCollection(SingletonCollection::DoctrineEntityListener)]
#[Singleton(provides: DoctrineEntityListenerCollection::class)]
final readonly class DoctrineEntityListenerCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DoctrineEntityListenerCollection
    {
        $entityListenerCollection = new DoctrineEntityListenerCollection();

        foreach (new SingletonContainerAttributeIterator($singletons, ListensToDoctrineEntityEvents::class) as $listenerAttribute) {
            $entityListenerCollection->addEntityListener(
                $listenerAttribute->attribute->className,
                $listenerAttribute->singleton,
            );
        }

        return $entityListenerCollection;
    }
}
