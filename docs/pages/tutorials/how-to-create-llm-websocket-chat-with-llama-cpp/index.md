---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
title: How to Create LLM WebSocket Chat with llama.cpp?
description: >
    Learn how to setup a WebSocket server and create basic chat with an open
    source LLM.
---

![Preview](https://s13.gifyu.com/images/S0UWx.gif)

## Preparations

Before starting this tutorial, it might be useful for you to familiarize yourself with
the other tutorials and documentation pages first:

* {{tutorials/how-to-serve-llm-completions/index}} - to setup 
    [llama.cpp](https://github.com/ggerganov/llama.cpp) server and configure
    Resonance to use it
* {{docs/features/websockets/index}} - to learn how WebSocket features are
    implemented in Resonance

In this tutorial, we will also use [Stimulus](https://stimulus.hotwired.dev/)
for the front-end code.

Once you have both Resonance and 
[llama.cpp](https://github.com/ggerganov/llama.cpp) runing, we can continue.

## Backend

### Security

First, we must create a security gate and decide which users can use our 
chat. Let's say all authenticated users can do that:

```php file:app/SiteActionGate/StartWebSocketRPCConnectionGate.php
<?php

namespace App\SiteActionGate;

use App\Role;
use Distantmagic\Resonance\Attribute\DecidesSiteAction;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUser;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteAction;
use Distantmagic\Resonance\SiteActionGate;

#[DecidesSiteAction(SiteAction::StartWebSocketRPCConnection)]
#[Singleton(collection: SingletonCollection::SiteActionGate)]
final readonly class StartWebSocketRPCConnectionGate extends SiteActionGate
{
    public function can(?AuthenticatedUser $authenticatedUser): bool
    {
        return true === $authenticatedUser?->user->getRole()->isAtLeast(Role::User);
    }
}
```

### HTTP Responder

First, we need to create a route in our application that will render the HTML
chat.

We are using a few attributes:

1. `Can` - this is an {{docs/features/security/authorization/index}} attribute
    it checks if user can connect with {{docs/features/websockets/index}}. If
    they can't - it will return 403 error page instead. That is optional, but
    without `Can` attribute, all users will be able to visit the page, but 
    they won't be able to establish a WebSocket connection anyway.
2. `RespondsToHttp` - registers route
3. `Singleton` - adds your `HttpResponder` to 
    {{docs/features/dependency-injection/index}} container. 
    We added `#[WantsFeature(Feature::WebSocket)` attribute to enable 
    `WebSocket` server.

```php file:app/HttpResponder/LlmChat.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteAction;
use Distantmagic\Resonance\TwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Can(SiteAction::StartWebSocketRPCConnection)]
#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/chat',
    routeSymbol: HttpRouteSymbol::LlmChat,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
#[WantsFeature(Feature::WebSocket)]
final readonly class LlmChat extends HttpResponder
{
    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        return new TwigTemplate('turbo/llmchat/index.twig');
    }
}
```

### WebSocket Message Validation

First, we need to register our WebSocket RPC methods. Default WebSocket 
implementation follows a simple RPC protocol. All messages need to be a tuple:

```
[method, payload, null|request id]
```

Method, the first field, is validated against `RPCMethodInterface`, through
`RPCMethodValidator` (you need to provide both):

```php file:app/RPCMethod.php
<?php

namespace App;

use Distantmagic\Resonance\EnumValuesTrait;
use Distantmagic\Resonance\RPCMethodInterface;

enum RPCMethod: string implements RPCMethodInterface
{
    use EnumValuesTrait;

    case LlmChatPrompt = 'llm_chat_prompt';
    case LlmToken = 'llm_token';

    public function getValue(): string
    {
        return $this->value;
    }
}
```

```php file:app/RPCMethodValidator.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\RPCMethodInterface;
use Distantmagic\Resonance\RPCMethodValidatorInterface;

#[Singleton(provides: RPCMethodValidatorInterface::class)]
#[WantsFeature(Feature::WebSocket)]
readonly class RPCMethodValidator implements RPCMethodValidatorInterface
{
    public function cases(): array
    {
        return RPCMethod::cases();
    }

    public function castToRPCMethod(string $methodName): RPCMethodInterface
    {
        return RPCMethod::from($methodName);
    }

    public function values(): array
    {
        return RPCMethod::values();
    }
}
```

The above means your application will support `llm_chat_prompt` and 
`llm_chat_token` messages.

### WebSocket Message Responder

Now, we need to register a WebSocket responder that will run every time 
someone sends `llama_chat_prompt` message to the server. 

It will then use `LlamaCppClient` to fetch tokens from 
[llama.cpp](https://github.com/ggerganov/llama.cpp) server and forward them
to the WebSocket connection:

```php file:app/WebSocketRPCResponder/LlmChatPromptResponder.php
<?php

namespace App\WebSocketRPCResponder;

use App\RPCMethod;
use Distantmagic\Resonance\Attribute\RespondsToWebSocketRPC;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\RPCNotification;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\RPCResponse;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;

#[RespondsToWebSocketRPC(RPCMethod::LlmChatPrompt)]
#[Singleton(collection: SingletonCollection::WebSocketRPCResponder)]
#[WantsFeature(Feature::WebSocket)]
final readonly class LlmChatPromptResponder extends WebSocketRPCResponder
{
    public function __construct(
        private LlamaCppClient $llamaCppClient,
    ) {}

    public function getConstraint(): Constraint
    {
        return new ObjectConstraint([
            'prompt' => new StringConstraint(),
        ]);
    }

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): void {
        $request = new LlamaCppCompletionRequest($rpcNotification->payload->prompt);

        $completion = $this->llamaCppClient->generateCompletion($request);

        foreach ($completion as $token) {
            if ($webSocketConnection->status->isOpen()) {
                $webSocketConnection->push(new RPCNotification(
                    RPCMethod::LlmToken,
                    $token->content,
                ));
            } else {
                $completion->stop();
            }
        }
    }
}
```

## Frontend

[Stimulus](https://stimulus.hotwired.dev/) controller starts WebSocket 
connection and sends user prompts to the server. Not that WebSocket
connection uses both {{docs/features/security/csrf-protection/index}} and 
`dm-rpc` {{docs/features/websockets/protocols}}:

```typescript file:resources/ts/controller_llmchat.ts
import { Controller } from "@hotwired/stimulus";

import { stimulus } from "../stimulus";

@stimulus("llmchat")
export class controller_llmchat extends Controller<HTMLElement> {
  public static targets = ["userInputField", "userInputForm", "chatLog"];
  public static values = {
    csrfToken: String,
  };

  private declare readonly chatLogTarget: HTMLElement;
  private declare readonly csrfTokenValue: string;
  private declare readonly userInputFieldTarget: HTMLTextAreaElement;

  private webSocket: null|WebSocket = null;

  public connect(): void {
    const servertUrl = new URL(__WEBSOCKET_URL);

    servertUrl.searchParams.append("csrf", this.csrfTokenValue);

    const webSocket = new WebSocket(servertUrl, ["dm-rpc"]);

    webSocket.addEventListener("close", () => {
      this.webSocket = null;
    });

    webSocket.addEventListener("open", () => {
      this.webSocket = webSocket;
    });

    webSocket.addEventListener("message", (evt: MessageEvent) => {
      if ("string" !== typeof evt.data) {
        return;
      }

      const parsed: unknown = JSON.parse(evt.data);

      if (Array.isArray(parsed) && "string" === typeof parsed[1]) {
        this.chatLogTarget.append(parsed[1]);
      }
    });
  }

  public disconnect(): void {
    this.webSocket?.close();
  }

  public onFormSubmit(evt: Event): void {
    evt.preventDefault();

    this.chatLogTarget.innerHTML = '';

    this.webSocket?.send(JSON.stringify([
      "llm_chat_prompt",
      {
        prompt: this.userInputFieldTarget.value,
      },
      null
    ]));

    this.userInputFieldTarget.value = '';
  }
}
```

We will use {{docs/features/asset-bundling-esbuild/index}} to bundle front-end
TypeScript code.

```shell
$ ./node_modules/.bin/esbuild \
    --bundle \
    --define:global=globalThis \
    --entry-names="./[name]_$(BUILD_ID)" \
    --format=esm \
    --log-limit=0 \
    --metafile=esbuild-meta-app.json \
    --minify \
    --outdir=./$(BUILD_TARGET_DIRECTORY) \
    --platform=browser \
    --sourcemap \
    --target=es2022,safari16 \
    --tree-shaking=true \
    --tsconfig=tsconfig.json \
    resources/ts/controller_llmchat.ts \
;
```

Finally, the HTML:

```twig file:app/views/llmchat.twig
<script 
    defer 
    type="module" 
    src="{{ esbuild(request, 'controller_llmchat.ts') }}"
></script>

<div
    data-controller="llmchat"
    data-llmchat-csrf-token-value="{{ csrf_token(request, response) }}"
>
    <div data-llmchat-target="chatLog"></div>
    <form data-action="submit->llmchat#onFormSubmit">
        <input
            autofocus
            data-llmchat-target="userInputField"
            type="text"
        ></input>
        <button type="submit">Send</button>
    </form>
</div>
```

## Summary

Now, you can serve LLM chat in your application. Enjoy!
