---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Serving Assets
description: >
    Use Responders to serve assets over HTTP.
---

# Serving Assets

Before reading this section, you might want to familiarize yourself with 
{{docs/features/http/responders}} and {{docs/features/configuration/index}} 
(System Configuration) first.

# Usage

You should use a single URL path (like `/public/*`) to serve assets.

1. You can use some web server (like [Nginx](https://www.nginx.com/)) in front
    of Resonance to intercept all the requests coming to that path and return
    assets instead of forwarding the request to Resonance.
2. Resonance can send asset files back to the browser.

We will focus on the latter.

## Asset File Registry

Resonance is bundled with `AssetFileRegistry` which you can use to serve assets
in your {{docs/features/http/responders}}.

To use it, you need to create an HTTP Responder that forwards the request to 
the asset registry.

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\AssetFileRegistry;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpRouteMatchRegistry;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/public/{asset}',
    requirements: [
        'asset' => '[\-\.\w\/]+',
    ],
    routeSymbol: HttpRouteSymbol::Asset,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Asset extends HttpResponder
{
    public function __construct(
        private AssetFileRegistry $assetFileRegistry,
        private HttpRouteMatchRegistry $routeMatchRegistry,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface|ResponseInterface
    {
        return $this->assetFileRegistry->sendAsset(
            $response, 
            $this->routeMatchRegistry->getVar($request, 'asset'),
        );
    }
}
```

## Security 

The most common attack related to serving files through PHP is 
[Path Traversal](https://owasp.org/www-community/attacks/Path_Traversal). 

To mitigate that threat, `AssetFileRegistry` indexes all your asset 
files (from the directory defined in the `DM_PUBLIC_ROOT` constant) and serves
only the files found in that directory.

That achieves two things: 

1. Resonance does not need to check the filesystem during each request,
    because it keeps the list of available files in memory, so it's faster and
    uses less resources.
2. It prevents path traversal - if there is no file with a given name in the
    internal registry, it just responds with 404.

Once Resonance determines that the file exists and the request is correct,
it sends that file using Swoole's 
[$response->sendfile(...)](https://wiki.swoole.com/#/http_server?id=sendfile)
method.
