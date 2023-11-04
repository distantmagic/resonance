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

use Distantmagic\Resonance\HttpResponderInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class MyBlogPostTemplate implements HttpResponderInterface
{
    public function __construct(
        private string $title, 
        private string $content,
    )
    {
    }

    public function respond(Request $request, Response $response): null
    {
        $response->end(<<<HTML
        <html>
            <head></head>
            <body>
                <h1>{$this->title}</h1>
                <p>{$this->content}</p>
            </body>
        </html>
        HTML);

        return null;
    }
}
```

Responder:

```php
<?php

use Distantmagic\Resonance\HttpResponderInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class MyResponder implements HttpResponderInterface
{
    public function respond(Request $request, Response $response): HttpResponderInterface
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

use Distantmagic\Resonance\HttpResponderInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class MyBaseTemplate implements HttpResponderInterface
{
    abstract protected function renderBodyContent(Request $request, Response $response): string;

    public function respond(Request $request, Response $response): null
    {
        $response->end(<<<HTML
        <html>
            <head></head>
            <body>
                <nav>my navigation menu</nav>
                {$this->renderBodyContent($request, $response)}
            </body>
        </html>
        HTML);

        return null;
    }
}
```

Then in other pages:

```php
<?php

use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class MyBlogPost extends MyBaseTemplate
{
    protected function renderBodyContent(Request $request, Response $response): string
    {
        return 'Hello!';
    }
}
```

Finally, you should use `new MyBlogPost()` in your 
{{docs/features/http/responders}}.
