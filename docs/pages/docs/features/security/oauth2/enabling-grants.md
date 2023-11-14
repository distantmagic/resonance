---
collections: 
    - documents
layout: dm:document
parent: docs/features/security/oauth2/index
title: Enabling Grants
description: >
    Learn how to add methods of acquiring access tokens.
---

# Enabling Grants

Grant represents a method of obtaining an access token.

By default, the OAuth2 server has no grants enabled, so you have to add at 
least one if you want to use it.

# Usage

You can follow 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/authorization-server/which-grant/)
recommendations to decide which grants you want to enable. You can either use 
League's built-in grants or provide your own.

## Providing Grant Types

Then for each one you have to add a grant provider. For example, if you want to
enable client credentials grant:

```php file:app/OAuth2GrantProvider/ClientCredentialsGrantProvider.php
<?php

namespace App\OAuth2GrantProvider;

use Distantmagic\Resonance\Attribute\ProvidesOAuth2Grant;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2GrantProvider;
use Distantmagic\Resonance\SingletonCollection;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;

#[ProvidesOAuth2Grant]
#[Singleton(collection: SingletonCollection::OAuth2Grant)]
readonly class ClientCredentialsGrantProvider extends OAuth2GrantProvider
{
    public function getGrant(): GrantTypeInterface
    {
        return new ClientCredentialsGrant();
    }
}
```

## Persistent Data Repositories

### Access Token Repository

All grant types require this repository.

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/access-token-repository-interface/)
documentation.

```php file:app/OAuth2AccessTokenRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use ReturnTypeWillChange;

#[Singleton(provides: AccessTokenRepositoryInterface::class)]
readonly class OAuth2AccessTokenRepository implements AccessTokenRepositoryInterface
{
    // (...)
}
```

### Auth Code Repository

You have to implement this repository if you want to use the Authorization Code
Grant. Otherwise it's optional.

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/auth-code-repository-interface/)
documentation.

```php file:app/OAuth2AuthCodeRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

#[Singleton(provides: AuthCodeRepositoryInterface::class)]
readonly class OAuth2AuthCodeRepository implements AuthCodeRepositoryInterface
{
    // (...)
}
```

### Client Repository

All grant types require this repository. 

It provides and validates clients that can connect to the OAuth 2.0 server.

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/client-repository-interface/)
documentation.

```php file:app/OAuth2ClientRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

#[Singleton(provides: ClientRepositoryInterface::class)]
readonly class OAuth2ClientRepository implements ClientRepositoryInterface
{
    // (...)
}
```

### Scope Repository

All grant types require this repository. 

It provides and validates scopes that the client requested while 
authenticating.

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/scope-repository-interface/)
documentation.

```php file:app/OAuth2ScopeRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

#[Singleton(provides: ScopeRepositoryInterface::class)]
readonly class OAuth2ScopeRepository implements ScopeRepositoryInterface
{
    // (...)
}
```
