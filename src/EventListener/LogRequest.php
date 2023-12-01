<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Distantmagic\Resonance\EventInterface;
use Distantmagic\Resonance\EventListener;
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
    public function handle(EventInterface $event): void
    {
        $server = $event->request->server;

        if (!is_array($server)) {
            $this->logger->debug('request has no server data to log');

            return;
        }

        $this->logger->debug(sprintf(
            '%s: %s',
            (string) $server['request_method'],
            (string) $server['path_info'],
        ));
    }

    public function shouldRegister(): bool
    {
        return $this->swooleConfiguration->logRequests;
    }
}
