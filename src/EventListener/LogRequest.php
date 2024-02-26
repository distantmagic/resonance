<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\HttpResponderLoggableInterface;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SwooleConfiguration;
use Psr\Log\LoggerInterface;

/**
 * @template-extends EventListener<HttpResponseReady,void>
 */
#[ListensTo(HttpResponseReady::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class LogRequest extends EventListener
{
    public function __construct(
        private SwooleConfiguration $swooleConfiguration,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param HttpResponseReady $event
     */
    public function handle(object $event): void
    {
        if (($event->responder instanceof HttpResponderLoggableInterface) && !$event->responder->shoudLog()) {
            return;
        }

        $this->logger->debug(sprintf(
            '%s: %s',
            $event->request->getMethod(),
            $event->request->getUri()->getPath(),
        ));
    }

    public function shouldRegister(): bool
    {
        return $this->swooleConfiguration->logRequests;
    }
}
