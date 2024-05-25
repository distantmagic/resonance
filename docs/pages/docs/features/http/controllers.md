---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Controllers
description: >
    Handle CRUD operations with HTTP Controllers.
---

# HTTP Controllers

Controllers are an extension of {{docs/features/http/responders}} tailored for 
CRUD (Create-Read-Update-Delete) operations. These operations often involve 
manipulating database entities, such as creating and editing blog posts 
or deleting records. 

Controllers aim to automate these repetitive tasks as much as possible.

# Usage

## Writing Controllers

Unlike {{docs/features/http/responders}}, Controllers do not use the `respond` 
method. Instead, they rely on the `createResponse` method to manage incoming 
requests. 

The `respond` method is used internally for handling tasks like parameter 
binding, so you should not override it.

## Route Parameters

### Binding Route Parameters

Controllers handle parameter binding (associating database entities with 
parameters) and authorization using the exact mechanism. For instance, if you 
want to display a blog post, you can use the `RouteParameter` attribute. In 
this attribute, you specify which route parameter the framework should use to 
fetch the entity. Additionally, you indicate your intent with the entity using 
`CrudAction` (by default, set to `CrudAction::Read`). The firewall uses the 
intent value to check if the currently authenticated user can manage the 
resource.

:::note
Using the `RouteParameter` might require to create a Crud Gate. See more at
the {{docs/features/security/authorization/index}} page.
:::

Remember that the framework resolves parameters assigned to the 
`createResponse` method on runtime during the request lifecycle.

The above is contrary to the constructor arguments, which the framework 
resolves during the application bootstrap phase thanks to the 
{{docs/features/dependency-injection/index}}.

:::note
You can learn more about CRUD actions on the
{{docs/features/security/authorization/index}} page.
:::

```php
<?php

namespace App\HttpResponder;

use App\DatabaseEntity\BlogPost;
use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\RouteParameter;
use Distantmagic\Resonance\CrudAction;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/blog/{blog_post_slug}',
    routeSymbol: HttpRouteSymbol::BlogPostShow,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class BlogPostShow extends HttpController
{
    public function createResponse(
        #[RouteParameter(
            from: 'blog_post_slug', 
            intent: CrudAction::Read,
        )]
        BlogPost $blogPost,
        ResponseInterface $response,
    ): HttpResponderInterface {
        // ...
    }
}
```

### Providing Doctrine Entities as Parameters

You can learn more at Doctrine {{docs/features/database/doctrine/entities}} 
page.

### Providing Route Parameters

To inform the framework where to retrieve route parameter values, you must 
provide an `HttpRouteParameterBinder` for each parameter type. The 
`$routeParameterValue` corresponds to the parameter specified in the `from:` 
field of the `RouteParameter` attribute. 

If `null` is returned, the `Controller` will respond with a 404 page.

For example:

```php
<?php

namespace App\HttpRouteParameterBinder;

use App\DatabaseEntity\BlogPost;
use Distantmagic\Resonance\Attribute\ProvidesRouteParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpRouteParameterBinderInterface;
use Distantmagic\Resonance\SingletonCollection;

#[ProvidesRouteParameter(BlogPost::class)]
#[Singleton(collection: SingletonCollection::HttpParameterBinder)]
final readonly class BlogPostBinder implements HttpRouteParameterBinderInterface
{
    public function provide(string $routeParameterValue): ?BlogPost
    {
        return /* find blog post by using the route parameter value */;
    }
}
```

### Providing the Authenticated User (Session)

If you need to fetch the authenticated user in your controller, you can add 
a parameter with the `#[SessionAuthenticated]` attribute to the 
`createResponse` method.

The controller fetches an authenticated user through 
{{docs/features/http/sessions}}.

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
use Distantmagic\Resonance\CrudAction;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\UserInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/test',
    routeSymbol: HttpRouteSymbol::Test,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class MyController extends HttpController
{
    public function createResponse(
        // If you make this parameter required, then the framework will
        // return 403 page when user is unauthenticated.
        #[SessionAuthenticated]
        ?UserInterface $user,
    ): HttpResponderInterface {
        // ...
    }
}
```

## Custom Route Parameter Resolvers

By default, the Resonance framework provides support for some parameter 
attributes (like `RouteParameter`), but you can add your own. 

```php
<?php

namespace App\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpControllerParameterResolver<MyAttribute>
 */
#[ResolvesHttpControllerParameter(MyAttribute::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class RouteParameterResolver extends HttpControllerParameterResolver
{
    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerParameter $httpControllerParameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::NotFound,
        );
    }
}
```

## Extending Controllers

If you want to extend a controller, call its constructor. All the
controller parameters are wrapped inside a single class: 
`Distantmagic\Resonance\HttpControllerDependencies`. 
The above is done for convenience.

```php
<?php

namespace App;

use Distantmagic\Resonance\HttpController;
use Distantmagic\Resonance\HttpControllerDependencies;

readonly class MyHttpController extends HttpController
{
    public function __construct(
        HttpControllerDependencies $httpControllerDependencies
        private MyFooDependency $myFooDependency,
    ) {
        parent::__construct($httpControllerDependencies);
    }

    public function createResponse()
    {
        // ...
    }
}
```

Of course, you don't have to call the parent constructor if you are
not adding your properties to your controller class.
