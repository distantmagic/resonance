---
collections: 
    - name: documents
      next: docs/features/security/oauth2/enabling-grants/index
layout: dm:document
next: docs/features/security/oauth2/enabling-grants/index
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

## HTTP Authorization Server Endpoints

You need to add endpoints to your application that expose OAuth2 server.


### Access Token Endpoint

Most grants require just the Access Token Endpoint:

:::note
{{docs/features/security/oauth2/authorization-code-grant/index}} requires more 
setup and it has more detailed explanation on it's documentation page.
:::

```php file:app/HttpResponder/OAuth2AccessToken.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\OAuth2\AccessToken;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/oauth2/access_token',
    routeSymbol: HttpRouteSymbol::OAuth2AccessToken,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class OAuth2AccessToken extends HttpResponder
{
    public function __construct(private AccessToken $accessTokenResponder) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface
    {
        return $this->accessTokenResponder;
    }
}
```

### Using League OAuth 2.0 Server Directly

If you need to, you can define your own endpoints by manipulating the 
Authorization Server directly:

```php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PsrServerRequestConverter;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
readonly class MyOAuth2Server extends HttpResponder
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private PsrServerRequestConverter $psrServerRequestConverter,
        private Psr17Factory $psr17Factory,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface
    {
        /**
         * Convert Swoole http request to PSR Server request object
         */
        $serverRequest = $this->psrServerRequestConverter->convertToServerRequest($request);

        // ... do something with $this->leagueAuthorizationServer

        return new PsrResponder($psrResponse);
    }
}
```
