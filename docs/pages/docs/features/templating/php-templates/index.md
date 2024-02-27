---
collections: 
    - name: documents
      next: docs/features/templating/twig/index
layout: dm:document
parent: docs/features/templating/index
title: PHP Templates
description: >
    Define your own templates using vanilla PHP to maximize performance.
---

# PHP Templates

If you need to squeeze out every inch of performance from your 
application, it's hard to beat the vanilla PHP. You can define your templates
by using the vanilla PHP classes.

:::note
You should familiarize yourself with {{docs/features/http/responders}} and 
{{docs/features/dependency-injection/index}} before using PHP Templates.
:::

:::caution
You should be sure that you need this kind of performance 
before deciding to use pure PHP instead of 
{{docs/features/templating/twig/index}} or any other templating engine.
:::

# Usage

## Writing PHP Templates

There is no "best" way to handle pure PHP templates, but one way to handle them
is to use templates as {{docs/features/http/responders}} that you can forward 
your requests into. That makes them reusable.

Template file:

```php
<?php

use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class MyBlogPostTemplate extends HttpResponder
{
    public function __construct(
        private string $title, 
        private string $content,
    )
    {
    }

    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->with($this->createStream(<<<HTML
        <html>
            <head></head>
            <body>
                <h1>{$this->title}</h1>
                <p>{$this->content}</p>
            </body>
        </html>
        HTML));
    }
}
```

Responder:

```php
<?php

use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class MyResponder implements HttpResponderInterface
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface
    {
        return new MyTemplate('title', 'content');
    }
}
```

## Extending Templates, Creating Layouts

Let's say you need to have a common layout that you need to share between 
multiple pages. One way to do so is to use OOP inheritance:

:::note
Please keep in mind that compiled and cached Twig templates are almost no 
different than this approach, as Twig produces PHP classes that emit strings. 

Using vanilla PHP would still be more performant, but make sure you *need* this
kind of performance.
:::

```php
<?php

use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class MyBaseTemplate extends HttpResponder
{
    abstract protected function renderBodyContent(Request $request, Response $response): string;

    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withBody($this->createStream(<<<HTML
        <html>
            <head></head>
            <body>
                <nav>my navigation menu</nav>
                {$this->renderBodyContent($request, $response)}
            </body>
        </html>
        HTML));
    }
}
```

Then in other pages:

```php
<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class MyBlogPost extends MyBaseTemplate
{
    protected function renderBodyContent(ServerRequestInterface $request, ResponseInterface $response): string
    {
        return 'Hello!';
    }
}
```

Finally, you should use `new MyBlogPost()` in your 
{{docs/features/http/responders}}.
