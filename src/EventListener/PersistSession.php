<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SessionManager;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @template-extends EventListener<HttpResponseReady,void>
 */
#[GrantsFeature(Feature::HttpSession)]
#[ListensTo(HttpResponseReady::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class PersistSession extends EventListener
{
    public function __construct(
        private SessionManager $sessionManager,
    ) {}

    /**
     * @param HttpResponseReady $event
     */
    public function handle(object $event): void
    {
        $this->sessionManager->persistSession($event->request);
    }
}
