---
collections: 
    - name: documents
      next: docs/features/security/oauth2/authorization-code-grant/index
layout: dm:document
next: docs/features/security/oauth2/authorization-code-grant/index
parent: docs/features/security/oauth2/index
title: Enabling Grants
description: >
    Learn how to add methods of acquiring access tokens.
---

# Enabling Grants

Grant represents a method of obtaining an access token or, in other words, 
different authentication flows (through password, token, etc.).

By default, the OAuth2 server has no grants enabled, so you have to add at 
least one if you want to use it.

# Usage

You can follow 
[thephpleague/oauth2-server](https://oauth2.thephpleague.com/authorization-server/which-grant/)
recommendations to decide which grants you want to enable. You can either use 
League's built-in grants or provide your own.

## Doctrine Considerations

If you want to implement repositories by using
{{docs/features/database/doctrine/index}}, you should probably use 
`withRepository()` method to obtain the Entity Manager. For example:

```php
<?php

use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Doctrine\ORM\EntityRepository;

#[Singleton(provides: AccessTokenRepositoryInterface::class)]
readonly class OAuth2AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(private DoctrineEntityManagerRepository $doctrineEntityManagerRepository) 
    {
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $this
            ->doctrineEntityManagerRepository
            ->withRepository(MyDoctrineTokenRepository::class, function (EntityRepository $entityRepository) {
                // ...
            })
        ;

        // ...
    }

    // ...
}
```

## Enabling Grant Types

For each grant you want to enable you have to add a grant provider. For 
example, if you want to enable client credentials grant:

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
