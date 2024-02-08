<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineConnectionRepository;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @template-extends EventListener<HttpResponseReady,void>
 */
#[GrantsFeature(Feature::Doctrine)]
#[ListensTo(HttpResponseReady::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class CloseDoctrineConnection extends EventListener
{
    public function __construct(
        private DoctrineConnectionRepository $doctrineConnectionRepository,
    ) {}

    /**
     * Since Doctrine caches Repositories and other objects and many of them
     * may hold a connection reference, those connections need to be manually
     * closed and returned to the pool after each request.
     *
     * @param HttpResponseReady $event
     */
    public function handle(object $event): void
    {
        $connections = $this->doctrineConnectionRepository->connections;

        if (!$connections->offsetExists($event->request)) {
            return;
        }

        foreach ($connections->offsetGet($event->request) as $connection) {
            /**
             * This doesn't really terminate the connection, instead (as of
             * writing this), internally to Doctrine, it just sets the pointer
             * to that connection to NULL, thus allowing the GC to collect the
             * underlying PDO object.
             *
             * This is exactly the behavior we need.
             *
             * This also seems to be the PDO's recommended way of terminating
             * connections - PDO closes a connection when all references to
             * that connection are gone.
             */
            $connection->close();
        }
    }
}
