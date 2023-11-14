---
collections: 
    - name: documents
      next: docs/features/security/oauth2/enabling-grants
layout: dm:document
next: docs/features/security/oauth2/enabling-grants
parent: docs/features/security/oauth2/index
title: Configuration
description: >
    Learn how to configure OAuth 2.0 server.
---

# Configuration

## Encryption Keys Paths

You can configure the paths by adding this section to the configuration file:

```ini
[oauth2]
encryption_key = oauth2/defuse.key
jwt_signing_key_passphrase =
jwt_signing_key_private = oauth2/private.key
jwt_signing_key_public = oauth2/public.key
```

## HTTP Authorization Server Endpoint

You need to add an endpoint to your application that exposes OAuth2 server.

The simplest one can forward every request to the OAuth2 authorization 
server, the framework takes care of the rest:

```php file:app/HttpResponder/OAuth2AuthorizationServer.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\OAuth2\AuthorizationServer;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/oauth2',
    routeSymbol: HttpRouteSymbol::OAuth2AuthorizationServer,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class OAuth2AuthorizationServer extends HttpResponder
{
    public function __construct(private AuthorizationServer $authorizationServer) {}

    public function respond(Request $request, Response $response): HttpResponderInterface
    {
        return $this->authorizationServer;
    }
}
```
