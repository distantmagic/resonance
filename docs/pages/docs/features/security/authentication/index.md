---
collections: 
    - documents
layout: dm:document
next: docs/features/security/authorization/index
parent: docs/features/security/index
title: Authentication
description: >
    Learn how to provide authentication.
---

# Authentication

*Currently* Resonance provides just the Session Authentication. It uses 
HTTP {{docs/features/http/sessions}} to store the authenticated user's data.

## Session Authentication

To authenticate users, you need to provide an authentication procedure on your 
own. You can use Resonance's user and session storage to store the 
authenticated user's data securely.

You can follow the {{tutorials/session-based-authentication/index}} tutorial
to see how Session Authentication can be implemented.

### Storing the Authenticated User

Then, you can use the `Distantmagic\Resonance\SessionAuthentication` helper
class to store the authenticated user in the current session. It starts the
session if it still needs to be started and then stores the current user.

For example, to store the user:

```php file:app/HttpResponder/LoginValidation.php
<?php

namespace App\HttpResponder;

// ...
use Distantmagic\Resonance\SessionAuthentication;
// ...

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::LoginValidation,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
#[ValidatesCSRFToken]
final readonly class LoginValidation extends HttpController
{
    public function __construct(
        private HttpControllerDependencies $controllerDependencies,
        private SessionAuthentication $sessionAuthentication,
    ) {
        parent::__construct($controllerDependencies);
    }

    public function handle(Request $request, Response $response): HttpInterceptableInterface {
        $user = /* obtain user somehow */;

        $this->sessionAuthentication->setAuthenticatedUser(
            $request, 
            $response, 
            $user,
        );

        return new InternalRedirect(HttpRouteSymbol::Homepage);
    }
}
```

### Obtaining the Authenticated User

You can use the `$sessionAuthentication->getAuthenticatedUser($request)` 
method.

### Session Authentication Attribute

You can use the `#[SessionAuthenticated]` attribute as a controller parameter.
If an authenticated user is stored in the session, it will 
bind that user. If there is no user in the session, then it's going to either
return 403 (if that parameter is not optional) or set it to null (if it's 
optional):

For example, to store the user:

```php file:app/HttpResponder/MyController.php
<?php

namespace App\HttpResponder;

// ...
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
// ...

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::MyController,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class MyController extends HttpController
{
    public function __construct(
        private HttpControllerDependencies $controllerDependencies,
        private SessionAuthentication $sessionAuthentication,
    ) {
        parent::__construct($controllerDependencies);
    }

    /**
     * @param ?UserInterface $user null if not authenticated
     */
    public function handle(
        Request $request,
        #[SessionAuthenticated]
        ?UserInterface $user,
    ): HttpInterceptableInterface {
        // ....
    }
}
```

## Custom Authentication Providers

In your authentication class, you need to use `ProvidesAuthenticatedUser` 
attribute, implement `AuthenticatedUserStoreInterface`, and add it to 
`AuthenticatedUserStore` collection:

```php
use Distantmagic\Resonance\Attribute\ProvidesAuthenticatedUser;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUserStoreInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;

#[ProvidesAuthenticatedUser(1200)]
#[Singleton(collection: SingletonCollection::AuthenticatedUserStore)]
readonly class MyAuthentication implements AuthenticatedUserStoreInterface
{
    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser
    {
        // ...
    }
}
```

For example, if you want to use OAuth2 client to fetch authenticated users, you 
can issue a request with access token:

```php
use Distantmagic\Resonance\Attribute\ProvidesAuthenticatedUser;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUser;
use Distantmagic\Resonance\AuthenticatedUserSource;
use Distantmagic\Resonance\AuthenticatedUserStoreInterface;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\UserInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use Swoole\Http\Request;

use function Swoole\Coroutine\Http\get;

#[ProvidesAuthenticatedUser(1200)]
#[Singleton(collection: SingletonCollection::AuthenticatedUserStore)]
readonly class MyAuthentication implements AuthenticatedUserStoreInterface
{
    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser
    {
        $userData = get('https://your-server.example.com/user', [], [
            'Authorization' => sprintf('Bearer %s', $request->cookie['access_token']),
        ]);
        $userDataDecoded = json_decode($userData);

        // Instead of anonymous class you should probably define a class 
        // instead. This is just for the sake of an example:
        return new AuthenticatedUser(
            AuthenticatedUserSource::OAuth2,
            new class($userDataDecoded) implements UserInterface {
                public function __construct(private object $userData) {
                }

                public function getIdentifier(): int|string {
                    return $this->userData->id;
                }
            }
        );
    }
}
```
