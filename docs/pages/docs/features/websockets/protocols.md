---
collections: 
    - documents
layout: dm:document
parent: docs/features/websockets/index
title: Protocols
description: >
    Learn how to use WebSocket protocols (subprotocols) to manage the incoming
    connections.
---

# Protocols

## Json RPC

```graphviz render
digraph { 
    Message 
        -> WebSocketServerController 
        -> JsonRPCProtocolController
        -> WebSocketJsonRPCResponder
    ;
}
```

### Establishing the Connection

Besides connecting to the server and choosing a protocol, you also have to
add {{docs/features/security/csrf-protection/index}} token. Since we cannot
use `POST` to establish connection, we need to add it as a `GET` variable.

```typescript
const serverUrl = new URL('wss://localhost:9501');

serverUrl.searchParams.append('csrf', /* obtain CSRF token */);

const webSocket = new WebSocket(serverUrl, ['jsonrpc']);
```

For example, if you are using {{docs/features/templating/twig/index}}, you can
put CSRF token into a meta tag:

```twig
<meta name="csrf-token" content="{{ csrf_token(request, 'turbo') }}">
```

Then, in JavaScript:

```typescript
serverUrl.searchParams.append(
    'csrf', 
    document.querySelector("meta[name=csrf-token]").attributes.content.value,
);
```

### Writing Json RPC Responders

```php
<?php

namespace App\WebSocketJsonRPCResponder;

use App\JsonRPCMethod;
use Distantmagic\Resonance\Attribute\RespondsToWebSocketJsonRPC;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\JsonRPCRequest;
use Distantmagic\Resonance\JsonRPCResponse;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketJsonRPCResponder;

#[RespondsToWebSocketJsonRPC(JsonRPCMethod::Echo)]
#[Singleton(collection: SingletonCollection::WebSocketJsonRPCResponder)]
#[WantsFeature(Feature::WebSocket)]
final readonly class EchoResponder extends WebSocketJsonRPCResponder
{
    public function getConstraint(): Constraint
    {
        return new StringConstraint();
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void {
        $webSocketConnection->push(new JsonRPCResponse(
            $rpcRequest,
            $rpcRequest->payload,
        ));
    }
}
```

### RPC Connection Controller (Optional)

In case you want not only respond to RPC messages, but also be able to push
notifications to the client at any moment, you can implement 
`Distantmagic\Resonance\WebSocketJsonRPCConnectionControllerInterface` and 
register in in the {{docs/features/dependency-injection/index}} container.

For example:

```php file:app/WebSocketJsonRPCConnectionController.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\JsonRPCNotification;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketJsonRPCConnectionControllerInterface;

#[Singleton(provides: WebSocketJsonRPCConnectionControllerInterface::class)]
#[WantsFeature(Feature::WebSocket)]
readonly class WebSocketJsonRPCConnectionController implements WebSocketJsonRPCConnectionControllerInterface
{
    public function onClose(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {
        // called when connection is closed
    }

    public function onOpen(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void
    {
        if ($webSocketConnection->status === WebSocketConnectionStatus::Closed) {
            // connection is closed
        }

        $webSocketConnection->push(new JsonRPCNotification(
            JsonRPCMethod::YourMethod,
            [
                // your payload
            ]
        ));
    }
}
```
