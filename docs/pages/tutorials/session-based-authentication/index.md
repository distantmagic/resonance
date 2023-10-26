---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
title: Session-Based Authentication
description: >
    Learn how to create basic authentication pages and how to secure your 
    routes with session-based authentication.
---

# Preparations

In this tutorial, we are going to use the following concepts; you might want to 
familiarize yourself with the following documentation pages before we start:

- {{docs/features/database/index}}
- {{docs/features/http/responders}}
- {{docs/features/http/sessions}}
- {{docs/features/security/index}}
- {{docs/features/templating/index}}
- {{docs/features/validation/index}}

Before starting, you should have the basic Resonance project ready. You can 
learn more at {{docs/getting-started/installation-and-requirements}}. You 
also might want to read the "{{tutorials/hello-world/index}}" tutorial.

We will need [Redis](https://redis.io/) to store user sessions and and
SQL database to store users' credentials.

Prepare the `users` table in your SQL database upfront:

Field | Type | Description
-|-|-
id | int | auto increment
username | string
password_hash | string | We will use [`password_verify`](https://www.php.net/manual/en/function.password-verify.php) later to validate the password

Then, add a user with `username` of `test_login` and `password` of
```
$2y$10$qElJNHEKCbwHrxFcSHOyTuLNLfwwNlPWzUuWGsQ4WWqStZ9TeFKRO
```
which is a Bcrpyt encrypted string of `test`). 
So the login/password pair evaluates to `test_login` / `test`.

# How Does Session Authentication Work?

This method of authentication uses {{docs/features/http/sessions}} to
store the information about the authenticated user in the server-side session.

We will set a session cookie in the user's browser with just the session ID
and then we will store the information about which user is authenticated in 
the server-side session - so it's not readable nor modifiable in the browser.

From the user's perspective, it's the standard login and password authentication 
flow.

# Implementation

## HTTP Responders

We are going to need a few HTTP {{docs/features/http/responders}}:

1. `GET /login` - shows login form
2. `POST /login` - validates authentication credentials
3. `GET /logout` - shows logout form
4. `POST /logout` - clears the session, removes authentication data

By design, each Responder handles just one HTTP route.

## Route Symbols

Let's start with defining all the HTTP Route Symbols we will need. They can be
thought of as a registry of route names:

```php file:app/HttpRouteSymbol.php
<?php

namespace App;

use Distantmagic\Resonance\CastableEnumTrait;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\NameableEnumTrait;

enum HttpRouteSymbol implements HttpRouteSymbolInterface
{
    use CastableEnumTrait;
    use NameableEnumTrait;

    case Homepage;
    case LoginForm;
    case LoginValidation;
    case LogoutForm;
    case LogoutValidation;
}
```

## Login Form

This responder is going to return the login form template. If you want to learn
more how `TwigTemplate` is converted into an HTML response, you can check the
{{docs/features/http/interceptors}} documentation:

```php file:app/HttpResponder/LoginForm.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/login',
    routeSymbol: HttpRouteSymbol::LoginForm,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class LoginForm extends HttpResponder
{
    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        return new TwigTemplate('auth/login_form.twig');
    }
}
```

```twig file:app/views/auth/login_form.twig
<form
    action="{{ route(constant('App\\HttpRouteSymbol::LoginValidation')) }}"
    method="post"
>
    <input
        type="hidden"
        name="csrf"
        value="{{ csrf_token(request, response) }}"
    >
    <fieldset>
        <input
            autocapitalize="none"
            autocomplete="username"
            autofocus
            name="username"
            placeholder="Username"
            required
            type="text"
        >
        <p>{{ errors.get("username", null) }}</p>
        <input
            autocapitalize="none"
            autocomplete="current-password"
            name="password"
            placeholder="Password"
            required
            type="password"
        >
        <p>{{ errors.get("password", null) }}</p>
    </fieldset>
    <button>
        Login
    </button>
</form>
```

Notice that we used the `csrf_token` function. It's a part of 
{{docs/features/templating/twig}} extension bundled with Resonance. 
`csrf_token` stores a {{docs/features/security/csrf-protection}} token in the
session. Resonance will validate that token after the user submits the form.

We do not return the `errors` variable yet, but we will reuse the same view
in the Login Validation responder later, so we might as well put those there
upfront. `errors` variable is a 
[`Ds\Map<string,string>`](https://www.php.net/manual/en/book.ds.php)
(a [`Map`](https://www.php.net/manual/en/class.ds-map.php) with both keys
and values as strings).

## Login Validation

We will need to validate two things:

1. Data submitted by the Login Form - to check if all the required fields are
    there. That is handled by the form {{docs/features/validation/index}}.
2. After validating the Login Form data - we need to check if the username and
    password pair is valid.

## Login Form Validation

First, let's define the `UsernamePasword` form model and validator.

Notice that the `UsernamePassword` is just a plain PHP object representing
the validated form data. We also used the `SensitiveParameter` attribute, so
the username and password won't appear in the application stack traces.

[`SensitiveParameter`](https://www.php.net/manual/en/class.sensitiveparameter.php)
is a PHP built-in attribute.

```php file:app/InputValidatedData/UsernamePassword.php
<?php

namespace App\InputValidatedData;

use Distantmagic\Resonance\InputValidatedData;
use SensitiveParameter;

readonly class UsernamePassword extends InputValidatedData
{
    public function __construct(
        #[SensitiveParameter]
        public string $username,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
```

We will use this input validator in the HTTP Responder. It uses
[Nette's Data Validator](https://doc.nette.org/en/schemahttps://www.php.net/manual/en/class.sensitiveparameter.php)
to validate the incoming data:

```php file:app/InputValidator/UsernamePasswordValidator.php
<?php

namespace App\InputValidator;

use App\InputValidatedData\UsernamePassword;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\SingletonCollection;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @extends InputValidator<UsernamePassword, object{
 *     csrf: string,
 *     username: string,
 *     password: string,
 * }>
 */
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class UsernamePasswordValidator extends InputValidator
{
    protected function castValidatedData(mixed $data): UsernamePassword
    {
        return new UsernamePassword($data->username, $data->password);
    }

    protected function makeSchema(): Schema
    {
        return Expect::structure([
            'csrf' => Expect::string()->required(),
            'username' => Expect::string()->min(1)->required(),
            'password' => Expect::string()->min(1)->required(),
        ]);
    }
}
```

## Authentication Credentials Validation

To validate the login and password, we are going to use the SQL query. Notice 
that it uses the form data as a constructor parameter. 

Let's start with a user role. We do not distinguish roles yet, so let's just
use a default `User` role for now:

```php file:app/Role.php
<?php

namespace App;

use Distantmagic\Resonance\UserRoleInterface;

enum Role: string implements UserRoleInterface
{
    case User = 'user';

    public function isAtLeast(UserRoleInterface $other): bool
    {
        return $this->toInt() >= $other->toInt();
    }

    public function toInt(): int
    {
        return match ($this) {
            Role::Admin => 2,
            Role::User => 1,
        };
    }
}
```

User model that implements framework's `UserInterface`:

```php file:app/DatabaseEntity/User.php
<?php

namespace App\DatabaseEntity;

use App\Role;
use Distantmagic\Resonance\DatabaseEntity;
use Distantmagic\Resonance\UserInterface;

final readonly class User extends DatabaseEntity implements UserInterface
{
    public function __construct(
        private int $id,
        private Role $role,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
```

This query returns null if it doesn't find any user with a given username and 
password:

```php file:app/DatabaseQuery/SelectUserByUsernamePassword.php
<?php

namespace App\DatabaseQuery;

use App\DatabaseEntity\User;
use App\InputValidatedData\UsernamePassword;
use App\Role;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\DatabaseQuery;
use Distantmagic\Resonance\UserInterface;

/**
 * @template-extends DatabaseQuery<null|UserInterface>
 */
final readonly class SelectUserByUsernamePassword extends DatabaseQuery
{
    public function __construct(
        DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private UsernamePassword $usernamePassword,
    ) {
        parent::__construct($databaseConnectionPoolRepository);
    }

    public function execute(): ?UserInterface
    {
        /**
         * @var null|array{
         *     id: int,
         *     password_hash: string,
         * }
         */
        $userData = $this
            ->getConnection()
            ->prepare(<<<'SQL'
                SELECT
                    users.id,
                    users.password_hash
                WHERE users.username = :username
                LIMIT 1
            SQL)
            ->bindValue('username', $this->usernamePassword->username)
            ->execute()
            ->first()
        ;

        if (!is_array($userData)) {
            return null;
        }

        if (!password_verify($this->usernamePassword->password, $userData['password_hash'])) {
            return null;
        }

        return new User($userData['id'], Role::User);
    }
}
```

## User Repository

{{docs/features/http/sessions}} use the `UserRepository` to check if the
user that is currently stored in the session is valid, so the `findById` method
has to be implemented:

```php file:app/DatabaseQuery/SelectUserById.php
<?php

namespace App\DatabaseQuery;

use App\DatabaseEntity\User;
use App\Role;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\DatabaseQuery;
use Distantmagic\Resonance\UserInterface;
use PDO;

/**
 * @template-extends DatabaseQuery<null|UserInterface>
 */
final readonly class SelectUserById extends DatabaseQuery
{
    public function __construct(
        DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private int $userId,
    ) {
        parent::__construct($databaseConnectionPoolRepository);
    }

    public function execute(): ?UserInterface
    {
        /**
         * @var null|array{
         *     id: int,
         *     role: string,
         * }
         */
        $userData = $this
            ->getConnection()
            ->prepare(<<<'SQL'
                SELECT users.id
                FROM users
                WHERE users.id = :user_id
                LIMIT 1
            SQL)
            ->bindValue('user_id', $this->userId, PDO::PARAM_INT)
            ->execute()
            ->first()
        ;

        if (!$userData || !isset($userData['id'])) {
            return null;
        }

        return new User($userData['id'], Role::User);
    }
}
```

Finally, the `UserRepository`:

```php file:app\UserRepository.php
<?php

declare(strict_types=1);

namespace App;

use App\DatabaseQuery\SelectUserById;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\UserInterface;
use Distantmagic\Resonance\UserRepositoryInterface;
use LogicException;

#[Singleton(provides: UserRepositoryInterface::class)]
readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
    ) {}

    public function findUserById(int|string $userId): ?UserInterface
    {
        if (is_string($userId)) {
            throw new LogicException('This user repository only supports int ids');
        }

        $selectUserById = new SelectUserById(
            $this->databaseConnectionPoolRepository,
            $userId,
        );

        return $selectUserById->execute();
    }
}
```

## Authenticating Users

Let's create another HTTP Responder that responds to the `POST /login` request, 
validates the incoming data and authenticates the user.

`#[ValidatesCSRFToken]` is used to check the CSRF token.

It uses the `#[ValidatedRequest]` attribute to run the 
`UsernamePasswordValidator` over the HTTP Request's POST data.

Only if form data is valid, the `handle` method is executed, and the database 
is queried to check if a user with given credentials exists.

```php file:app/HttpResponder/LoginValidation.php
<?php

namespace App\HttpResponder;

use App\DatabaseQuery\SelectUserByUsernamePassword;
use App\HttpRouteSymbol;
use App\InputValidatedData\UsernamePassword;
use App\InputValidator\UsernamePasswordValidator;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatedRequest;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\Attribute\ValidationErrors;
use Distantmagic\Resonance\Attribute\ValidationErrorsHandler;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\InternalRedirect;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SessionAuthentication;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Ds\Map;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/login',
    routeSymbol: HttpRouteSymbol::LoginValidation,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
#[ValidatesCSRFToken]
final readonly class LoginValidation extends HttpController
{
    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private HttpControllerDependencies $controllerDependencies,
        private SessionAuthentication $sessionAuthentication,
    ) {
        parent::__construct($controllerDependencies);
    }

    public function handle(
        Request $request,
        Response $response,
        #[ValidatedRequest(UsernamePasswordValidator::class)]
        UsernamePassword $usernamePassword,
    ): HttpInterceptableInterface {
        $selectUser = new SelectUserByUsernamePassword(
            $this->databaseConnectionPoolRepository,
            $usernamePassword,
        );

        $user = $selectUser->execute();

        if (!$user) {
            return $this->handleValidationErrors($response, new Map([
                'username' => 'Invalid credentials',
            ]));
        }

        $this->sessionAuthentication->setAuthenticatedUser($request, $response, $user);

        return new InternalRedirect(HttpRouteSymbol::Homepage);
    }

    /**
     * @param Map<string,string> $errors
     */
    #[ValidationErrorsHandler]
    public function handleValidationErrors(
        Response $response,
        #[ValidationErrors]
        Map $errors,
    ): HttpInterceptableInterface {
        $response->status(400);

        return new TwigTemplate('turbo/auth/login_form.twig', [
            'errors' => $errors,
        ]);
    }
}
```

After calling the `setAuthenticatedUser` method, the user will be stored in the
current session.

## Logging Out

We need two responders. The first one is to show the user the logout form:

```php file:app/HttpResponder/LogoutForm.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use App\SiteAction;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/logout',
    routeSymbol: HttpRouteSymbol::LogoutForm,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class LogoutForm extends HttpResponder
{
    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        return new TwigTemplate('auth/logout_form.twig');
    }
}
```
```twig file:app/views/logout_form.twig
<form
    action="{{ route(constant('App\\HttpRouteSymbol::LogoutValidation')) }}"
    method="post"
>
    <input
        name="csrf"
        type="hidden"
        value="{{ csrf_token(request, response) }}"
    >
    <p>Do you want to logout?</p>
    <button>Logout</button>
</form>
```

Then, we need a responder to handle the `POST /logout` request:

```php file:app/HttpResponder/LogoutValidation.php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use App\SiteAction;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\InternalRedirect;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SessionAuthentication;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/logout',
    routeSymbol: HttpRouteSymbol::LogoutValidation,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
#[ValidatesCSRFToken]
final readonly class LogoutValidation extends HttpResponder
{
    public function __construct(
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        $this->sessionAuthentication->clearAuthenticatedUser($request);

        $response->header('clear-site-data', '*');

        return new InternalRedirect(HttpRouteSymbol::Homepage);
    }
}
```

Sending an additional 
[`clear-site-data`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data)
header is optional. It prompts the browser to remove all the data associated
with the currently visited website. It's a good practice to use that header as
modern browsers will remove all the cookies, storage, caches, etc, that might 
still contain the current user-related data.

## Summary

That is just a basic session authentication. Try expanding upon this 
concept and adding different authentication methods to your application.
