---
collections:
  - tutorials
draft: true
layout: dm:tutorial
parent: tutorials/index
title: How to Create LLM Chat with WebSocket and llama.cpp?
description: >
    Learn how to setup a WebSocket server and create basic chat with an open
    source LLM.
---

## Preparations

Before starting this tutorial, it might be useful for you to familiarize with
the other tutorials and documentation pages first:

* {{tutorials/how-to-serve-llm-completions/index}} - to setup 
    [llama.cpp](https://github.com/ggerganov/llama.cpp) server and configure
    Resonance to use it
* {{docs/features/websockets/index}} - to learn how WebSocket featres are
    implemented in Resonance

In this tutorial, we will also use [Stimulus](https://stimulus.hotwired.dev/)
for the front-end code.

Once you have both Resonance and 
[llama.cpp](https://github.com/ggerganov/llama.cpp) runing, we can continue.

## Http Responder

```php file:app/RPCMethod.php
<?php

namespace App;

use Distantmagic\Resonance\EnumValuesTrait;
use Distantmagic\Resonance\NameableEnumTrait;
use Distantmagic\Resonance\RPCMethodInterface;

enum RPCMethod: string implements RPCMethodInterface
{
    use EnumValuesTrait;
    use NameableEnumTrait;

    case LlmChatReady = 'llm_chat_ready';
}
```

```php file:app/HttpResponder/LlmChat.php
<?php

declare(strict_types=1);

namespace App\HttpResponder\Webapp;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
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
final readonly class LlmChat extends HttpResponder
{
    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        return new TwigTemplate('llmchat.twig');
    }
}
```

```twig file:app/views/llamachat.twig
turbo/llmchat/index.twig
```

## Front-end

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

```typescript file:resources/ts/controller_llmchat.ts
import { Controller } from "@hotwired/stimulus";

import { stimulus } from "../stimulus";

@stimulus("llmchat")
export class controller_llmchat extends Controller<HTMLElement> {
  public static targets = ["textarea", "textareaGrow"];

  private declare readonly textareaGrowTarget: HTMLElement;
  private declare readonly textareaTarget: HTMLTextAreaElement;
}
```
