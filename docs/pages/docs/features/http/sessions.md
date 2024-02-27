---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Sessions
description: >
    Manage HTTP Sessions in the anynchronous PHP environment.
---

# HTTP Sessions

The default [PHP Sessions](https://www.php.net/manual/en/intro.session.php)
do not work seamlessly with Swoole, as they are designed for PHP scripts 
handling one HTTP request at a time. This limitation necessitated the 
development of a custom solution to enable sessions in Swoole-based 
applications.

## Understanding Sessions

You can use sessions to persist arbitrary data on the server tied to an HTTP 
client that supports 
[Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies).

This data is *not* necessarily tied to a specific user. It's only related to 
the specific client. 

On the client side, only the session cookie is stored (with the globally unique 
identifier of the session), and the rest of the data is stored on the server. 
You can use this to store some volatile data that should not be accessible 
directly by the HTTP browser.

For example, you can use sessions for storing the contents of an e-commerce 
shopping basket for the current client device.

# Usage

Session Manager relies on [Redis](https://redis.io/) for storage and 
[igbinary](https://www.php.net/manual/en/intro.igbinary.php) for data
serialization.

Redis connection is obtained from the connection pool only
when the session is requested. Sessions do not start automatically with
HTTP Requests.

## Configuration

You need to specify the session cookie parameters and the desired Redis 
connection pool that the session manager is going to use:

```ini file:config.ini
; ...

[session]
cookie_name = dmsession
; lifespan in seconds
cookie_lifespan = 86400
redis_connection_pool = default

; ...
```

## Session Manager

`Resonance\SessionManager` is a singleton, and you can use it with
{{docs/features/dependency-injection/index}}.

The typical usage follows the 'start -> modify -> persist' pattern:

```php
<?php

/**
 * Start a new session or reuse the session that has already been started for
 * the current request.
 * 
 * `$sessionManager->start()` also sets the session cookie in the response.
 * 
 * @var Resonance\Session $session
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Swoole\Http\Response $response
 */
$session = $sessionManager->start($request);

/**
 * Anything that is serializable by igbinary can be used as a value.
 */
$session->data->put('my-data-key', 'my-value');

/**
 * Only now the session data is persisted and stored in the Redis database.
 * Just setting the data in `$session->data` does not persist that data.
 */
$sessionManager->persistSession($request);
```

If you are using Session Manager from inside the HTTP
{{docs/features/http/responders}} or {{docs/features/http/controllers}}, you
do not have to persist the session data manually. Resonance does that 
after Swoole sends the HTTP response back to the client.
