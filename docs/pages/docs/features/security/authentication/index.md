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
