<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ProvidesAuthenticatedUser;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUserStoreAggregate;
use Distantmagic\Resonance\AuthenticatedUserStoreInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Ds\PriorityQueue;

/**
 * @template-extends SingletonProvider<AuthenticatedUserStoreAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::AuthenticatedUserStore)]
#[Singleton(provides: AuthenticatedUserStoreAggregate::class)]
final readonly class AuthenticatedUserStoreAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): AuthenticatedUserStoreAggregate
    {
        $authenticatedUserStoreAggregate = new AuthenticatedUserStoreAggregate();

        /**
         * @var PriorityQueue<AuthenticatedUserStoreInterface>
         */
        $storages = new PriorityQueue();

        foreach ($this->collectStores($singletons) as $authenticatedUserStoreAttribute) {
            $storages->push(
                $authenticatedUserStoreAttribute->singleton,
                $authenticatedUserStoreAttribute->attribute->priority,
            );
        }

        foreach ($storages as $storage) {
            $authenticatedUserStoreAggregate->storages->add($storage);
        }

        return $authenticatedUserStoreAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<AuthenticatedUserStoreInterface,ProvidesAuthenticatedUser>>
     */
    private function collectStores(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            AuthenticatedUserStoreInterface::class,
            ProvidesAuthenticatedUser::class,
        );
    }
}
