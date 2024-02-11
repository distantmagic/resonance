<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DoctrineEventSubscriber;

use Distantmagic\Resonance\Attribute\ListensToDoctrineEvents;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityListenerCollection;
use Distantmagic\Resonance\DoctrineEventSubscriber;
use Distantmagic\Resonance\SingletonCollection;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Builder\EntityListenerBuilder;

#[ListensToDoctrineEvents]
#[Singleton(collection: SingletonCollection::DoctrineEventListener)]
readonly class RegisterEntityListeners extends DoctrineEventSubscriber
{
    public function __construct(
        private DoctrineEntityListenerCollection $doctrineEntityListenerCollection,
    ) {}

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();

        $entityListeners = $this
            ->doctrineEntityListenerCollection
            ->listeners
            ->get($classMetadata->name, null)
        ;

        if (is_null($entityListeners)) {
            return;
        }

        foreach ($entityListeners as $entityListener) {
            EntityListenerBuilder::bindEntityListener($classMetadata, $entityListener::class);
        }
    }
}
