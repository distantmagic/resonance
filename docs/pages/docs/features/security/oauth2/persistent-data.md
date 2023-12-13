---
collections: 
    - documents
layout: dm:document
parent: docs/features/security/oauth2/index
title: Persistent Data
description: >
    Learn how to persist OAuth2 tokens and other data by using Doctrine.
---

# Persistent Data

You can implement only the ones you need in your application. Grants use 
repositories to store and retrieve the data they need to operate.

Grant Type | Note | Repositories to Implement
-|-|-
{{docs/features/security/oauth2/authorization-code-grant}} | Machine <-> Human. For example: display authentication form, use login and password to obtain the access token. | Access Token, Auth Code, Client, Refresh Token, Scope
Client Credentials | Machine <-> Machine. For example: exchange client's secret over API to obtain the access token. | Access Token, Client, Refresh Token, Scope
Password | Use *only* on first party sites. Consider using Authorization Code instead. | Access Token, Client, User, Refresh Token, Scope
Refresh Token | Exchange refresh token obtained from any other grant for a fresh access token. | Access Token, Client, Refresh Token, Scope

{{docs/features/security/oauth2/authorization-code-grant}} requires an 
additional step compared to other grant types (exchanging code for an access 
token instead of immediately generating access token), thus it has it's own
documentation page that explains the process further.

## Persistent Data Repositories

You can learn more on {{docs/features/database/doctrine/entity-managers}} page.

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
    // (...) implement all the interface methods here
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
    // (...) implement all the interface methods here
}
```

### Client Repository

:::tip
If you have a static list of clients, for example you only use OAuth in your
website and mobile app, you do not have to use a database to store them. You
can instead hard-code them and parametrize client secrets in a config file.
:::

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
    // (...) implement all the interface methods here
}
```

### Refresh Token Repository

All grant types require this repository. 

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/refresh-token-repository-interface/)
documentation.

```php file:app/OAuth2RefreshTokenRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

#[Singleton(provides: RefreshTokenRepositoryInterface::class)]
readonly class OAuth2RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    // (...) implement all the interface methods here
}
```

### Scope Repository

:::tip
If you have a static list of scopes, you do not have to necessarily use a 
database to store them. You can as well hard-code them since they rely on your
application features anyway.

You can even implement `ScopeEntityInterface` as an enum to maintain 
consistency over your application:

```php
<?php

namespace App;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

enum OAuth2Scope: string implements ScopeEntityInterface
{
    case BlogPostCreate = "blog_post:create";
    case BlogPostDelete = "blog_post:delete";
    case UserProfileEdit = "user_profile:edit";

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getIdentifier();
    }
}
```

If you decide to use an enum, then the implemention of the scope repository
becomes easy:

```php
public function getScopeEntityByIdentifier($identifier): null|ScopeEntityInterface
{
    return OAuth2Scope::tryFrom($identifier);
}
```
:::

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
    // (...) implement all the interface methods here
}
```

### User Repository

Only password grant requires user repository explicitly.

You might want to consider combining this repository with 
{{docs/features/database/doctrine/index}}.

See more at 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/user-repository-interface/)
documentation.

```php file:app/OAuth2UserRepository.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

#[Singleton(provides: UserRepositoryInterface::class)]
readonly class OAuth2UserRepository implements UserRepositoryInterface
{
    // (...) implement all the interface methods here
}
```
