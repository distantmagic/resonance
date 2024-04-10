---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
title: "'Hello, World' with Resonance"
description: >
    Let's walk step by step through the basic Resonance project.
---

## Preparations

To start with, you need to follow the 
{{docs/getting-started/installation-and-requirements}} steps. Make sure to
checkout the [distantmagic/resonance-project](https://github.com/distantmagic/resonance-project).

You should have the initial project already installed, with the recommended
PHP extensions.

## About the HTTP Server

If you were coding in PHP before, you might be used to using tools like 
[FPM](https://www.php.net/manual/en/install.fpm.php) combined with
[Nginx](https://www.nginx.com/) or [Apache](https://httpd.apache.org/).

Resonance does not 
*require* an extra server like Nginx or such because it has one built-in - 
provided by [Swoole](https://www.swoole.com/), so the only software you need is 
PHP itself (with Swoole extension).

### CGI Context

Of course, there are many optimizations available, like 
[OPCache](https://www.php.net/manual/en/book.opcache.php), FPM worker pools and
such, but the general rule remains more or less the same: when the client 
issues the HTTP Request, it starts the PHP script, waits for the PHP script
to produce the response, then terminates it.

In simplified terms, when using PHP FPM with web servers, your entire script
is parsed, started, and stopped during each request, so there is no 
long-running process.

### Swoole

Running a web server with Swoole is similar to how it's done in other languages
like, for example, in [Node.js](https://nodejs.org/en) or 
[Go](https://gobyexample.com/http-server). The PHP script is started, and 
bootstrapped once, then the same script handles multiple requests 
asynchronously.

## Starting the HTTP Server

### Create the Configuration File

You can copy `config.ini.example` to `config.ini`. That's the only 
configuration file that Resonance uses. You can leave the default values, but
feel free to modify them. 

You can change the port on which the HTTP server should be listening.

### Start the HTTP Server

To start the server, you need to run this command in the terminal:

```shell
$ php ./bin/resonance.php serve
```

Then you should see the message:

```shell
HTTP server is listening at http://127.0.0.1:9501
```

If you open that URL in the web browser you should see:

```text
Hello, world!
```

## How Was the Request Handled by The Framework?

Requests are handled by {{docs/features/http/responders}} (or 
{{docs/features/http/controllers}} - which are also Responders, but with more
features and a bit more overhead).

In the example project, there is only one HTTP Responder defined. We will go 
over this responder step by step:

```php
<?php

declare(strict_types=1);

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::Homepage,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Homepage extends HttpResponder
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface
    {
        return new TwigTemplate('homepage.twig');
    }
}
```

### Routing

When you entered the `http://127.0.0.1:9501/` URL in the browser, that request
was forwarded directly to the HTTP Server you launched. Then, Resonance
matched the URL with the Responder using its {{docs/features/http/routing}}
component.

Routes are defined with the `#[RespondsToHttp]` attribute. They can only be
attached to the HTTP Responders. Since the URL's path is `"/"`, which matched
the Responder's `pattern: '/'`, it was selected to handle the request.

If there were more responders, Resonance would go over each of them and find
the one that matches the URL. If it found none, then it would return the
`404` response.

### Route Symbols

`routeSymbol` is the name of the route. It is used to create internal links 
(for example, if you have a `BlogBost` responder, you might want to link to
that responder in your views - you can use Route Symbols to do that).

You can think of them as route names, just with some additional consistency 
validation since the route symbol is an enum:

```php
<?php

declare(strict_types=1);

namespace App;

use Distantmagic\Resonance\CastableEnumTrait;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\NameableEnumTrait;

enum HttpRouteSymbol implements HttpRouteSymbolInterface
{
    use CastableEnumTrait;
    use NameableEnumTrait;

    case Homepage;
}
```

This prevents typos and allows some extra runtime checks during the server
bootstrap phase to make sure there are no incorrect links in your PHP code.

### Returning the Template

Each of the {{docs/features/http/responders}} handles only one route. It's 
`respond` method can do one of the three things:

1. It might terminate the request processing by returning `null`
2. It might forward the request to another responder for further processing
3. It might return the arbitrary object that must be intercepted later

The example Responder used the 3rd option. It returned the `TwigTemplate` 
object, which is a Resonance's built-in object, to be handled with 
{{docs/features/http/interceptors}} later

`TwigTemplate` by itself is more or less just a plain PHP object:

```php
<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Html)]
final readonly class TwigTemplate implements HttpInterceptableInterface
{
    public function __construct(
        private string $templatePath,
        private array $templateData = [],
    ) {}

    public function getTemplateData(ServerRequestInterface $request, ResponseInterface $response): array
    {
        return $this->templateData + [
            'request' => $request,
            'response' => $response,
        ];
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }
}
```

It's completely passive and does not produce an HTTP response by itself. 
Instead, it's "intercepted" by the Twig template renderer and converted into a 
valid HTTP template response. Intercepting objects is a concept specific to the
Resonance framework.

## Summary

In this tutorial, we went step by step through the basic Resonance project,
learned how to start a web server and how HTTP requests are handled.

If you are ready to learn about more advanced topics, check out other 
tutorials.
