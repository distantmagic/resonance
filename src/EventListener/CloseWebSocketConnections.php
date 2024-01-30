<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpServerBeforeStop;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\ServerPipeMessage\CloseWebSocketConnection;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\WebSocketServerConnectionTable;
use RuntimeException;

/**
 * @template-extends EventListener<HttpServerBeforeStop,void>
 */
#[GrantsFeature(Feature::WebSocket)]
#[ListensTo(HttpServerBeforeStop::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class CloseWebSocketConnections extends EventListener
{
    public function __construct(
        private ?WebSocketServerConnectionTable $webSocketServerConnectionTable = null,
    ) {}

    /**
     * @param HttpServerBeforeStop $event
     */
    public function handle(object $event): void
    {
        if (!$this->webSocketServerConnectionTable) {
            throw new RuntimeException('WebSocket close connections listener should not have been registered');
        }

        foreach ($this->webSocketServerConnectionTable as $fd => $workerId) {
            $pipeMessage = new CloseWebSocketConnection($fd);

            /**
             * @psalm-suppress InvalidArgument `sendMessage` has type errors
             */
            if (!$event->server->sendMessage($pipeMessage, $workerId)) {
                throw new RuntimeException('Unable to send server message');
            }
        }
    }

    public function shouldRegister(): bool
    {
        return !is_null($this->webSocketServerConnectionTable);
    }
}
