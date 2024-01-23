---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Pipe Messages
description: >
    Handle HTTP requests with asynchronous HTTP Responders. 
---

# Pipe Messages

When Swoole Server starts, it spawns multiple workers to handle incoming 
HTTP requests. If you want to communicate with those workers, you have to
use some kind of IPC mechanism (unix sockets, dgram, etc).

Pipe messages feature provides a way to register both messages handlers and
issuers.

# Usage

## Registering Message Handlers

You need to add `HandlesServerPipeMessage` attribute and to implement 
`ServerPipeMessageHandlerInterface`:

```php
use Distantmagic\Resonance\Attribute\HandlesServerPipeMessage;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ServerPipeMessageHandlerInterface;

#[HandlesServerPipeMessage(MyPipeMessage::class)]
#[Singleton(
    collection: SingletonCollection::ServerPipeMessageHandler,
    grantsFeature: Feature::WebSocket,
)]
class MyServerPipeMessageHandler implements ServerPipeMessageHandlerInterface
{
    /**
     * @param CloseWebSocketConnection $serverPipeMessage
     */
    public function handleServerPipeMessage(ServerPipeMessageInterface $serverPipeMessage): void
    {
        // ...
    }
}
```

## Dispatching Pipe Messages

You need to have direct access to the `Server` object:

```php
use Distantmagic\Resonance\ServerPipeMessageInterface;
use Swoole\Http\Server;

/**
 * @var Server $server
 * @var ServerPipeMessageInterface $message
 * @var int $workerId
 */
$server->sendMessage($message, $workerId);
```
